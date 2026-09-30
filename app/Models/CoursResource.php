<?php

// app/Models/CoursResource.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoursResource extends Model
{
    protected $fillable = ['cours_id', 'type', 'titre', 'chemin', 'url', 'extension', 'taille'];

    public function cours()
    {
        return $this->belongsTo(Cours::class);
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