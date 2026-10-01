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
    ];

    /**
     * Un chapitre appartient à un cours.
     */
    public function cours(): BelongsTo
    {
        return $this->belongsTo(Cours::class);
    }

    /**
     * Un chapitre possède plusieurs ressources.
     */
    public function resources(): HasMany
    {
        return $this->hasMany(CoursResource::class);
    }
}