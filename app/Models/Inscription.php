<?php

// app/Models/Inscription.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inscription extends Model
{
    protected $fillable = ['date_inscription', 'statut', 'user_id', 'classe_id','taux_presence', 'attestation_eligible',];

    protected $casts = [
        'attestation_eligible' => 'boolean',
    ];

    /**
     * Scope "payées" : inscriptions dont le paiement est validé (statut = 'valide').
     * Même définition que CoursController::utilisateurAAccesPaye().
     * Utilisation : Inscription::payees()->... ou $classe->inscriptions()->payees()
     */
    public function scopePayees($query)
    {
        return $query->whereHas('paiement', function ($q) {
            $q->where('statut', 'valide');
        });
    }

    // La table "inscriptions" a user_id -> belongsTo
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // La table "inscriptions" a classe_id -> belongsTo
    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }

    // Une Inscription donne lieu à UN SEUL Paiement -> hasOne
    public function paiement()
    {
        return $this->hasOne(Paiement::class);
    }
}