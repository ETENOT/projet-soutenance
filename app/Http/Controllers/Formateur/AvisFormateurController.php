<?php

namespace App\Http\Controllers\Formateur;

use App\Models\AvisFormateur;
use App\Models\Classe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Côté FORMATEUR : consulter les avis reçus pour s'améliorer.
// Le nom du stagiaire n'est PAS affiché : il peut s'exprimer librement.
// (Les évaluations de la formation elle-même sont réservées à l'administration.)
class AvisFormateurController extends FormateurController
{
    /**
     * Avis de toutes mes classes, ou d'une seule classe avec ?classe=ID.
     */
    public function index(Request $request)
    {
        $mesClasses = Classe::where('formateur_id', Auth::id())
            ->whereHas('avisFormateurs')
            ->with('cours')
            ->withCount('avisFormateurs')
            ->orderByDesc('date_debut')
            ->get();

        // Filtre facultatif : 403 si la classe n'est pas la sienne
        $classeFiltre = null;
        if ($request->filled('classe')) {
            $classeFiltre = $this->classeAutorisee(Classe::findOrFail($request->integer('classe')));
        }

        $avis = AvisFormateur::query()
            ->whereIn('classe_id', $classeFiltre ? [$classeFiltre->id] : $mesClasses->pluck('id'))
            ->with('classe.cours')
            ->latest()
            ->get();

        return view('avis_formateurs.formateur.index', [
            'mesClasses' => $mesClasses,
            'classeFiltre' => $classeFiltre,
            'avis' => $avis,
            'moyennes' => AvisFormateur::moyennes($avis),
            'nombreAvisTotal' => $classeFiltre
                ? $classeFiltre->avisFormateurs()->count()
                : $mesClasses->sum('avis_formateurs_count'),
        ]);
    }
}