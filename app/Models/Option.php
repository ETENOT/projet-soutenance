<?php

// app/Models/Option.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    protected $fillable = ['libelle', 'est_correct', 'question_id'];

    // La table "options" a question_id -> belongsTo
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
