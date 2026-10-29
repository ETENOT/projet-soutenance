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

    // Les profils « particulier » rattachés à cette entreprise
    public function particuliers()
    {
        return $this->hasMany(Particulier::class);
    }

    // Les MEMBRES de l'entreprise (comptes utilisateurs des particuliers rattachés).
    // Particulier.entreprise_id -> Particulier.id <- User.particulier_id
    // Liste que le futur module Devis proposera pour inscrire un membre.
    public function membres()
    {
        return $this->hasManyThrough(User::class, Particulier::class, 'entreprise_id', 'particulier_id');
    }
}