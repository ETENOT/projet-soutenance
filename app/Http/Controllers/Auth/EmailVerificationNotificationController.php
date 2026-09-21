<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        // Si déjà vérifié entre-temps (ex: onglet dupliqué, vérifié dans
        // l'autre), inutile de renvoyer un code.
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

        // Génère un NOUVEAU code (écrase l'ancien, cf. updateOrInsert dans
        // User::generateVerificationCode()) et l'envoie par email.
        $request->user()->sendEmailVerificationNotification();

        // Flash session lu par verify.blade.php pour afficher le message de
        // confirmation "Un nouveau code vient d'être envoyé."
        return back()->with('status', 'verification-code-sent');
    }
}