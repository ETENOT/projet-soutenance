<?php

// app/Models/Inscription.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

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

    protected static function booted(): void
    {
        // Toute inscription à une classe de SESSION est rattachée à l'entreprise de la session,
        // quel que soit l'écran admin utilisé. Les inscriptions personnelles gardent null.
        static::creating(function (Inscription $inscription) {
            $classe = $inscription->classe_id ? Classe::with('session')->find($inscription->classe_id) : null;

            // Filet de sécurité, quel que soit l'écran : une classe de session n'accepte que
            // les membres de l'entreprise de la session
            if ($classe && $inscription->user_id && ($erreur = $classe->erreurAppartenance(User::findOrFail($inscription->user_id)))) {
                throw ValidationException::withMessages(['user_id' => $erreur]);
            }

            if ($inscription->via_entreprise_id === null) {
                $inscription->via_entreprise_id = $classe?->session?->entreprise_id;
            }
        });
    }

    public function viaEntreprise() { return $this->belongsTo(Entreprise::class, 'via_entreprise_id'); }
    public function scopePersonnelles($query) { return $query->whereNull('via_entreprise_id'); }

    // Nommé « faitesParEntreprise » et non « viaEntreprise » : un scope du même nom que la relation
    // ferait planter l'appel statique Inscription::viaEntreprise(...)
    public function scopeFaitesParEntreprise($query, ?int $entrepriseId = null)
    {
        return $query->when($entrepriseId,
            fn ($q) => $q->where('via_entreprise_id', $entrepriseId),
            fn ($q) => $q->whereNotNull('via_entreprise_id'));
    }
}