<?php

use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use Illuminate\Support\Facades\Route;

// Ce fichier remplace l'ancien Auth::routes() (fourni par laravel/ui, qu'on a retiré).
// Il définit à la main les routes de connexion/inscription, reliées aux controllers
// Breeze déjà présents dans app/Http/Controllers/Auth/.

// Routes accessibles UNIQUEMENT si l'utilisateur n'est PAS connecté (middleware "guest")
// Un utilisateur déjà connecté n'a pas besoin de revoir /login ou /register
Route::middleware('guest')->group(function () {

    // Affiche le formulaire d'inscription
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');

    // Traite la soumission du formulaire d'inscription (création du User)
    Route::post('register', [RegisteredUserController::class, 'store']);

    // Affiche le formulaire de connexion
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');

    // Traite la soumission du formulaire de connexion
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // --- Mot de passe oublié ---

    // Affiche le formulaire "Mot de passe oublié" (saisie de l'e-mail)
    // C'est cette route que le lien du login.blade.php recherche via Route::has('password.request')
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    // Traite l'envoi du lien de réinitialisation par e-mail
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    // Affiche le formulaire de saisie du nouveau mot de passe (lien reçu par e-mail avec token)
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    // Traite la soumission du nouveau mot de passe
    // Nom "password.store" (et non "password.update") pour ne pas entrer en conflit
    // avec la route de changement de mot de passe d'un utilisateur déjà connecté
    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

// Route accessible UNIQUEMENT si l'utilisateur EST connecté (middleware "auth")
// Logique : on ne peut pas se déconnecter si on n'est pas connecté
Route::middleware('auth')->group(function () {

    // Affiche la page de vérification de l'e-mail
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    // Vérifie le code de vérification par e-mail
    Route::post('verify-email', VerifyEmailController::class)
        ->middleware('throttle:10,1')
        ->name('verification.verify');

    // Renvoie le code de vérification par e-mail
    // throttle:10,1 = anti brute-force : 10 tentatives par minute maximum.
    Route::post('verify-email/resend', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
        
    // Déconnecte l'utilisateur et détruit sa session
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});