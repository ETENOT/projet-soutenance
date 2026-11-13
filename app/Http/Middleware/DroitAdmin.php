<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Vérifie qu'un administrateur a le droit d'ouvrir la page demandée.
 *
 * - le module est trouvé grâce au nom de la route (voir config/droits.php) ;
 * - consulter (GET/HEAD) demande au moins « lecture », tout le reste demande « gestion » ;
 * - une route absente de la configuration est réservée au super-admin.
 *
 * À placer APRÈS 'role:admin' (ce dernier vérifie déjà que l'utilisateur est administrateur).
 */
class DroitAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $module = $this->moduleDeLaRoute($request->route()?->getName());

        // Route non répertoriée : seul le super-admin passe
        if ($module === null) {
            abort_unless($user->estSuperAdmin(), 403, 'Accès réservé au super-administrateur.');

            return $next($request);
        }

        $niveauRequis = $request->isMethodSafe() ? 'lecture' : 'gestion';

        abort_unless(
            $user->peut($module, $niveauRequis),
            403,
            "Votre profil n'autorise pas cette action."
        );

        return $next($request);
    }

    private function moduleDeLaRoute(?string $nomRoute): ?string
    {
        if ($nomRoute === null) {
            return null;
        }

        foreach (config('droits.routes', []) as $motif => $module) {
            if (Str::is($motif, $nomRoute)) {
                return $module;
            }
        }

        return null;
    }
}