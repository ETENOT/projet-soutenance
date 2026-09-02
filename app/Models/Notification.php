<?php

// app/Models/Notification.php
class Notification extends Model
{
    protected $fillable = ['message', 'est_lue', 'utilisateur_id'];

    // La table "notifications" a utilisateur_id -> belongsTo
    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class);
    }
}
