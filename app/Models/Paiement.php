<?php

// app/Models/Paiement.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    protected $fillable = ['montant', 'inscription_id'];

    // La table "paiements" a inscription_id -> belongsTo
    public function inscription()
    {
        return $this->belongsTo(Inscription::class);
    }
}
