<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
        // Vérifie qu'un utilisateur est connecté
        // et qu'un rôle lui est bien associé.
        if (!$request->user() || !$request->user()->role) {
            abort(403);
        }

        // Vérifie que le rôle de l'utilisateur correspond
        // au rôle demandé par la route.
        if ($request->user()->role->nom !== $role) {
            abort(403);
        }

        // Si toutes les vérifications sont réussies,
        // la requête peut continuer.
        return $next($request);
    }
}