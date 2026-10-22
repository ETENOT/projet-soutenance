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

    /**
     * Inscription payée = un paiement au statut "valide" existe (même définition que scopePayees).
     * Sert à afficher "Payé" / "En attente" dans la liste et à protéger les suppressions.
     */
    public function estPayee(): bool
    {
        return $this->paiement?->statut === 'valide';
    }

    // Une inscription éligible donne lieu à UNE attestation (créée à la fermeture de la classe)
    public function attestation()
    {
        return $this->hasOne(Attestation::class);
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