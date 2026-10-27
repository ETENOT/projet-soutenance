<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategorieController extends Controller
{
    public function index()
    {
        return view('categories.index', [
            'categories' => Categorie::withCount('cours')->orderBy('nom')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:255', 'unique:categories,nom'],
        ]);

        Categorie::create($data);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Catégorie ajoutée avec succès.');
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
}
