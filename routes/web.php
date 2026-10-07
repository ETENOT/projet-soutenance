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
| contient le groupe de middleware "web".
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


// ==========================================================================
// PAIEMENTS
// ==========================================================================

// Choix du paiement pour une classe ou une inscription
Route::get('/classes/{classe}/paiements/choix', [App\Http\Controllers\PaiementController::class, 'choix'])
    ->middleware('auth')
    ->name('paiements.choix');

Route::middleware('auth')->group(function () {

    Route::post('/classes/{classe}/paiements/especes', [App\Http\Controllers\PaiementController::class, 'especes'])
        ->name('paiements.especes');

    Route::get('/inscriptions/{inscription}/paiement', [App\Http\Controllers\PaiementController::class, 'create'])
        ->name('paiements.create');

    Route::post('/inscriptions/{inscription}/paiement/payer', [App\Http\Controllers\PaiementController::class, 'payer'])
        ->name('paiements.payer');

    Route::get('statut-paiement', [App\Http\Controllers\CoursController::class, 'statutPaiement'])
        ->name('paiements.statut_paiement');

    // Retours SingPay
    Route::get('/paiement/success', [App\Http\Controllers\PaiementController::class, 'success'])
        ->name('paiements.success');

    Route::get('/paiement/error', [App\Http\Controllers\PaiementController::class, 'error'])
        ->name('paiements.error');
});

// Paiement direct au comptoir — réservé à l'admin
Route::post('/inscriptions/{inscription}/paiement/direct', [App\Http\Controllers\PaiementController::class, 'store'])
    ->middleware(['auth', 'role:admin'])
    ->name('paiements.store');


// ==========================================================================
// NOTIFICATIONS
// ==========================================================================

// Marquer toutes les notifications comme lues
Route::post('/notifications/lues', [App\Http\Controllers\NotificationController::class, 'marquerToutesLues'])
    ->middleware('auth')
    ->name('notifications.lues');

// Lire une notification
Route::get('/notifications/{notification}', [App\Http\Controllers\NotificationController::class, 'show'])
    ->middleware('auth')
    ->name('notifications.show');

// Historique de toutes les notifications
Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])
    ->middleware('auth')
    ->name('notifications.index');

// Action groupée sur les notifications
Route::post('/notifications/actions', [App\Http\Controllers\NotificationController::class, 'action'])
    ->middleware('auth')
    ->name('notifications.action');


// ==========================================================================
// COURS PUBLICS
// ==========================================================================

// Catalogue des cours
Route::get('/catalogue-cours', [App\Http\Controllers\CoursController::class, 'catalogue'])
    ->name('cours.catalogue');

// Détail d'un cours — côté utilisateur
Route::get('/cours/{cours}', [App\Http\Controllers\CoursController::class, 'show'])
    ->name('cours.show');


// ==========================================================================
// QUIZ
// ==========================================================================

Route::middleware('quiz.acces')->group(function () {

    // Visiteur : saisir son email
    Route::get('/cours/{cours}/quiz/email', [App\Http\Controllers\QuizAttemptController::class, 'emailVisiteur'])
        ->name('quiz.visiteur.email');

    Route::post('/cours/{cours}/quiz/email', [App\Http\Controllers\QuizAttemptController::class, 'enregistrerEmailVisiteur'])
        ->middleware('throttle:10,1')
        ->name('quiz.visiteur.email.store');

    // Quiz visiteur terminé
    Route::get('/quiz/{quiz}/termine', [App\Http\Controllers\QuizAttemptController::class, 'termineVisiteur'])
        ->name('quiz.visiteur.termine');

    // Afficher ou reprendre un quiz
    Route::get('/cours/{cours}/quiz', [App\Http\Controllers\QuizAttemptController::class, 'show'])
        ->name('quiz.tentative.show');

    // Enregistrer une réponse
    Route::post('/quiz/{quiz}/repondre', [App\Http\Controllers\QuizAttemptController::class, 'repondre'])
        ->name('quiz.tentative.repondre');

    // Terminer une tentative
    Route::post('/quiz/{quiz}/terminer', [App\Http\Controllers\QuizAttemptController::class, 'terminer'])
        ->name('quiz.tentative.terminer');

    // Annuler une tentative
    Route::post('/quiz/{quiz}/annuler', [App\Http\Controllers\QuizAttemptController::class, 'annuler'])
        ->name('quiz.tentative.annuler');
});


// ==========================================================================
// ESPACE PARTICULIER
// ==========================================================================

Route::middleware(['auth', 'role:particulier'])->group(function () {

    // Parcours des formations de l'utilisateur
    Route::get('/mes-formations', [CoursController::class, 'mesCours'])
        ->name('cours.mes');

    // Résultat d'un quiz
    Route::get('/mes-resultats/{resultatQuiz}', [App\Http\Controllers\QuizAttemptController::class, 'resultat'])
        ->name('quiz.resultat');

    // Historique des quiz
    Route::get('/cours/{cours}/historique-quiz', [App\Http\Controllers\QuizAttemptController::class, 'historique'])
        ->name('quiz.historique');

    // Liste des cours où le particulier a passé des quiz (avec le nombre de quiz par cours)
    Route::get('/historique-quiz', [App\Http\Controllers\QuizAttemptController::class, 'historiqueCours'])
    ->name('quiz.historique.cours');

    // Espace d'apprentissage
    Route::get('/cours/{cours}/espace', [CoursController::class, 'espace'])
        ->name('cours.espace');

    // Consulter un chapitre
    Route::get('/cours/{cours}/chapitre/{chapitre}', [CoursController::class, 'chapitre'])
        ->name('cours.chapitre');

    // Consulter une ressource
    Route::get('/cours/{cours}/ressource/{resource}', [CoursController::class, 'ressource'])
        ->name('cours.ressource');
});


// ==========================================================================
// INSCRIPTIONS
// ==========================================================================

// Inscrire un utilisateur à une classe
Route::post('/classes/{classe}/inscription', [App\Http\Controllers\InscriptionController::class, 'store'])
    ->middleware('auth')
    ->name('classes.inscription.store');

// Désinscrire un utilisateur
Route::delete('/classes/{classe}/inscription', [App\Http\Controllers\InscriptionController::class, 'destroy'])
    ->middleware('auth')
    ->name('classes.inscription.destroy');


// ==========================================================================
// RESSOURCES PÉDAGOGIQUES — UTILISATEUR
// ==========================================================================

// Ouvrir une ressource
Route::get('/cours/{cours}/resources/{resource}/voir', [App\Http\Controllers\CoursResourceController::class, 'voir'])
    ->middleware('auth')
    ->name('cours.resources.voir');

// Télécharger un document
Route::get('/cours/{cours}/resources/{resource}/telecharger', [App\Http\Controllers\CoursResourceController::class, 'download'])
    ->middleware('auth')
    ->name('cours.resources.download');


// ==========================================================================
// ADMINISTRATION
// ==========================================================================

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // ------------------------------------------------------------------
        // COURS
        // ------------------------------------------------------------------

        // Liste des cours
        Route::get('/cours', [App\Http\Controllers\CoursController::class, 'index'])
            ->name('cours.index');

        // Création d'un cours
        Route::get('/cours/create', [App\Http\Controllers\CoursController::class, 'create'])
            ->name('cours.create');

        Route::post('/cours', [App\Http\Controllers\CoursController::class, 'store'])
            ->name('cours.store');

        // Consulter un cours depuis l'administration
        Route::get('/cours/{cours}', [App\Http\Controllers\CoursController::class, 'adminShow'])
            ->name('cours.show');

        // Gérer les chapitres et les ressources
        Route::get('/cours/{cours}/contenus/', [App\Http\Controllers\CoursController::class, 'contenus'])
            ->name('cours.contenus');

        // Modifier un cours
        Route::get('/cours/{cours}/edit', [App\Http\Controllers\CoursController::class, 'edit'])
            ->name('cours.edit');

        Route::put('/cours/{cours}', [App\Http\Controllers\CoursController::class, 'update'])
            ->name('cours.update');

        // Supprimer un cours
        Route::delete('/cours/{cours}', [App\Http\Controllers\CoursController::class, 'destroy'])
            ->name('cours.destroy');


        // ------------------------------------------------------------------
        // CHAPITRES
        // ------------------------------------------------------------------

        // Ajouter un chapitre
        Route::post('/cours/{cours}/chapitres', [App\Http\Controllers\ChapitreController::class, 'store'])
            ->name('cours.chapitres.store');

        // Modifier un chapitre
        Route::put('/cours/{cours}/chapitres/{chapitre}', [App\Http\Controllers\ChapitreController::class, 'update'])
            ->name('cours.chapitres.update');

        // Supprimer un chapitre
        Route::delete('/cours/{cours}/chapitres/{chapitre}', [App\Http\Controllers\ChapitreController::class, 'destroy'])
            ->name('cours.chapitres.destroy');


        // ------------------------------------------------------------------
        // RESSOURCES
        // ------------------------------------------------------------------

        // Ajouter une ressource à un cours
        Route::post('/cours/{cours}/resources', [App\Http\Controllers\CoursResourceController::class, 'store'])
            ->name('cours.resources.store');

        // Supprimer une ressource
        Route::delete('/cours/{cours}/resources/{resource}', [App\Http\Controllers\CoursResourceController::class, 'destroy'])
            ->name('cours.resources.destroy');

        // Banque de questions (QCM) : CRUD + import CSV.
    // Les routes /questions/import* sont déclarées AVANT la resource, sinon "import"
    // serait interprété comme l'id d'une question ({question}).
    Route::get('/questions/import', [App\Http\Controllers\QuestionImportController::class, 'form'])->name('questions.import');
    Route::post('/questions/import', [App\Http\Controllers\QuestionImportController::class, 'traiter'])->name('questions.import.store');
    Route::get('/questions/import/modele', [App\Http\Controllers\QuestionImportController::class, 'modele'])->name('questions.import.modele');
    Route::resource('questions', App\Http\Controllers\QuestionController::class)->except(['show']);

    // Suivi des quiz (tentatives des particuliers et des visiteurs) : liste
    Route::get('/quiz', [App\Http\Controllers\AdminQuizController::class, 'index'])->name('quiz.index');
    // Réglage du "noté sur" de chaque cours. Déclaré AVANT /quiz/{quiz}, sinon "reglages"
    // serait pris pour l'id d'un quiz.
    Route::get('/quiz/reglages', [App\Http\Controllers\AdminQuizController::class, 'reglages'])->name('quiz.reglages');
    Route::put('/quiz/reglages', [App\Http\Controllers\AdminQuizController::class, 'enregistrerReglages'])->name('quiz.reglages.update');
    // Suivi des quiz (tentatives des particuliers et des visiteurs) : détail, suppression
    Route::get('/quiz/{quiz}', [App\Http\Controllers\AdminQuizController::class, 'show'])->name('quiz.show');
    Route::delete('/quiz/{quiz}', [App\Http\Controllers\AdminQuizController::class, 'destroy'])->name('quiz.destroy');

        // ------------------------------------------------------------------
        // UTILISATEURS
        // ------------------------------------------------------------------

        Route::get('/utilisateurs', [App\Http\Controllers\UserController::class, 'index'])
            ->name('users.index');

        Route::get('/utilisateurs/create', [App\Http\Controllers\UserController::class, 'create'])
            ->name('users.create');

        Route::post('/utilisateurs', [App\Http\Controllers\UserController::class, 'store'])
            ->name('users.store');

        Route::get('/utilisateurs/{user}/edit', [App\Http\Controllers\UserController::class, 'edit'])
            ->name('users.edit');

        Route::put('/utilisateurs/{user}', [App\Http\Controllers\UserController::class, 'update'])
            ->name('users.update');

        Route::delete('/utilisateurs/{user}', [App\Http\Controllers\UserController::class, 'destroy'])
            ->name('users.destroy');


        // ------------------------------------------------------------------
        // CLASSES
        // ------------------------------------------------------------------

        Route::prefix('cours/{cours}/classes')
            ->name('cours.classes.')
            ->group(function () {

                Route::get('/', [App\Http\Controllers\ClasseController::class, 'index'])
                    ->name('index');

                Route::get('/create', [App\Http\Controllers\ClasseController::class, 'create'])
                    ->name('create');

                Route::post('/', [App\Http\Controllers\ClasseController::class, 'store'])
                    ->name('store');

                Route::get('/{classe}/edit', [App\Http\Controllers\ClasseController::class, 'edit'])
                    ->name('edit');

                Route::put('/{classe}', [App\Http\Controllers\ClasseController::class, 'update'])
                    ->name('update');

                Route::delete('/{classe}', [App\Http\Controllers\ClasseController::class, 'destroy'])
                    ->name('destroy');

                // Reporter une session
                Route::put('/{classe}/reporter', [App\Http\Controllers\ClasseController::class, 'reporter'])
                    ->name('reporter');

                // Ajouter un utilisateur dans une classe
                Route::post('/{classe}/inscrits', [App\Http\Controllers\ClasseController::class, 'ajouterUtilisateur'])
                    ->name('inscrits.store');

                // Retirer un utilisateur d'une classe
                Route::delete('/{classe}/inscrits/{user}', [App\Http\Controllers\ClasseController::class, 'retirerUtilisateur'])
                    ->name('inscrits.destroy');
            });
    });

    
// ==========================================================================
// ROUTE GÉNÉRALE
// ==========================================================================
// DOIT rester la toute dernière route du fichier.

Route::get('{any}', [App\Http\Controllers\HomeController::class, 'index'])
    ->name('index');