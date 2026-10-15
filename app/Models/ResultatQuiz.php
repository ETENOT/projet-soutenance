<?php

// app/Models/ResultatQuiz.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResultatQuiz extends Model
{
    // Sans ça, Eloquent chercherait "resultat_quizzes" au lieu de "resultats_quiz"
    protected $table = 'resultats_quiz';

    protected $fillable = ['score', 'quiz_id', 'user_id'];

    // belongsTo car "resultats_quiz" contient quiz_id
    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    // belongsTo car "resultats_quiz" contient aussi user_id
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Points du graphique de progression d'un utilisateur sur UN cours,
     * du quiz le plus ancien au plus récent (ordre chronologique).
     *
     * Le barème peut changer d'une tentative à l'autre (il vaut le nombre de questions
     * tirées, voir QuizAttemptController::demarrer), donc la courbe est tracée en
     * pourcentage (score / barème) pour que les tentatives restent comparables.
     * Le score brut et le barème sont aussi renvoyés pour l'infobulle du graphique.
     */
    public static function progressionPour(int $userId, int $coursId): array
    {
        return static::where('user_id', $userId)
            ->whereHas('quiz', fn ($q) => $q->where('cours_id', $coursId))
            ->with('quiz')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->values()
            ->map(function (self $resultat, int $index) {
                $bareme = (int) ($resultat->quiz->bareme ?? 0);

                return [
                    'tentative'   => $index + 1,
                    'date'        => $resultat->created_at->format('d/m/Y'),
                    'heure'       => $resultat->created_at->format('H:i'),
                    'score'       => (int) $resultat->score,
                    'bareme'      => $bareme,
                    'pourcentage' => $bareme > 0 ? round($resultat->score / $bareme * 100, 1) : 0,
                ];
            })
            ->all();
    }
}
