<?php

namespace App\Http\Controllers;

use App\Models\AvisFormateur;
use App\Models\Classe;
use App\Models\EvaluationFormation;
use App\Models\User;
use Illuminate\Http\Request;

// Côté ADMINISTRATEUR : avis sur les formateurs ET évaluations des formations, avec les noms.
class AdminEvaluationController extends Controller
{
    public function index(Request $request)
    {
        // ---------- Onglet « Formateurs » ----------
        $tousLesAvis = AvisFormateur::with('classe.cours', 'classe.formateur', 'user')->latest()->get();

        $synthesesFormateurs = $tousLesAvis
            ->filter(fn ($a) => $a->classe->formateur_id !== null)
            ->groupBy(fn ($a) => $a->classe->formateur_id)
            ->map(fn ($groupe) => [
                'formateur' => $groupe->first()->classe->formateur,
                'nombre' => $groupe->count(),
                'moyennes' => AvisFormateur::moyennes($groupe),
            ])
            ->sortByDesc(fn ($ligne) => $ligne['moyennes']['globale'])
            ->values();

        $formateurId = $request->integer('formateur') ?: null;
        $avis = $formateurId
            ? $tousLesAvis->filter(fn ($a) => $a->classe->formateur_id === $formateurId)->values()
            : $tousLesAvis;

        // ---------- Onglet « Formations » ----------
        $toutesLesEvaluations = EvaluationFormation::with('classe.cours', 'classe.formateur', 'user')->latest()->get();

        $synthesesClasses = $toutesLesEvaluations
            ->groupBy('classe_id')
            ->map(fn ($groupe) => [
                'classe' => $groupe->first()->classe,
                'nombre' => $groupe->count(),
                'moyennes' => EvaluationFormation::moyennes($groupe),
            ])
            ->sortByDesc(fn ($ligne) => $ligne['moyennes']['globale'])
            ->values();

        $classeId = $request->integer('classe') ?: null;
        $evaluations = $classeId
            ? $toutesLesEvaluations->filter(fn ($e) => $e->classe_id === $classeId)->values()
            : $toutesLesEvaluations;

        // Onglet affiché : celui du filtre utilisé, sinon celui demandé, sinon « Formateurs »
        $onglet = $classeId ? 'formations' : ($formateurId ? 'formateurs' : ($request->input('onglet') === 'formations' ? 'formations' : 'formateurs'));

        return view('evaluations.admin.index', [
            'onglet' => $onglet,
            'synthesesFormateurs' => $synthesesFormateurs,
            'avis' => $avis,
            'formateurFiltre' => $formateurId ? User::find($formateurId) : null,
            'synthesesClasses' => $synthesesClasses,
            'evaluations' => $evaluations,
            'classeFiltre' => $classeId ? Classe::find($classeId) : null,
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