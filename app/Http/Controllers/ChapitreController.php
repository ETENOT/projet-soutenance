<?php

namespace App\Http\Controllers;

use App\Models\Chapitre;
use App\Models\Cours;
use Illuminate\Http\Request;

class ChapitreController extends Controller
{
    public function create(Cours $cours)
    {
        return view('chapitres.create', compact('cours'));
    }

    public function edit(Cours $cours, Chapitre $chapitre)
    {
        $this->verifierCoursParent($cours, $chapitre);

        return view('chapitres.edit', compact('cours', 'chapitre'));
    }

    /**
     * Enregistre un nouveau chapitre dans un cours.
     */
    public function store(Request $request, Cours $cours)
    {
        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'contenu' => 'nullable|string',
            'ordre' => 'nullable|integer|min:1',
        ]);

        if (!isset($validated['ordre'])) {
            $dernierOrdre = $cours->chapitres()->max('ordre');

            $validated['ordre'] = $dernierOrdre
                ? $dernierOrdre + 1
                : 1;
        }

        $validated['cours_id'] = $cours->id;

        Chapitre::create($validated);

        return redirect()
            ->route('admin.cours.contenus', $cours)
            ->with('success', 'Le chapitre a été ajouté avec succès.');
    }

    public function update(Request $request, Cours $cours, Chapitre $chapitre)
    {
        $this->verifierCoursParent($cours, $chapitre);

        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'contenu' => 'nullable|string',
            'ordre' => 'nullable|integer|min:1',
        ]);

        $chapitre->update($validated);

        return redirect()
            ->route('admin.cours.contenus', $cours)
            ->with('success', 'Le chapitre a été modifié avec succès.');
    }

    public function destroy(Cours $cours, Chapitre $chapitre)
    {
        $this->verifierCoursParent($cours, $chapitre);

        $chapitre->delete();

        return redirect()
            ->route('admin.cours.contenus', $cours)
            ->with('success', 'Le chapitre a été supprimé avec succès.');
    }

    private function verifierCoursParent(Cours $cours, Chapitre $chapitre): void
    {
        abort_unless($chapitre->cours_id === $cours->id, 404);
    }
}