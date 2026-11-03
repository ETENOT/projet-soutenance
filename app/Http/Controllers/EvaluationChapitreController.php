<?php

namespace App\Http\Controllers;

use App\Models\Chapitre;
use App\Models\Cours;
use App\Models\TentativeEvaluation;
use App\Services\ParcoursEvaluationService;
use Illuminate\Http\Request;

/**
 * Côté particulier : quiz de chapitre et quiz final (parcours type Cisco).
 * Routes protégées par auth + role:particulier (les visiteurs anonymes n'y ont pas accès).
 * Le quiz du cours existant reste géré par QuizAttemptController, sans changement.
 */
class EvaluationChapitreController extends Controller
{
    public function __construct(private ParcoursEvaluationService $parcours)
    {
    }

    // Parcours complet : chaque étape avec son état et toutes ses tentatives (historique)
    public function parcours(Request $request, Cours $cours)
    {
        $etapes = $this->parcours->etapes($cours, $request->user());

        // Toutes les tentatives terminées, la plus récente en haut, rangées par étape
        $historique = TentativeEvaluation::where('user_id', $request->user()->id)
            ->where('cours_id', $cours->id)
            ->terminees()
            ->latest('termine_le')
            ->get()
            ->groupBy(fn ($t) => (string) $t->chapitre_id);

        return view('evaluations.parcours', [
            'cours'      => $cours,
            'etapes'     => $etapes,
            'historique' => $historique,
            'inscrit'    => $this->parcours->estInscrit($request->user(), $cours),
        ]);
    }

    // Passer (ou reprendre) le quiz d'un chapitre
    public function chapitre(Request $request, Cours $cours, Chapitre $chapitre)
    {
        // Le chapitre doit appartenir au cours de l'URL
        abort_unless((int) $chapitre->cours_id === (int) $cours->id, 404);

        return $this->passer($request, $cours, $chapitre);
    }

    // Passer (ou reprendre) le quiz final du cours
    public function final(Request $request, Cours $cours)
    {
        return $this->passer($request, $cours, null);
    }

    // Enregistre la réponse à UNE question (AJAX, à chaque clic sur une option)
    public function repondre(Request $request, TentativeEvaluation $tentative)
    {
        $this->verifierProprietaire($request, $tentative);
        abort_if($tentative->estTerminee(), 409);

        $data = $request->validate([
            'question_id' => ['required', 'integer'],
            'option_id'   => ['required', 'integer'],
        ]);

        // La question doit faire partie de CETTE tentative (sinon 404)…
        $reponse = $tentative->reponses()->where('question_id', $data['question_id'])->firstOrFail();

        // … et l'option doit appartenir à cette question (pas de réponse fabriquée)
        abort_unless($reponse->question->options()->whereKey($data['option_id'])->exists(), 422);

        $reponse->update(['option_id' => $data['option_id']]);

        return response()->json(['ok' => true]);
    }

    // Clôture la tentative et affiche la correction
    public function terminer(Request $request, TentativeEvaluation $tentative)
    {
        $this->verifierProprietaire($request, $tentative);

        $tentative = $this->parcours->terminer($tentative);

        return redirect()->route('evaluations.tentative.resultat', $tentative);
    }

    // Abandonne une tentative en cours (elle est supprimée et ne compte pas)
    public function annuler(Request $request, TentativeEvaluation $tentative)
    {
        $this->verifierProprietaire($request, $tentative);

        if ($tentative->estTerminee()) {
            return redirect()->route('evaluations.parcours', $tentative->cours_id)
                ->with('error', 'Ce quiz est déjà terminé, il ne peut plus être annulé.');
        }

        $coursId = $tentative->cours_id;
        $tentative->delete();   // les réponses partent en cascade

        return redirect()->route('evaluations.parcours', $coursId)->with('success', 'Le quiz a été annulé.');
    }

    // Note + correction (✅ bonne réponse, ❌ réponse choisie fausse)
    public function resultat(Request $request, TentativeEvaluation $tentative)
    {
        $this->verifierProprietaire($request, $tentative);

        // Tentative pas encore terminée : on renvoie la terminer
        if (!$tentative->estTerminee()) {
            return redirect($this->urlEtape($tentative));
        }

        $tentative->load('cours', 'chapitre');
        $reponses = $tentative->reponses()->with('question.options')->orderBy('ordre')->get();

        return view('evaluations.resultat', [
            'tentative' => $tentative,
            'reponses'  => $reponses,
            'urlRepasser' => $this->urlEtape($tentative),
        ]);
    }

    // Partie commune à chapitre() et final()
    private function passer(Request $request, Cours $cours, ?Chapitre $chapitre)
    {
        $etape = $this->parcours->etapePour($cours, $request->user(), $chapitre);

        // Étape verrouillée (non inscrit, ou quiz précédent pas encore fait) ou sans question
        if (!$this->parcours->estAccessible($etape)) {
            $message = $etape['raison'] ?? 'Ce quiz ne contient pas encore de questions.';

            return redirect()->route('evaluations.parcours', $cours)->with('error', $message);
        }

        $tentative = $this->parcours->reprendreOuDemarrer($request->user(), $cours, $chapitre);
        $tentative->load('cours', 'chapitre');

        $reponses = $tentative->reponses()->with('question.options')->orderBy('ordre')->get();

        return view('evaluations.passer', compact('tentative', 'reponses'));
    }

    // Un particulier ne touche qu'à ses propres tentatives
    private function verifierProprietaire(Request $request, TentativeEvaluation $tentative): void
    {
        abort_unless((int) $tentative->user_id === (int) $request->user()->id, 403);
    }

    // Adresse du quiz de l'étape de cette tentative (bouton « Repasser le quiz »)
    private function urlEtape(TentativeEvaluation $tentative): string
    {
        return $tentative->estFinal()
            ? route('evaluations.final', $tentative->cours_id)
            : route('evaluations.chapitre', [$tentative->cours_id, $tentative->chapitre_id]);
    }
}