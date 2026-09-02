<?php

// app/Models/ResultatQuiz.php
class ResultatQuiz extends Model
{
    // Sans ça, Eloquent chercherait "resultat_quizzes" au lieu de "resultats_quiz"
    protected $table = 'resultats_quiz';

    protected $fillable = ['score', 'quiz_id', 'utilisateur_id'];

    // belongsTo car "resultats_quiz" contient quiz_id
    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    // belongsTo car "resultats_quiz" contient aussi utilisateur_id
    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class);
    }
}
