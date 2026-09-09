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
            // Collection vide par défaut : couvre le cas visiteur non connecté
            // sans avoir à mettre isset()/?? partout dans le Blade.
            $notifications = collect();

            if (Auth::check()) {
                // Les 5 dernières notifications non lues
                $notifications = Auth::user()->notifications()
                    ->where('est_lue', false)
                    ->latest()
                    ->take(5)
                    ->get();
            }

            $view->with('notifications', $notifications);
            $view->with('notificationsCount', $notifications->count());
        });
    }
}