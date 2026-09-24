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
    ->middleware('auth', 'verified')
    ->name('dashboard');

// Mise à jour du profil
Route::get('/pages-profile', [App\Http\Controllers\HomeController::class, 'index'])
    ->middleware('auth')
    ->name('profile.edit');

Route::post('/update-profile/{id}', [App\Http\Controllers\HomeController::class, 'updateProfile'])
    ->middleware('auth')
    ->name('updateProfile');

// Mise à jour du mot de passe : vérifie l'ancien mot de passe,
// prépare le nouveau mot de passe et envoie le code par email.
Route::post('/update-password/{id}', [App\Http\Controllers\HomeController::class, 'updatePassword'])
    ->middleware('auth')
    ->name('updatePassword');

// Page dédiée où l'utilisateur saisit le code reçu par email
// pour confirmer le changement de mot de passe.
Route::get('/verify-password-change', [App\Http\Controllers\HomeController::class, 'showPasswordChangeVerification'])
    ->middleware('auth')
    ->name('passwordChangeVerification');

// Confirmation du code reçu par email.
// Si le code est correct, le nouveau mot de passe est appliqué.
Route::post('/confirm-password-change', [App\Http\Controllers\HomeController::class, 'confirmPasswordChange'])
    ->middleware('auth')
    ->name('confirmPasswordChange');

// Demande d'un nouveau code de vérification.
// Le contrôleur vérifiera également le délai de 2 minutes
// avant d'autoriser un nouvel envoi.
Route::post('/resend-password-change-code', [App\Http\Controllers\HomeController::class, 'resendPasswordChangeCode'])
    ->middleware('auth')
    ->name('resendPasswordChangeCode');

//l'utilisateur ne voit que les cours auxquels il est inscrit
Route::get('/mes-cours', [App\Http\Controllers\CoursController::class, 'mesCours'])
    ->middleware('auth')
    ->name('cours.mes');

// Marque comme lues toutes les notifications non lues de l'utilisateur connecté (bouton "Tout marquer comme lu" de la cloche)
Route::post('/notifications/lues', [App\Http\Controllers\NotificationController::class, 'marquerToutesLues'])
    ->middleware('auth')
    ->name('notifications.lues');

// Lire une notification (affiche son message complet et la marque comme lue)
Route::get('/notifications/{notification}', [App\Http\Controllers\NotificationController::class, 'show'])
    ->middleware('auth')
    ->name('notifications.show');

// Historique de toutes les notifications de l'utilisateur (lues et non lues)
Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])
    ->middleware('auth')
    ->name('notifications.index');

// Action groupée sur les notifications cochées (marquer lues, archiver, restaurer, supprimer)
Route::post('/notifications/actions', [App\Http\Controllers\NotificationController::class, 'action'])
    ->middleware('auth')
    ->name('notifications.action');

// Catalogue des cours — accessible à tout le monde, visiteur anonyme ET utilisateur connecté
// (cf. diagramme de cas d'utilisation : "accéder au programme des cours" est relié aux deux acteurs)
Route::get('/catalogue-cours', [App\Http\Controllers\CoursController::class, 'catalogue'])->name('cours.catalogue');

// Détail d'un cours précis — public aussi, pas besoin d'être connecté pour consulter
Route::get('/cours/{cours}', [App\Http\Controllers\CoursController::class, 'show'])->name('cours.show');

// Inscription/désinscription d'un utilisateur connecté à une classe
// Ces routes protègent l'inscription et la désinscription par authentification.
Route::post('/classes/{classe}/inscription', [App\Http\Controllers\InscriptionController::class, 'store'])
    ->middleware('auth')
    ->name('classes.inscription.store');

Route::delete('/classes/{classe}/inscription', [App\Http\Controllers\InscriptionController::class, 'destroy'])
    ->middleware('auth')
    ->name('classes.inscription.destroy');

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
    // Gestion des utilisateurs réservée à l'administrateur.
    Route::get('/utilisateurs', [App\Http\Controllers\UserController::class,'index',])->name('users.index');
    Route::get('/utilisateurs/create', [App\Http\Controllers\UserController::class, 'create'])->name('users.create');
    Route::post('/utilisateurs', [App\Http\Controllers\UserController::class, 'store'])->name('users.store');
    Route::get('/utilisateurs/{user}/edit', [App\Http\Controllers\UserController::class, 'edit'])->name('users.edit');
    Route::put('/utilisateurs/{user}', [App\Http\Controllers\UserController::class, 'update'])->name('users.update');
    Route::delete('/utilisateurs/{user}', [App\Http\Controllers\UserController::class,'destroy',])->name('users.destroy');

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

    // Enregistre le paiement direct (comptoir) d'une inscription
    Route::post('/inscriptions/{inscription}/paiement', [App\Http\Controllers\PaiementController::class, 'store'])
        ->name('inscriptions.paiement.store');
});

// Demande de paiement en ligne d'une inscription : réservée à son propriétaire
Route::get('/inscriptions/{inscription}/paiement', [App\Http\Controllers\PaiementController::class, 'create'])
    ->middleware('auth')
    ->name('inscriptions.paiement.create');
Route::post('/inscriptions/{inscription}/paiement', [App\Http\Controllers\PaiementController::class, 'payer'])
    ->middleware('auth')
    ->name('inscriptions.paiement.payer');

// Retours de l'interface SingPay : la transaction sera vérifiée dans le contrôleur.
Route::get('/paiement/success', [App\Http\Controllers\PaiementController::class, 'success'])
    ->middleware('auth')
    ->name('paiement.singpay.success');
Route::get('/paiement/error', [App\Http\Controllers\PaiementController::class, 'error'])
    ->middleware('auth')
    ->name('paiement.singpay.error');

// Route générale — DOIT rester la toute dernière route du fichier,
// sinon elle intercepte tout ce qui n'a pas encore été défini avant elle
Route::get('{any}', [App\Http\Controllers\HomeController::class, 'index'])
    ->name('index');