<?php

namespace App\Services;

use App\Models\Quiz;
use App\Models\ResultatQuiz;
use Illuminate\Support\Carbon;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\DB;


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
        $limite = Carbon::parse($quiz->date)->setTimeFromTimeString($quiz->heure_fin);

        // Si l'heure de fin est "plus petite" que l'heure de début, la deadline
        // est passée après minuit : elle tombe le lendemain.
        if ($quiz->heure_fin < $quiz->heure_debut) {
            $limite->addDay();
        }

        return $limite;
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

        $resultat = ResultatQuiz::create([
            'score'   => $score,
            'quiz_id' => $quiz->id,
            'user_id' => $quiz->user_id, // null pour un visiteur
        ]);

        // Un visiteur n'a pas encore de compte : sa notification sera créée à l'inscription
        if ($quiz->user_id) {
            Notification::create([
                'user_id' => $quiz->user_id,
                'message' => 'Quiz terminé : « ' . $quiz->cours->titre . ' ». Votre note : '
                    . $score . ' / ' . $quiz->bareme . '.',
            ]);
        }

        return $resultat;
    }

    // Tentative en cours d'un utilisateur connecté OU d'un visiteur (via son token de session)
    public function tentativeEnCours(?User $user, ?int $coursId = null, ?string $token = null): ?Quiz
    {
        // Sans utilisateur ni token : on s'arrête, sinon where('visiteur_token', null)
        // deviendrait "IS NULL" et renverrait les quiz de tous les visiteurs
        if (!$user && !$token) {
            return null;
        }

        $tentatives = Quiz::query()
            ->when(
                $user,
                fn ($q) => $q->where('user_id', $user->id),
                fn ($q) => $q->whereNull('user_id')->where('visiteur_token', $token)
            )
            ->when($coursId, fn ($q) => $q->where('cours_id', $coursId))
            ->whereDoesntHave('resultats')
            ->with('cours')
            ->latest('id')
            ->get();

        $enCours = null;
        foreach ($tentatives as $quiz) {
            if ($this->estExpire($quiz)) {
                $this->finaliser($quiz);
                continue;
            }
            $enCours ??= $quiz;
        }
        return $enCours;
    }
    // Le visiteur a-t-il au moins un quiz terminé qui attend son inscription ?
    public function visiteurAResultatEnAttente(?string $token): bool
    {
        return $token && Quiz::whereNull('user_id')
            ->where('visiteur_token', $token)
            ->whereHas('resultats')
            ->exists();
    }

    // Rattache les quiz anonymes terminés au nouvel utilisateur + crée les notifications
    public function rattacherVisiteur(User $user, string $token): void
    {
        DB::transaction(function () use ($user, $token) {
            $quizzes = Quiz::whereNull('user_id')
                ->where('visiteur_token', $token)
                ->with(['cours', 'resultats'])
                ->get();

            foreach ($quizzes as $quiz) {
                $resultat = $quiz->resultats->first();

                if (
                    !$resultat) {
                    // Tentative jamais terminée : on ne la reprend pas
                    $quiz->delete();
                    continue;
                }

                $quiz->update(['user_id' => $user->id, 'visiteur_token' => null]);
                $resultat->update(['user_id' => $user->id]);

                Notification::create([
                    'user_id' => $user->id,
                    'message' => 'Quiz terminé : « ' . $quiz->cours->titre . ' ». Votre note : '
                        . $resultat->score . ' / ' . $quiz->bareme . '.',
                ]);
            }
        });
    }
}