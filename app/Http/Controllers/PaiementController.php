<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\Inscription;
use App\Models\Notification;
use App\Models\Paiement;
use App\Notifications\InscriptionEnregistree;
use App\Notifications\PaiementConfirme;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PaiementController extends Controller
{
    /**
     * Affiche la page de choix du mode de paiement.
     *
     * IMPORTANT :
     * À ce stade, aucune inscription n'est encore créée.
     * L'utilisateur peut donc cliquer sur "Annuler" sans laisser
     * d'inscription, de paiement ou de notification.
     */
    public function choix(Classe $classe)
    {
        $user = Auth::user();

        // Vérifie si l'utilisateur est déjà inscrit à cette classe.
        if ($classe->inscriptions()
            ->where('user_id', $user->id)
            ->exists()) {

            return redirect()->route('cours.mes')
                ->with('error', 'Vous êtes déjà inscrit à cette classe.');
        }

        // Vérifie la capacité actuelle de la classe.
        if ($classe->inscriptions()->count() >= $classe->capacite_max) {
            return redirect()->route('cours.show', $classe->cours)
                ->with('error', 'Cette classe est déjà complète.');
        }

        $classe->load('cours');

        $montant = $this->montantDePourUtilisateur(
            $classe->cours,
            $user
        );

        return view('paiements.choix', [
            'classe' => $classe,
            'montant' => $montant,
        ]);
    }

    /**
     * Paiement en espèces.
     *
     * L'inscription est créée uniquement lorsque l'utilisateur
     * choisit réellement le paiement en espèces.
     */
    public function especes(Classe $classe)
    {
        $user = Auth::user();

        $inscription = DB::transaction(function () use ($classe, $user) {

            // Verrouillage de la classe pour éviter deux inscriptions
            // simultanées qui dépasseraient la capacité.
            $classeVerrouillee = Classe::where('id', $classe->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Vérifie si l'utilisateur est déjà inscrit.
            if ($classeVerrouillee->inscriptions()
                ->where('user_id', $user->id)
                ->exists()) {

                return null;
            }

            // Vérifie la capacité.
            if ($classeVerrouillee->inscriptions()->count()
                >= $classeVerrouillee->capacite_max) {

                return false;
            }

            // Création de l'inscription.
            $inscription = Inscription::create([
                'user_id' => $user->id,
                'classe_id' => $classeVerrouillee->id,
                'date_inscription' => now(),
            ]);

            // Notification interne d'inscription.
            Notification::create([
                'user_id' => $user->id,
                'message' => 'Inscription enregistrée : « '
                    . $classeVerrouillee->cours->titre
                    . ' », début le '
                    . $classeVerrouillee->date_debut->format('d/m/Y')
                    . '. Paiement en attente de confirmation par l’administration.',
            ]);

            return $inscription;
        });

        if ($inscription === null) {
            return back()->with(
                'error',
                'Vous êtes déjà inscrit à cette classe.'
            );
        }

        if ($inscription === false) {
            return back()->with(
                'error',
                'Cette classe est déjà complète.'
            );
        }

        // Email d'inscription envoyé seulement après le choix
        // du paiement en espèces.
        try {
            $user->notify(
                new InscriptionEnregistree($classe)
            );
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()
            ->route('paiements.statut_paiement')
            ->with(
                'success',
                'Votre inscription est enregistrée. Vous pouvez maintenant régler les frais auprès de l’administration.'
            );
    }

    /**
     * Page de paiement en ligne d'une inscription.
     */
    public function create(Inscription $inscription)
    {
        abort_unless(
            $inscription->user_id === Auth::id(),
            403
        );

        if ($inscription->paiement()->exists()) {
            return redirect()
                ->route('cours.mes')
                ->with(
                    'error',
                    'Cette inscription est déjà payée.'
                );
        }

        $inscription->load('classe.cours');

        return view('paiements.create', [
            'inscription' => $inscription,
            'montant' => $this->montantDe($inscription),
        ]);
    }

    /**
     * Demande à SingPay de créer un lien de paiement externe.
     *
     * L'inscription est créée au moment où l'utilisateur choisit
     * réellement SingPay.
     *
     * Aucun Paiement local n'est créé avant confirmation de SingPay.
     */
    public function payer(Classe $classe)
    {
        $user = Auth::user();

        /*
         * Création de l'inscription dans une transaction.
         */
        $inscription = DB::transaction(function () use ($classe, $user) {

            $classeVerrouillee = Classe::where('id', $classe->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Si l'utilisateur est déjà inscrit, on réutilise
            // son inscription.
            $existante = $classeVerrouillee->inscriptions()
                ->where('user_id', $user->id)
                ->first();

            if ($existante) {
                return $existante;
            }

            // Vérification de la capacité.
            if ($classeVerrouillee->inscriptions()->count()
                >= $classeVerrouillee->capacite_max) {

                return false;
            }

            return Inscription::create([
                'user_id' => $user->id,
                'classe_id' => $classeVerrouillee->id,
                'date_inscription' => now(),
            ]);
        });

        if ($inscription === false) {
            return back()->with(
                'error',
                'Cette classe est déjà complète.'
            );
        }

        $inscription->load('classe.cours');

        /*
         * Maintenant que l'inscription existe réellement,
         * on enregistre la notification et on envoie l'email.
         */
        $this->notifierNouvelleInscription($inscription);

        $clientId = config('services.singpay.client_id');
        $clientSecret = config('services.singpay.client_secret');
        $walletId = config('services.singpay.wallet_id');
        $publicUrl = rtrim(
            (string) config('services.singpay.public_url'),
            '/'
        );
        $logoUrl = config('services.singpay.logo_url');

        if (
            ! $clientId ||
            ! $clientSecret ||
            ! $walletId ||
            ! $publicUrl ||
            ! $logoUrl
        ) {
            return back()->with(
                'error',
                'La configuration SingPay est incomplète.'
            );
        }

        $montant = $this->montantDe($inscription);

        $reference = 'NEO-PART-INS-'
            . $inscription->id
            . '-'
            . Str::upper(Str::random(8));

        try {
            $reponse = Http::withHeaders([
                'Accept' => '*/*',
                'x-client-id' => $clientId,
                'x-client-secret' => $clientSecret,
                'x-wallet' => $walletId,
            ])->post(
                config('services.singpay.base_url') . '/ext',
                [
                    'portefeuille' => $walletId,
                    'reference' => $reference,
                    'redirect_success' => $publicUrl . '/paiement/success',
                    'redirect_error' => $publicUrl . '/paiement/error',
                    'amount' => $montant,
                    'disbursement' => '',
                    'logoURL' => $logoUrl,
                    'isTransfer' => false,
                ]
            );
        } catch (\Throwable $exception) {
            report($exception);

            return back()->with(
                'error',
                'SingPay est momentanément inaccessible.'
            );
        }

        $lien = $reponse->json('link');

        if (
            $reponse->failed() ||
            ! is_string($lien) ||
            $lien === ''
        ) {
            report(
                new \RuntimeException(
                    'Réponse SingPay invalide : '
                    . $reponse->body()
                )
            );

            return back()->with(
                'error',
                'SingPay n’a pas accepté la demande de paiement.'
            );
        }

        /*
         * On conserve uniquement les informations nécessaires
         * pour vérifier le paiement au retour de SingPay.
         *
         * Aucun Paiement n'est encore créé dans la base.
         */
        session()->put('singpay_pending_payment', [
            'inscription_id' => $inscription->id,
            'reference' => $reference,
            'amount' => $montant,
        ]);

        return redirect()->away($lien);
    }

    /**
     * Retour SingPay après un paiement supposé réussi.
     *
     * On vérifie réellement la transaction auprès de SingPay
     * avant de créer le paiement local.
     */
    public function success()
    {
        $attente = session('singpay_pending_payment');

        if (
            ! is_array($attente) ||
            empty($attente['reference']) ||
            empty($attente['inscription_id'])
        ) {
            return redirect()
                ->route('cours.mes')
                ->with(
                    'error',
                    'Aucun paiement SingPay en attente n’a été trouvé.'
                );
        }

        $inscription = Inscription::with('user')
            ->findOrFail($attente['inscription_id']);

        abort_unless(
            $inscription->user_id === Auth::id(),
            403
        );

        try {
            $reponse = Http::withHeaders([
                'Accept' => '*/*',
                'x-client-id' => config('services.singpay.client_id'),
                'x-client-secret' => config('services.singpay.client_secret'),
                'x-wallet' => config('services.singpay.wallet_id'),
            ])->get(
                config('services.singpay.base_url')
                . '/transaction/api/search/by-reference/'
                . urlencode($attente['reference'])
            );
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()
                ->route('cours.mes')
                ->with(
                    'error',
                    'La vérification SingPay est momentanément inaccessible.'
                );
        }

        $transaction = $reponse->json('transaction')
            ?: $reponse->json();

        $statut = $transaction['status'] ?? null;
        $resultat = $transaction['result'] ?? null;
        $montant = $transaction['amount'] ?? null;

        /*
         * Toutes les conditions doivent être vraies :
         *
         * - réponse SingPay valide
         * - même référence
         * - transaction terminée
         * - résultat Success
         * - montant identique
         */
        if (
            $reponse->failed() ||
            ($transaction['reference'] ?? null)
                !== $attente['reference'] ||
            $statut !== 'Terminate' ||
            $resultat !== 'Success' ||
            (float) $montant !== (float) $attente['amount']
        ) {
            return redirect()
                ->route('cours.mes')
                ->with(
                    'error',
                    'Le paiement SingPay n’a pas pu être confirmé.'
                );
        }

        /*
         * Création du paiement local uniquement maintenant.
         */
        $paiement = $this->enregistrer(
            $inscription,
            'singpay',
            $attente['reference'],
            $transaction['id']
                ?? $transaction['_id']
                ?? null,
            $statut,
            $resultat
        );

        session()->forget('singpay_pending_payment');

        if (! $paiement) {
            return redirect()
                ->route('cours.mes')
                ->with(
                    'success',
                    'Cette inscription était déjà payée.'
                );
        }

        $this->envoyerEmail($paiement);

        return redirect()
            ->route('cours.mes')
            ->with(
                'success',
                'Paiement confirmé. Votre inscription est validée.'
            );
    }

    /**
     * Retour SingPay en cas d'abandon ou d'échec.
     *
     * Aucun paiement local n'est créé.
     *
     * L'inscription reste cependant existante car l'utilisateur
     * l'avait réellement créée en choisissant SingPay.
     */
    public function error()
    {
        session()->forget('singpay_pending_payment');

        return redirect()
            ->route('cours.mes')
            ->with(
                'error',
                'Le paiement SingPay a échoué ou a été annulé. Votre inscription reste enregistrée et peut être régularisée auprès de l’administration.'
            );
    }

    /**
     * Enregistre le paiement direct au comptoir.
     * Réservé à l'administrateur.
     */
    public function store(Inscription $inscription)
    {
        $paiement = $this->enregistrer(
            $inscription,
            'direct',
            null
        );

        if (! $paiement) {
            return back()->with(
                'error',
                'Cette inscription est déjà payée.'
            );
        }

        $this->envoyerEmail($paiement);

        return back()->with(
            'success',
            'Paiement enregistré : l’inscription est maintenant payée.'
        );
    }

    /**
     * Calcule le montant d'une inscription.
     *
     * Le prix est toujours récupéré depuis le cours,
     * jamais depuis un formulaire utilisateur.
     */
    private function montantDe(Inscription $inscription)
    {
        $inscription->loadMissing(
            'classe.cours',
            'user.role'
        );

        $cours = $inscription->classe->cours;

        return $inscription->user->role?->nom === 'entreprise'
            ? $cours->prix_entreprise
            : $cours->prix_particulier;
    }

    /**
     * Calcule le montant à partir d'un cours et d'un utilisateur.
     *
     * Utilisé avant que l'inscription existe.
     */
    private function montantDePourUtilisateur($cours, $user)
    {
        $user->loadMissing('role');

        return $user->role?->nom === 'entreprise'
            ? $cours->prix_entreprise
            : $cours->prix_particulier;
    }

    /**
     * Crée le paiement.
     *
     * Utilisé par :
     * - SingPay après confirmation ;
     * - paiement direct par l'administration.
     */
    private function enregistrer(
        Inscription $inscription,
        string $mode,
        ?string $reference,
        ?string $singpayTransactionId = null,
        ?string $singpayStatus = null,
        ?string $singpayResult = null
    ): ?Paiement {

        return DB::transaction(function () use (
            $inscription,
            $mode,
            $reference,
            $singpayTransactionId,
            $singpayStatus,
            $singpayResult
        ) {

            // Verrouillage de l'inscription.
            $insc = Inscription::with([
                'classe.cours',
                'user.role'
            ])
                ->lockForUpdate()
                ->findOrFail($inscription->id);

            // Empêche la création de deux paiements.
            if ($insc->paiement()->exists()) {
                return null;
            }

            $cours = $insc->classe->cours;

            $montant = $this->montantDe($insc);

            $paiement = Paiement::create([
                'montant' => $montant,
                'inscription_id' => $insc->id,
                'mode' => $mode,
                'reference' => $reference,
                'singpay_transaction_id' => $singpayTransactionId,
                'singpay_status' => $singpayStatus,
                'singpay_result' => $singpayResult,
            ]);

            $moyen = $mode === 'direct'
                ? 'au comptoir'
                : 'via ' . Paiement::MODES[$mode];

            // Notification de paiement confirmé.
            Notification::create([
                'user_id' => $insc->user_id,
                'message' => 'Paiement confirmé : « '
                    . $cours->titre
                    . ' » ('
                    . $insc->classe->nom
                    . '), '
                    . number_format(
                        $montant,
                        0,
                        ',',
                        ' '
                    )
                    . ' fcfa '
                    . $moyen
                    . '. Votre inscription est validée.',
            ]);

            return $paiement;
        });
    }

    /**
     * Notification + email d'une nouvelle inscription.
     */
    private function notifierNouvelleInscription(
        Inscription $inscription
    ): void {

        $inscription->loadMissing(
            'classe.cours',
            'user'
        );

        $user = $inscription->user;
        $classe = $inscription->classe;

        Notification::create([
            'user_id' => $user->id,
            'message' => 'Inscription enregistrée : « '
                . $classe->cours->titre
                . ' », début le '
                . $classe->date_debut->format('d/m/Y')
                . '. Paiement en attente de confirmation.',
        ]);

        try {
            $user->notify(
                new InscriptionEnregistree($classe)
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /**
     * Email de confirmation du paiement.
     */
    private function envoyerEmail(Paiement $paiement): void
    {
        try {
            $paiement->inscription
                ->user
                ->notify(
                    new PaiementConfirme($paiement)
                );
        } catch (\Throwable $e) {
            report($e);
        }
    }
}