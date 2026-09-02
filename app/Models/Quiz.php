<?php

// app/Models/Quiz.php
class Quiz extends Model
{
    protected $fillable = ['date', 'heure_debut', 'heure_fin', 'cours_id'];

    // La table "quizzes" a cours_id -> belongsTo
    public function cours()
    {
        return $this->belongsTo(Cours::class);
    }

    // Un Quiz peut avoir plusieurs résultats (un par utilisateur qui l'a passé)
    public function resultats()
    {
        return $this->hasMany(ResultatQuiz::class);
    }
}