<?php

// app/Models/Paiement.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    // Pas besoin de forcer $table ici : "Paiement" -> "paiements" au pluriel
    // correspond déjà au vrai nom de la table (contrairement à Cours/Devis).

    protected $fillable = ['montant', 'inscription_id'];

    // La table "paiements" a inscription_id -> belongsTo
    public function inscription()
    {
        return $this->belongsTo(Inscription::class);
    }
}