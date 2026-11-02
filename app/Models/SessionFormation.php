<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionFormation extends Model
{
    protected $table = 'sessions_formation';
    protected $fillable = ['nom', 'description', 'entreprise_id', 'date_debut', 'date_fin'];
    protected $casts = ['date_debut' => 'date', 'date_fin' => 'date'];

    public function entreprise() { return $this->belongsTo(Entreprise::class); }
    public function classes()    { return $this->hasMany(Classe::class, 'session_id'); }

    // Début = 1re classe qui commence, fin = dernière classe qui termine.
    // saveQuietly : évite toute boucle d'évènements.
    public function recalculerDates(): void
    {
        $this->forceFill([
            'date_debut' => $this->classes()->min('date_debut'),
            'date_fin'   => $this->classes()->max('date_fin'),
        ])->saveQuietly();
        $this->unsetRelation('classes');
    }

    // État calculé : terminée si TOUTES les classes le sont, à venir si aucune n'a commencé
    // (ou aucune classe), en cours sinon.
    public function getEtatAttribute(): string
    {
        $etats = $this->classes->map->etat;
        if ($etats->isEmpty()) return 'a_venir';
        if ($etats->every(fn ($e) => $e === 'terminee')) return 'terminee';
        if ($etats->every(fn ($e) => $e === 'a_venir')) return 'a_venir';
        return 'en_cours';
    }
    // + getEtatLibelleAttribute() / getEtatCouleurAttribute() (comme Classe)
}