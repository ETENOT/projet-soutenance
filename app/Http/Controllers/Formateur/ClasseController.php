<?php

namespace App\Http\Controllers\Formateur;

use App\Models\Classe;
use App\Services\ClasseClotureService;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClasseController extends FormateurController
{
    /**
     * Liste des classes du formateur : état, dates, lieu, inscrits payés / capacité.
     */
    public function index(Request $request)
    {
        $etats = ['a_venir', 'en_cours', 'terminee'];
        $etatFiltre = in_array($request->query('etat'), $etats, true)
            ? $request->query('etat')
            : null;

        $query = Classe::where('formateur_id', Auth::id())
            ->with('cours')
            // withCount avec filtre : crée l'attribut $classe->payes_count en UNE requête
            // pour toutes les classes (sinon on ferait une requête par ligne du tableau).
            ->withCount(['inscriptions as payes_count' => fn ($q) => $q->payees()]);

        if ($recherche = trim((string) $request->query('q'))) {
            $query->where(function ($q) use ($recherche) {
                $q->where('nom', 'like', "%{$recherche}%")
                    ->orWhere('lieu', 'like', "%{$recherche}%")
                    ->orWhereHas('cours', fn ($cours) => $cours->where('titre', 'like', "%{$recherche}%"));
            });
        }

        if ($etatFiltre) {
            $query->avecEtat($etatFiltre);
        }

        return view('classes.formateur.index', [
            'classes' => $query->orderByDesc('date_debut')->get(),
            'etatFiltre' => $etatFiltre,
            'recherche' => $request->query('q', ''),
        ]);
    }

    /**
     * Détail d'une classe : le cours concerné + la liste des stagiaires qui ont PAYÉ.
     */
    public function show(Classe $classe)
    {
        $this->classeAutorisee($classe);

        $classe->load('cours');

        return view('classes.formateur.show', [
            'classe' => $classe,
            'cours' => $classe->cours,
            'inscrits' => $this->inscritsPayes($classe),
        ]);
    }

    /**
     * Fermer la classe (action réservée au formateur de la classe et à l'administrateur).
     * La logique est dans ClasseClotureService pour rester identique côté admin.
     */
    public function fermer(Classe $classe, ClasseClotureService $service)
    {
        $this->classeAutorisee($classe);

        try {
            $service->fermer($classe, Auth::user());
        } catch (DomainException $e) {
            // Classe déjà fermée ou pas encore commencée : message clair, rien n'est modifié
            return redirect()->route('classes.formateur.show', $classe)->with('error', $e->getMessage());
        }

        return redirect()->route('classes.formateur.show', $classe)
            ->with('success', 'La classe est fermée. Les taux de présence et les attestations ont été calculés.');
    }
}