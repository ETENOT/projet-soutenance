<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\CoursController;

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

// Mise à jour du mot de passe
Route::post('/profile/password/envoyer-lien', [App\Http\Controllers\HomeController::class, 'sendPasswordResetLink'])
    ->middleware('auth')
    ->name('password.sendResetLink');

// soumission du mot de passe → envoi du code → confirmation.

//l'utilisateur vers la page du choix de paiement pour une classe ou une inscription
Route::get('/classes/{classe}/paiements/choix', [App\Http\Controllers\PaiementController::class, 'choix'])
    ->middleware('auth')
    ->name('paiements.choix');



// Paiement en ligne d'une inscription — propriétaire vérifié dans le contrôleur
Route::middleware('auth')->group(function () {

    Route::post('/classes/{classe}/paiements/especes', [App\Http\Controllers\PaiementController::class, 'especes'])
    ->name('paiements.especes');

    Route::get('/inscriptions/{inscription}/paiement', [App\Http\Controllers\PaiementController::class, 'create'])
        ->name('paiements.create');

    Route::post('/inscriptions/{inscription}/paiement/payer', [App\Http\Controllers\PaiementController::class, 'payer'])
        ->name('paiements.payer');

    Route::get('statut-paiement', [App\Http\Controllers\CoursController::class, 'statutPaiement'])
        ->middleware('auth')
        ->name('paiements.statut_paiement');

    // Retours SingPay (redirect_success / redirect_error définis dans PaiementController::payer)
    Route::get('/paiement/success', [App\Http\Controllers\PaiementController::class, 'success'])
        ->name('paiements.success');

    Route::get('/paiement/error', [App\Http\Controllers\PaiementController::class, 'error'])
        ->name('paiements.error');
});

// Paiement direct au comptoir — réservé à l'admin
Route::post('/inscriptions/{inscription}/paiement/direct', [App\Http\Controllers\PaiementController::class, 'store'])
    ->middleware(['auth', 'role:admin'])
    ->name('paiements.store');

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

// Quiz : visiteur (avec e-mail) OU particulier connecté
Route::middleware('quiz.acces')->group(function () {

    Route::get('/cours/{cours}/quiz/email', [App\Http\Controllers\QuizAttemptController::class, 'emailVisiteur'])
        ->name('quiz.visiteur.email');

    Route::post('/cours/{cours}/quiz/email', [App\Http\Controllers\QuizAttemptController::class, 'enregistrerEmailVisiteur'])
        ->middleware('throttle:10,1')
        ->name('quiz.visiteur.email.store');
        
    Route::get('/quiz/{quiz}/termine', [App\Http\Controllers\QuizAttemptController::class, 'termineVisiteur'])
        ->name('quiz.visiteur.termine');

    // Afficher ou reprendre un quiz lié à un cours
    Route::get('/cours/{cours}/quiz', [App\Http\Controllers\QuizAttemptController::class, 'show'])
        ->name('quiz.tentative.show');


    // Enregistrer une réponse pendant le quiz
    Route::post('/quiz/{quiz}/repondre', [App\Http\Controllers\QuizAttemptController::class, 'repondre'])
        ->name('quiz.tentative.repondre');


    // Terminer une tentative
    Route::post('/quiz/{quiz}/terminer', [App\Http\Controllers\QuizAttemptController::class, 'terminer'])
        ->name('quiz.tentative.terminer');

    // Annuler une tentative en cours
    Route::post('/quiz/{quiz}/annuler', [App\Http\Controllers\QuizAttemptController::class, 'annuler'])
        ->name('quiz.tentative.annuler');
});

// Historique, résultats, chapitre, ressources : réservés aux particuliers connectés
Route::middleware(['auth', 'role:particulier'])->group(function () {
    // Parcours des formations de l'utilisateur particulier
    Route::get('/mes-formations', [CoursController::class, 'mesCours'])
        ->name('cours.mes');
    // Voir le résultat après correction
    Route::get('/mes-resultats/{resultatQuiz}', [App\Http\Controllers\QuizAttemptController::class, 'resultat'])
        ->name('quiz.resultat');

    // Voir l'historique de toutes les tentatives de quiz d'un cours (avec score et date)
    Route::get('/cours/{cours}/historique-quiz', [App\Http\Controllers\QuizAttemptController::class, 'historique'])
        ->name('quiz.historique');

    Route::get('/cours/{cours}/espace', [CoursController::class, 'espace'])
    ->name('cours.espace');

    Route::get('/cours/{cours}/chapitre/{chapitre}', [CoursController::class, 'chapitre'])
    ->middleware(['auth', 'role:particulier'])
    ->name('cours.chapitre');

    Route::get('/cours/{cours}/ressource/{resource}', [App\Http\Controllers\CoursController::class, 'ressource'])
    ->middleware('auth')
    ->name('cours.ressource');
    });

// Inscription/désinscription d'un utilisateur connecté à une classe
// Ces routes protègent l'inscription et la désinscription par authentification.
Route::post('/classes/{classe}/inscription', [App\Http\Controllers\InscriptionController::class, 'store'])
    ->middleware('auth')
    ->name('classes.inscription.store');

Route::delete('/classes/{classe}/inscription', [App\Http\Controllers\InscriptionController::class, 'destroy'])
    ->middleware('auth')
    ->name('classes.inscription.destroy');

// Ouvrir / télécharger un document de cours — accès vérifié dans le contrôleur
// (inscrit + payé sur une classe de ce cours, ou admin)
Route::get('/cours/{cours}/resources/{resource}/voir', [App\Http\Controllers\CoursResourceController::class, 'voir'])
    ->middleware('auth')
    ->name('cours.resources.voir');

Route::get('/cours/{cours}/resources/{resource}/telecharger', [App\Http\Controllers\CoursResourceController::class, 'download'])
    ->middleware('auth')
    ->name('cours.resources.download');

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

    // Documents (fichier ou lien vidéo) d'un cours, imbriqués sous cours/{cours}
       Route::post('/cours/{cours}/resources', [App\Http\Controllers\CoursResourceController::class, 'store'])->name('cours.resources.store');
    Route::delete('/cours/{cours}/resources/{resource}', [App\Http\Controllers\CoursResourceController::class, 'destroy'])->name('cours.resources.destroy');

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
});

// Route générale — DOIT rester la toute dernière route du fichier,
// sinon elle intercepte tout ce qui n'a pas encore été défini avant elle
Route::get('{any}', [App\Http\Controllers\HomeController::class, 'index'])
    ->name('index');