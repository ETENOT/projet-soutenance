<?php

// app/Models/JourExclu.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Un jour que l'admin ou le formateur a choisi de ne PAS compter dans le taux de présence.
class JourExclu extends Model
{
    // Nom de table explicite : "jour_exclus" serait déduit automatiquement, ce qui ne correspond pas
    protected $table = 'jours_exclus';

    protected $fillable = ['classe_id', 'date'];

    protected $casts = [
        'date' => 'date',
    ];

    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }
}