<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Vite;

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
        //
        Schema::defaultStringLength(191);

        // Vite 5 génère le manifest dans .vite/manifest.json au lieu de manifest.json
        // directement à la racine de public/build/. On indique explicitement à Laravel où le trouver.
        Vite::useManifestFilename('.vite/manifest.json');
    }
}