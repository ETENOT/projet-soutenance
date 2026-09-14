<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| routes web
|--------------------------------------------------------------------------
|
| Ici vous pouvez enregistrer les routes web pour votre application. Ces
| routes sont chargées par le RouteServiceProvider dans un groupe qui
| contient le groupe de middleware "web". Maintenant, créez quelque chose !
|
*/

// Charge toutes les routes d'authentification
require __DIR__.'/auth.php';

// Traduction de la langue
Route::get('index/{locale}', [App\Http\Controllers\HomeController::class, 'lang']);

// Page d'accueil
Route::get('/', [App\Http\Controllers\HomeController::class, 'root'])
    ->name('root');

// Dashboard protégé
Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware('auth')
    ->name('dashboard');

// Mise à jour du profil
Route::post('/update-profile/{id}', [App\Http\Controllers\HomeController::class, 'updateProfile'])
    ->middleware('auth')
    ->name('updateProfile');

// Mise à jour du mot de passe
Route::post('/update-password/{id}', [App\Http\Controllers\HomeController::class, 'updatePassword'])
    ->middleware('auth')
    ->name('updatePassword');

// Catalogue des cours — accessible à tout le monde, visiteur anonyme ET utilisateur connecté
// (cf. diagramme de cas d'utilisation : "accéder au programme des cours" est relié aux deux acteurs)
Route::get('/catalogue-cours', [App\Http\Controllers\CoursController::class, 'catalogue'])->name('cours.catalogue');

// Détail d'un cours précis — public aussi, pas besoin d'être connecté pour consulter
Route::get('/cours/{cours}', [App\Http\Controllers\CoursController::class, 'show'])->name('cours.show');

// Gestion des cours et des classes (création/modification/suppression) — réservée à l'admin
// ->middleware(['auth', 'role:admin']) : il faut être connecté ET avoir le rôle admin
// ->prefix('admin') : toutes les URLs de ce groupe commencent par /admin/...
// ->name('admin.') : tous les noms de route de ce groupe commencent par admin....
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/cours', [App\Http\Controllers\CoursController::class, 'index'])->name('cours.index');
    Route::get('/cours/create', [App\Http\Controllers\CoursController::class, 'create'])->name('cours.create');
    Route::post('/cours', [App\Http\Controllers\CoursController::class, 'store'])->name('cours.store');
    Route::get('/cours/{cours}/edit', [App\Http\Controllers\CoursController::class, 'edit'])->name('cours.edit');
    Route::put('/cours/{cours}', [App\Http\Controllers\CoursController::class, 'update'])->name('cours.update');
    Route::delete('/cours/{cours}', [App\Http\Controllers\CoursController::class, 'destroy'])->name('cours.destroy');

    // Classes, imbriquées sous un cours (une classe appartient toujours à un cours)
    Route::prefix('cours/{cours}/classes')->name('cours.classes.')->group(function () {
        Route::get('/', [App\Http\Controllers\ClasseController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\ClasseController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\ClasseController::class, 'store'])->name('store');
        Route::get('/{classe}/edit', [App\Http\Controllers\ClasseController::class, 'edit'])->name('edit');
        Route::put('/{classe}', [App\Http\Controllers\ClasseController::class, 'update'])->name('update');
        Route::delete('/{classe}', [App\Http\Controllers\ClasseController::class, 'destroy'])->name('destroy');

        // Cas d'utilisation "Reporter sessions d'une classe" — distinct de update()
        Route::put('/{classe}/reporter', [App\Http\Controllers\ClasseController::class, 'reporter'])->name('reporter');

        // Ajout/retrait manuel d'un utilisateur dans la classe (action admin directe)
        Route::post('/{classe}/inscrits', [App\Http\Controllers\ClasseController::class, 'ajouterUtilisateur'])->name('inscrits.store');
        Route::delete('/{classe}/inscrits/{user}', [App\Http\Controllers\ClasseController::class, 'retirerUtilisateur'])->name('inscrits.destroy');
    });
});

// Route générale — DOIT rester la toute dernière route du fichier,
// sinon elle intercepte tout ce qui n'a pas encore été défini avant elle
Route::get('{any}', [App\Http\Controllers\HomeController::class, 'index'])
    ->name('index');