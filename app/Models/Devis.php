<?php

// app/Models/Devis.php
class Devis extends Model
{
    // Même souci que "Cours" : "Devis" au pluriel reste "devis" en français,
    // mais Eloquent (en anglais) chercherait "devi". On force le vrai nom.
    protected $table = 'devis';

    protected $fillable = ['date_demande', 'statut', 'entreprise_id'];

    // La table "devis" a entreprise_id -> belongsTo
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }
}