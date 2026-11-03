<?php

// app/Models/Question.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    // chapitre_id : facultatif, rattache la question au quiz d'un chapitre (NULL = banque générale)
    protected $fillable = ['enonce', 'cours_id', 'chapitre_id'];

    // La table "questions" a cours_id -> belongsTo
    public function cours()
    {
        return $this->belongsTo(Cours::class);
    }

    // Chapitre auquel la question est rattachée (NULL = question générale du cours)
    public function chapitre()
    {
        return $this->belongsTo(Chapitre::class);
    }

    // Une Question a plusieurs Options de réponse (QCM)
    public function options()
    {
        return $this->hasMany(Option::class);
    }
    // Les lignes reponses_quiz où cette question a été posée (une par tentative concernée)
    // Sert à compter "posée N fois" et à avertir l'admin avant une modification/suppression
    public function reponses()
    {
        return $this->hasMany(ReponseQuiz::class);
    }
}