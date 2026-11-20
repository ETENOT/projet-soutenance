<?php
// app/Http/Controllers/EntrepriseAttestationController.php  (lecture seule, rôle « entreprise »)
namespace App\Http\Controllers;

use App\Models\Attestation;
use App\Models\Entreprise;
use App\Services\AttestationService;
use Illuminate\Http\Request;

/**
 * Attestations reçues par les membres de l'entreprise, uniquement pour les classes
 * où c'est l'ENTREPRISE qui les a inscrits (inscriptions.via_entreprise_id).
 * Les formations qu'un membre a suivies de lui-même (inscription personnelle) n'y figurent pas.
 * L'entreprise peut voir chaque attestation dans le navigateur et la télécharger en PDF.
 */
class EntrepriseAttestationController extends Controller
{
    // Entreprise du compte connecté (403 si le compte n'en a pas)
    private function entreprise(Request $request): Entreprise
    {
        $entreprise = $request->user()->entreprise;
        abort_unless($entreprise, 403, 'Aucune entreprise n’est rattachée à ce compte.');

        return $entreprise;
    }

    // Attestations des inscriptions faites par cette entreprise
    private function requete(Entreprise $entreprise)
    {
        return Attestation::whereHas('inscription', fn ($q) => $q->where('via_entreprise_id', $entreprise->id));
    }

    // Liste : de la plus récente à la plus ancienne
    public function index(Request $request)
    {
        $entreprise = $this->entreprise($request);

        $query = $this->requete($entreprise)->with('user', 'classe.cours', 'classe.session');

        // Recherche facultative : membre, formation ou numéro d'attestation
        if ($recherche = trim((string) $request->input('q'))) {
            $query->where(function ($q) use ($recherche) {
                $q->where('beneficiaire', 'like', "%{$recherche}%")
                  ->orWhere('intitule', 'like', "%{$recherche}%")
                  ->orWhere('numero', 'like', "%{$recherche}%");
            });
        }

        // Plus récemment délivrées en premier ; l'id départage une classe entière fermée dans la même seconde
        $attestations = $query->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('espace-entreprise.attestations.index', [
            'entreprise' => $entreprise,
            'attestations' => $attestations,
            // Total indépendant de la recherche et de la page affichée
            'totalAttestations' => $this->requete($entreprise)->count(),
        ]);
    }

    // Affiche le PDF dans le navigateur
    public function voir(Request $request, Attestation $attestation, AttestationService $service)
    {
        $this->autoriser($request, $attestation);

        return response($service->octetsPdf($attestation), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $attestation->nomFichier() . '"',
        ]);
    }

    // Propose l'enregistrement du PDF
    public function telecharger(Request $request, Attestation $attestation, AttestationService $service)
    {
        $this->autoriser($request, $attestation);

        return response($service->octetsPdf($attestation), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $attestation->nomFichier() . '"',
        ]);
    }

    // Seules les attestations des inscriptions faites par CETTE entreprise sont accessibles
    private function autoriser(Request $request, Attestation $attestation): void
    {
        $entreprise = $this->entreprise($request);

        abort_unless(
            (int) $attestation->inscription?->via_entreprise_id === (int) $entreprise->id,
            404
        );
    }
}
