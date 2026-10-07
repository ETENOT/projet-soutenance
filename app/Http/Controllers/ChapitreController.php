<?php

namespace App\Http\Controllers;

use App\Models\Chapitre;
use App\Models\Cours;
use Illuminate\Http\Request;

class ChapitreController extends Controller
{
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
}