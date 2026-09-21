<?php

namespace App\Models;

use App\Notifications\VerifyEmailWithCode;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

// IMPORTANT : on extends Authenticatable (pas Model) car c'est LUI qui se connecte à l'application
//
// "implements MustVerifyEmail" active le middleware 'verified' (déjà enregistré
// dans app/Http/Kernel.php -> Illuminate\Auth\Middleware\EnsureEmailIsVerified).
// hasVerifiedEmail()/markEmailAsVerified() existent déjà sans rien faire de plus :
// elles viennent du trait interne à Illuminate\Foundation\Auth\User (la classe
// "Authenticatable" ci-dessus). Seule sendEmailVerificationNotification() est
// surchargée plus bas pour envoyer notre code à 6 chiffres au lieu du lien signé
// par défaut.
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;
        // Délai minimum (en secondes) entre deux demandes de code, pour éviter
    // qu'un utilisateur (ou un bot) ne spamme le bouton "Renvoyer le code" —
    // en plus du throttle:6,1 sur la route, qui limite par IP mais autoriserait
    // quand même 6 clics rapprochés sans ce délai.
    private const RESEND_COOLDOWN_SECONDS = 60;

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
        /**
     * Génère un code à 6 chiffres et l'enregistre (haché) dans la table
     * verification_codes, indexée par email — même logique que password_resets.
     * updateOrInsert() écrase l'éventuel code précédent : un seul code actif
     * à la fois par utilisateur.
     */
    public function generateVerificationCode(): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('verification_codes')->updateOrInsert(
            ['email' => $this->email],
            ['code' => Hash::make($code), 'created_at' => now()]
        );

        return $code;
    }

    /**
     * Retourne 'valid', 'expired' (aucun code actif, ou créé il y a plus de
     * 15 min) ou 'invalid' (code actif et non expiré, mais mal saisi) — pour
     * pouvoir donner un message différent selon le cas côté contrôleur.
     */
    public function verificationCodeStatus(string $code): string
    {
        $record = DB::table('verification_codes')->where('email', $this->email)->first();

        if (! $record || Carbon::parse($record->created_at)->addMinutes(5)->isPast()) {
            return 'expired';
        }

        return Hash::check($code, $record->code) ? 'valid' : 'invalid';
    }

    /**
     * Supprime la ligne une fois le code utilisé avec succès.
     */
    public function clearVerificationCode(): void
    {
        DB::table('verification_codes')->where('email', $this->email)->delete();
    }

    /**
     * Remplace la notification native (lien signé) par l'envoi d'un code à 6
     * chiffres. Appelée automatiquement à l'inscription (event Registered,
     * déjà branché dans EventServiceProvider) et au clic sur "Renvoyer le code".
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailWithCode($this->generateVerificationCode()));
    }
    
    /**
     * Nombre de secondes restantes avant de pouvoir redemander un code.
     * Retourne 0 si aucun code n'est en attente, ou si le délai est écoulé.
     *
     * Calculé à partir de created_at (déjà stocké dans verification_codes
     * pour l'expiration à 5 min) : pas besoin d'une colonne dédiée pour ce
     * second délai, plus court, on réutilise la même valeur.
     */
    public function secondsUntilCanResendVerificationCode(): int
    {
        $record = DB::table('verification_codes')->where('email', $this->email)->first();

        if (! $record) {
            return 0;
        }

        $secondsElapsed = Carbon::parse($record->created_at)->diffInSeconds(now());
        $remaining = self::RESEND_COOLDOWN_SECONDS - $secondsElapsed;

        return max(0, $remaining);
    }
}
