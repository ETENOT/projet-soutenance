<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

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