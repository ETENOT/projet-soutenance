<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use Illuminate\Support\Facades\Auth;

// Classe de base des contrôleurs de l'espace formateur.
// Sert uniquement à partager LA règle de sécurité : un formateur ne voit et ne modifie
// que SES classes (l'URL /formateur/classes/5 ne doit pas marcher pour la classe d'un collègue).
abstract class FormateurController extends Controller
{
    // Refuse (403) l'accès si la classe n'est pas affectée au formateur connecté.
    protected function classeAutorisee(Classe $classe): Classe
    {
        abort_unless(
            $classe->formateur_id === Auth::id(),
            403,
            'Cette classe ne vous est pas affectée.'
        );

        return $classe;
    }

    // Les stagiaires qui ont PAYÉ la classe, triés par nom (liste utilisée par plusieurs écrans).
    protected function inscritsPayes(Classe $classe)
    {
        return $classe->inscriptions()
            ->payees()
            ->with('user')
            ->get()
            ->sortBy(fn ($inscription) => mb_strtolower($inscription->user->name ?? ''))
            ->values();
    }
}