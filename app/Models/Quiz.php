<?php

// app/Models/Quiz.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = ['date', 'heure_debut', 'heure_fin', 'cours_id', 'user_id', 'bareme'];

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

    // belongsTo car "quizzes" contient aussi user_id
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Les questions tirées pour cette tentative, avec la réponse (option_id) choisie pour chacune.
    // hasMany car une tentative (un Quiz) a plusieurs lignes reponses_quiz (une par question tirée)
    public function reponses()
    {
        return $this->hasMany(ReponseQuiz::class);
    }
}