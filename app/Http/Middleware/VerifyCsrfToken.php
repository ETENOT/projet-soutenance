<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Notification de rappel d'Airtel Money : envoyée par les serveurs d'Airtel, sans jeton CSRF
        'paiements/airtel/rappel',
    ];
}
