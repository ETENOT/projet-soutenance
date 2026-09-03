<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

// IMPORTANT : on extends Authenticatable (pas Model) car c'est LUI qui se connecte à l'application
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'role_id',
        'particulier_id',
        'entreprise_id',
    ];

    // Champs cachés quand on convertit l'utilisateur en JSON/array
    // (évite d'exposer le mot de passe ou le token de connexion "remember me")
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

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
