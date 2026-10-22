<?php

namespace App\Services;

use Throwable;
use App\Models\Classe;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

// Centralise la FERMETURE d'une classe, pour que la règle soit strictement identique
// que la fermeture soit demandée par le formateur ou par l'administrateur.
//
// Règle métier : à la fermeture, on calcule le taux de présence de chaque stagiaire
// (payé) et on FIGE le résultat sur son inscription. Un taux < 50 % = pas d'attestation.
class ClasseClotureService
{
    /**
     * Ferme la classe et fige taux de présence + éligibilité à l'attestation.
     *
     * @throws DomainException si la classe est déjà fermée ou n'a pas encore commencé
     */

    // AttestationService est injecté automatiquement par Laravel (type-hint du constructeur)
    public function __construct(private AttestationService $attestations)
    {
    }

    public function fermer(Classe $classe, User $par): Classe
    {
        // Transaction : soit TOUT est enregistré (inscriptions + fermeture), soit rien.
        $classeFermee = DB::transaction(function () use ($classe, $par) {

            // lockForUpdate() verrouille la ligne : si formateur ET admin cliquent
            // en même temps, le 2e attend puis constate que la classe est déjà fermée.
            $classe = Classe::whereKey($classe->id)->lockForUpdate()->firstOrFail();

            if ($classe->estFermee()) {
                throw new DomainException('Cette classe est déjà fermée.');
            }

            if (! $classe->peutEtreFermee()) {
                throw new DomainException('Cette classe n\'a pas encore commencé : elle ne peut pas être fermée.');
            }

            // [user_id => nombre de jours de présence], calculé en UNE requête
            $presences = $classe->presencesParStagiaire();

            // Seuls les stagiaires qui ont payé sont concernés par l'attestation
            $inscriptions = $classe->inscriptions()->payees()->get();

            foreach ($inscriptions as $inscription) {
                $taux = $classe->tauxPresence((int) ($presences[$inscription->user_id] ?? 0));

                $inscription->update([
                    'taux_presence' => $taux,
                    // "-50 %" = strictement moins de 50 % : exactement 50 % donne droit à l'attestation
                    'attestation_eligible' => $taux >= Classe::SEUIL_ATTESTATION,
                ]);
            }

            // On marque la classe fermée en dernier, une fois les résultats figés
            $classe->update([
                'fermee_le' => now(),
                'fermee_par' => $par->id,
            ]);

            return $classe;
        });
        // Une fois la fermeture VALIDÉE en base, on génère les PDF et on envoie les e-mails.
        // Volontairement hors transaction : un souci d'envoi (serveur mail en panne...) ne doit
        // jamais annuler la fermeture. En cas d'échec, `php artisan attestations:generer` rattrape.
        try {
            $this->attestations->genererPourClasse($classeFermee);
        } catch (Throwable $e) {
            report($e);
        }

        return $classeFermee;
    }
}