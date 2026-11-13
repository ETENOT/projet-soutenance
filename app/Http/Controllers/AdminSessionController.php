<?php
// app/Http/Controllers/AdminSessionController.php
namespace App\Http\Controllers;

use App\Models\Classe;
use App\Models\Cours;
use App\Models\Entreprise;
use App\Models\SessionFormation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminSessionController extends Controller
{
    private const ETATS = ['en_cours', 'a_venir', 'terminee'];

    // Liste : recherche (session ou entreprise) + filtre d'état.
    // L'état étant calculé à partir des classes, il est filtré en PHP puis paginé à la main.
    public function index(Request $request)
    {
        $request->validate([
            'date_debut' => ['nullable', 'date'],
        ]);

        // Les classes de chaque session servent à l'état, aux dates et à la fiche « Voir » de la liste
        $query = SessionFormation::query()
            ->with(['entreprise', 'classes' => fn ($q) => $q->with('cours', 'formateur', 'inscriptions.user', 'inscriptions.paiement')->orderBy('date_debut')])
            ->withCount('classes');

        if ($recherche = trim((string) $request->input('q'))) {
            $query->where(function ($q) use ($recherche) {
                $q->where('nom', 'like', "%{$recherche}%")
                    ->orWhereHas('entreprise', fn ($e) => $e->where('raison_sociale', 'like', "%{$recherche}%"));
            });
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('date_debut', $request->input('date_debut'));
        }

        $sessions = $query->orderByDesc('created_at')->orderByDesc('id')->get();

        // Plusieurs états cochés = l'un OU l'autre
        $etats = array_values(array_intersect(self::ETATS, (array) $request->input('etat', [])));
        if ($etats) {
            $sessions = $sessions->filter(fn ($s) => in_array($s->etat, $etats, true))->values();
        }

        $parPage = 10;
        $page = LengthAwarePaginator::resolveCurrentPage();

        // Session à déplier (?ouvrir=ID) : on affiche la page de la liste qui la contient
        if ($request->filled('ouvrir') && ! $request->filled('page')) {
            $position = $sessions->search(fn ($s) => $s->id === $request->integer('ouvrir'));
            if ($position !== false) {
                $page = intdiv($position, $parPage) + 1;
            }
        }
        $sessionsPage = new LengthAwarePaginator(
            $sessions->forPage($page, $parPage)->values(), $sessions->count(), $parPage, $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('sessions.index', [
            'sessions' => $sessionsPage,
            'etats' => $etats,
            'entreprises' => Entreprise::orderBy('raison_sociale')->get(),
            // listes proposées dans la fenêtre « Ajouter / modifier une classe »
            'tousLesCours' => Cours::orderBy('titre')->get(['id', 'titre']),
            'tousLesFormateurs' => User::whereHas('role', fn ($q) => $q->where('nom', 'formateur'))->orderBy('name')->get(['id', 'name']),
            'stats' => [
                'sessions' => SessionFormation::count(),
                'entreprises' => SessionFormation::distinct()->count('entreprise_id'),
                'classes' => Classe::whereNotNull('session_id')->count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $session = SessionFormation::create($request->validate($this->regles(), $this->messages()));

        // La session s'affiche dépliée : on peut y ajouter ses classes tout de suite
        return redirect()->route('admin.sessions.index', ['ouvrir' => $session->id])
            ->with('success', 'Session créée : ajoutez maintenant les classes qui la composent.');
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

        // back() : on reste sur la page d'où vient la modification (liste ou page de la session)
        return back()->with('success', 'Session modifiée avec succès.');
    }

    // Refusée tant que la session contient des classes (elles redeviendraient publiques par accident)
    public function destroy(SessionFormation $sessionFormation)
    {
        if ($sessionFormation->classes()->exists()) {
            return redirect()->route('admin.sessions.index')
                ->with('error', 'Cette session contient encore des classes : supprimez-les avant de supprimer la session.');
        }

        $nom = $sessionFormation->nom;
        $sessionFormation->delete();

        return redirect()->route('admin.sessions.index')->with('success', "La session « {$nom} » a été supprimée.");
    }

    // Suppression multiple : une session qui contient encore des classes est ignorée et expliquée dans le message
    public function destroyMultiple(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ], [
            'ids.required' => 'Sélectionnez au moins une session.',
        ]);

        $supprimees = 0;
        $ignorees = [];

        foreach (SessionFormation::withCount('classes')->whereIn('id', $data['ids'])->get() as $session) {
            if ($session->classes_count > 0) {
                $ignorees[] = "« {$session->nom} »";
                continue;
            }
            $session->delete();
            $supprimees++;
        }

        $retour = back();
        if ($supprimees > 0) {
            $retour->with('success', "{$supprimees} session(s) supprimée(s).");
        }
        if ($ignorees) {
            $retour->with('error', 'Non supprimée(s), elles contiennent encore des classes : ' . implode(' ; ', $ignorees));
        }

        return $retour;
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