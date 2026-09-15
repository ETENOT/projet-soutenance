<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

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

        $request->user()->markEmailAsVerified();
        $request->user()->clearVerificationCode();

        event(new Verified($request->user()));

        return redirect()->route('dashboard')->with('status', 'email-verified');
    }
}