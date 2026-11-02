<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

// Côté ENTREPRISE : onglet « Membres entreprises » du sidebar, en LECTURE SEULE.
// Les membres sont ajoutés, modifiés et retirés par l'administrateur de la plateforme.
class EntrepriseMembreController extends Controller
{
    public function index(Request $request)
    {
        $entreprise = $request->user()->entreprise;
        abort_unless($entreprise, 403, 'Aucune entreprise n’est rattachée à ce compte.');

        $membres = $entreprise->membres()->with('particulier');

        if ($recherche = trim((string) $request->input('q'))) {
            $membres->where(function ($q) use ($recherche) {
                $q->where('users.name', 'like', "%{$recherche}%")
                    ->orWhere('users.email', 'like', "%{$recherche}%");
            });
        }

        return view('espace-entreprise.membres', [
            'entreprise' => $entreprise,
            'membres' => $membres->orderBy('users.name')->paginate(15)->withQueryString(),
        ]);
    }

    public function destroy(Request $request, User $user)
    {
        $entreprise = $request->user()->entreprise;
        abort_unless($entreprise, 403);

        // Sécurité : on ne retire qu'un membre de SA propre entreprise
        $particulier = $user->particulier;
        abort_unless($particulier && (int) $particulier->entreprise_id === (int) $entreprise->id, 404);

        $particulier->update(['entreprise_id' => null]);   // compte et inscriptions conservés
        Notification::create(['user_id' => $user->id,
            'message' => "L’entreprise « {$entreprise->raison_sociale} » vous a retiré de ses membres."]);

        return back()->with('success', "{$user->name} ne fait plus partie de votre entreprise.");
    }
}