<?php

// app/Models/TentativeEvaluation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Tentative d'un particulier sur le quiz d'un CHAPITRE (chapitre_id renseigné)
 * ou sur le quiz FINAL du cours (chapitre_id = NULL).
 *
 * Totalement séparée du quiz du cours (tables quizzes / resultats_quiz) :
 * aucune page existante ne la voit.
 */
class TentativeEvaluation extends Model
{
    protected $table = 'tentatives_evaluation';

    protected $fillable = [
        'user_id', 'cours_id', 'chapitre_id', 'bareme', 'score', 'commence_le', 'termine_le',
    ];

    protected $casts = [
        'commence_le' => 'datetime',
        'termine_le'  => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cours()
    {
        return $this->belongsTo(Cours::class);
    }

    // NULL pour le quiz final
    public function chapitre()
    {
        return $this->belongsTo(Chapitre::class);
    }

    // Questions tirées, avec l'option choisie pour chacune
    public function reponses()
    {
        return $this->hasMany(ReponseEvaluation::class);
    }

    // Tentatives encore ouvertes (pas encore notées)
    public function scopeEnCours($query)
    {
        return $query->whereNull('termine_le');
    }

    // Tentatives terminées (notées)
    public function scopeTerminees($query)
    {
        return $query->whereNotNull('termine_le');
    }

    // Quiz d'un chapitre précis, ou quiz final quand $chapitreId vaut null
    public function scopePourEtape($query, ?int $chapitreId)
    {
        return $chapitreId ? $query->where('chapitre_id', $chapitreId) : $query->whereNull('chapitre_id');
    }

    public function estFinal(): bool
    {
        return $this->chapitre_id === null;
    }

    public function estTerminee(): bool
    {
        return $this->termine_le !== null;
    }

    // Libellé affiché : « Chapitre 2 — Les réseaux » ou « Quiz final »
    public function titre(): string
    {
        return $this->estFinal()
            ? 'Quiz final'
            : 'Chapitre ' . $this->chapitre->ordre . ' — ' . $this->chapitre->titre;
    }

    // Note en pourcentage (pour la couleur : vert si ≥ 50 %, rouge sinon)
    public function pourcentage(): ?float
    {
        if (!$this->estTerminee() || $this->bareme <= 0) {
            return null;
        }

        return round($this->score / $this->bareme * 100, 1);
    }
}