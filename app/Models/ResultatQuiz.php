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
}
