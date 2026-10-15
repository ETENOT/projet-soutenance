<?php

namespace App\Providers;

use App\Listeners\EnvoyerMailBienvenue;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        // ← ajouté : inscription finalisée (code à 6 chiffres validé) : email de bienvenue.
        Verified::class => [
            EnvoyerMailBienvenue::class,
        ],
    ];

    /**
     * ← ajouté : les listeners sont déclarés explicitement dans $listen ci-dessus ;
     * on désactive la découverte automatique pour éviter qu'un listener soit
     * enregistré deux fois (ce qui enverrait l'email de bienvenue en double).
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }

    public function boot()
    {
        //
    }
}