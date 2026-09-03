<?php

// app/Models/Particulier.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Particulier extends Model
{
    protected $fillable = ['telephone', 'date_de_naissance'];

    // Un Particulier est lié à UN SEUL User (relation 1-1)
    // hasOne = "j'ai un seul..."
    // Permet de faire : $particulier->user
    public function user()
    {
        return $this->hasOne(User::class);
    }
}