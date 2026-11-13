<?php

/*
|--------------------------------------------------------------------------
| Droits des administrateurs
|--------------------------------------------------------------------------
| Ce fichier est le SEUL endroit à modifier pour changer ce que chaque profil peut faire.
|
| Niveaux (du plus faible au plus fort) :
|   'aucun'   : le module n'apparaît pas dans le menu et son accès est refusé
|   'lecture' : on peut consulter, on ne peut rien créer, modifier ni supprimer
|   'gestion' : on peut tout faire dans le module
|
| Profils : super_admin (tout, y compris les comptes administrateurs),
|           admin_centre et assistant (droits ci-dessous).
|
| Après une modification : php artisan config:clear
*/

return [

    // Profils proposés quand le super-admin crée un compte administrateur.
    // (super_admin n'est pas proposé : il n'y en a qu'un, créé à l'installation.)
    'profils' => [
        'admin_centre' => [
            'libelle' => 'Admin du centre',
            'description' => 'Pilote le centre : gère tous les modules, sauf les comptes administrateurs.',
            'droits' => [
                'catalogue'    => 'gestion',
                'classes'      => 'gestion',
                'inscriptions' => 'gestion',
                'formateurs'   => 'gestion',
                'entreprises'  => 'gestion',
                'quiz'         => 'gestion',
                'attestations' => 'gestion',
                'evaluations'  => 'lecture',
                'utilisateurs' => 'gestion',   // comptes des stagiaires, entreprises, formateurs
            ],
        ],
        'assistant' => [
            'libelle' => 'Assistant(e)',
            'description' => 'Support opérationnel : classes, inscriptions, paiements, attestations. Consulte les utilisateurs sans les modifier.',
            'droits' => [
                'catalogue'    => 'gestion',
                'classes'      => 'gestion',
                'inscriptions' => 'gestion',
                'formateurs'   => 'gestion',
                'entreprises'  => 'gestion',
                'quiz'         => 'gestion',
                'attestations' => 'gestion',
                'evaluations'  => 'lecture',
                'utilisateurs' => 'lecture',
            ],
        ],
    ],

    // Le super-admin n'est pas dans la liste ci-dessus : il a tous les droits partout.
    'super_admin' => ['libelle' => 'Super-admin'],

    // Profil appliqué à un compte administrateur sans profil (par prudence : le plus limité).
    'profil_par_defaut' => 'assistant',

    // Modules : clé => nom affiché dans le tableau récapitulatif
    'modules' => [
        'catalogue'    => 'Catalogue (cours, chapitres, catégories)',
        'classes'      => 'Classes et sessions',
        'inscriptions' => 'Inscriptions et paiements',
        'formateurs'   => 'Formateurs',
        'entreprises'  => 'Entreprises',
        'quiz'         => 'Quiz et banque de questions',
        'attestations' => 'Attestations',
        'evaluations'  => 'Évaluations',
        'utilisateurs' => 'Utilisateurs',
    ],

    // Quel module protège quelle route (par nom de route). La PREMIÈRE ligne qui correspond gagne :
    // les motifs précis doivent donc rester AVANT les motifs larges (ex. cours.classes avant cours).
    // Une route admin absente de cette liste est réservée au super-admin (sécurité par défaut).
    'routes' => [
        'admin.cours.classes.*' => 'classes',
        'admin.classes.*'       => 'classes',
        'admin.sessions.*'      => 'classes',
        'admin.cours.*'         => 'catalogue',
        'admin.categories.*'    => 'catalogue',
        'admin.inscriptions.*'  => 'inscriptions',
        'paiements.store'       => 'inscriptions',
        'admin.formateurs.*'    => 'formateurs',
        'admin.entreprises.*'   => 'entreprises',
        'admin.questions.*'     => 'quiz',
        'admin.quiz.*'          => 'quiz',
        'admin.attestations.*'  => 'attestations',
        'admin.evaluations.*'   => 'evaluations',
        'admin.users.*'         => 'utilisateurs',
    ],
];