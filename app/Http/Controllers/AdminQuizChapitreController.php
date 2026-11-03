<?php

namespace App\Http\Controllers;

use App\Models\Cours;
use App\Models\TentativeEvaluation;
use Illuminate\Http\Request;

/**
 * Administration des quiz de chapitre et du quiz final d'un cours :
 * « noté sur » de chaque chapitre et du quiz final, nombre de questions disponibles,
 * nombre de tentatives. Les questions se rattachent aux chapitres depuis la banque de questions.
 * Le réglage du quiz du cours (cours.note_sur) reste sur la page « Réglages de notation ».
 */
class AdminQuizChapitreController extends Controller
{
    public function edit(Cours $cours)
    {
        // questions_count : questions de la banque rattachées à chaque chapitre
        $chapitres = $cours->chapitres()
            ->withCount(['questions' => fn ($q) => $q->where('cours_id', $cours->id)])
            ->withCount('tentativesEvaluation')
            ->get();

        return view('quiz.admin.chapitres', [
            'cours'               => $cours,
            'chapitres'           => $chapitres,
            // Le quiz final pioche dans toutes les questions rattachées à un chapitre
            'questionsFinal'      => $chapitres->sum('questions_count'),
            'tentativesFinal'     => TentativeEvaluation::where('cours_id', $cours->id)->whereNull('chapitre_id')->count(),
            // Questions du cours qui ne sont rattachées à aucun chapitre (utilisées seulement par le quiz du cours)
            'questionsGenerales'  => $cours->questions()->whereNull('chapitre_id')->count(),
        ]);
    }

    public function update(Request $request, Cours $cours)
    {
        $data = $request->validate([
            // note_sur[ID_DU_CHAPITRE] = valeur ; vide = toutes les questions du chapitre
            'note_sur'       => ['array'],
            'note_sur.*'     => ['nullable', 'integer', 'min:1', 'max:100'],
            'note_sur_final' => ['nullable', 'integer', 'min:1', 'max:100'],
        ], [
            'note_sur.*.integer'     => 'La note doit être un nombre entier.',
            'note_sur.*.min'         => 'La note doit être d\'au moins 1.',
            'note_sur.*.max'         => 'La note ne peut pas dépasser 100.',
            'note_sur_final.integer' => 'La note du quiz final doit être un nombre entier.',
            'note_sur_final.min'     => 'La note du quiz final doit être d\'au moins 1.',
            'note_sur_final.max'     => 'La note du quiz final ne peut pas dépasser 100.',
        ]);

        $valeurs = $data['note_sur'] ?? [];

        // On ne modifie que les chapitres de CE cours présents dans le formulaire
        foreach ($cours->chapitres as $chapitre) {
            if (array_key_exists($chapitre->id, $valeurs)) {
                $chapitre->update(['quiz_note_sur' => $valeurs[$chapitre->id]]);
            }
        }

        // Ne touche pas à cours.note_sur (quiz du cours existant)
        $cours->update(['note_sur_final' => $data['note_sur_final'] ?? null]);

        // Les tentatives déjà commencées gardent leur barème (figé au démarrage)
        return redirect()->route('admin.cours.quiz-chapitres', $cours)
            ->with('success', 'Les réglages des quiz de chapitre ont été enregistrés.');
    }
}