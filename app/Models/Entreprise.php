<?php

// app/Models/Entreprise.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Entreprise extends Model
{
    protected $fillable = ['raison_sociale', 'adresse', 'contact_principal', 'secteur_activite'];

    // Une Entreprise peut avoir PLUSIEURS Users (contacts)
    public function users()
    {
        return $this->hasMany(User::class);
    }

    // Une Entreprise peut avoir PLUSIEURS Devis
    public function devis()
    {
        return $this->hasMany(Devis::class);
    }
}