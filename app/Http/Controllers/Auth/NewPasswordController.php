<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Nous allons ici tenter de réinitialiser le mot de passe de l'utilisateur. En cas de succès,
        // nous mettrons à jour le mot de passe sur le modèle utilisateur correspondant et l'enregistrerons
        // en base de données. Sinon, nous analyserons l'erreur et renverrons la réponse.
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // Si le mot de passe a été réinitialisé avec succès, nous redirigerons l'utilisateur
        // vers la vue d'accueil de l'application réservée aux utilisateurs authentifiés. En cas d'erreur,
        // nous pouvons le renvoyer à sa page d'origine avec le message d'erreur correspondant.
        return $status == Password::PASSWORD_RESET
                    ? redirect()->route(Auth::check() ? 'profile.edit' : 'login')->with('status', __($status))
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
    }
}
