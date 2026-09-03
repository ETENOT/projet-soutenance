<?php

// app/Models/Inscription.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inscription extends Model
{
    protected $fillable = ['date_inscription', 'user_id', 'classe_id'];

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