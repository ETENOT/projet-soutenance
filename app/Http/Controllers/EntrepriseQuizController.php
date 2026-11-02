<?php

namespace App\Http\Controllers;

use App\Models\Inscription;
use App\Models\Quiz;
use App\Models\ResultatQuiz;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class EntrepriseQuizController extends Controller
{
    // Entreprise du compte connecté (403 si le compte n'en a pas)
    private function entreprise(Request $request)
    {
        $entreprise = $request->user()->entreprise;
        abort_unless($entreprise, 403, 'Aucune entreprise n’est rattachée à ce compte.');
        return $entreprise;
    }

    // Liste : une ligne par (membre, classe) inscrit par l'entreprise, avec ses statistiques de quiz
    public function index(Request $request)
    {
        $entreprise = $this->entreprise($request);

        $inscriptions = Inscription::faitesParEntreprise($entreprise->id)
            ->with(['user.particulier', 'classe.cours', 'classe.session'])
            // Filtre « session »
            ->when($request->filled('session_id'), fn ($q) => $q->whereHas('classe',
                fn ($c) => $c->where('session_id', $request->integer('session_id'))))
            // Filtre « classe » (bouton « Suivi des quiz » de la page d'une classe)
            ->when($request->filled('classe_id'), fn ($q) => $q->where('classe_id', $request->integer('classe_id')))
            // Recherche par nom ou e-mail du membre
            ->when($request->filled('q'), function ($q) use ($request) {
                $terme = '%' . trim($request->input('q')) . '%';
                $q->whereHas('user', fn ($u) => $u->where('name', 'like', $terme)->orWhere('email', 'like', $terme));
            })
            ->get();

        // UNE seule requête pour toutes les tentatives possibles (membres x cours concernés),
        // puis on répartit en PHP par inscription (évite une requête par ligne).
        $tentatives = $inscriptions->isEmpty() ? collect() : Quiz::query()
            ->whereIn('user_id', $inscriptions->pluck('user_id')->unique())
            ->whereIn('cours_id', $inscriptions->pluck('classe.cours_id')->unique())
            ->whereHas('resultats')
            ->with('resultats')
            ->get();

        $lignes = $inscriptions
            ->map(fn (Inscription $inscription) => [
                'inscription' => $inscription,
                'stats' => $this->statistiques($this->tentativesDe($inscription, $tentatives)),
            ])
            // Classes les plus récentes d'abord, puis membres par ordre alphabétique
            ->sortBy([
                fn ($a, $b) => $b['inscription']->classe->date_debut <=> $a['inscription']->classe->date_debut,
                fn ($a, $b) => strcasecmp($a['inscription']->user->name, $b['inscription']->user->name),
            ])
            ->values();

        return view('espace-entreprise.quiz.index', [
            'entreprise' => $entreprise,
            'lignes' => $lignes,
            'sessions' => $entreprise->sessions()->orderByDesc('date_debut')->get(['id', 'nom']),
            'classeFiltree' => $request->filled('classe_id') ? $inscriptions->first()?->classe : null,
        ]);
    }

    // Détail : courbe d'évolution + liste des tentatives d'UN membre dans UNE classe
    public function show(Request $request, Inscription $inscription)
    {
        $entreprise = $this->entreprise($request);

        // Seulement une inscription faite par CETTE entreprise (404 sinon : on ne révèle rien)
        abort_unless((int) $inscription->via_entreprise_id === (int) $entreprise->id, 404);

        $inscription->load('user.particulier', 'classe.cours', 'classe.formateur', 'classe.session');
        $classe = $inscription->classe;

        $tentatives = Quiz::suivisPourInscription($inscription)
            ->with('resultats')->orderBy('date')->orderBy('heure_debut')->get();

        // Même courbe que celle du particulier, limitée à la période de la classe
        $progression = ResultatQuiz::progressionPour(
            $inscription->user_id, $classe->cours_id,
            $classe->date_debut->toDateString(), $classe->date_fin->toDateString()
        );

        return view('espace-entreprise.quiz.show', [
            'entreprise' => $entreprise,
            'inscription' => $inscription,
            'classe' => $classe,
            'membre' => $inscription->user,
            'tentatives' => $tentatives->reverse()->values(),   // plus récente en haut du tableau
            'stats' => $this->statistiques($tentatives),
            'progression' => $progression,
        ]);
    }

    // Tentatives (déjà chargées) qui correspondent à la règle de Quiz::scopeSuivisPourInscription()
    private function tentativesDe(Inscription $inscription, Collection $tentatives): Collection
    {
        $classe = $inscription->classe;
        $du = $classe->date_debut->toDateString();
        $au = $classe->date_fin->toDateString();

        return $tentatives
            ->filter(function (Quiz $quiz) use ($inscription, $classe, $du, $au) {
                $jour = Carbon::parse($quiz->date)->toDateString();   // date de la tentative (Y-m-d)

                return (int) $quiz->user_id === (int) $inscription->user_id   // ce membre
                    && (int) $quiz->cours_id === (int) $classe->cours_id      // le cours de la classe
                    && $jour >= $du && $jour <= $au;                          // pendant la classe
            })
            ->sortBy(fn (Quiz $quiz) => $quiz->date . ' ' . $quiz->heure_debut)   // chronologique
            ->values();
    }

    // Chiffres affichés ; le barème peut varier d'une tentative à l'autre : on compare en pourcentage
    private function statistiques(Collection $tentatives): array
    {
        $pourcentages = $tentatives->map(function (Quiz $quiz) {
            $bareme = (int) $quiz->bareme;
            $score = (int) ($quiz->resultats->first()?->score ?? 0);
            return $bareme > 0 ? round($score / $bareme * 100, 1) : 0.0;
        });

        $derniere = $tentatives->last();

        return [
            'nombre' => $tentatives->count(),
            'derniere_note' => $derniere ? (int) $derniere->resultats->first()?->score . ' / ' . (int) $derniere->bareme : null,
            'derniere_pct' => $pourcentages->last(),
            'derniere_date' => $derniere ? Carbon::parse($derniere->date) : null,
            'meilleure_pct' => $pourcentages->max(),
            'moyenne_pct' => $pourcentages->isEmpty() ? null : round($pourcentages->avg(), 1),
            // progression entre la 1re et la dernière tentative (en points de pourcentage)
            'evolution' => $pourcentages->count() >= 2 ? round($pourcentages->last() - $pourcentages->first(), 1) : null,
        ];
    }
}