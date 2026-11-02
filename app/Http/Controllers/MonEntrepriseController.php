<?php
// app/Http/Controllers/MonEntrepriseController.php
namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class MonEntrepriseController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Inscriptions faites PAR UNE ENTREPRISE et payées (celles du particulier restent dans « Mes classes »).
        // On garde celles d'une ancienne entreprise (historique).
        $inscriptions = $user->inscriptions()
            ->faitesParEntreprise()
            ->payees()
            ->with('classe.cours', 'classe.session', 'viaEntreprise', 'attestation')
            ->get();

        // Même rangement que « Mes classes » : l'état vient de la classe (fermée ou fin dépassée = terminée)
        $parEtat = fn (string $etat) => $inscriptions->filter(fn ($i) => $i->classe->etat === $etat);

        return view('espace-particulier.entreprise', [
            'entreprise' => $user->particulier?->entreprise,   // null si aucune entreprise
            'inscriptionsEnCours' => $parEtat('en_cours')->sortBy(fn ($i) => $i->classe->date_debut)->values(),
            'prochainesFormations' => $parEtat('a_venir')->sortBy(fn ($i) => $i->classe->date_debut)->values(),
            'anciensCours' => $parEtat('terminee')->sortByDesc(fn ($i) => $i->classe->date_fin)->values(),
            // Classes déjà évaluées par le particulier :
            // sert à afficher « Modifier mon évaluation » au lieu de « Évaluer la formation »
            'classesEvaluees' => $user->evaluationsFormation()->pluck('classe_id')->toArray(),
        ]);
    }

    // Le particulier quitte son entreprise ; ses inscriptions faites via elle sont conservées
    public function quitter(Request $request)
    {
        $user = $request->user();
        $particulier = $user->particulier;
        $entreprise = $particulier?->entreprise;

        abort_unless($entreprise, 404, 'Vous n’êtes membre d’aucune entreprise.');

        $particulier->update(['entreprise_id' => null]);

        // On prévient le(s) compte(s) de l'entreprise
        foreach ($entreprise->users as $compte) {
            Notification::create(['user_id' => $compte->id, 'message' => "{$user->name} a quitté votre entreprise."]);
        }

        return redirect()->route('particulier.entreprise.index')
            ->with('success', "Vous avez quitté l’entreprise « {$entreprise->raison_sociale} ».");
    }
}