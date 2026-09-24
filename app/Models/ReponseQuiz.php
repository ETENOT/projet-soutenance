<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReponseQuiz extends Model
{
    // Le nom de la table ne suit pas la convention Eloquent standard
    // (elle ne s'appelle pas "reponse_quizzes"), donc on la précise explicitement
    protected $table = 'reponses_quiz';

    protected $fillable = ['quiz_id', 'question_id', 'option_id', 'ordre'];

    // La tentative à laquelle appartient cette réponse
    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    // La question tirée
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    // L'option choisie (peut être null si pas encore répondu)
    public function option()
    {
        return $this->belongsTo(Option::class);
    }
}