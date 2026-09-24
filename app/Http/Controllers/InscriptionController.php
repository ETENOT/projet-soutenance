<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\Inscription;
use App\Models\Notification;
use App\Notifications\InscriptionEnregistree;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InscriptionController extends Controller
{
    /**
     * Inscrit l'utilisateur connecté à une classe.
     * Cas d'utilisation "s'inscrire au cours" -> <<Include>> "choisir une classe".
     */
    public function store(Classe $classe)
    {
        $user = Auth::user();

        if ($classe->inscriptions()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'Vous êtes déjà inscrit à cette classe.');
        }

        $inscription = DB::transaction(function () use ($classe, $user) {
            // lockForUpdate verrouille la ligne "classe" le temps de la transaction :
            // un deuxième clic concurrent sur la même classe attend que celui-ci finisse
            // avant de recompter les places, donc plus de dépassement de capacité.
            $classeVerrouillee = Classe::where('id', $classe->id)
                ->lockForUpdate()
                ->first();

            if ($classeVerrouillee->inscriptions()->count() >= $classeVerrouillee->capacite_max) {
                return false;
            }

            // Création de l'inscription.
            // Elle existe immédiatement mais reste impayée tant qu'aucun
            // enregistrement Paiement n'est créé.
            $inscription = Inscription::create([
                'user_id' => $user->id,
                'classe_id' => $classe->id,
                'date_inscription' => now(),
            ]);

            // Notification dans l'application.
            Notification::create([
                'user_id' => $user->id,
                'message' => 'Inscription enregistrée : « ' . $classe->cours->titre . ' », début le '
                    . $classe->date_debut->format('d/m/Y') . '. Paiement en attente.',
            ]);

            return $inscription;
        });

        if (! $inscription) {
            return back()->with('error', 'Cette classe est déjà complète.');
        }

        // Email envoyé APRÈS la transaction.
        // Un problème d'envoi ne doit pas annuler l'inscription.
        try {
            $user->notify(new InscriptionEnregistree($classe));
        } catch (\Throwable $e) {
            report($e);
        }

        // L'inscription existe maintenant.
        // On envoie l'utilisateur vers la page où il choisira
        // son mode de paiement.
        return redirect()->route('paiements.choix', $inscription);
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