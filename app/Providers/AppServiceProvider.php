<?php

namespace App\Providers;

use App\Http\Middleware\AbandonnerQuizVisiteur;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Vite;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */

    public function boot()
    {
        Schema::defaultStringLength(191);

        // Pagination au format Bootstrap 5 (Velzon n'utilise pas Tailwind)
        Paginator::useBootstrapFive();

        Vite::useManifestFilename('.vite/manifest.json');

        // Ajoute mon middleware à la liste de priorité de Laravel, juste après StartSession.
        // Sans cela, Laravel peut le placer avant le démarrage de la session,
        // et la lecture de $request->session() échoue.
        app(Kernel::class)->addToMiddlewarePriorityAfter(
            StartSession::class,
            AbandonnerQuizVisiteur::class
        );
    }
}