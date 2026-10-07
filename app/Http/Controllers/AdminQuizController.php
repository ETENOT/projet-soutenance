<?php

namespace App\Http\Controllers;

use App\Models\Cours;
use App\Models\Quiz;
use App\Models\ResultatQuiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Suivi des quiz côté administrateur.
// Dans cette application un "Quiz" est une TENTATIVE passée par un particulier ou un visiteur
// (les tentatives sont créées par QuizAttemptController, pas par l'admin) :
// l'admin peut donc les consulter (liste, détail avec correction) et les supprimer.
class AdminQuizController extends Controller
{
    public function index(Request $request)
    {
        $recherche = trim((string) $request->input('q'));

        $quizzes = Quiz::with(['cours:id,titre', 'user:id,name,email', 'resultats'])
            ->withCount('reponses')
            ->when($request->filled('cours_id'), fn ($q) => $q->where('cours_id', $request->input('cours_id')))
            // "Terminé" = il existe une ligne resultats_quiz pour cette tentative
            ->when($request->input('statut') === 'termine', fn ($q) => $q->whereHas('resultats'))
            ->when($request->input('statut') === 'en_cours', fn ($q) => $q->whereDoesntHave('resultats'))
            // Recherche : e-mail du visiteur OU nom/e-mail de l'utilisateur inscrit
            ->when($recherche !== '', function ($q) use ($recherche) {
                $q->where(function ($w) use ($recherche) {
                    $w->where('email_visiteur', 'like', "%{$recherche}%")
                      ->orWhereHas('user', fn ($u) => $u
                          ->where('name', 'like', "%{$recherche}%")
                          ->orWhere('email', 'like', "%{$recherche}%"));
                });
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        // Chiffres clés affichés en haut de page
        $stats = [
            'total'    => Quiz::count(),
            'termines' => ResultatQuiz::count(),
            // Moyenne en % : score / barème de chaque tentative terminée (barème 0 ignoré)
            'moyenne'  => DB::table('resultats_quiz')
                ->join('quizzes', 'quizzes.id', '=', 'resultats_quiz.quiz_id')
                ->where('quizzes.bareme', '>', 0)
                ->avg(DB::raw('resultats_quiz.score / quizzes.bareme * 100')),
        ];

        $cours = Cours::orderBy('titre')->get(['id', 'titre']);

        return view('quiz.admin.index', compact('quizzes', 'cours', 'stats'));
    }

    // Page "Réglages" : pour chaque cours, l'admin choisit sur combien est noté le quiz
    // (= nombre de questions tirées au hasard dans la banque, 1 point par question)
    public function reglages()
    {
        // questions_count = taille de la banque du cours, affichée à côté du champ
        $cours = Cours::withCount('questions')->orderBy('titre')->get();

        return view('quiz.admin.reglages', compact('cours'));
    }

    public function enregistrerReglages(Request $request)
    {
        $request->validate([
            // note_sur[ID_DU_COURS] = valeur ; vide = pas de limite (toute la banque)
            'note_sur'   => ['array'],
            'note_sur.*' => ['nullable', 'integer', 'min:1', 'max:100'],
        ], [
            'note_sur.*.integer' => 'La note doit être un nombre entier.',
            'note_sur.*.min'     => 'La note doit être d\'au moins 1.',
            'note_sur.*.max'     => 'La note ne peut pas dépasser 100.',
        ]);

        $valeurs = $request->input('note_sur', []);

        // On ne touche qu'aux cours réellement présents dans le formulaire
        foreach (Cours::pluck('id') as $id) {
            if (array_key_exists($id, $valeurs)) {
                // Champ vide -> null (Laravel convertit "" en null) : toute la banque
                Cours::whereKey($id)->update(['note_sur' => $valeurs[$id]]);
            }
        }

        // Les quiz déjà commencés ne changent pas : leur barème a été figé au démarrage
        return redirect()->route('admin.quiz.reglages')->with('success', 'Les réglages de notation ont été enregistrés.');
    }

    // Détail d'une tentative : infos, score et correction question par question
    public function show(Quiz $quiz)
    {
        $quiz->load(['cours:id,titre', 'user:id,name,email', 'resultats']);
        $reponses = $quiz->reponses()->with(['question.options'])->orderBy('ordre')->get();

        return view('quiz.admin.show', [
            'quiz'     => $quiz,
            'resultat' => $quiz->resultats->first(),
            'reponses' => $reponses,
        ]);
    }

    public function destroy(Quiz $quiz)
    {
        // Les réponses (reponses_quiz) et le résultat (resultats_quiz) partent en cascade
        $quiz->delete();

        return redirect()->route('admin.quiz.index')->with('success', 'La tentative de quiz a été supprimée.');
    }
}