<?php

namespace App\Http\Controllers\Formateur;

use App\Models\Classe;
use App\Models\Quiz;
use Illuminate\Support\Facades\Auth;

// Résultats aux quiz des stagiaires d'une classe, avec TROIS restrictions cumulatives :
//  1. uniquement les stagiaires de la classe qui ont payé ;
//  2. uniquement les quiz passés PENDANT la période de la classe (date_debut → date_fin) ;
//  3. uniquement le quiz du cours de cette classe.
class QuizController extends FormateurController
{
    public function index(Classe $classe)
    {
        $this->classeAutorisee($classe);

        $classe->load('cours');

        // (1) stagiaires payés de la classe
        $stagiairesIds = $classe->inscriptions()->payees()->pluck('user_id');

        $tentatives = Quiz::query()
            // (3) quiz du cours de la classe uniquement
            ->where('cours_id', $classe->cours_id)
            ->whereIn('user_id', $stagiairesIds)
            // (2) période d'affectation = période de la classe (bornes incluses)
            ->whereBetween('date', [$classe->date_debut->toDateString(), $classe->date_fin->toDateString()])
            // Seules les tentatives TERMINÉES ont un résultat (les abandonnées/en cours sont exclues)
            ->whereHas('resultats')
            ->with(['user', 'resultats'])
            ->orderByDesc('date')
            ->orderByDesc('heure_debut')
            ->get();

        return view('quiz.formateur.index', [
            'classe' => $classe,
            'cours' => $classe->cours,
            'tentatives' => $tentatives,
        ]);
    }
    // Correction détaillée d'une tentative d'un stagiaire de la classe
    // (bonne réponse en vert, réponse du stagiaire en rouge si fausse).
    public function correction(Classe $classe, ResultatQuiz $resultatQuiz)
    {
        $this->classeAutorisee($classe);

        // Le résultat doit correspondre à une tentative visible par ce formateur pour CETTE classe
        // (stagiaire payé, période de la classe, cours de la classe). Sinon 404 : on ne révèle
        // pas l'existence d'un résultat d'un autre cours, d'une autre classe ou d'un autre formateur.
        $quiz = $this->tentativesDeLaClasse($classe)
            ->whereKey($resultatQuiz->quiz_id)
            ->firstOrFail();

        $quiz->load('user');

        $reponses = $quiz->reponses()
            ->with(['question.options'])
            ->orderBy('ordre')
            ->get();

        return view('quiz.formateur.correction', [
            'classe' => $classe,
            'cours' => $classe->cours,
            'quiz' => $quiz,
            'stagiaire' => $quiz->user,
            'resultatQuiz' => $resultatQuiz,
            'reponses' => $reponses,
        ]);
    }

    // Requête commune : tentatives TERMINÉES visibles par le formateur pour cette classe.
    private function tentativesDeLaClasse(Classe $classe)
    {
        // (1) stagiaires payés de la classe
        $stagiairesIds = $classe->inscriptions()->payees()->pluck('user_id');

        return Quiz::query()
            // (3) quiz du cours de la classe uniquement
            ->where('cours_id', $classe->cours_id)
            ->whereIn('user_id', $stagiairesIds)
            // (2) période d'affectation = période de la classe (bornes incluses)
            ->whereBetween('date', [$classe->date_debut->toDateString(), $classe->date_fin->toDateString()])
            // Seules les tentatives TERMINÉES ont un résultat (les abandonnées/en cours sont exclues)
            ->whereHas('resultats');
    }

    public function classes()
    {
        $classes = Classe::where('formateur_id', Auth::id())
            ->with('cours')
            ->orderByDesc('date_debut')
            ->get();

        return view('quiz.formateur.classes', ['classes' => $classes]);
    }
}