<?php

// app/Models/Option.php
class Option extends Model
{
    protected $fillable = ['libelle', 'est_correct', 'question_id'];

    // La table "options" a question_id -> belongsTo
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
