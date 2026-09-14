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
     */
    public function ajouterUtilisateur(Request $request, Cours $cours, Classe $classe)
    {
        $data = $request->validate(['user_id' => ['required', 'exists:users,id']]);

        // firstOrCreate évite un doublon, cohérent avec la contrainte
        // UNIQUE(user_id, classe_id) déjà en base
        Inscription::firstOrCreate(
            ['user_id' => $data['user_id'], 'classe_id' => $classe->id],
            ['date_inscription' => now()]
        );

        return redirect()->route('admin.cours.classes.index', $cours)
            ->with('success', 'Utilisateur ajouté à la classe.');
    }

    public function retirerUtilisateur(Cours $cours, Classe $classe, User $user)
    {
        Inscription::where('classe_id', $classe->id)->where('user_id', $user->id)->delete();

        return redirect()->route('admin.cours.classes.index', $cours)
            ->with('success', 'Utilisateur retiré de la classe.');
    }
}