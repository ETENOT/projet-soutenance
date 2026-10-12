<?php

namespace App\Http\Controllers;

use App\Models\Avis;
use App\Models\Cours;
use Illuminate\Http\Request;

class AvisController extends Controller
{
    /**
     * Enregistre l'avis de l'utilisateur connecté sur un cours.
     * S'il en a déjà un, il est mis à jour (un seul avis par personne et par cours).
     */
    public function store(Request $request, Cours $cours)
    {
        $user = $request->user();

        // Seuls les inscrits dont le paiement est validé peuvent noter
        if (! $user->aPayeCours($cours->id)) {
            return redirect(route('cours.show', $cours) . '#avis')
                ->with('error', 'Seuls les stagiaires inscrits à cette formation peuvent donner leur avis.');
        }

        $data = $request->validate([
            'note' => ['required', 'integer', 'between:1,5'],
            'commentaire' => ['nullable', 'string', 'max:1000'],
        ], [
            'note.required' => 'Choisissez une note de 1 à 5 étoiles.',
            'note.integer' => 'La note doit être un nombre entier.',
            'note.between' => 'La note doit être comprise entre 1 et 5.',
            'commentaire.max' => 'Le commentaire ne doit pas dépasser 1000 caractères.',
        ]);

        // user_id et cours_id viennent de la session et de l'URL, jamais du formulaire (sécurité)
        Avis::updateOrCreate(
            ['user_id' => $user->id, 'cours_id' => $cours->id],
            ['note' => $data['note'], 'commentaire' => $data['commentaire'] ?? null]
        );

        return redirect(route('cours.show', $cours) . '#avis')
            ->with('success', 'Merci, votre avis a bien été enregistré.');
    }

    /**
     * Supprime un avis : son auteur ou un administrateur (modération).
     */
    public function destroy(Request $request, Avis $avis)
    {
        $user = $request->user();

        abort_unless(
            $user->id === $avis->user_id || $user->role?->nom === 'admin',
            403
        );

        $cours = $avis->cours_id;
        $avis->delete();

        return redirect(route('cours.show', $cours) . '#avis')
            ->with('success', 'Avis supprimé.');
    }
}