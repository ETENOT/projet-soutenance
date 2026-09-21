<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Affiche la page de saisie du code (GET /verify-email).
 *
 * C'est la route vers laquelle le middleware 'verified' redirige
 * automatiquement tout utilisateur connecté mais non vérifié dès qu'il
 * essaie d'accéder à une route protégée par 'verified' (ex: /dashboard) —
 * 'verification.notice' est le nom de route attendu EN DUR par
 * Illuminate\Auth\Middleware\EnsureEmailIsVerified, on ne peut pas le
 * renommer.
 *
 * Contrôleur "invokable" (une seule action -> __invoke), comme le reste des
 * contrôleurs Breeze à une seule méthode dans ce projet.
 */
class EmailVerificationPromptController extends Controller
{
    public function __invoke(Request $request): RedirectResponse|View
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        // On calcule le délai restant à CHAQUE affichage de la page (donc
        // aussi après une erreur de code, puisque back() revient ici en GET) :
        // le décompte JS repart toujours de la vraie valeur côté serveur,
        // jamais d'une valeur périmée en cache navigateur.
        return view('auth.verify', [
            'resendAvailableInSeconds' => $request->user()->secondsUntilCanResendVerificationCode(),
        ]);
    }
}