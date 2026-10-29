<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

// Côté PARTICULIER : onglet « Entreprise » du sidebar, en LECTURE SEULE.
// Le rattachement à une entreprise est décidé par l'administrateur : rien à accepter ni à refuser.
class MonEntrepriseController extends Controller
{
    public function index(Request $request)
    {
        return view('espace-particulier.entreprise', [
            // null si le particulier n'est membre d'aucune entreprise
            'entreprise' => $request->user()->particulier?->entreprise,
        ]);
    }
}