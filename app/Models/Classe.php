<?php

// app/Models/Classe.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classe extends Model
{
    // Ces champs correspondent aux informations d'une session de formation.
    protected $fillable = ['nom', 'capacite_max', 'date_debut', 'date_fin', 'lieu', 'cours_id'];
    // Force Laravel à traiter ces colonnes comme des objets Carbon
    // plutôt que de simples chaînes de caractères, pour pouvoir utiliser
    // ->format() directement dans les vues
    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
    ];

    // La table "classes" a la colonne cours_id -> belongsTo
    public function cours()
    {
        return $this->belongsTo(Cours::class);
    }

    // Une Classe peut avoir plusieurs Inscriptions (élèves inscrits)
    public function inscriptions()
    {
        return $this->hasMany(Inscription::class);
    }
}