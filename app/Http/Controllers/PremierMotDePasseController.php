<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

// Page affichée à la première connexion d'un compte créé par un administrateur :
// l'utilisateur doit remplacer le mot de passe par défaut avant d'accéder à la plateforme.
class PremierMotDePasseController extends Controller
{
    public function edit(Request $request)
    {
        // Rien à changer : on ne laisse pas cette page accessible aux autres comptes
        if (! $request->user()->must_change_password) {
            return redirect()->route('dashboard');
        }

        return view('auth.force-password');
    }

    public function update(Request $request)
    {
        $user = $request->user();

        if (! $user->must_change_password) {
            return redirect()->route('dashboard');
        }

        $request->validate([
            'password' => [
                'required',
                'confirmed',
                Password::defaults(),
                // Interdit de « changer » pour le même mot de passe (le mot de passe par défaut)
                function (string $attribut, mixed $valeur, \Closure $echec) use ($user) {
                    if (Hash::check($valeur, $user->password)) {
                        $echec('Le nouveau mot de passe doit être différent du mot de passe temporaire.');
                    }
                },
            ],
        ], [
            'password.required' => 'Saisissez votre nouveau mot de passe.',
            'password.confirmed' => 'La confirmation ne correspond pas au mot de passe saisi.',
        ]);

        $user->forceFill([
            'password' => Hash::make($request->input('password')),
            'must_change_password' => false,
        ])->save();

        // Retour à la page demandée avant la redirection (ou au tableau de bord)
        return redirect()->intended(route('dashboard'))
            ->with('success', 'Votre mot de passe a été enregistré. Bienvenue sur la plateforme !');
    }
}