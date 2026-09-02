<?php

// app/Models/Cours.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cours extends Model
{
    // IMPORTANT : par défaut Laravel met le nom de classe au pluriel 
    // pour deviner la table ("Cours" -> il chercherait "cour", ce qui est faux)
    // Donc on force le vrai nom de table ici
    protected $table = 'cours';

    protected $fillable = ['titre', 'prix'];

    // Un Cours a plusieurs Classes (sessions)
    public function classes()
    {
        return $this->hasMany(Classe::class);
    }

    // Un Cours a plusieurs Quiz
    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }

    // Un Cours a plusieurs Questions (banque de questions partagée)
    public function questions()
    {
        return $this->hasMany(Question::class);
    }
}