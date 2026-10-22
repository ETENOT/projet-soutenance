<?php

namespace App\Http\Controllers;

use App\Models\Attestation;
use Illuminate\Http\Request;

// Liste de toutes les attestations délivrées (réservé à l'administrateur : voir le groupe
// de routes « admin » dans routes/web.php, middleware role:admin).
class AdminAttestationController extends Controller
{
    public function index(Request $request)
    {
        // with() charge le stagiaire et la classe/cours en une seule requête chacun
        // (évite une requête par ligne du tableau)
        $query = Attestation::with('user', 'classe.cours');

        // Recherche facultative : nom du bénéficiaire, formation ou numéro d'attestation
        if ($recherche = trim((string) $request->input('q'))) {
            $query->where(function ($q) use ($recherche) {
                $q->where('beneficiaire', 'like', "%{$recherche}%")
                  ->orWhere('intitule', 'like', "%{$recherche}%")
                  ->orWhere('numero', 'like', "%{$recherche}%");
            });
        }

        // Les plus récemment délivrées en premier. Le tri sur id départage les attestations
        // créées dans la même seconde (ex. toute une classe fermée d'un coup).
        $attestations = $query->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('attestations.index', [
            'attestations' => $attestations,
            // Total général, indépendant de la recherche et de la page affichée
            'totalAttestations' => Attestation::count(),
        ]);
    }
}