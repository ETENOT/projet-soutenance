<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategorieController extends Controller
{
    public function index(Request $request)
    {
        // cours_count : nombre de cours de chaque catégorie (sert à bloquer la suppression), en une seule requête SQL
        $recherche = trim((string) $request->input('q'));

        $categories = Categorie::withCount('cours')
            ->when($recherche !== '', fn ($q) => $q->where('nom', 'like', '%' . $recherche . '%'))
            // La dernière catégorie créée s'affiche en haut de la liste
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('categories.index', [
            'categories' => $categories,
            // Total sur toute la base (pas seulement la page ni la recherche)
            'nombreCategories' => Categorie::count(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:255', 'unique:categories,nom'],
        ]);

        Categorie::create($data);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Catégorie créée avec succès.');
    }

    public function update(Request $request, Categorie $categorie)
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:255', Rule::unique('categories', 'nom')->ignore($categorie->id)],
        ]);

        $categorie->update($data);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Catégorie modifiée avec succès.');
    }

    public function destroy(Categorie $categorie)
    {
        if ($categorie->cours()->exists()) {
            return redirect()->route('admin.categories.index')
                ->with('error', 'Cette catégorie ne peut pas être supprimée car des cours y sont rattachés.');
        }

        $categorie->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Catégorie supprimée avec succès.');
    }

    /**
     * Suppression multiple : supprime les catégories cochées dans la liste.
     * Chaque catégorie est traitée séparément : celles qui ont des cours sont ignorées
     * et expliquées dans le message, les autres sont supprimées normalement.
     */
    public function destroyMultiple(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:categories,id'],
        ], [
            'ids.required' => 'Sélectionnez au moins une catégorie.',
        ]);

        $supprimees = 0;
        $ignorees = [];

        foreach (Categorie::withCount('cours')->whereIn('id', $data['ids'])->get() as $categorie) {
            if ($categorie->cours_count > 0) {
                $ignorees[] = "« {$categorie->nom} » : {$categorie->cours_count} cours rattaché(s)";
                continue;
            }

            $categorie->delete();
            $supprimees++;
        }

        // back() : on revient sur la liste avec la même recherche
        $retour = back();
        if ($supprimees > 0) {
            $retour->with('success', "{$supprimees} catégorie(s) supprimée(s).");
        }
        if ($ignorees) {
            $retour->with('error', 'Non supprimée(s) : ' . implode(' ; ', $ignorees) . '.');
        }

        return $retour;
    }
}