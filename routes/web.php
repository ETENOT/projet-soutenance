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

    Route::get('/paiements/{paiement}/recu', [App\Http\Controllers\PaiementController::class, 'recu'])
        ->name('paiements.recu');

    Route::get('/paiements/{paiement}/recu/telecharger', [App\Http\Controllers\PaiementController::class, 'telechargerRecu'])
        ->name('paiements.recu.telecharger');

    // Retours SingPay
    Route::get('/paiement/success', [App\Http\Controllers\PaiementController::class, 'success'])
        ->name('paiements.success');

    Route::get('/paiement/error', [App\Http\Controllers\PaiementController::class, 'error'])
        ->name('paiements.error');

    // Première connexion d'un compte créé par l'admin : changement obligatoire du mot de passe par défaut.
    // Noms en « password.force.* » : le middleware ForcePasswordChange laisse passer ces routes.
    Route::get('/premier-mot-de-passe', [App\Http\Controllers\PremierMotDePasseController::class, 'edit'])
        ->name('password.force.edit');
    Route::put('/premier-mot-de-passe', [App\Http\Controllers\PremierMotDePasseController::class, 'update'])
        ->name('password.force.update');
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

// Visualisation d'une attestation dans le navigateur (propriétaire ou administrateur)
Route::get('/attestations/{attestation}/voir', [App\Http\Controllers\AttestationController::class, 'voir'])
    ->middleware('auth')
    ->name('attestations.voir');

// Téléchargement d'une attestation de formation (propriétaire ou administrateur)
Route::get('/attestations/{attestation}/telecharger', [App\Http\Controllers\AttestationController::class, 'telecharger'])
    ->middleware('auth')
    ->name('attestations.telecharger');

// ==========================================================================
// COURS PUBLICS
// ==========================================================================

// Catalogue des cours
Route::get('/catalogue-cours', [App\Http\Controllers\CoursController::class, 'catalogue'])
    ->name('cours.catalogue');

// Télécharger les chapitres d'un cours au format PDF
Route::get('/cours/{cours}/chapitres.pdf', [App\Http\Controllers\CoursController::class, 'chapitresPdf'])
    ->name('cours.programme.pdf');

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
    Route::get('/mes-classes', [CoursController::class, 'mesClasses'])
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

    // Evaluation de la formation (formateur + formation) à la fin d'une classe
    Route::get('/classes/{classe}/evaluation', [App\Http\Controllers\EvaluationFinFormationController::class, 'create'])
        ->name('evaluations.create');

    Route::post('/classes/{classe}/evaluation', [App\Http\Controllers\EvaluationFinFormationController::class, 'store'])
        ->name('evaluations.store');

    // Onglet « Entreprise » : entreprise dont le particulier est membre (lecture seule)
    Route::get('/mon-entreprise', [App\Http\Controllers\MonEntrepriseController::class, 'index'])
        ->name('particulier.entreprise.index');
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

// Page de consultation d'une ressource dans l'espace du cours
Route::get('/cours/{cours}/resources/{resource}', [App\Http\Controllers\CoursController::class, 'ressource'])
    ->middleware('auth')
    ->name('cours.ressource');

// Télécharger un document
Route::get('/cours/{cours}/resources/{resource}/telecharger', [App\Http\Controllers\CoursResourceController::class, 'download'])
    ->middleware('auth')
    ->name('cours.resources.download');


// ==========================================================================
// ESPACE PARTICULIER
// ==========================================================================

Route::middleware(['auth', 'role:entreprise'])->group(function () {
    // Onglet « Membres entreprises » : membres gérés par l'admin (lecture seule)
    Route::get('/entreprise/membres', [App\Http\Controllers\EntrepriseMembreController::class, 'index'])
        ->name('entreprise.membres.index');
});

// ==========================================================================
// ADMINISTRATION
// ==========================================================================

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/categories', [App\Http\Controllers\CategorieController::class, 'index'])
            ->name('categories.index');
        Route::post('/categories', [App\Http\Controllers\CategorieController::class, 'store'])
            ->name('categories.store');
        Route::put('/categories/{categorie}', [App\Http\Controllers\CategorieController::class, 'update'])
            ->name('categories.update');
        Route::delete('/categories/{categorie}', [App\Http\Controllers\CategorieController::class, 'destroy'])
            ->name('categories.destroy');

        // Suppression multiple des catégories cochées dans la liste
        Route::post('/categories/suppression-multiple', [App\Http\Controllers\CategorieController::class, 'destroyMultiple'])
            ->name('categories.destroy-multiple');

        // ------------------------------------------------------------------
        // ATTESTATIONS délivrées (liste, du plus récent au plus ancien)
        // ------------------------------------------------------------------
        Route::get('/attestations', [App\Http\Controllers\AdminAttestationController::class, 'index'])
            ->name('attestations.index');   // nom complet : admin.attestations.index

        // ------------------------------------------------------------------
        // FORMATEURS (CRUD + affectation aux classes + avis anonymes)
        // ------------------------------------------------------------------
        // /formateurs/create doit rester AVANT /formateurs/{formateur}
        // (sinon "create" serait interprété comme un identifiant de formateur)

        Route::get('/formateurs', [App\Http\Controllers\AdminFormateurController::class, 'index'])
            ->name('formateurs.index');

        Route::post('/formateurs', [App\Http\Controllers\AdminFormateurController::class, 'store'])
            ->name('formateurs.store');

        Route::put('/formateurs/{formateur}', [App\Http\Controllers\AdminFormateurController::class, 'update'])
            ->name('formateurs.update');

        Route::delete('/formateurs/{formateur}', [App\Http\Controllers\AdminFormateurController::class, 'destroy'])
            ->name('formateurs.destroy');

        // Affecter une classe à ce formateur
        Route::post('/formateurs/{formateur}/classes', [App\Http\Controllers\AdminFormateurController::class, 'affecterClasse'])
            ->name('formateurs.classes.store');

        // Retirer une classe à ce formateur
        Route::delete('/formateurs/{formateur}/classes/{classe}', [App\Http\Controllers\AdminFormateurController::class, 'retirerClasse'])
            ->name('formateurs.classes.destroy');

        Route::post('/formateurs/suppression-multiple', [App\Http\Controllers\AdminFormateurController::class, 'destroyMultiple'])
            ->name('formateurs.destroy-multiple');

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

        // Suppression multiple des cours cochés dans la liste
        Route::post('/cours/suppression-multiple', [App\Http\Controllers\CoursController::class, 'destroyMultiple'])
            ->name('cours.destroy-multiple');

        // Actions groupées sur les cours cochés (changer de catégorie, supprimer)
        Route::post('/cours/actions', [App\Http\Controllers\CoursController::class, 'actionsGroupees'])
            ->name('cours.actions');

        // Evaluation de la formation (formateur + formation) à la fin d'une classe
        Route::get('/evaluations', [App\Http\Controllers\AdminEvaluationController::class, 'index'])
            ->name('evaluations.index');

        Route::get('/evaluations/{evaluation}', [App\Http\Controllers\AdminEvaluationController::class, 'show'])
            ->name('evaluations.show');
        
        // ------------------------------------------------------------------
        // CHAPITRES
        // ------------------------------------------------------------------

        Route::get('/cours/{cours}/chapitres/create', [App\Http\Controllers\ChapitreController::class, 'create'])
            ->name('cours.chapitres.create');

        Route::get('/cours/{cours}/chapitres/{chapitre}/edit', [App\Http\Controllers\ChapitreController::class, 'edit'])
            ->name('cours.chapitres.edit');

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

        Route::get('/questions/import', [App\Http\Controllers\QuestionImportController::class, 'form'])->name('questions.import');
        Route::post('/questions/import', [App\Http\Controllers\QuestionImportController::class, 'traiter'])->name('questions.import.store');
        Route::get('/questions/import/modele', [App\Http\Controllers\QuestionImportController::class, 'modele'])->name('questions.import.modele');
        // Actions groupées sur les questions cochées (changer de cours, supprimer).
        // Déclarée AVANT la resource, comme les routes d'import.
        Route::post('/questions/actions', [App\Http\Controllers\QuestionController::class, 'actionsGroupees'])->name('questions.actions');
        Route::resource('questions', App\Http\Controllers\QuestionController::class)->only(['index', 'store', 'update', 'destroy']);

        // Suivi des quiz (tentatives des particuliers et des visiteurs) : liste
        Route::get('/quiz', [App\Http\Controllers\AdminQuizController::class, 'index'])->name('quiz.index');
        // Réglage du "noté sur" de chaque cours. Déclaré AVANT /quiz/{quiz}, sinon "reglages"
        // serait pris pour l'id d'un quiz.
        Route::get('/quiz/reglages', [App\Http\Controllers\AdminQuizController::class, 'reglages'])->name('quiz.reglages');
        Route::put('/quiz/reglages', [App\Http\Controllers\AdminQuizController::class, 'enregistrerReglages'])->name('quiz.reglages.update');
        Route::delete('/quiz/{quiz}', [App\Http\Controllers\AdminQuizController::class, 'destroy'])->name('quiz.destroy');
        Route::post('/quiz/suppression-multiple', [App\Http\Controllers\AdminQuizController::class, 'destroyMultiple'])
            ->name('quiz.destroy-multiple');
        // ------------------------------------------------------------------
        // INSCRIPTIONS (liste filtrable, création, fiche, modification, suppression)
        // ------------------------------------------------------------------
        // /inscriptions/create et /suppression-multiple doivent rester AVANT
        // /inscriptions/{inscription} (sinon "create" serait pris pour l'identifiant d'une inscription)

        Route::get('/inscriptions', [App\Http\Controllers\AdminInscriptionController::class, 'index'])
            ->name('inscriptions.index');

        Route::get('/inscriptions/create', [App\Http\Controllers\AdminInscriptionController::class, 'create'])
            ->name('inscriptions.create');

        Route::post('/inscriptions', [App\Http\Controllers\AdminInscriptionController::class, 'store'])
            ->name('inscriptions.store');

        // Suppression multiple des inscriptions cochées dans la liste
        Route::post('/inscriptions/suppression-multiple', [App\Http\Controllers\AdminInscriptionController::class, 'destroyMultiple'])
            ->name('inscriptions.destroy-multiple');

        Route::get('/inscriptions/{inscription}', [App\Http\Controllers\AdminInscriptionController::class, 'show'])
            ->name('inscriptions.show');

        Route::get('/inscriptions/{inscription}/edit', [App\Http\Controllers\AdminInscriptionController::class, 'edit'])
            ->name('inscriptions.edit');

        Route::put('/inscriptions/{inscription}', [App\Http\Controllers\AdminInscriptionController::class, 'update'])
            ->name('inscriptions.update');

        Route::delete('/inscriptions/{inscription}', [App\Http\Controllers\AdminInscriptionController::class, 'destroy'])
            ->name('inscriptions.destroy');

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

        // Suppression multiple des utilisateurs cochés dans la liste
        Route::post('/utilisateurs/suppression-multiple', [App\Http\Controllers\UserController::class, 'destroyMultiple'])
            ->name('users.destroy-multiple');

        Route::get('/classe', [App\Http\Controllers\ClasseController::class, 'toutes'])
            ->name('classes.index');

        // Actions groupées sur les classes cochées dans la liste (affecter formateur, fermer, supprimer...)
        Route::post('/classe/actions', [App\Http\Controllers\ClasseController::class, 'actionsGroupees'])
            ->name('classes.actions');

        // ------------------------------------------------------------------
        // ENTREPRISES ET LEURS MEMBRES
        // (les routes fixes « create » et « suppression-multiple » passent AVANT les routes à {entreprise})
        // ------------------------------------------------------------------

        Route::get('/entreprises', [App\Http\Controllers\AdminEntrepriseController::class, 'index'])
            ->name('entreprises.index');

        Route::get('/entreprises/create', [App\Http\Controllers\AdminEntrepriseController::class, 'create'])
            ->name('entreprises.create');

        Route::post('/entreprises', [App\Http\Controllers\AdminEntrepriseController::class, 'store'])
            ->name('entreprises.store');

        // Suppression multiple des entreprises cochées dans la liste
        Route::post('/entreprises/suppression-multiple', [App\Http\Controllers\AdminEntrepriseController::class, 'destroyMultiple'])
            ->name('entreprises.destroy-multiple');

        Route::get('/entreprises/{entreprise}', [App\Http\Controllers\AdminEntrepriseController::class, 'show'])
            ->name('entreprises.show');

        Route::get('/entreprises/{entreprise}/edit', [App\Http\Controllers\AdminEntrepriseController::class, 'edit'])
            ->name('entreprises.edit');

        Route::put('/entreprises/{entreprise}', [App\Http\Controllers\AdminEntrepriseController::class, 'update'])
            ->name('entreprises.update');

        Route::delete('/entreprises/{entreprise}', [App\Http\Controllers\AdminEntrepriseController::class, 'destroy'])
            ->name('entreprises.destroy');

        // Membres d'une entreprise
        Route::get('/entreprises/{entreprise}/membres/create', [App\Http\Controllers\AdminEntrepriseMembreController::class, 'create'])
            ->name('entreprises.membres.create');

        // Recherche en direct des particuliers inscrits (JSON)
        Route::get('/entreprises/{entreprise}/membres/rechercher', [App\Http\Controllers\AdminEntrepriseMembreController::class, 'rechercher'])
            ->name('entreprises.membres.rechercher');

        // Ajouter des particuliers déjà inscrits
        Route::post('/entreprises/{entreprise}/membres', [App\Http\Controllers\AdminEntrepriseMembreController::class, 'store'])
            ->name('entreprises.membres.store');

        // Créer un nouveau particulier directement membre de l'entreprise
        Route::post('/entreprises/{entreprise}/membres/nouveau', [App\Http\Controllers\AdminEntrepriseMembreController::class, 'storeNouveau'])
            ->name('entreprises.membres.store-nouveau');

        // Retirer plusieurs membres cochés dans la liste
        Route::post('/entreprises/{entreprise}/membres/retrait-multiple', [App\Http\Controllers\AdminEntrepriseMembreController::class, 'destroyMultiple'])
            ->name('entreprises.membres.destroy-multiple');

        Route::get('/entreprises/{entreprise}/membres/{user}/edit', [App\Http\Controllers\AdminEntrepriseMembreController::class, 'edit'])
            ->name('entreprises.membres.edit');

        Route::put('/entreprises/{entreprise}/membres/{user}', [App\Http\Controllers\AdminEntrepriseMembreController::class, 'update'])
            ->name('entreprises.membres.update');

        Route::delete('/entreprises/{entreprise}/membres/{user}', [App\Http\Controllers\AdminEntrepriseMembreController::class, 'destroy'])
            ->name('entreprises.membres.destroy');

        // ------------------------------------------------------------------
        // CLASSES
        // ------------------------------------------------------------------

        Route::prefix('cours/{cours}/classes')
            ->name('cours.classes.')
            ->group(function () {

                Route::post('/actions', [App\Http\Controllers\ClasseController::class, 'actionsGroupees'])
                    ->name('actions');

                Route::get('/', [App\Http\Controllers\ClasseController::class, 'index'])
                    ->name('index');

                Route::get('/create', [App\Http\Controllers\ClasseController::class, 'create'])
                    ->name('create');

                Route::post('/', [App\Http\Controllers\ClasseController::class, 'store'])
                    ->name('store');

                Route::get('/{classe}', [App\Http\Controllers\ClasseController::class, 'show'])
                    ->name('show');

                Route::get('/{classe}/edit', [App\Http\Controllers\ClasseController::class, 'edit'])
                    ->name('edit');

                Route::put('/{classe}', [App\Http\Controllers\ClasseController::class, 'update'])
                    ->name('update');

                // Jours à ne pas compter dans le taux de présence (formulaire dédié)
                Route::put('/{classe}/jours-exclus', [App\Http\Controllers\ClasseController::class, 'updateJoursExclus'])
                    ->name('jours-exclus');

                Route::delete('/{classe}', [App\Http\Controllers\ClasseController::class, 'destroy'])
                    ->name('destroy');

                // Fermer la classe (calcule les taux de présence / attestations)
                Route::post('/{classe}/fermer', [App\Http\Controllers\ClasseController::class, 'fermer'])
                    ->name('fermer');

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
    // ESPACE FORMATEUR
    // ==========================================================================
    // Toutes ces routes exigent d'être connecté ET d'avoir le rôle "formateur".
    // Chaque contrôleur vérifie en plus que la classe/le cours appartient bien au formateur connecté.

    Route::middleware(['auth', 'role:formateur'])
        ->prefix('formateur')
        ->group(function () {

            // Liste de mes classes (état, dates, lieu, payés/capacité)
            Route::get('/classes', [App\Http\Controllers\Formateur\ClasseController::class, 'index'])
                ->name('classes.formateur.index');

            // Nombre de forums avec nouveaux messages (badge du menu).
            // DOIT rester avant '/{classe}', sinon "non-lus" serait pris pour un id de classe.
            Route::get('/non-lus', [App\Http\Controllers\ForumController::class, 'nonLus'])->name('forum.non-lus');

            // Détail d'une classe : cours concerné + stagiaires payés
            Route::get('/classes/{classe}', [App\Http\Controllers\Formateur\ClasseController::class, 'show'])
                ->name('classes.formateur.show');

            // Fermer la classe (formateur de la classe ; l'admin a sa propre route)
            Route::post('/classes/{classe}/fermer', [App\Http\Controllers\Formateur\ClasseController::class, 'fermer'])
                ->name('classes.formateur.fermer');

            // Feuille de présence : afficher un jour / enregistrer un jour
            Route::get('/classes/{classe}/presences', [App\Http\Controllers\Formateur\PresenceController::class, 'edit'])
                ->name('presences.formateur.edit');

            Route::put('/classes/{classe}/presences', [App\Http\Controllers\Formateur\PresenceController::class, 'update'])
                ->name('presences.formateur.update');

            // Jours à ne pas compter dans le taux de présence (jour férié, séance annulée...)
            Route::put('/classes/{classe}/presences/jours-exclus', [App\Http\Controllers\Formateur\PresenceController::class, 'updateJoursExclus'])
                ->name('presences.formateur.jours-exclus');

            // Résultats au quiz des stagiaires de la classe
            Route::get('/classes/{classe}/quiz', [App\Http\Controllers\Formateur\QuizController::class, 'index'])
                ->name('quiz.formateur.index');

            // Supports de cours : liste + dépôt (le dépôt réutilise le contrôleur de l'admin)
            Route::get('/cours/{cours}/supports', [App\Http\Controllers\Formateur\SupportController::class, 'index'])
                ->name('cours.resources.formateur.index');

            Route::post('/cours/{cours}/supports', [App\Http\Controllers\CoursResourceController::class, 'store'])
                ->name('cours.resources.formateur.store');

            // Correction détaillée (question par question) d'une tentative d'un stagiaire de la classe
            Route::get('/classes/{classe}/quiz/resultats/{resultatQuiz}', [App\Http\Controllers\Formateur\QuizController::class, 'correction'])
                ->name('quiz.formateur.correction');
            
            // Entrée du menu « Quiz des stagiaires » : liste de mes classes avec accès à leurs résultats
            Route::get('/quiz', [App\Http\Controllers\Formateur\QuizController::class, 'classes'])
                ->name('quiz.formateur.classes');

            // Avis reçus par le formateur (sans le nom des stagiaires)
            Route::get('/avis', [App\Http\Controllers\Formateur\AvisFormateurController::class, 'index'])
                ->name('avis_formateurs.formateur.index');
        });

    // ==========================================================================
    // FORUM DE DISCUSSION (un forum par classe)
    // ==========================================================================
    // Accessible à tout utilisateur connecté ; ForumController vérifie ensuite qu'il est
    // bien membre (stagiaire payé de la classe, formateur de la classe, ou admin).

    Route::middleware('auth')
        ->prefix('forum')
        ->name('forum.')
        ->group(function () {

            // Mes forums
            Route::get('/', [App\Http\Controllers\ForumController::class, 'index'])->name('index');

            // Galerie : photos de tous mes forums.
            // DOIT rester avant '/{classe}', sinon "galerie" serait pris pour un id de classe.
            Route::get('/galerie', [App\Http\Controllers\ForumController::class, 'galerie'])->name('galerie');

            // Page du forum d'une classe
            Route::get('/{classe}', [App\Http\Controllers\ForumController::class, 'show'])->name('show');

            // Messages en JSON (rafraîchis automatiquement par la page)
            Route::get('/{classe}/messages', [App\Http\Controllers\ForumController::class, 'messages'])->name('messages');

            // Galerie de CE forum en JSON (bouton « Galerie » de la page du forum)
            Route::get('/{classe}/galerie', [App\Http\Controllers\ForumController::class, 'galerieClasse'])->name('galerie-classe');

            // Poster un message / une photo (limité à 30 envois par minute et par utilisateur)
            Route::post('/{classe}/messages', [App\Http\Controllers\ForumController::class, 'store'])
                ->middleware('throttle:30,1')
                ->name('store');

            // Supprimer un message
            Route::delete('/{classe}/messages/{message}', [App\Http\Controllers\ForumController::class, 'destroy'])->name('destroy');

            // Afficher une photo (réservé aux membres)
            Route::get('/{classe}/photos/{message}', [App\Http\Controllers\ForumController::class, 'photo'])->name('photo');
        });



// ==========================================================================
// ROUTE GÉNÉRALE
// ==========================================================================
// DOIT rester la toute dernière route du fichier.

Route::get('{any}', [App\Http\Controllers\HomeController::class, 'index'])
    ->name('index');