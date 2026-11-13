<?php

namespace App\Http\Controllers;

use App\Models\Attestation;
use App\Services\AttestationService;
use Illuminate\Support\Facades\Auth;

// Téléchargement d'une attestation de formation (PDF).
class AttestationController extends Controller
{
    // Seuls le propriétaire de l'attestation et l'administrateur y ont accès.
    // Regroupé ici pour que "voir" et "télécharger" appliquent exactement la même règle.
    private function verifierAcces(Attestation $attestation): void
    {
        $user = Auth::user();
        $estAdmin = $user->role?->nom === 'admin';

        abort_unless(
            $attestation->user_id === $user->id || $estAdmin,
            403,
            'Cette attestation ne vous appartient pas.'
        );

        // Le stagiaire ne voit / ne télécharge son attestation que si la formation est payée
        // en TOTALITÉ (un paiement partiel bloque l'attestation). L'admin garde l'accès.
        if (! $estAdmin) {
            $inscription = $attestation->inscription()->with('paiement')->first();

            abort_unless(
                $inscription?->estSoldee(),
                403,
                'Vous devez payer la totalité de la formation pour recevoir votre attestation de suivi de formation.'
            );
        }
    }

    /**
     * GET /attestations/{attestation}/voir
     * Affiche le PDF DANS le navigateur (visionneuse PDF intégrée), sans l'enregistrer.
     */
    public function voir(Attestation $attestation, AttestationService $service)
    {
        $this->verifierAcces($attestation);

        return response($service->octetsPdf($attestation), 200, [
            'Content-Type' => 'application/pdf',
            // inline = le navigateur affiche le fichier au lieu de le télécharger
            'Content-Disposition' => 'inline; filename="' . $attestation->nomFichier() . '"',
        ]);
    }

    /**
     * GET /attestations/{attestation}/telecharger
     * Propose l'enregistrement du PDF.
     */
    public function telecharger(Attestation $attestation, AttestationService $service)
    {
        $this->verifierAcces($attestation);

        return response($service->octetsPdf($attestation), 200, [
            'Content-Type' => 'application/pdf',
            // attachment = le navigateur propose directement l'enregistrement du fichier
            'Content-Disposition' => 'attachment; filename="' . $attestation->nomFichier() . '"',
        ]);
    }
}