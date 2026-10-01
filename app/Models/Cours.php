<?php

// app/Models/Cours.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Cours extends Model
{
    // IMPORTANT : par défaut Laravel met le nom de classe au pluriel
    // pour deviner la table ("Cours" -> il chercherait "cour", ce qui est faux)
    // Donc on force le vrai nom de table ici
    protected $table = 'cours';

        protected $fillable = [
    'titre',
    'categorie',
    'description',
    'programme',
    'image',
    'duree',
    'niveau',
    'prix_particulier',
    'prix_entreprise',
    'statut',
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

    /**
     * Transforme le texte libre de 'programme' en grands points + sous-points,
     * pour un affichage structuré côté vue publique.
     * Convention : une ligne commençant par "## " = un grand point (un module),
     * une ligne commençant par "- " juste après = un sous-point de ce module.
     * Si le texte ne suit pas cette convention (ancien format en texte libre),
     * retourne un tableau vide — la vue garde alors l'ancien affichage en texte brut.
     */
    public function getPlanSectionsAttribute(): array
    {
        if (empty($this->programme)) {
            return [];
        }

        $lignes = preg_split('/\r\n|\r|\n/', trim($this->programme));
        $sections = [];

        foreach ($lignes as $ligne) {
            $ligne = trim($ligne);

            if ($ligne === '') {
                continue;
            }

            if (str_starts_with($ligne, '## ')) {
                $sections[] = [
                    'titre' => trim(substr($ligne, 3)),
                    'points' => [],
                ];
                continue;
            }

            if (str_starts_with($ligne, '- ') && !empty($sections)) {
                $sections[count($sections) - 1]['points'][] = trim(substr($ligne, 2));
            }
        }

        return $sections;
    }

    public function chapitres(): HasMany
    {
        return $this->hasMany(Chapitre::class)->orderBy('ordre');
    }
}