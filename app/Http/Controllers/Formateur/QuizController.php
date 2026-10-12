<?php

namespace App\Http\Controllers\Formateur;

use App\Models\Classe;
use App\Models\Quiz;

// Résultats aux quiz des stagiaires d'une classe, avec TROIS restrictions cumulatives :
//  1. uniquement les stagiaires de la classe qui ont payé ;
//  2. uniquement les quiz passés PENDANT la période de la classe (date_debut → date_fin) ;
//  3. uniquement le quiz du cours de cette classe.
class QuizController extends FormateurController
{
    public function index(Classe $classe)
    {
        $this->classeAutorisee($classe);

        $classe->load('cours');

        // (1) stagiaires payés de la classe
        $stagiairesIds = $classe->inscriptions()->payees()->pluck('user_id');

        $tentatives = Quiz::query()
            // (3) quiz du cours de la classe uniquement
            ->where('cours_id', $classe->cours_id)
            ->whereIn('user_id', $stagiairesIds)
            // (2) période d'affectation = période de la classe (bornes incluses)
            ->whereBetween('date', [$classe->date_debut->toDateString(), $classe->date_fin->toDateString()])
            // Seules les tentatives TERMINÉES ont un résultat (les abandonnées/en cours sont exclues)
            ->whereHas('resultats')
            ->with(['user', 'resultats'])
            ->orderByDesc('date')
            ->orderByDesc('heure_debut')
            ->get();

        return view('quiz.formateur.index', [
            'classe' => $classe,
            'cours' => $classe->cours,
            'tentatives' => $tentatives,
        ]);
    }
}