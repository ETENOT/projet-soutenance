<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Traite la soumission du code (POST /verify-email).
 *
 * Remplace la logique par défaut de Breeze, qui utilise
 * Illuminate\Foundation\Auth\EmailVerificationRequest pour valider un LIEN
 * signé présent dans l'URL. Ici, la preuve que l'utilisateur possède bien
 * sa boîte mail passe par un CODE tapé à la main dans un formulaire, donc on
 * n'a plus besoin de ce mécanisme de signature d'URL : une simple Request
 * avec un champ 'code' suffit.
 */
class VerifyEmailController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        // Même garde-fou que dans EmailVerificationPromptController : si
        // l'utilisateur est déjà vérifié, pas besoin de retraiter le code.
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        // digits:6 = exactement 6 caractères, tous numériques.
        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        // Voir User::verificationCodeStatus() : distingue "expiré" de
        // "juste faux" pour donner le bon message à l'utilisateur.
        $status = $request->user()->verificationCodeStatus($request->code);

        if ($status === 'expired') {
            return back()->withErrors([
                'code' => 'Ce code a expiré. Cliquez sur "Renvoyer le code" pour en recevoir un nouveau.',
            ]);
        }

        if ($status === 'invalid') {
            return back()->withErrors([
                'code' => 'Ce code est incorrect. Vérifiez les 6 chiffres et réessayez.',
            ]);
        }

        // markEmailAsVerified() vient du trait Illuminate\Auth\MustVerifyEmail
        // (via Authenticatable) : met email_verified_at à now() et sauvegarde.
        $request->user()->markEmailAsVerified();
        
        // clearVerificationCode() nettoie les champs code et expires_at
        $request->user()->clearVerificationCode();

        // event(new Verified($request->user())) déclenche l'événement Verified
        // qui est écouté par Breeze pour afficher le toast de succès.
        event(new Verified($request->user()));

        // Redirige vers le dashboard avec un message de statut
        return redirect()->route('dashboard')->with('status', 'email-verified');
    }
}