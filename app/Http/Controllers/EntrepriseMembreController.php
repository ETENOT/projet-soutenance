<?php

namespace App\Http\Controllers;

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
}