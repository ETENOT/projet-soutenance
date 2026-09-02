<?php

// app/Models/Utilisateur.php

namespace App\Models;

// On importe la classe Authenticatable de Laravel et on la renomme "Authenticatable"
// C'est elle qui donne à ce model toutes les capacités de connexion/déconnexion (login, logout, session...)
use Illuminate\Foundation\Auth\User as Authenticatable;

// Permet d'envoyer des notifications à cet utilisateur (email, etc.)
use Illuminate\Notifications\Notifiable;

// IMPORTANT : on extends Authenticatable (pas Model) car c'est LUI qui se connecte à l'application
class Utilisateur extends Authenticatable
{
    use Notifiable;

    // Champs cachés quand on convertit l'utilisateur en JSON/array
    // (évite d'exposer le mot de passe ou le token de connexion "remember me")
    protected $hidden = ['mot_de_passe', 'remember_token'];

    // Laravel s'attend par défaut à une colonne "password" pour vérifier le mot de passe.
    // Comme notre colonne s'appelle "mot_de_passe", on lui dit explicitement où la trouver.
    public function getAuthPassword()
    {
        return $this->mot_de_passe;
    }

    // Champs qu'on autorise à remplir en masse (via create()/update())
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