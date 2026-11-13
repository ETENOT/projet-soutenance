<?php

namespace App\Services;

use App\Models\Classe;
use App\Models\Inscription;
use App\Models\Notification;
use App\Models\Paiement;
use App\Models\User;
use App\Notifications\PaiementConfirme;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Tout ce qui concerne l'enregistrement des paiements.
 *
 * Deux sortes de paiement :
 *  - COMPLET (statut 'valide')  : montant versé = montant total dû, reste à payer = 0 ;
 *  - PARTIEL (statut 'partiel') : montant versé < montant total dû.
 * Les deux donnent accès au cours ; seul le paiement complet donne droit à l'attestation.
 */
class PaiementService
{
    /**
     * Enregistre un paiement direct au comptoir et envoie son e-mail de confirmation.
     * $montantVerse = null => paiement complet ; sinon paiement partiel de ce montant.
     */
    public function enregistrerAuComptoir(Inscription $inscription, ?float $montantVerse = null): ?Paiement
    {
        $paiement = $this->creerAuComptoir($inscription, $montantVerse);

        if ($paiement) {
            $this->envoyerEmail($paiement);
        }

        return $paiement;
    }

    /**
     * Crée le paiement comptoir dans une transaction englobante, sans envoyer l'e-mail.
     * $montantVerse = null => paiement complet.
     */
    public function creerAuComptoir(Inscription $inscription, ?float $montantVerse = null): ?Paiement
    {
        return $this->enregistrer($inscription, 'direct', null, montantVerse: $montantVerse);
    }

    /**
     * Crée le paiement d'une inscription (complet ou partiel), sans doublon pour une inscription.
     * Retourne null si l'inscription avait déjà un paiement.
     *
     * $montantVerse : null (ou >= au total) => paiement complet, statut 'valide' ;
     *                 inférieur au total    => paiement partiel, statut 'partiel'.
     */
    public function enregistrer(
        Inscription $inscription,
        string $mode,
        ?string $reference,
        ?string $operateurTransactionId = null,   // identifiant de l'opération chez Airtel Money
        ?string $operateurStatut = null,          // code final Airtel (TS = réussi)
        ?string $operateurResultat = null,        // message renvoyé par Airtel
        ?float $montantVerse = null
    ): ?Paiement {
        return DB::transaction(function () use (
            $inscription,
            $mode,
            $reference,
            $operateurTransactionId,
            $operateurStatut,
            $operateurResultat,
            $montantVerse
        ) {
            $insc = Inscription::with(['classe.cours', 'user.role'])
                ->lockForUpdate()
                ->findOrFail($inscription->id);

            if ($insc->paiement()->exists()) {
                return null;
            }

            $cours = $insc->classe->cours;
            $total = (float) $this->montantDe($insc);

            // Pas de montant précisé, ou montant qui couvre tout : paiement complet
            $verse = ($montantVerse === null || $montantVerse >= $total) ? $total : $montantVerse;
            $statut = $verse >= $total ? Paiement::STATUT_VALIDE : Paiement::STATUT_PARTIEL;

            $paiement = Paiement::create([
                'montant' => $verse,
                'montant_total' => $total,
                'inscription_id' => $insc->id,
                'mode' => $mode,
                'statut' => $statut,
                'reference' => $reference,
                'operateur_transaction_id' => $operateurTransactionId,
                'operateur_statut' => $operateurStatut,
                'operateur_resultat' => $operateurResultat,
            ]);

            $moyen = $mode === 'direct'
                ? 'au comptoir'
                : 'via ' . (Paiement::MODES[$mode] ?? $mode);

            // Notification de la cloche : le message précise le reste à payer en cas de paiement partiel
            $message = $statut === Paiement::STATUT_VALIDE
                ? 'Paiement confirmé : « ' . $cours->titre . ' » (' . $insc->classe->nom . '), '
                    . $this->fcfa($verse) . ' ' . $moyen . '. Votre inscription est validée.'
                : 'Paiement partiel enregistré : « ' . $cours->titre . ' » (' . $insc->classe->nom . '), '
                    . $this->fcfa($verse) . ' ' . $moyen . '. Reste à payer : ' . $this->fcfa($total - $verse)
                    . '. Vous avez accès au cours ; l\'attestation sera disponible une fois la totalité réglée.';

            Notification::create([
                'user_id' => $insc->user_id,
                'message' => $message,
            ]);

            return $paiement;
        });
    }

    /**
     * L'administrateur modifie la somme versée d'un paiement (paiement partiel en général).
     *  - montant = total  => le paiement devient complet ('valide') ;
     *  - montant < total  => il reste (ou redevient) partiel.
     * Si l'inscription devient soldée et que la classe est déjà fermée, l'attestation est
     * générée et envoyée tout de suite (si le stagiaire y est éligible).
     *
     * Le montant doit être compris entre 1 et le montant total (vérifié par le contrôleur).
     */
    public function modifierMontantVerse(Paiement $paiement, float $nouveauMontant): Paiement
    {
        $resultat = DB::transaction(function () use ($paiement, $nouveauMontant) {
            $p = Paiement::with('inscription.classe.cours')->lockForUpdate()->findOrFail($paiement->id);

            $etaitSolde = $p->statut === Paiement::STATUT_VALIDE;
            $total = (float) $p->montant_total;
            $verse = min($nouveauMontant, $total);
            $statut = $verse >= $total ? Paiement::STATUT_VALIDE : Paiement::STATUT_PARTIEL;

            $p->update(['montant' => $verse, 'statut' => $statut]);

            $cours = $p->inscription->classe->cours;
            Notification::create([
                'user_id' => $p->inscription->user_id,
                'message' => $statut === Paiement::STATUT_VALIDE
                    ? 'Votre formation « ' . $cours->titre . ' » est maintenant entièrement payée ('
                        . $this->fcfa($verse) . '). Votre attestation de suivi de formation vous sera délivrée.'
                    : 'Votre paiement pour « ' . $cours->titre . ' » a été mis à jour : ' . $this->fcfa($verse)
                        . ' versés, reste à payer ' . $this->fcfa($total - $verse) . '.',
            ]);

            // [paiement à jour, vient-il de devenir soldé ?]
            return [$p, ! $etaitSolde && $statut === Paiement::STATUT_VALIDE];
        });

        [$paiementAJour, $devientSolde] = $resultat;

        if ($devientSolde) {
            $this->envoyerEmail($paiementAJour);
            $this->delivrerAttestationSiPossible($paiementAJour->inscription);
        }

        return $paiementAJour;
    }

    /**
     * Envoie l'e-mail de confirmation du paiement (complet ou partiel).
     */
    public function envoyerEmail(Paiement $paiement): void
    {
        try {
            $paiement->inscription
                ->user
                ->notify(new PaiementConfirme($paiement));
        } catch (Throwable $e) {
            report($e);
        }
    }

    /**
     * Montant total dû pour une inscription existante.
     */
    public function montantDe(Inscription $inscription)
    {
        $inscription->loadMissing('classe.cours', 'classe.session', 'user.role');

        // Inscription faite via une entreprise (classe de session) : tarif entreprise
        if ($inscription->via_entreprise_id) {
            return $inscription->classe->cours->prix_entreprise;
        }

        return $this->montantPour($inscription->classe, $inscription->user);
    }

    /**
     * Montant total dû par $user pour $classe, AVANT que l'inscription existe
     * (même règle que montantDe) : tarif entreprise pour un compte entreprise ou une
     * classe de session, tarif particulier sinon.
     */
    public function montantPour(Classe $classe, User $user)
    {
        $classe->loadMissing('cours');
        $user->loadMissing('role');

        return $user->role?->nom === 'entreprise' || $classe->session_id
            ? $classe->cours->prix_entreprise
            : $classe->cours->prix_particulier;
    }

    /**
     * Minimum à verser pour un paiement partiel EN LIGNE : la moitié du prix (arrondie au franc supérieur).
     */
    public function minimumPartielEnLigne(float $total): float
    {
        return (float) ceil($total / 2);
    }

    /**
     * Après qu'une inscription est devenue soldée : si la classe est déjà fermée et que le stagiaire
     * est éligible (présence suffisante), on génère et envoie son attestation.
     * Hors transaction : un souci d'e-mail ou de PDF ne doit pas annuler le paiement.
     */
    private function delivrerAttestationSiPossible(Inscription $inscription): void
    {
        $inscription->loadMissing('classe');

        if (! $inscription->attestation_eligible || ! $inscription->classe->estFermee()) {
            return;
        }

        try {
            $service = app(AttestationService::class);
            $service->envoyer($service->generer($inscription));
        } catch (Throwable $e) {
            report($e);
        }
    }

    // 25000 -> "25 000 fcfa"
    private function fcfa($montant): string
    {
        return number_format((float) $montant, 0, ',', ' ') . ' fcfa';
    }
}
