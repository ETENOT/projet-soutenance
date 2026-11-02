<?php
// app/Http/Controllers/EntrepriseSessionController.php  (lecture seule)
namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\Inscription;
use App\Models\SessionFormation;
use Illuminate\Http\Request;

class EntrepriseSessionController extends Controller
{
    private function entreprise(Request $request)
    {
        $entreprise = $request->user()->entreprise;
        abort_unless($entreprise, 403, 'Aucune entreprise n’est rattachée à ce compte.');
        return $entreprise;
    }

    // Une session n'est consultable que par SON entreprise
    private function autoriser(Request $request, SessionFormation $session): void
    {
        abort_unless((int) $session->entreprise_id === (int) $this->entreprise($request)->id, 404);
    }

    // Sessions de l'entreprise, rangées en cours / à venir / terminées
    public function index(Request $request)
    {
        $entreprise = $this->entreprise($request);

        $sessions = $entreprise->sessions()
            ->with('classes')            // nécessaire au calcul de l'état
            ->withCount('classes')
            ->orderBy('date_debut')
            ->get();

        // Nombre de membres inscrits (via l'entreprise) par session
        $inscrits = Inscription::faitesParEntreprise($entreprise->id)
            ->whereHas('classe', fn ($q) => $q->whereIn('session_id', $sessions->pluck('id')))
            ->with('classe:id,session_id')
            ->get()
            ->groupBy(fn ($i) => $i->classe->session_id)
            ->map(fn ($lignes) => $lignes->pluck('user_id')->unique()->count());

        return view('espace-entreprise.sessions.index', [
            'entreprise' => $entreprise,
            'enCours' => $sessions->where('etat', 'en_cours')->values(),
            'aVenir' => $sessions->where('etat', 'a_venir')->values(),
            'terminees' => $sessions->where('etat', 'terminee')->sortByDesc('date_fin')->values(),
            'inscrits' => $inscrits,
        ]);
    }

    // Classes d'une session, avec le nombre de membres inscrits À CHACUNE via l'entreprise
    public function show(Request $request, SessionFormation $sessionFormation)
    {
        $this->autoriser($request, $sessionFormation);
        $entreprise = $this->entreprise($request);

        $classes = $sessionFormation->classes()
            ->with('cours', 'formateur')
            ->withCount(['inscriptions as inscrits_entreprise_count' => fn ($q) => $q->where('via_entreprise_id', $entreprise->id)])
            ->orderBy('date_debut')
            ->get();

        return view('espace-entreprise.sessions.show', [
            'session' => $sessionFormation,
            'enCours' => $classes->where('etat', 'en_cours')->values(),
            'aVenir' => $classes->where('etat', 'a_venir')->values(),
            'terminees' => $classes->where('etat', 'terminee')->sortByDesc('date_fin')->values(),
        ]);
    }

    // Membres de l'entreprise inscrits à la classe (uniquement via l'entreprise)
    public function classe(Request $request, SessionFormation $sessionFormation, Classe $classe)
    {
        $this->autoriser($request, $sessionFormation);
        $entreprise = $this->entreprise($request);

        abort_unless((int) $classe->session_id === (int) $sessionFormation->id, 404);   // classe de CETTE session

        $classe->load('cours', 'formateur');

        $inscriptions = $classe->inscriptions()
            ->where('via_entreprise_id', $entreprise->id)   // les auto-inscriptions des membres sont invisibles
            ->with('user.particulier')
            ->get()
            ->sortBy(fn ($i) => mb_strtolower($i->user->name))
            ->values();

        return view('espace-entreprise.sessions.classe', [
            'session' => $sessionFormation, 'classe' => $classe,
            'inscriptions' => $inscriptions, 'entreprise' => $entreprise,
        ]);
    }
}