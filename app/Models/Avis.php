<?php

// app/Models/Avis.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Avis extends Model
{
    // "avis" est invariable en français : on force le nom de la table
    protected $table = 'avis';

    protected $fillable = ['user_id', 'cours_id', 'note', 'commentaire'];

    // La table "avis" a user_id -> belongsTo
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // La table "avis" a cours_id -> belongsTo
    public function cours()
    {
        return $this->belongsTo(Cours::class);
    }
}