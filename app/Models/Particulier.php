<?php

// app/Models/Particulier.php
class Particulier extends Model
{
    protected $fillable = ['telephone', 'date_de_naissance'];

    // Un Particulier est lié à UN SEUL Utilisateur (relation 1-1)
    // hasOne = "j'ai un seul..."
    // Permet de faire : $particulier->utilisateur
    public function utilisateur()
    {
        return $this->hasOne(Utilisateur::class);
    }
}