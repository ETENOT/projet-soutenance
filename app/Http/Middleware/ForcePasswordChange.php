<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

// Tant que l'utilisateur connecté n'a pas remplacé son mot de passe par défaut
// (compte créé par un administrateur), toutes les pages le renvoient vers la page de changement.
// Seules la page de changement elle-même et la déconnexion restent accessibles.
class ForcePasswordChange
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->must_change_password && ! $request->routeIs('password.force.*', 'logout')) {
            // Requête JSON (ex. recherche en direct) : une redirection n'aurait aucun sens
            if ($request->expectsJson()) {
                abort(403, 'Vous devez d’abord changer votre mot de passe.');
            }

            // guest() mémorise la page demandée : après le changement, l'utilisateur y est renvoyé
            return redirect()->guest(route('password.force.edit'));
        }

        return $next($request);
    }
}