<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chapitre extends Model
{
    protected $fillable = [
        'titre',
        'contenu',
        'ordre',
        'cours_id',
        'quiz_note_sur',   // nombre de questions du quiz de ce chapitre (NULL = toutes)
    ];

    /**
     * Un chapitre appartient à un cours.
     */
    public function cours(): BelongsTo
    {
        return $this->belongsTo(Cours::class);
    }

    /**
     * Questions de la banque rattachées à ce chapitre (quiz du chapitre).
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    /**
     * Tentatives des particuliers sur le quiz de ce chapitre.
     */
    public function tentativesEvaluation(): HasMany
    {
        return $this->hasMany(TentativeEvaluation::class);
    }
}