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

        $inscrit = DB::transaction(function () use ($classe, $user) {
            // lockForUpdate verrouille la ligne "classe" le temps de la transaction :
            // un deuxième clic concurrent sur la même classe attend que celui-ci finisse
            // avant de recompter les places, donc plus de dépassement de capacité.
            $classeVerrouillee = Classe::where('id', $classe->id)->lockForUpdate()->first();

            if ($classeVerrouillee->inscriptions()->count() >= $classeVerrouillee->capacite_max) {
                return false;
            }

            Inscription::create([
                'user_id' => $user->id,
                'classe_id' => $classe->id,
                'date_inscription' => now(),
            ]);

            // Notification dans l'application. Elle est dans la transaction : si elle échoue,
            // l'inscription est annulée aussi. Le message est cohérent avec le dashboard :
            // une inscription sans Paiement compte comme "impayée".
            Notification::create([
                'user_id' => $user->id,
                'message' => 'Inscription enregistrée : « ' . $classe->cours->titre . ' », début le '
                    . $classe->date_debut->format('d/m/Y') . '. Paiement en attente.',
            ]);

            return true;
        });

        if (! $inscrit) {
            return back()->with('error', 'Cette classe est déjà complète.');
        }

        // Email envoyé APRÈS la transaction : un problème d'envoi (SMTP indisponible...)
        // ne doit jamais annuler une inscription déjà enregistrée. On journalise l'erreur
        // avec report() au lieu de la laisser remonter à l'utilisateur.
        try {
            $user->notify(new InscriptionEnregistree($classe));
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()->route('cours.mes')
            ->with('success', 'Inscription confirmée pour "' . $classe->nom . '".');
    }

    /**
     * Désinscrit l'utilisateur connecté d'une classe.
     */
    public function destroy(Classe $classe)
    {
        Inscription::where('classe_id', $classe->id)
            ->where('user_id', Auth::id())
            ->delete();

        return back()->with('success', 'Désinscription effectuée.');
    }
}