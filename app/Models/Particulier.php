<?php

// app/Models/Particulier.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Particulier extends Model
{
    protected $fillable = ['telephone', 'date_de_naissance', 'entreprise_id'];

    // Un Particulier est lié à UN SEUL User (relation 1-1)
    // hasOne = "j'ai un seul..."
    // Permet de faire : $particulier->user
    public function user()
    {
        return $this->hasOne(User::class);
    }

    // L'entreprise dont ce particulier est membre (null = aucune).
    // La table particuliers a entreprise_id -> belongsTo
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }
}