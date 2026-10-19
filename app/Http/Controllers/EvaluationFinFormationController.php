<?php

namespace App\Http\Controllers;

use App\Models\Avis;
use App\Models\AvisFormateur;
use App\Models\Classe;
use App\Models\EvaluationFormation;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

// Côté STAGIAIRE : évaluer la formation à la fin d'une classe (formateur + formation), en étoiles.
// Une seule page, qui alimente trois choses :
//  - avis_formateurs        : les 3 critères du formateur (admin + formateur)
//  - evaluations_formation  : les critères de la formation et les remarques (admin seulement)
//  - avis                   : la satisfaction globale = l'avis public du cours (même système d'étoiles)
class EvaluationFinFormationController extends Controller
{
    /**
     * Affiche la fiche (pré-remplie si le stagiaire l'a déjà remplie).
     */
    public function create(Request $request, Classe $classe)
    {
        if ($refus = $this->refuser($request, $classe)) {
            return $refus;
        }

        $user = $request->user();
        $classe->load('cours', 'formateur');

        return view('evaluations.create', [
            'classe' => $classe,
            'avisFormateur' => AvisFormateur::where('classe_id', $classe->id)->where('user_id', $user->id)->first(),
            'evaluation' => EvaluationFormation::where('classe_id', $classe->id)->where('user_id', $user->id)->first(),
            'avisCours' => Avis::where('cours_id', $classe->cours_id)->where('user_id', $user->id)->first(),
        ]);
    }

    /**
     * Enregistre la fiche. user_id et classe_id viennent de la session et de l'URL,
     * jamais du formulaire (sécurité). Refaire la fiche met à jour (une seule par classe).
     */
    public function store(Request $request, Classe $classe)
    {
        if ($refus = $this->refuser($request, $classe)) {
            return $refus;
        }

        $user = $request->user();
        $aFormateur = $classe->formateur_id !== null;

        // --- Règles : toutes les notes sont obligatoires (1 à 5), les textes facultatifs ---
        $criteres = EvaluationFormation::CRITERES;
        $notes = collect($criteres)->collapse()->all();
        if ($aFormateur) {
            $notes += AvisFormateur::CRITERES;
        }
        $notes['note_globale'] = 'Satisfaction globale';

        $regles = [];
        $messages = [];
        foreach ($notes as $colonne => $libelle) {
            $regles[$colonne] = ['required', 'integer', 'between:1,5'];
            $messages["$colonne.required"] = "Choisissez une note de 1 à 5 étoiles pour « $libelle ».";
            $messages["$colonne.integer"] = "La note de « $libelle » n'est pas valide.";
            $messages["$colonne.between"] = "La note de « $libelle » doit être comprise entre 1 et 5.";
        }

        $regles['commentaire_formateur'] = ['nullable', 'string', 'max:1000'];
        $messages['commentaire_formateur.max'] = 'Le commentaire sur le formateur ne doit pas dépasser 1000 caractères.';
        // Commentaire public sur la formation (affiché dans l'onglet « Avis » du cours)
        $regles['commentaire_cours'] = ['nullable', 'string', 'max:1000'];
        $messages['commentaire_cours.max'] = 'Le commentaire sur la formation ne doit pas dépasser 1000 caractères.';
        foreach (EvaluationFormation::TEXTES as $colonne => $libelle) {
            $regles[$colonne] = ['nullable', 'string', 'max:2000'];
            $messages["$colonne.max"] = 'Ce champ ne doit pas dépasser 2000 caractères.';
        }

        $data = $request->validate($regles, $messages);

        DB::transaction(function () use ($data, $classe, $user, $aFormateur) {

            // 1. Le formateur (seulement s'il y en a un d'affecté à la classe)
            if ($aFormateur) {
                AvisFormateur::updateOrCreate(
                    ['classe_id' => $classe->id, 'user_id' => $user->id],
                    Arr::only($data, array_keys(AvisFormateur::CRITERES))
                        + ['commentaire' => $data['commentaire_formateur'] ?? null]
                );
            }

            // 2. La formation : critères + remarques
            EvaluationFormation::updateOrCreate(
                ['classe_id' => $classe->id, 'user_id' => $user->id],
                Arr::only($data, array_merge(
                    EvaluationFormation::colonnesNotes(),
                    array_keys(EvaluationFormation::TEXTES)
                ))
            );

            // 3. Satisfaction globale + commentaire public = avis du cours (même système d'étoiles).
            //    Un champ laissé vide efface l'ancien commentaire (le stagiaire peut le retirer).
            Avis::updateOrCreate(
                ['user_id' => $user->id, 'cours_id' => $classe->cours_id],
                [
                    'note' => $data['note_globale'],
                    'commentaire' => $data['commentaire_cours'] ?? null,
                ]
            );
        });

        return redirect()->route('cours.mes')
            ->with('success', 'Merci ! Votre évaluation de la formation a bien été enregistrée.');
    }

    /**
     * Conditions pour remplir la fiche. Retourne une redirection si refusé, null sinon :
     *  - stagiaire inscrit à la classe avec paiement validé (sinon 403) ;
     *  - formation terminée (dernier jour atteint ou classe fermée).
     */
    private function refuser(Request $request, Classe $classe)
    {
        $estInscritPaye = $classe->inscriptions()->payees()->where('user_id', $request->user()->id)->exists();
        abort_unless($estInscritPaye, 403, 'Seuls les stagiaires inscrits à cette classe peuvent l\'évaluer.');

        if (! $classe->peutEtreEvaluee()) {
            return redirect()->route('cours.mes')
                ->with('error', 'Vous pourrez évaluer la formation à la fin de celle-ci.');
        }

        return null;
    }
}