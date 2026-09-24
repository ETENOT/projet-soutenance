<?php

namespace App\Services;

use App\Models\Quiz;
use App\Models\ResultatQuiz;
use Illuminate\Support\Carbon;

// Centralise toute la logique de "fin de tentative" (calcul de la deadline,
// détection du dépassement, calcul du score) pour qu'elle soit identique
// que la clôture vienne du contrôleur (l'utilisateur revient) ou de la
// commande planifiée (personne ne revient, clôture automatique).
class QuizFinalisationService
{
    // Reconstruit la date/heure exacte de la deadline à partir des colonnes
    // "date" (ex: 2026-09-22) et "heure_fin" (ex: 15:32:10) de la tentative
    public function limite(Quiz $quiz): Carbon
    {
        return Carbon::parse($quiz->date)->setTimeFromTimeString($quiz->heure_fin);
    }

    // true si l'heure actuelle a dépassé la deadline calculée ci-dessus
    public function estExpire(Quiz $quiz): bool
    {
        return now()->greaterThanOrEqualTo($this->limite($quiz));
    }

    // Calcule le score et crée la ligne resultats_quiz correspondante.
    // Appelée aussi bien pour une clôture "normale" (bouton Terminer)
    // que pour une clôture automatique (temps écoulé).
    public function finaliser(Quiz $quiz): ResultatQuiz
    {
        // On récupère TOUTES les questions posées (répondues ou non) :
        // une question sans réponse doit quand même compter comme "0 point"
        // dans le calcul, pas être ignorée
        $reponses = $quiz->reponses()->with(['question.options'])->get();

        // Chaque bonne réponse vaut exactement 1 point.
        // Une réponse fausse (option_id pointe vers une mauvaise option)
        // ou une question laissée sans réponse (option_id = null) valent 0 point
        // -> dans les deux cas, la comparaison ci-dessous renvoie simplement "false"
        $bonnes = $reponses->filter(function ($r) {
            $bonneOption = $r->question->options->firstWhere('est_correct', true);
            return $bonneOption && $r->option_id === $bonneOption->id;
        })->count();

        // Le score est directement le nombre de bonnes réponses.
        // Exemple : 10 questions répondues sur 30 posées, disons 7 bonnes
        // -> score = 7, affiché ensuite comme "7 / 30" (30 = le barème de la tentative)
        $score = $bonnes;

        return ResultatQuiz::create([
            'score' => $score,
            'quiz_id' => $quiz->id,
            'user_id' => $quiz->user_id,
        ]);
    }
}