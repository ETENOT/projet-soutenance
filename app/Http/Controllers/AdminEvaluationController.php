<?php

namespace App\Http\Controllers;

use App\Models\AvisFormateur;
use App\Models\Classe;
use App\Models\EvaluationFormation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

// Côté ADMINISTRATEUR : avis sur les formateurs ET évaluations des formations, avec les noms.
class AdminEvaluationController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'formateur' => ['nullable', 'integer', 'exists:users,id'],
            'classe' => ['nullable', 'integer', 'exists:classes,id'],
            'date_du' => ['nullable', 'date'],
            'date_au' => ['nullable', 'date', 'after_or_equal:date_du'],
        ], [
            'date_au.after_or_equal' => 'La fin de la période ne peut pas être avant son début.',
        ]);

        $formateurId = $request->integer('formateur') ?: null;
        $classeId = $request->integer('classe') ?: null;
        $recherche = trim((string) $request->input('q'));
        $dateDu = $request->input('date_du');
        $dateAu = $request->input('date_au');

        $correspondRecherche = function (...$valeurs) use ($recherche) {
            if ($recherche === '') {
                return true;
            }

            foreach ($valeurs as $valeur) {
                if (Str::contains(Str::lower((string) $valeur), Str::lower($recherche))) {
                    return true;
                }
            }

            return false;
        };

        // ---------- Onglet « Formateurs » ----------
        $tousLesAvis = AvisFormateur::with('classe.cours', 'classe.formateur', 'user')->latest()->get();

        $formateursListe = $tousLesAvis->pluck('classe.formateur')
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();

        $avis = $tousLesAvis
            ->filter(function ($avis) use ($formateurId, $classeId, $dateDu, $dateAu, $correspondRecherche) {
                $date = $avis->created_at->toDateString();

                return (! $formateurId || $avis->classe->formateur_id === $formateurId)
                    && (! $classeId || $avis->classe_id === $classeId)
                    && (! $dateDu || $date >= $dateDu)
                    && (! $dateAu || $date <= $dateAu)
                    && $correspondRecherche(
                        $avis->user?->name,
                        $avis->classe->formateur?->name,
                        $avis->classe->cours->titre,
                        $avis->classe->nom,
                        $avis->commentaire
                    );
            })
            ->values();

        // ---------- Onglet « Formations » ----------
        $toutesLesEvaluations = EvaluationFormation::with('classe.cours', 'classe.formateur', 'user')->latest()->get();

        $classesListe = $toutesLesEvaluations->pluck('classe')
            ->filter()
            ->unique('id')
            ->sortBy('nom')
            ->values();

        $evaluations = $toutesLesEvaluations
            ->filter(function ($evaluation) use ($formateurId, $classeId, $dateDu, $dateAu, $correspondRecherche) {
                $date = $evaluation->created_at->toDateString();

                return (! $formateurId || $evaluation->classe->formateur_id === $formateurId)
                    && (! $classeId || $evaluation->classe_id === $classeId)
                    && (! $dateDu || $date >= $dateDu)
                    && (! $dateAu || $date <= $dateAu)
                    && $correspondRecherche(
                        $evaluation->user?->name,
                        $evaluation->classe->formateur?->name,
                        $evaluation->classe->cours->titre,
                        $evaluation->classe->nom
                    );
            })
            ->values();

        $stats = [
            'total_avis' => $tousLesAvis->count(),
            'moyenne_avis' => $tousLesAvis->isNotEmpty()
                ? round((float) $tousLesAvis->avg(fn ($avis) => $avis->note), 1)
                : null,
            'total_evaluations' => $toutesLesEvaluations->count(),
            'moyenne_evaluations' => $toutesLesEvaluations->isNotEmpty()
                ? round((float) $toutesLesEvaluations->avg('note'), 1)
                : null,
        ];

        // Onglet affiché : celui du filtre utilisé, sinon celui demandé, sinon « Formateurs »
        $onglet = $classeId ? 'formations' : ($formateurId ? 'formateurs' : ($request->input('onglet') === 'formations' ? 'formations' : 'formateurs'));

        return view('evaluations.admin.index', [
            'onglet' => $onglet,
            'avis' => $avis,
            'formateursListe' => $formateursListe,
            'formateurFiltre' => $formateurId ? User::find($formateurId) : null,
            'evaluations' => $evaluations,
            'classesListe' => $classesListe,
            'classeFiltre' => $classeId ? Classe::find($classeId) : null,
            'stats' => $stats,
        ]);
    }

    /**
     * Une fiche complète d'un stagiaire (formation + avis sur le formateur de la même classe).
     */
    public function show(EvaluationFormation $evaluation)
    {
        $evaluation->load('classe.cours', 'classe.formateur', 'user');

        return view('evaluations.admin.show', [
            'evaluation' => $evaluation,
            'avisFormateur' => AvisFormateur::where('classe_id', $evaluation->classe_id)
                ->where('user_id', $evaluation->user_id)
                ->first(),
        ]);
    }
}