<?php

namespace App\Http\Controllers;

use App\Models\AvisFormateur;
use App\Models\Classe;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

// Côté ADMINISTRATEUR : gestion complète des comptes formateurs (CRUD),
// affectation à des classes et consultation des avis des stagiaires.
// Les avis sont ANONYMES : aucun écran de ce contrôleur ne lit ni n'affiche user_id.
class AdminFormateurController extends Controller
{
    /**
     * Liste des formateurs avec, pour chacun : nombre de classes, classes en cours,
     * nombre d'avis reçus et note moyenne.
     */
    public function index(Request $request)
    {
        $query = $this->formateurs()
            ->withCount([
                'classesAnimes',
                // Classe "en cours" = non fermée, commencée, pas encore terminée
                'classesAnimes as classes_en_cours_count' => fn ($q) => $q
                    ->whereNull('fermee_le')
                    ->whereDate('date_debut', '<=', today())
                    ->whereDate('date_fin', '>=', today()),
            ]);

        if ($request->input('situation') === 'classes_en_cours') {
            $query->whereHas('classesAnimes', fn ($q) => $q
                ->whereNull('fermee_le')
                ->whereDate('date_debut', '<=', today())
                ->whereDate('date_fin', '>=', today()));
        } elseif ($request->input('situation') === 'sans_classe') {
            $query->whereDoesntHave('classesAnimes');
        }

        // Recherche facultative par nom ou e-mail
        if ($recherche = trim((string) $request->input('q'))) {
            $query->where(function ($q) use ($recherche) {
                $q->where('name', 'like', "%{$recherche}%")
                  ->orWhere('email', 'like', "%{$recherche}%");
            });
        }

        $formateurs = $query->orderBy('name')->paginate(10)->withQueryString();

        // Avis par formateur : une seule requête groupée pour toute la page (évite N requêtes)
        $statsAvis = AvisFormateur::query()
            ->join('classes', 'classes.id', '=', 'avis_formateurs.classe_id')
            ->whereIn('classes.formateur_id', $formateurs->pluck('id'))
            ->groupBy('classes.formateur_id')
            ->selectRaw('classes.formateur_id, COUNT(*) as nb, AVG((note_maitrise_sujet + note_qualites_pedagogiques + note_comprehension_problemes) / 3) as moyenne')
            ->get()
            ->keyBy('formateur_id');

        return view('formateurs.index', [
            'formateurs' => $formateurs,
            'statsAvis' => $statsAvis,
            'totalFormateurs' => $this->formateurs()->count(),
            'classesSansFormateur' => Classe::whereNull('formateur_id')->whereNull('fermee_le')->count(),
        ]);
    }

    public function create()
    {
        return view('formateurs.create');
    }

    /**
     * Crée un compte formateur : le rôle est imposé côté serveur (jamais lu dans le formulaire).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $formateur = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id' => $this->roleFormateurId(),
        ]);

        // Compte créé par l'admin : pas de code de vérification à saisir
        $formateur->markEmailAsVerified();

        return redirect()->route('admin.formateurs.show', $formateur)
            ->with('success', 'Formateur créé avec succès. Vous pouvez maintenant lui affecter des classes.');
    }

    /**
     * Fiche d'un formateur : classes affectées, affectation, avis anonymes.
     */
    public function show(Request $request, User $formateur)
    {
        $this->verifierFormateur($formateur);

        // Classes du formateur, avec nombre d'inscrits payés et d'avis reçus
        $classes = $formateur->classesAnimes()
            ->with('cours')
            ->withCount([
                'inscriptions as inscrits_payes_count' => fn ($q) => $q->payees(),
                'avisFormateurs',
            ])
            ->orderByDesc('date_debut')
            ->get();

        // Filtre facultatif des avis par classe (doit être l'une de SES classes)
        $classeFiltre = $request->filled('classe')
            ? $classes->firstWhere('id', $request->integer('classe'))
            : null;

        // ANONYMAT : on ne sélectionne volontairement PAS la colonne user_id.
        // Même par erreur, une vue ne pourra donc pas afficher l'auteur de l'avis.
        $avis = AvisFormateur::query()
            ->select([
                'id', 'classe_id',
                'note_maitrise_sujet', 'note_qualites_pedagogiques', 'note_comprehension_problemes',
                'commentaire', 'created_at',
            ])
            ->whereIn('classe_id', $classeFiltre ? [$classeFiltre->id] : $classes->pluck('id'))
            ->with('classe.cours')
            ->latest()
            ->get();

        // Classes que l'on peut lui affecter : non fermées et pas encore terminées,
        // qui n'ont pas de formateur ou qui en ont un autre (réaffectation directe).
        $classesAffectables = Classe::with('cours', 'formateur')
            ->whereNull('fermee_le')
            ->whereDate('date_fin', '>=', today())
            ->where(fn ($q) => $q->whereNull('formateur_id')->orWhere('formateur_id', '!=', $formateur->id))
            ->orderBy('date_debut')
            ->get();

        return view('formateurs.show', [
            'formateur' => $formateur,
            'classes' => $classes,
            'classeFiltre' => $classeFiltre,
            'avis' => $avis,
            'moyennes' => AvisFormateur::moyennes($avis),
            'classesAffectables' => $classesAffectables,
        ]);
    }

    public function edit(User $formateur)
    {
        $this->verifierFormateur($formateur);

        return view('formateurs.edit', ['formateur' => $formateur]);
    }

    /**
     * Modifie le nom, l'e-mail et (facultatif) le mot de passe. Le rôle ne change jamais ici.
     */
    public function update(Request $request, User $formateur)
    {
        $this->verifierFormateur($formateur);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($formateur->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        // Mot de passe vide = on garde l'ancien
        if (blank($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $formateur->update($data);

        return redirect()->route('admin.formateurs.show', $formateur)
            ->with('success', 'Formateur modifié avec succès.');
    }

    /**
     * Supprime le compte. Refusé tant qu'une classe non fermée lui est affectée :
     * on ne laisse pas une formation en cours ou à venir sans formateur par accident.
     */
    public function destroy(User $formateur)
    {
        $this->verifierFormateur($formateur);

        if ($formateur->classesAnimes()->whereNull('fermee_le')->exists()) {
            return back()->with('error', 'Impossible de supprimer ce formateur : des classes non fermées lui sont encore affectées. Retirez-le d\'abord de ces classes ou réaffectez-les.');
        }

        try {
            // Ses classes fermées sont conservées (formateur_id passe à NULL)
            $formateur->delete();
        } catch (QueryException $e) {
            report($e);

            return back()->with('error', 'Suppression impossible : ce compte est encore lié à d\'autres données.');
        }

        return redirect()->route('admin.formateurs.index')
            ->with('success', 'Formateur supprimé avec succès.');
    }

    /**
     * Affecte une classe au formateur (réaffecte si elle avait déjà un autre formateur).
     */
    public function affecterClasse(Request $request, User $formateur)
    {
        $this->verifierFormateur($formateur);

        $data = $request->validate([
            'classe_id' => ['required', 'integer', 'exists:classes,id'],
        ], [
            'classe_id.required' => 'Choisissez la classe à affecter.',
        ]);

        $classe = Classe::findOrFail($data['classe_id']);

        // Une classe fermée est figée (même règle que l'action groupée de la liste des classes)
        if ($classe->estFermee()) {
            return back()->with('error', 'Cette classe est fermée : son formateur ne peut plus être modifié.');
        }

        if ($classe->date_fin->lt(today())) {
            return back()->with('error', 'Cette classe est déjà terminée : on ne peut plus lui affecter de formateur.');
        }

        $classe->update(['formateur_id' => $formateur->id]);

        return back()->with('success', "La classe « {$classe->nom} » est maintenant affectée à {$formateur->name}.");
    }

    /**
     * Retire une classe au formateur (la classe reste, sans formateur).
     */
    public function retirerClasse(User $formateur, Classe $classe)
    {
        $this->verifierFormateur($formateur);

        // Sécurité : la classe doit bien appartenir à CE formateur
        abort_unless($classe->formateur_id === $formateur->id, 404);

        if ($classe->estFermee()) {
            return back()->with('error', 'Cette classe est fermée : son formateur ne peut plus être modifié.');
        }

        $classe->update(['formateur_id' => null]);

        return back()->with('success', "La classe « {$classe->nom} » n'a plus de formateur.");
    }

    // ------------------------------------------------------------------
    // Outils internes
    // ------------------------------------------------------------------

    // Requête de base : uniquement les comptes ayant le rôle "formateur"
    private function formateurs()
    {
        return User::where('role_id', $this->roleFormateurId());
    }

    private function roleFormateurId(): int
    {
        return Role::where('nom', 'formateur')->value('id');
    }

    // 404 si l'utilisateur de l'URL n'est pas un formateur (empêche /admin/formateurs/{id d'un admin})
    private function verifierFormateur(User $user): void
    {
        abort_unless($user->role_id === $this->roleFormateurId(), 404);
    }
}