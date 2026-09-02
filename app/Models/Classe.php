<?php

// app/Models/Classe.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classe extends Model
{
    protected $fillable = ['nom', 'capacite_max', 'date_debut', 'date_fin', 'cours_id'];

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