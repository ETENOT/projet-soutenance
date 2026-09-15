<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        // Vérifie le délai même côté serveur : le bouton désactivé en JS
        // empêche un clic normal, mais rien n'empêche quelqu'un de
        // soumettre le formulaire directement (JS désactivé, requête
        // rejouée, etc.). Le vrai garde-fou est ici.
        $remaining = $request->user()->secondsUntilCanResendVerificationCode();

        if ($remaining > 0) {
            return back()->withErrors([
                'resend' => "Merci de patienter encore {$remaining} secondes avant de redemander un code.",
            ]);
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-code-sent');
    }
}