<?php
// app/Http/Controllers/AdminSessionController.php
namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\Entreprise;
use App\Models\SessionFormation;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminSessionController extends Controller
{
    private const ETATS = ['en_cours', 'a_venir', 'terminee'];

    // Liste : recherche (session ou entreprise) + filtre d'état.
    // L'état étant calculé à partir des classes, il est filtré en PHP puis paginé à la main.
    public function index(Request $request)
    {
        $query = SessionFormation::query()->with('entreprise', 'classes')->withCount('classes');

        if ($recherche = trim((string) $request->input('q'))) {
            $query->where(function ($q) use ($recherche) {
                $q->where('nom', 'like', "%{$recherche}%")
                    ->orWhereHas('entreprise', fn ($e) => $e->where('raison_sociale', 'like', "%{$recherche}%"));
            });
        }

        $sessions = $query->orderByDesc('date_debut')->orderByDesc('id')->get();

        // Plusieurs états cochés = l'un OU l'autre
        $etats = array_values(array_intersect(self::ETATS, (array) $request->input('etat', [])));
        if ($etats) {
            $sessions = $sessions->filter(fn ($s) => in_array($s->etat, $etats, true))->values();
        }

        $parPage = 10;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $sessionsPage = new LengthAwarePaginator(
            $sessions->forPage($page, $parPage)->values(), $sessions->count(), $parPage, $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('sessions.index', [
            'sessions' => $sessionsPage,
            'etats' => $etats,
            'stats' => [
                'sessions' => SessionFormation::count(),
                'entreprises' => SessionFormation::distinct()->count('entreprise_id'),
                'classes' => Classe::whereNotNull('session_id')->count(),
            ],
        ]);
    }

    public function create(Request $request)
    {
        return view('sessions.create', [
            'entreprises' => Entreprise::orderBy('raison_sociale')->get(),
            'entrepriseId' => $request->integer('entreprise_id') ?: null,   // présélection éventuelle
        ]);
    }

    public function store(Request $request)
    {
        $session = SessionFormation::create($request->validate($this->regles(), $this->messages()));

        return redirect()->route('admin.sessions.show', $session)
            ->with('success', 'Session créée : ajoutez maintenant les classes qui la composent.');
    }

    // Fiche : classes, inscrits, membres de l'entreprise, classes libres rattachables
    public function show(SessionFormation $sessionFormation)
    {
        $sessionFormation->load([
            'entreprise',
            'classes' => fn ($q) => $q->orderBy('date_debut')->with('cours', 'formateur', 'inscriptions.user'),
        ]);

        return view('sessions.show', [
            'session' => $sessionFormation,
            'membres' => $sessionFormation->entreprise->membres()->orderBy('users.name')->get(),
            // classes publiques, non terminées et sans inscrit : seules rattachables
            'classesRattachables' => Classe::publiques()->nonTerminees()->doesntHave('inscriptions')
                ->with('cours')->orderBy('date_debut')->get(),
        ]);
    }

    public function edit(SessionFormation $sessionFormation)
    {
        return view('sessions.edit', [
            'session' => $sessionFormation,
            'entreprises' => Entreprise::orderBy('raison_sociale')->get(),
            'entrepriseId' => $sessionFormation->entreprise_id,
        ]);
    }

    public function update(Request $request, SessionFormation $sessionFormation)
    {
        $data = $request->validate($this->regles(), $this->messages());

        // Changer d'entreprise alors que des membres sont inscrits via l'ancienne n'aurait pas de sens
        if ((int) $data['entreprise_id'] !== (int) $sessionFormation->entreprise_id
            && $sessionFormation->classes()->has('inscriptions')->exists()) {
            return back()->withInput()->withErrors([
                'entreprise_id' => "Des membres sont déjà inscrits aux classes de cette session : l'entreprise ne peut plus être changée.",
            ]);
        }

        $sessionFormation->update($data);

        return redirect()->route('admin.sessions.show', $sessionFormation)->with('success', 'Session modifiée avec succès.');
    }

    // Refusée tant que la session contient des classes (elles redeviendraient publiques par accident)
    public function destroy(SessionFormation $sessionFormation)
    {
        if ($sessionFormation->classes()->exists()) {
            return redirect()->route('admin.sessions.show', $sessionFormation)
                ->with('error', 'Cette session contient encore des classes : détachez-les ou supprimez-les avant de supprimer la session.');
        }

        $nom = $sessionFormation->nom;
        $sessionFormation->delete();

        return redirect()->route('admin.sessions.index')->with('success', "La session « {$nom} » a été supprimée.");
    }

    private function regles(): array
    {
        return [
            'nom' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'entreprise_id' => ['required', 'integer', 'exists:entreprises,id'],
        ];
    }

    private function messages(): array
    {
        return [
            'nom.required' => 'Le nom de la session est obligatoire.',
            'entreprise_id.required' => 'Choisissez l’entreprise pour laquelle la session est créée.',
            'entreprise_id.exists' => 'Cette entreprise n’existe pas.',
        ];
    }
}