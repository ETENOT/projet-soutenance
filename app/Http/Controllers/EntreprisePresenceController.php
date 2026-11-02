<?php
// app/Http/Controllers/EntreprisePresenceController.php  (lecture seule, rôle « entreprise »)
namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\Entreprise;
use App\Models\Inscription;
use App\Models\Presence;
use Barryvdh\DomPDF\Facade\Pdf;   // dompdf, déjà installé pour les attestations et les reçus
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Présences des membres inscrits PAR l'entreprise, consultables une fois la classe FERMÉE
 * (taux et attestation sont alors figés) : liste des classes, feuille jour par jour, PDF.
 */
class EntreprisePresenceController extends Controller
{
    // Entreprise du compte connecté (403 si le compte n'en a pas)
    private function entreprise(Request $request): Entreprise
    {
        $entreprise = $request->user()->entreprise;
        abort_unless($entreprise, 403, 'Aucune entreprise n’est rattachée à ce compte.');

        return $entreprise;
    }

    // Classes où l'entreprise a inscrit au moins un membre : fermées (consultables) et pas encore fermées
    public function index(Request $request)
    {
        $entreprise = $this->entreprise($request);

        $classes = Classe::query()
            ->whereHas('inscriptions', fn ($q) => $q->where('via_entreprise_id', $entreprise->id))
            ->withCount(['inscriptions as inscrits_entreprise_count' => fn ($q) => $q->where('via_entreprise_id', $entreprise->id)])
            ->with('cours', 'formateur', 'session')
            ->orderByDesc('date_fin')
            ->get();

        return view('espace-entreprise.presences.index', [
            'entreprise' => $entreprise,
            // fermées : la plus récemment fermée en haut
            'fermees' => $classes->filter->estFermee()->sortByDesc('fermee_le')->values(),
            // pas encore fermées : la feuille sera disponible après la fermeture
            'enAttente' => $classes->reject->estFermee()->sortBy('date_fin')->values(),
        ]);
    }

    // Feuille de présence d'une classe fermée (membres inscrits par l'entreprise uniquement)
    public function show(Request $request, Classe $classe)
    {
        $entreprise = $this->entreprise($request);
        $this->autoriser($classe, $entreprise);

        return view('espace-entreprise.presences.show', $this->feuille($classe, $entreprise));
    }

    // Même feuille, en PDF (A4 paysage)
    public function pdf(Request $request, Classe $classe)
    {
        $entreprise = $this->entreprise($request);
        $this->autoriser($classe, $entreprise);

        $nomFichier = 'presences-' . Str::slug($classe->cours->titre . '-' . $classe->nom) . '.pdf';

        return Pdf::loadView('espace-entreprise.presences.pdf', $this->feuille($classe, $entreprise))
            ->setPaper('a4', 'landscape')
            ->download($nomFichier);
    }

    /**
     * Accès : la classe doit contenir au moins un membre inscrit par CETTE entreprise (sinon 404,
     * on ne révèle pas l'existence de la classe) et être fermée (sinon 403 avec un message clair).
     */
    private function autoriser(Classe $classe, Entreprise $entreprise): void
    {
        abort_unless($classe->inscriptions()->where('via_entreprise_id', $entreprise->id)->exists(), 404);
        abort_unless($classe->estFermee(), 403, 'La feuille de présence sera disponible une fois la classe fermée.');
    }

    /**
     * Données communes à la page et au PDF.
     * Pour chaque membre et chaque jour de cours (lundi → samedi) :
     *  - 'exclu'   : jour non comptabilisé (ne compte ni comme présence ni comme absence) ;
     *  - 'present' : une ligne de présence « present = true » existe ce jour-là ;
     *  - 'absent'  : sinon (un jour comptabilisé sans présence enregistrée = absence,
     *                même règle que le calcul du taux de présence).
     */
    private function feuille(Classe $classe, Entreprise $entreprise): array
    {
        $classe->load('cours', 'formateur', 'session', 'joursExclus');

        $inscriptions = $classe->inscriptions()
            ->where('via_entreprise_id', $entreprise->id)   // jamais les auto-inscriptions des membres
            ->with('user')
            ->get()
            ->sortBy(fn (Inscription $i) => mb_strtolower($i->user->name))
            ->values();

        $jours = $classe->joursOuvres();        // tous les jours de cours, exclus compris (affichés en gris)
        $exclus = $classe->datesExclues();      // 'Y-m-d' des jours non comptabilisés

        // Jours de présence par membre, en UNE requête : [user_id => ['2026-11-03' => true, ...]]
        $presences = Presence::where('classe_id', $classe->id)
            ->whereIn('user_id', $inscriptions->pluck('user_id'))
            ->where('present', true)
            ->get(['user_id', 'date_seance'])
            ->groupBy('user_id')
            ->map(fn ($lignes) => $lignes->mapWithKeys(fn ($p) => [$p->date_seance->toDateString() => true]));

        $lignes = $inscriptions->map(function (Inscription $inscription) use ($jours, $exclus, $presences) {
            $presentLe = $presences->get($inscription->user_id, collect());

            // Statut de chaque jour pour ce membre : ['2026-11-03' => 'present', ...]
            $statuts = $jours->mapWithKeys(function ($jour) use ($exclus, $presentLe) {
                $date = $jour->toDateString();
                $statut = $exclus->contains($date) ? 'exclu' : ($presentLe->has($date) ? 'present' : 'absent');

                return [$date => $statut];
            });

            return [
                'membre' => $inscription->user,
                'statuts' => $statuts,
                'presents' => $statuts->filter(fn ($s) => $s === 'present')->count(),
                'absents' => $statuts->filter(fn ($s) => $s === 'absent')->count(),
                // taux et attestation FIGÉS à la fermeture : ce sont eux qui font foi
                'taux' => (float) ($inscription->taux_presence ?? 0),
                'attestation' => (bool) $inscription->attestation_eligible,
            ];
        });

        return [
            'entreprise' => $entreprise,
            'classe' => $classe,
            'jours' => $jours,
            'nbJoursComptes' => $classe->joursDeFormation()->count(),   // dénominateur du taux
            'lignes' => $lignes,
            'seuil' => Classe::SEUIL_ATTESTATION,
        ];
    }
}