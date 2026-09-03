<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
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
});

// Route accessible UNIQUEMENT si l'utilisateur EST connecté (middleware "auth")
// Logique : on ne peut pas se déconnecter si on n'est pas connecté
Route::middleware('auth')->group(function () {

    // Déconnecte l'utilisateur et détruit sa session
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});