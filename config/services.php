<?php

return [
/* ce code dit à laravel où trouver les paramètres singpay 
    sans écrire les informations sensibles directement dans le contrôleur
*/
    'singpay' => [
        'base_url' => env('SINGPAY_BASE_URL', 'https://gateway.singpay.ga/v1'),
        'client_id' => env('SINGPAY_CLIENT_ID'),
        'client_secret' => env('SINGPAY_CLIENT_SECRET'),
        'wallet_id' => env('SINGPAY_WALLET_ID'),
        'public_url' => env('SINGPAY_PUBLIC_URL'),
        'logo_url' => env('SINGPAY_LOGO_URL'),
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
