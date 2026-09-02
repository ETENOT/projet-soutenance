<?php

// app/Models/Utilisateur.php
class Utilisateur extends Model
{
    protected $fillable = ['nom', 'email', 'mot_de_passe', 'role_id', 'particulier_id', 'entreprise_id'];

    // La table "utilisateurs" contient la colonne role_id (clé étrangère)
    // => c'est TOUJOURS la table qui a la colonne "xxx_id" qui utilise belongsTo
    // belongsTo = "j'appartiens à..."
    // Permet de faire : $utilisateur->role
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // Même logique : utilisateurs a particulier_id -> belongsTo
    public function particulier()
    {
        return $this->belongsTo(Particulier::class);
    }

    // Même logique : utilisateurs a entreprise_id -> belongsTo
    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }

    // Ici on repasse en hasMany car c'est les AUTRES tables (notifications, 
    // inscriptions, resultats_quiz) qui ont utilisateur_id, pas l'inverse
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function inscriptions()
    {
        return $this->hasMany(Inscription::class);
    }

    public function resultatsQuiz()
    {
        return $this->hasMany(ResultatQuiz::class);
    }
}