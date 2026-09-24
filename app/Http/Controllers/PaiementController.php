<?php

namespace App\Http\Controllers;

use App\Models\Inscription;
use App\Models\Notification;
use App\Models\Paiement;
use App\Notifications\PaiementConfirme;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PaiementController extends Controller
{
    /**
 * Affiche la page de choix du mode de paiement.
 * Réservée au propriétaire de l'inscription.
 */
    public function choix(Inscription $inscription)
    {
        abort_unless($inscription->user_id === Auth::id(), 403);

        // Si l'inscription est déjà payée, il n'est plus nécessaire
        // de choisir un mode de paiement.
        if ($inscription->paiement()->exists()) {
            return redirect()->route('cours.mes')
                ->with('error', 'Cette inscription est déjà payée.');
        }

        $inscription->load('classe.cours');

        return view('paiements.choix', [
            'inscription' => $inscription,
            'montant' => $this->montantDe($inscription),
        ]);
    }
    
    /**
     * Page de paiement en ligne d'une inscription.
     * Réservée au propriétaire de l'inscription.
     */
    public function create(Inscription $inscription)
    {
        abort_unless($inscription->user_id === Auth::id(), 403);

        if ($inscription->paiement()->exists()) {
            return redirect()->route('cours.mes')->with('error', 'Cette inscription est déjà payée.');
        }

        $inscription->load('classe.cours');

        return view('paiements.create', [
            'inscription' => $inscription,
            'montant' => $this->montantDe($inscription),
        ]);
    }

    /**
     * Demande à SingPay de créer un lien de paiement externe.
     * Aucun Paiement local n'est créé avant la confirmation de la transaction.
     */
    public function payer(Inscription $inscription)
    {
        abort_unless($inscription->user_id === Auth::id(), 403);

        if ($inscription->paiement()->exists()) {
            return redirect()->route('cours.mes')->with('error', 'Cette inscription est déjà payée.');
        }

        $clientId = config('services.singpay.client_id');
        $clientSecret = config('services.singpay.client_secret');
        $walletId = config('services.singpay.wallet_id');
        $publicUrl = rtrim((string) config('services.singpay.public_url'), '/');
        $logoUrl = config('services.singpay.logo_url');

        if (! $clientId || ! $clientSecret || ! $walletId || ! $publicUrl || ! $logoUrl) {
            return back()->with('error', 'La configuration SingPay est incomplète.');
        }

        $montant = $this->montantDe($inscription);
        $reference = 'NEO-PART-INS-' . $inscription->id . '-' . Str::upper(Str::random(8));

        try {
            $reponse = Http::withHeaders([
                'Accept' => '*/*',
                'x-client-id' => $clientId,
                'x-client-secret' => $clientSecret,
                'x-wallet' => $walletId,
            ])->post(config('services.singpay.base_url') . '/ext', [
                'portefeuille' => $walletId,
                'reference' => $reference,
                'redirect_success' => $publicUrl . '/paiement/success',
                'redirect_error' => $publicUrl . '/paiement/error',
                'amount' => $montant,
                'disbursement' => '',
                'logoURL' => $logoUrl,
                'isTransfer' => false,
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return back()->with('error', 'SingPay est momentanément inaccessible.');
        }

        $lien = $reponse->json('link');

        if ($reponse->failed() || ! is_string($lien) || $lien === '') {
            report(new \RuntimeException('Réponse SingPay invalide : ' . $reponse->body()));

            return back()->with('error', 'SingPay n’a pas accepté la demande de paiement.');
        }

        // La référence permettra de retrouver et vérifier la transaction au retour.
        session()->put('singpay_pending_payment', [
            'inscription_id' => $inscription->id,
            'reference' => $reference,
            'amount' => $montant,
        ]);

        return redirect()->away($lien);
    }

    /**
     * Vérifie la transaction SingPay avant de valider l'inscription localement.
     */
    public function success()
    {
        $attente = session('singpay_pending_payment');

        if (! is_array($attente) || empty($attente['reference']) || empty($attente['inscription_id'])) {
            return redirect()->route('cours.mes')
                ->with('error', 'Aucun paiement SingPay en attente n’a été trouvé.');
        }

        $inscription = Inscription::with('user')->findOrFail($attente['inscription_id']);
        abort_unless($inscription->user_id === Auth::id(), 403);

        try {
            $reponse = Http::withHeaders([
                'Accept' => '*/*',
                'x-client-id' => config('services.singpay.client_id'),
                'x-client-secret' => config('services.singpay.client_secret'),
                'x-wallet' => config('services.singpay.wallet_id'),
            ])->get(config('services.singpay.base_url') . '/transaction/api/search/by-reference/' . urlencode($attente['reference']));
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()->route('cours.mes')
                ->with('error', 'La vérification SingPay est momentanément inaccessible.');
        }

        $transaction = $reponse->json('transaction') ?: $reponse->json();
        $statut = $transaction['status'] ?? null;
        $resultat = $transaction['result'] ?? null;
        $montant = $transaction['amount'] ?? null;

        if ($reponse->failed()
            || ($transaction['reference'] ?? null) !== $attente['reference']
            || $statut !== 'Terminate'
            || $resultat !== 'Success'
            || (float) $montant !== (float) $attente['amount']) {
            return redirect()->route('cours.mes')
                ->with('error', 'Le paiement SingPay n’a pas pu être confirmé.');
        }

        $paiement = $this->enregistrer(
            $inscription,
            'singpay',
            $attente['reference'],
            $transaction['id'] ?? $transaction['_id'] ?? null,
            $statut,
            $resultat
        );
        session()->forget('singpay_pending_payment');

        if (! $paiement) {
            return redirect()->route('cours.mes')
                ->with('success', 'Cette inscription était déjà payée.');
        }

        $this->envoyerEmail($paiement);

        return redirect()->route('cours.mes')
            ->with('success', 'Paiement confirmé. Votre inscription est validée.');
    }

    /**
     * Retour SingPay en cas d'abandon ou d'échec : aucun paiement local n'est créé.
     */
    public function error()
    {
        session()->forget('singpay_pending_payment');

        return redirect()->route('cours.mes')
            ->with('error', 'Le paiement SingPay a échoué ou a été annulé.');
    }

    /**
     * Enregistre le paiement direct (comptoir) d'une inscription.
     * Réservé à l'administrateur : la route est dans le groupe "role:admin".
     */
    public function store(Inscription $inscription)
    {
        $paiement = $this->enregistrer($inscription, 'direct', null);

        if (! $paiement) {
            return back()->with('error', 'Cette inscription est déjà payée.');
        }

        $this->envoyerEmail($paiement);

        return back()->with('success', 'Paiement enregistré : l\'inscription est maintenant payée.');
    }

    /**
     * Montant à payer, TOUJOURS calculé côté serveur (prix du cours), jamais lu depuis un
     * formulaire : un utilisateur ne peut pas choisir son prix.
     * Même règle que le catalogue : tarif entreprise pour une entreprise, tarif particulier
     * pour les autres.
     */
    private function montantDe(Inscription $inscription)
    {
        $inscription->loadMissing('classe.cours', 'user.role');
        $cours = $inscription->classe->cours;

        return $inscription->user->role?->nom === 'entreprise'
            ? $cours->prix_entreprise
            : $cours->prix_particulier;
    }

    /**
     * Crée le paiement, commun au paiement direct et au paiement en ligne.
     * Renvoie le Paiement créé, ou null si l'inscription est déjà payée.
     */
    private function enregistrer(
        Inscription $inscription,
        string $mode,
        ?string $reference,
        ?string $singpayTransactionId = null,
        ?string $singpayStatus = null,
        ?string $singpayResult = null
    ): ?Paiement
    {
        return DB::transaction(function () use (
            $inscription,
            $mode,
            $reference,
            $singpayTransactionId,
            $singpayStatus,
            $singpayResult
        ) {
            // lockForUpdate : deux clics simultanés ne peuvent pas créer deux paiements,
            // le second attend puis voit que le paiement existe déjà.
            $insc = Inscription::with(['classe.cours', 'user.role'])
                ->lockForUpdate()
                ->findOrFail($inscription->id);

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

            $moyen = $mode === 'direct' ? 'au comptoir' : 'via ' . Paiement::MODES[$mode];

            // Notification dans l'application, dans la transaction : pas de paiement
            // enregistré sans que l'utilisateur en soit informé.
            Notification::create([
                'user_id' => $insc->user_id,
                'message' => 'Paiement confirmé : « ' . $cours->titre . ' » (' . $insc->classe->nom . '), '
                    . number_format($montant, 0, ',', ' ') . ' fcfa ' . $moyen
                    . '. Votre inscription est validée.',
            ]);

            return $paiement;
        });
    }

    /**
     * Email envoyé APRÈS la transaction : une panne du serveur mail ne doit jamais annuler
     * un paiement déjà enregistré. L'erreur est journalisée avec report().
     */
    private function envoyerEmail(Paiement $paiement): void
    {
        try {
            $paiement->inscription->user->notify(new PaiementConfirme($paiement));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}