<?php

// app/Models/Cours.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cours extends Model
{
    // IMPORTANT : par défaut Laravel met le nom de classe au pluriel
    // pour deviner la table ("Cours" -> il chercherait "cour", ce qui est faux)
    // Donc on force le vrai nom de table ici
    protected $table = 'cours';

    protected $fillable = [
    'titre',
    'categorie_id',
    'description',
    'image',
    'duree',
    'prix_particulier',
    'prix_entreprise',
    'statut',
    'note_sur',   // nombre de questions tirées pour le quiz (NULL = toute la banque)
];

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

    // Un Cours a plusieurs Ressources
    public function resources()
    {
        return $this->hasMany(CoursResource::class, 'cours_id');
    }

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(Categorie::class);
    }

    public function chapitres(): HasMany
    {
        return $this->hasMany(Chapitre::class)->orderBy('ordre');
    }

    // Un Cours reçoit plusieurs Avis
    public function avis()
    {
        return $this->hasMany(Avis::class);
    }

}