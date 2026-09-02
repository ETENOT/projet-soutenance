<?php

// app/Models/Question.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = ['enonce', 'cours_id'];

    // La table "questions" a cours_id -> belongsTo
    public function cours()
    {
        return $this->belongsTo(Cours::class);
    }

    // Une Question a plusieurs Options de réponse (QCM)
    public function options()
    {
        return $this->hasMany(Option::class);
    }
}
