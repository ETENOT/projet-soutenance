<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\Inscription;
use Illuminate\Support\Facades\Auth;

class InscriptionController extends Controller
{
    /**
     * Inscrit l'utilisateur connecté à une classe.
     * Cas d'utilisation "s'inscrire au cours" -> <<Include>> "choisir une classe".
     */
    public function store(Classe $classe)
    {
        $user = Auth::user();

        // On vérifie simplement si l'utilisateur est déjà inscrit.
        if ($classe->inscriptions()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'Vous êtes déjà inscrit à cette classe.');
        }

        // On vérifie immédiatement si la classe est complète.
        if ($classe->inscriptions()->count() >= $classe->capacite_max) {
            return back()->with('error', 'Cette classe est déjà complète.');
        }

        // IMPORTANT :
        // Aucune inscription n'est encore créée ici.
        // L'utilisateur doit d'abord choisir son moyen de paiement.
        return redirect()->route('paiements.choix', $classe);
    }

    /**
     * Désinscrit l'utilisateur connecté d'une classe.
     * Refusé si l'inscription est payée : sa suppression effacerait aussi le paiement
     * (cascade en base). L'annulation d'une inscription payée passe par l'administration.
     */
    public function destroy(Classe $classe)
    {
        $inscription = Inscription::where('classe_id', $classe->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($inscription && $inscription->paiement()->exists()) {
            return back()->with('error', 'Cette inscription est payée : contactez l\'administration pour l\'annuler.');
        }

        $inscription?->delete();

        return back()->with('success', 'Désinscription effectuée.');
    }
    
}