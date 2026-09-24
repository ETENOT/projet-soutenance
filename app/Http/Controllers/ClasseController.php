<?php

namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\Cours;
use App\Models\User;
use App\Models\Inscription;
use Illuminate\Http\Request;

class ClasseController extends Controller
{
    public function index(Cours $cours)
    {
        $classes = $cours->classes()->orderBy('date_debut')->get();

        return view('classes.index', ['cours' => $cours, 'classes' => $classes]);
    }

    public function create(Cours $cours)
    {
        return view('classes.create', ['cours' => $cours]);
    }

    private function validationRules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:255'],
            'capacite_max' => ['required', 'integer', 'min:1', 'max:255'],
            // le lieu de la formation (colonne "lieu" de la table classes)
            'lieu' => ['required', 'string', 'max:255'],
            'date_debut' => ['required', 'date'],
            // la date de fin ne peut pas précéder la date de début
            'date_fin' => ['required', 'date', 'after_or_equal:date_debut'],
        ];
    }

    public function store(Request $request, Cours $cours)
    {
        $data = $request->validate($this->validationRules());

        // cours_id forcé depuis l'URL, jamais depuis le formulaire (sécurité)
        $cours->classes()->create($data);

        return redirect()->route('admin.cours.classes.index', $cours)
            ->with('success', 'Classe créée avec succès.');
    }

    public function edit(Cours $cours, Classe $classe)
    {
        // Charge les inscrits, leur utilisateur et leur paiement en une fois (3 requêtes en tout).
        // Sans ça, la vue ferait une requête par ligne pour retrouver l'utilisateur
        // et savoir si l'inscription est payée.
        $classe->load('inscriptions.user', 'inscriptions.paiement');

        return view('classes.edit', ['cours' => $cours, 'classe' => $classe]);
    }

    public function update(Request $request, Cours $cours, Classe $classe)
    {
        $classe->update($request->validate($this->validationRules()));

        return redirect()->route('admin.cours.classes.index', $cours)
            ->with('success', 'Classe modifiée avec succès.');
    }

    public function destroy(Cours $cours, Classe $classe)
    {
        $classe->delete();

        return redirect()->route('admin.cours.classes.index', $cours)
            ->with('success', 'Classe supprimée avec succès.');
    }

    /**
     * "Reporter sessions d'une classe" — décale uniquement les dates,
     * séparé de update() pour rester fidèle au diagramme.
     */
    public function reporter(Request $request, Cours $cours, Classe $classe)
    {
        $data = $request->validate([
            'date_debut' => ['required', 'date'],
            'date_fin' => ['required', 'date', 'after_or_equal:date_debut'],
        ]);

        $classe->update($data);

        return redirect()->route('admin.cours.classes.index', $cours)
            ->with('success', 'Sessions reportées avec succès.');
    }

    /**
     * Ajout manuel d'un utilisateur (hors flux d'inscription/paiement classique).
     * Contrairement à InscriptionController::store(), cette action ne crée ni notification
     * ni email : l'utilisateur ajouté n'est pas prévenu, et l'inscription reste "impayée".
     */
    public function ajouterUtilisateur(Request $request, Cours $cours, Classe $classe)
    {
        $data = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'distinct', 'exists:users,id'],
        ]);

        $inscritsIds = $classe->inscriptions()->pluck('user_id');
        $nouveauxIds = collect($data['user_ids'])->diff($inscritsIds);

        // Vérifie la capacité en tenant compte uniquement des nouveaux inscrits.
        if ($inscritsIds->count() + $nouveauxIds->count() > $classe->capacite_max) {
            return redirect()->route('admin.cours.classes.edit', [$cours, $classe])
                ->with('error', 'Impossible d\'ajouter ces utilisateurs : la capacité maximale de la classe serait dépassée.');
        }

        foreach ($nouveauxIds as $userId) {
            Inscription::firstOrCreate(
                ['user_id' => $userId, 'classe_id' => $classe->id],
                ['date_inscription' => now()]
            );
        }

        // Retourne sur la page de gestion pour conserver le contexte de la classe.
        return redirect()->route('admin.cours.classes.edit', [$cours, $classe])
            ->with('success', 'Utilisateurs ajoutés à la classe.');
    }

    public function retirerUtilisateur(Cours $cours, Classe $classe, User $user)
    {
        Inscription::where('classe_id', $classe->id)->where('user_id', $user->id)->delete();

        // Après le retrait, reste sur la même page pour gérer la classe sans navigation inutile.
        return redirect()->route('admin.cours.classes.edit', [$cours, $classe])
            ->with('success', 'Utilisateur retiré de la classe.');
    }
}