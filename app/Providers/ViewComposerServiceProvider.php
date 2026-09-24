<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

// Injecte des données dans layouts.topbar sur toutes les pages,
// vu que ce fichier est inclus partout (pas juste sur /dashboard)
class ViewComposerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::composer('layouts.topbar', function ($view) {
            // Valeurs par défaut : couvre le cas visiteur non connecté
            // sans avoir à mettre isset()/?? partout dans le Blade.
            $notifications = collect();
            $notificationsCount = 0;

            if (Auth::check()) {
                $nonLues = Auth::user()->notifications()->where('est_lue', false);

                // Le badge affiche le vrai total des non lues...
                $notificationsCount = (clone $nonLues)->count();

                // ...mais la liste ne montre que les 5 plus récentes.
                $notifications = $nonLues->latest()->take(5)->get();
            }

            $view->with('notifications', $notifications);
            $view->with('notificationsCount', $notificationsCount);
        });
    }
}