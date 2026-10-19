<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoursResource extends Model
{
    /**
     * Extensions que le navigateur sait afficher lui-même dans un nouvel onglet.
     * Les autres (Word, PowerPoint, Excel, mov, avi, mkv...) sont à télécharger pour être lues.
     */
    private const EXTENSIONS_NAVIGATEUR = ['pdf', 'jpg', 'jpeg', 'png', 'mp4', 'webm', 'ogg'];

    protected $fillable = [
        'cours_id',
        'type',
        'titre',
        'chemin',
        'url',
        'extension',
        'taille'
    ];

    /**
     * Une ressource appartient à un cours.
     */
    public function cours(): BelongsTo
    {
        return $this->belongsTo(Cours::class);
    }

    /**
     * Vérifie si la ressource est une vidéo.
     */
    public function estVideo(): bool
    {
        return $this->type === 'video';
    }

    /**
     * Fichier stocké que le navigateur peut lire directement (PDF, image, vidéo mp4/webm/ogg).
     */
    public function ouvrableDansNavigateur(): bool
    {
        return $this->chemin
            && in_array(strtolower($this->extension ?? ''), self::EXTENSIONS_NAVIGATEUR, true);
    }

    /**
     * Retourne la taille du fichier dans un format lisible.
     */
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