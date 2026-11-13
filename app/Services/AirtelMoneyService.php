<?php

namespace App\Services;

use App\Models\Inscription;
use App\Models\Paiement;
use App\Models\TransactionAirtel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Passerelle de paiement Airtel Money (Airtel Africa Open API).
 *
 * Fonctionnement (« push USSD ») :
 *  1. demanderPaiement() : la plateforme demande à Airtel de débiter le numéro du client ;
 *  2. Airtel affiche une demande de confirmation sur le téléphone du client, qui tape son code PIN ;
 *  3. verifier() : la plateforme consulte le statut de l'opération auprès d'Airtel
 *     (page d'attente toutes les 5 s, et à chaque notification de rappel d'Airtel) ;
 *  4. statut TS (réussi) -> le Paiement est créé (complet ou partiel selon la somme payée).
 *
 * Le Paiement n'est JAMAIS créé sur la seule foi d'une notification reçue : on redemande
 * toujours le statut à Airtel avec nos identifiants.
 */
class AirtelMoneyService
{
    // Codes de statut d'une opération Airtel
    private const REUSSI = 'TS';            // Transaction Success
    private const ECHECS = ['TF', 'TE'];    // Transaction Failed / Expired
    // Les autres (TIP = en cours, TA = ambigu…) : on attend et on redemande plus tard

    /**
     * Configuration complète ? (identifiants présents dans le .env)
     */
    public function estConfigure(): bool
    {
        return (bool) config('services.airtel.client_id') && (bool) config('services.airtel.client_secret');
    }

    /**
     * Numéro saisi -> numéro d'abonné sur 8 chiffres, sans indicatif ni 0 de tête (ex. 74123456).
     *
     * Au Gabon (numérotation à 9 chiffres depuis avril 2024) un numéro Airtel s'écrit
     * « 074 12 34 56 » ou « 077 12 34 56 » en local, et « +241 74 12 34 56 » à l'international
     * (le 0 disparaît après l'indicatif). Toutes ces écritures sont acceptées.
     * Retourne null si ce n'est pas un numéro Airtel Gabon (préfixe 7 après le 0).
     */
    public function normaliserNumero(?string $saisie): ?string
    {
        $chiffres = preg_replace('/\D+/', '', (string) $saisie);
        $indicatif = (string) config('services.airtel.indicatif');

        // « 00 241 … » : préfixe international sans le « + »
        if (str_starts_with($chiffres, '00')) {
            $chiffres = substr($chiffres, 2);
        }

        // « +241 74 12 34 56 » ou « 241 74123456 » : on retire l'indicatif pays
        if ($indicatif !== '' && str_starts_with($chiffres, $indicatif) && strlen($chiffres) === strlen($indicatif) + 8) {
            $chiffres = substr($chiffres, strlen($indicatif));
        }

        // « 074 12 34 56 » (format local à 9 chiffres) : on retire le 0 de tête
        if (strlen($chiffres) === 9 && str_starts_with($chiffres, '0')) {
            $chiffres = substr($chiffres, 1);
        }

        // Numéro Airtel : 8 chiffres commençant par 7 (074…, 077… en local)
        if (! preg_match('/^7\d{7}$/', $chiffres)) {
            return null;
        }

        return $chiffres;
    }

    /**
     * Étape 1 : demande de paiement à Airtel (le client reçoit la demande sur son téléphone).
     * Crée la TransactionAirtel « en attente » ; lève une RuntimeException (message lisible)
     * si Airtel refuse la demande.
     */
    public function demanderPaiement(Inscription $inscription, float $montant, string $numero): TransactionAirtel
    {
        // Identifiant unique de l'opération, envoyé à Airtel et réutilisé pour consulter son statut
        $reference = 'NV' . $inscription->id . Str::upper(Str::random(10));

        $transaction = TransactionAirtel::create([
            'reference' => $reference,
            'inscription_id' => $inscription->id,
            'montant' => $montant,
            'msisdn' => $numero,
            'statut' => TransactionAirtel::EN_ATTENTE,
        ]);

        // Airtel attend le numéro sans indicatif (74123456). Si votre compte marchand le veut
        // avec le 0 local (074123456), mettez AIRTEL_MSISDN_AVEC_ZERO=true dans le .env.
        $msisdn = config('services.airtel.msisdn_avec_zero') ? '0' . $numero : $numero;
        $pays = config('services.airtel.pays');
        $devise = config('services.airtel.devise');

        try {
            $reponse = $this->client()->post(config('services.airtel.chemin_paiement'), [
                'reference' => 'Formation NeoVision',   // libellé affiché au client
                'subscriber' => [
                    'country' => $pays,
                    'currency' => $devise,
                    'msisdn' => $msisdn,
                ],
                'transaction' => [
                    // Le franc CFA n'a pas de centimes : montant entier
                    'amount' => (int) round($montant),
                    'country' => $pays,
                    'currency' => $devise,
                    'id' => $reference,
                ],
            ]);
        } catch (Throwable $e) {
            report($e);
            $this->marquerEchouee($transaction, null, 'Airtel Money injoignable.');
            throw new RuntimeException('Airtel Money est momentanément inaccessible. Veuillez réessayer.');
        }

        // Demande refusée (numéro non Airtel, compte marchand mal configuré…)
        if ($reponse->failed() || $reponse->json('status.success') !== true) {
            report(new RuntimeException('Demande Airtel Money refusée : ' . $reponse->body()));
            $this->marquerEchouee($transaction, null, (string) $reponse->json('status.message'));
            throw new RuntimeException('Airtel Money n\'a pas accepté la demande de paiement. Vérifiez votre numéro Airtel Money.');
        }

        return $transaction;
    }

    /**
     * Étape 3 : consulte le statut chez Airtel et met la transaction à jour.
     * Statut réussi -> création du Paiement (une seule fois, même si appelé plusieurs fois).
     * Retourne la transaction à jour.
     */
    public function verifier(TransactionAirtel $transaction): TransactionAirtel
    {
        if (! $transaction->estEnAttente()) {
            return $transaction;   // déjà terminée : rien à redemander
        }

        try {
            $reponse = $this->client()->get(config('services.airtel.chemin_statut') . urlencode($transaction->reference));
        } catch (Throwable $e) {
            report($e);
            return $transaction;   // Airtel injoignable : on réessaiera au prochain passage
        }

        if ($reponse->failed()) {
            return $transaction;
        }

        $code = (string) $reponse->json('data.transaction.status');
        $airtelId = $reponse->json('data.transaction.airtel_money_id');
        $message = $reponse->json('data.transaction.message');

        if ($code === self::REUSSI) {
            return $this->finaliser($transaction, $code, $airtelId, $message);
        }

        if (in_array($code, self::ECHECS, true)) {
            $this->marquerEchouee($transaction, $code, $message ?: 'Paiement refusé ou annulé.');
        }

        return $transaction->fresh();
    }

    /**
     * Paiement confirmé par Airtel : création du Paiement (complet ou partiel selon la somme),
     * e-mail de confirmation et notification. Protégé contre le double traitement
     * (page d'attente et notification d'Airtel peuvent arriver en même temps).
     */
    private function finaliser(TransactionAirtel $transaction, string $code, ?string $airtelId, ?string $message): TransactionAirtel
    {
        $paiementService = app(PaiementService::class);

        $paiement = DB::transaction(function () use ($transaction, $code, $airtelId, $message, $paiementService) {
            $t = TransactionAirtel::whereKey($transaction->id)->lockForUpdate()->first();

            // Une autre requête l'a déjà traitée
            if (! $t || ! $t->estEnAttente()) {
                return null;
            }

            $t->update([
                'statut' => TransactionAirtel::REUSSIE,
                'code_statut' => $code,
                'airtel_money_id' => $airtelId,
                'message' => $message,
            ]);

            // montantVerse = somme réellement payée : inférieure au prix -> « paiement partiel »
            return $paiementService->enregistrer(
                $t->inscription,
                'airtel_money',
                $t->reference,
                $airtelId,
                $code,
                $message,
                (float) $t->montant
            );
        });

        // E-mail hors transaction : un souci d'envoi n'annule pas le paiement
        if ($paiement instanceof Paiement) {
            $paiementService->envoyerEmail($paiement);
        }

        return $transaction->fresh();
    }

    private function marquerEchouee(TransactionAirtel $transaction, ?string $code, ?string $message): void
    {
        $transaction->update([
            'statut' => TransactionAirtel::ECHOUEE,
            'code_statut' => $code,
            'message' => $message,
        ]);
    }

    /**
     * Client HTTP prêt à l'emploi : adresse de l'API, jeton d'accès, pays et devise.
     */
    private function client()
    {
        return Http::baseUrl(rtrim((string) config('services.airtel.base_url'), '/'))
            ->acceptJson()
            ->asJson()
            ->timeout(30)
            ->withToken($this->jeton())
            ->withHeaders([
                'X-Country' => config('services.airtel.pays'),
                'X-Currency' => config('services.airtel.devise'),
            ]);
    }

    /**
     * Jeton d'accès OAuth2 (valable environ 1 h), gardé en cache pour ne pas
     * en redemander un à chaque appel.
     */
    private function jeton(): string
    {
        $cle = 'airtel_money_jeton_' . md5((string) config('services.airtel.client_id'));

        if ($jeton = Cache::get($cle)) {
            return $jeton;
        }

        $reponse = Http::baseUrl(rtrim((string) config('services.airtel.base_url'), '/'))
            ->acceptJson()
            ->asJson()
            ->timeout(30)
            ->post('/auth/oauth2/token', [
                'client_id' => config('services.airtel.client_id'),
                'client_secret' => config('services.airtel.client_secret'),
                'grant_type' => 'client_credentials',
            ]);

        $jeton = $reponse->json('access_token');

        if ($reponse->failed() || ! is_string($jeton) || $jeton === '') {
            throw new RuntimeException('Authentification Airtel Money impossible : ' . $reponse->body());
        }

        // On le garde un peu moins longtemps que sa durée de vie annoncée
        $duree = max(60, (int) $reponse->json('expires_in', 3600) - 120);
        Cache::put($cle, $jeton, $duree);

        return $jeton;
    }
}
