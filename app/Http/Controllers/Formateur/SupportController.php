<?php

namespace App\Http\Controllers\Formateur;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use App\Models\Cours;
use Illuminate\Support\Facades\Auth;

// Page "Supports de cours" du formateur : liste + formulaire de dépôt.
// L'enregistrement du fichier lui-même est fait par CoursResourceController::store()
// (le MÊME code que l'admin : mêmes types, mêmes extensions, mêmes limites).
class SupportController extends Controller
{
    public function index(Cours $cours)
    {
        // Le formateur ne dépose des supports que pour un cours dont il anime au moins une classe
        abort_unless(
            Classe::where('formateur_id', Auth::id())->where('cours_id', $cours->id)->exists(),
            403,
            'Vous n\'animez aucune classe de ce cours.'
        );

        return view('cours.resources.formateur.index', [
            'cours' => $cours,
            'resources' => $cours->resources()->orderByDesc('id')->get(),
        ]);
    }
}