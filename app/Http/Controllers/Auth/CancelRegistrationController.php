<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Entreprise;
use App\Models\Particulier;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CancelRegistrationController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Garde-fou : un compte déjà vérifié ne doit jamais être supprimé ici
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('root');
        }

        DB::transaction(function () use ($user) {
            $particulierId = $user->particulier_id;
            $entrepriseId  = $user->entreprise_id;

            $user->clearVerificationCode();
            $user->delete();

            if ($particulierId) {
                Particulier::whereKey($particulierId)->delete();
            }

            // Une entreprise peut être partagée par plusieurs users
            if ($entrepriseId && ! User::where('entreprise_id', $entrepriseId)->exists()) {
                Entreprise::whereKey($entrepriseId)->delete();
            }
        });

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('register');
    }
}