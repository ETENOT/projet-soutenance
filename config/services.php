<?php

return [
/*
 * Passerelle Airtel Money (Airtel Africa Open API) : paiements en ligne par push USSD.
 * Identifiants fournis par le portail développeur Airtel (developers.airtel.africa).
 *  - Environnement de test  : https://openapiuat.airtel.africa
 *  - Production             : https://openapi.airtel.africa
 * Le code n'y écrit jamais ces valeurs : tout vient du .env.
 */
    'airtel' => [
        'base_url' => env('AIRTEL_BASE_URL', 'https://openapiuat.airtel.africa'),
        'client_id' => env('AIRTEL_CLIENT_ID'),
        'client_secret' => env('AIRTEL_CLIENT_SECRET'),

        // Pays et devise du compte marchand (Gabon, franc CFA) : envoyés à chaque appel
        'pays' => env('AIRTEL_PAYS', 'GA'),
        'devise' => env('AIRTEL_DEVISE', 'XAF'),

        // Indicatif téléphonique du pays (retiré du numéro avant l'envoi à Airtel)
        'indicatif' => env('AIRTEL_INDICATIF', '241'),

        // Numéro envoyé à Airtel : 8 chiffres sans indicatif (74123456) par défaut ;
        // true = avec le 0 local (074123456), si le compte marchand l'exige (à vérifier en test)
        'msisdn_avec_zero' => (bool) env('AIRTEL_MSISDN_AVEC_ZERO', false),

        // Chemins de l'API (version 1 de l'API de collecte par défaut)
        'chemin_paiement' => env('AIRTEL_CHEMIN_PAIEMENT', '/merchant/v1/payments/'),
        'chemin_statut' => env('AIRTEL_CHEMIN_STATUT', '/standard/v1/payments/'),
    ],

    /*
     * Connexion « Continuer avec Google » (Laravel Socialite).
     * Les identifiants viennent de la Google Cloud Console (API et services > Identifiants >
     * ID client OAuth 2.0, type « Application Web »). L'URL de redirection déclarée chez Google
     * doit être EXACTEMENT celle de GOOGLE_REDIRECT_URI.
     */
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', '/auth/google/callback'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

];
