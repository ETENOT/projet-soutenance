<?php

// app/Models/Entreprise.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Entreprise extends Model
{
    protected $fillable = ['raison_sociale', 'adresse', 'contact_principal', 'secteur_activite'];

    // Une Entreprise peut avoir PLUSIEURS Utilisateurs (contacts)
    public function utilisateurs()
    {
        return $this->hasMany(Utilisateur::class);
    }

    // Une Entreprise peut avoir PLUSIEURS Devis
    public function devis()
    {
        return $this->hasMany(Devis::class);
    }
}