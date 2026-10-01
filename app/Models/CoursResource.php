<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoursResource extends Model
{
    protected $fillable = [
        'cours_id',
        'chapitre_id',
        'type',
        'titre',
        'chemin',
        'url',
        'extension',
        'taille'
    ];

    public function cours(): BelongsTo
    {
        return $this->belongsTo(Cours::class);
    }

    public function chapitre(): BelongsTo
    {
        return $this->belongsTo(Chapitre::class);
    }

    public function estVideo(): bool
    {
        return $this->type === 'video';
    }

    public function getTailleLisibleAttribute(): ?string
    {
        if (is_null($this->taille)) {
            return null;
        }

        $octets = $this->taille;

        if ($octets < 1024) {
            return $octets . ' o';
        }

        if ($octets < 1024 * 1024) {
            return round($octets / 1024, 1) . ' Ko';
        }

        return round($octets / (1024 * 1024), 1) . ' Mo';
    }
}