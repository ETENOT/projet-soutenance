<?php

// app/Models/ReponseEvaluation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Une question tirée pour une tentative de quiz de chapitre / quiz final,
// avec l'option choisie (NULL tant que le particulier n'a pas répondu).
class ReponseEvaluation extends Model
{
    protected $table = 'reponses_evaluation';

    protected $fillable = ['tentative_evaluation_id', 'question_id', 'option_id', 'ordre'];

    public function tentative()
    {
        return $this->belongsTo(TentativeEvaluation::class, 'tentative_evaluation_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function option()
    {
        return $this->belongsTo(Option::class);
    }

    // true si l'option choisie est la bonne réponse de la question
    public function estCorrecte(): bool
    {
        $bonne = $this->question->options->firstWhere('est_correct', true);

        return $bonne !== null && $this->option_id !== null && (int) $this->option_id === (int) $bonne->id;
    }
}