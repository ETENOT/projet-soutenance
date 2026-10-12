<?php

namespace App\Http\Controllers\Formateur;

use App\Models\Classe;
use App\Models\Presence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Feuille de présence : le formateur choisit un jour de formation puis coche les présents.
class PresenceController extends FormateurController
{
    /**
     * Affiche la feuille d'un jour + le récapitulatif des taux de présence.
     */
    public function edit(Request $request, Classe $classe)
    {
        $this->classeAutorisee($classe);

        // Jours qu'on peut renseigner : jours ouvrés de la classe, jusqu'à aujourd'hui
        $jours = $classe->joursRenseignables();

        // Jour affiché : celui de l'URL (?date=2026-10-07) s'il est valide, sinon le plus récent
        $demande = $request->query('date');
        $jour = $jours->first(fn ($j) => $j->toDateString() === $demande) ?? $jours->last();

        $inscrits = $this->inscritsPayes($classe);

        // Ids des stagiaires déjà marqués présents ce jour-là (pour pré-cocher les cases)
        $presentsCeJour = $jour
            ? $classe->presences()->where('date_seance', $jour->toDateString())->where('present', true)->pluck('user_id')->all()
            : [];

        return view('presences.formateur.edit', [
            'classe' => $classe,
            'jours' => $jours,
            'jour' => $jour,
            'inscrits' => $inscrits,
            'presentsCeJour' => $presentsCeJour,
            // Récapitulatif : [user_id => nombre de jours de présence] et nombre total de jours
            'totauxPresence' => $classe->presencesParStagiaire(),
            'totalJours' => $classe->joursDeFormation()->count(),
        ]);
    }

    /**
     * Enregistre la feuille d'un jour (réenregistrer le même jour met à jour, ne duplique pas).
     */
    public function update(Request $request, Classe $classe)
    {
        $this->classeAutorisee($classe);

        // Une classe fermée est figée : les taux ont déjà été calculés et conservés
        if ($classe->estFermee()) {
            return back()->with('error', 'La classe est fermée : la feuille de présence ne peut plus être modifiée.');
        }

        $data = $request->validate([
            'date_seance' => ['required', 'date_format:Y-m-d'],
            'present' => ['nullable', 'array'],
            'present.*' => ['integer'],
        ]);

        // Le jour doit être un jour de formation (ouvré, dans la période) et pas dans le futur
        $jourValide = $classe->joursRenseignables()
            ->contains(fn ($j) => $j->toDateString() === $data['date_seance']);

        if (! $jourValide) {
            return back()->with('error', 'Ce jour ne correspond pas à une séance de la classe (ou n\'a pas encore eu lieu).');
        }

        // Sécurité : on ignore tout identifiant qui n'est pas un stagiaire PAYÉ de cette classe
        $inscritsIds = $classe->inscriptions()->payees()->pluck('user_id');
        $presentsIds = collect($data['present'] ?? [])->map(fn ($id) => (int) $id)->intersect($inscritsIds);

        DB::transaction(function () use ($classe, $data, $inscritsIds, $presentsIds) {
            // Une ligne par stagiaire payé : présent s'il est coché, absent sinon
            foreach ($inscritsIds as $userId) {
                Presence::updateOrCreate(
                    ['classe_id' => $classe->id, 'user_id' => $userId, 'date_seance' => $data['date_seance']],
                    ['present' => $presentsIds->contains($userId)]
                );
            }
        });

        return redirect()
            ->route('presences.formateur.edit', ['classe' => $classe, 'date' => $data['date_seance']])
            ->with('success', 'Feuille de présence enregistrée.');
    }
}