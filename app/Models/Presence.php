<?php

// app/Models/Presence.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Une ligne de la feuille de présence : un stagiaire, une journée, présent ou absent.
class Presence extends Model
{
    // Les colonnes qu'on autorise à remplir avec create() / updateOrCreate()
    protected $fillable = ['classe_id', 'user_id', 'date_seance', 'present'];

    protected $casts = [
        'date_seance' => 'date',
        'present' => 'boolean',
    ];

    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}