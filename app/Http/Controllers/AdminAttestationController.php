<?php

namespace App\Http\Controllers;

use App\Models\Attestation;
use App\Models\Cours;
use Illuminate\Http\Request;

// Liste de toutes les attestations délivrées (réservé à l'administrateur : voir le groupe
// de routes « admin » dans routes/web.php, middleware role:admin).
class AdminAttestationController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'cours_id' => ['nullable', 'integer'],
            'date_du' => ['nullable', 'date'],
            'date_au' => ['nullable', 'date', 'after_or_equal:date_du'],
        ], [
            'date_au.after_or_equal' => 'La fin de la période ne peut pas être avant son début.',
        ]);

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

        // Filtre par cours (celui de la classe de l'attestation)
        if ($request->filled('cours_id')) {
            $query->whereHas('classe', fn ($c) => $c->where('cours_id', $request->integer('cours_id')));
        }

        // Filtre par période de délivrance
        if ($request->filled('date_du')) {
            $query->whereDate('created_at', '>=', $request->input('date_du'));
        }
        if ($request->filled('date_au')) {
            $query->whereDate('created_at', '<=', $request->input('date_au'));
        }

        // Les plus récemment délivrées en premier. Le tri sur id départage les attestations
        // créées dans la même seconde (ex. toute une classe fermée d'un coup).
        $attestations = $query->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('attestations.index', [
            'attestations' => $attestations,
            'tousLesCours' => Cours::orderBy('titre')->get(['id', 'titre']),
            // Total général, indépendant de la recherche et de la page affichée
            'totalAttestations' => Attestation::count(),
        ]);
    }

}