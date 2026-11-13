<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

/**
 * Choix fait par l'administrateur dans la fenêtre « Mode de paiement » :
 *  - type_paiement = 'complet' : paiement reçu en totalité (reste à payer = 0) ;
 *  - type_paiement = 'partiel' : paiement partiel, avec la somme versée dans « montant_partiel ».
 * La même somme s'applique à TOUS les particuliers sélectionnés ; l'admin peut ensuite modifier
 * la somme de chacun depuis la liste des inscrits.
 *
 * Partagé par ClasseController (ajout / actions groupées), PaiementController (encaissement)
 * et AdminInscriptionController (création d'une inscription).
 */
trait ChoixPaiementAdmin
{
    /**
     * Valide le choix et retourne la somme partielle, ou null pour un paiement complet.
     * Sans champ "type_paiement" (ancien formulaire), on garde l'ancien comportement : paiement complet.
     */
    protected function montantPartielDemande(Request $request): ?float
    {
        $data = $request->validate([
            'type_paiement' => ['nullable', 'in:complet,partiel'],
            'montant_partiel' => ['nullable', 'required_if:type_paiement,partiel', 'numeric', 'min:1'],
        ], [
            'type_paiement.in' => 'Choisissez « Paiement reçu » ou « Paiement partiel ».',
            'montant_partiel.required_if' => 'Indiquez la somme versée pour le paiement partiel.',
            'montant_partiel.numeric' => 'La somme versée doit être un nombre.',
            'montant_partiel.min' => 'La somme versée doit être d\'au moins 1 fcfa.',
        ]);

        return ($data['type_paiement'] ?? 'complet') === 'partiel'
            ? (float) $data['montant_partiel']
            : null;
    }

    /**
     * Un paiement partiel doit rester STRICTEMENT inférieur au prix (sinon c'est un paiement complet).
     * Retourne le message d'erreur, ou null si la somme convient.
     */
    protected function erreurMontantPartiel(?float $montantPartiel, float $total, string $nom): ?string
    {
        if ($montantPartiel === null || $montantPartiel < $total) {
            return null;
        }

        return 'La somme partielle (' . number_format($montantPartiel, 0, ',', ' ') . ' fcfa) doit être inférieure au prix de la formation pour '
            . $nom . ' (' . number_format($total, 0, ',', ' ') . ' fcfa). Pour un paiement complet, choisissez « Paiement reçu ».';
    }
}
