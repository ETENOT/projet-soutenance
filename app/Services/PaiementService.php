<?php

namespace App\Services;

use App\Models\Inscription;
use App\Models\Notification;
use App\Models\Paiement;
use App\Notifications\PaiementConfirme;
use Illuminate\Support\Facades\DB;

class PaiementService
{
    /**
     * Enregistre un paiement direct au comptoir et envoie son e-mail de confirmation.
     */
    public function enregistrerAuComptoir(Inscription $inscription): ?Paiement
    {
        $paiement = $this->creerAuComptoir($inscription);

        if ($paiement) {
            $this->envoyerEmail($paiement);
        }

        return $paiement;
    }

    /**
     * Crée le paiement comptoir dans une transaction englobante, sans envoyer l'e-mail.
     */
    public function creerAuComptoir(Inscription $inscription): ?Paiement
    {
        return $this->enregistrer($inscription, 'direct', null);
    }

    /**
     * Crée un paiement valide, sans doublon pour une inscription.
     */
    public function enregistrer(
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
                'statut' => 'valide',
                'reference' => $reference,
                'singpay_transaction_id' => $singpayTransactionId,
                'singpay_status' => $singpayStatus,
                'singpay_result' => $singpayResult,
            ]);

            $moyen = $mode === 'direct'
                ? 'au comptoir'
                : 'via ' . (Paiement::MODES[$mode] ?? $mode);

            Notification::create([
                'user_id' => $insc->user_id,
                'message' => 'Paiement confirmé : « '
                    . $cours->titre
                    . ' » ('
                    . $insc->classe->nom
                    . '), '
                    . number_format($montant, 0, ',', ' ')
                    . ' fcfa '
                    . $moyen
                    . '. Votre inscription est validée.',
            ]);

            return $paiement;
        });
    }

    /**
     * Envoie l'e-mail de confirmation du paiement.
     */
    public function envoyerEmail(Paiement $paiement): void
    {
        try {
            $paiement->inscription
                ->user
                ->notify(new PaiementConfirme($paiement));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    public function montantDe(Inscription $inscription)
    {
        $inscription->loadMissing('classe.cours', 'user.role');
        $cours = $inscription->classe->cours;

        return $inscription->user->role?->nom === 'entreprise'
            ? $cours->prix_entreprise
            : $cours->prix_particulier;
    }
}
