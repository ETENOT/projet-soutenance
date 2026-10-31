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
     * note moyenne ; et, pour la fenêtre « Voir », ses classes et ses avis anonymes.
     */
    public function index(Request $request)
    {
        $enCours = fn ($q) => $q
            ->whereNull('fermee_le')
            ->whereDate('date_debut', '<=', today())
            ->whereDate('date_fin', '>=', today());

        $query = $this->formateurs()
            ->withCount([
                'classesAnimes',
                // Classe "en cours" = non fermée, commencée, pas encore terminée
                'classesAnimes as classes_en_cours_count' => $enCours,
                // Classes non fermées : elles empêchent la suppression du compte
                'classesAnimes as classes_ouvertes_count' => fn ($q) => $q->whereNull('fermee_le'),
            ]);

        if ($request->input('situation') === 'classes_en_cours') {
            $query->whereHas('classesAnimes', $enCours);
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

        // Le dernier formateur créé s'affiche en haut de la liste
        $formateurs = $query->orderByDesc('id')->paginate(10)->withQueryString();

        // Avis par formateur : une seule requête groupée pour toute la page (évite N requêtes)
        $statsAvis = AvisFormateur::query()
            ->join('classes', 'classes.id', '=', 'avis_formateurs.classe_id')
            ->whereIn('classes.formateur_id', $formateurs->pluck('id'))
            ->groupBy('classes.formateur_id')
            ->selectRaw('classes.formateur_id, COUNT(*) as nb, AVG((note_maitrise_sujet + note_qualites_pedagogiques + note_comprehension_problemes) / 3) as moyenne')
            ->get()
            ->keyBy('formateur_id');

        // Fenêtre « Voir » : classes de chaque formateur de la page (une requête), avec inscrits payés et avis reçus
        $classesParFormateur = Classe::with('cours:id,titre')
            ->whereIn('formateur_id', $formateurs->pluck('id'))
            ->withCount([
                'inscriptions as inscrits_payes_count' => fn ($q) => $q->payees(),
                'avisFormateurs',
            ])
            ->orderByDesc('date_debut')
            ->get()
            ->groupBy('formateur_id');

        // ANONYMAT : on ne sélectionne volontairement PAS la colonne user_id.
        // Même par erreur, une vue ne pourra donc pas afficher l'auteur de l'avis.
        $avisParFormateur = AvisFormateur::query()
            ->select([
                'id', 'classe_id',
                'note_maitrise_sujet', 'note_qualites_pedagogiques', 'note_comprehension_problemes',
                'commentaire', 'created_at',
            ])
            ->whereIn('classe_id', $classesParFormateur->flatten()->pluck('id'))
            ->with('classe:id,nom,cours_id,formateur_id', 'classe.cours:id,titre')
            ->latest()
            ->get()
            ->groupBy(fn ($avis) => $avis->classe->formateur_id);

        // Classes que l'on peut affecter : non fermées et pas encore terminées
        // (la vue retire, pour chaque formateur, celles qu'il anime déjà)
        $classesAffectables = Classe::with('cours:id,titre', 'formateur:id,name')
            ->whereNull('fermee_le')
            ->whereDate('date_fin', '>=', today())
            ->orderBy('date_debut')
            ->get();

        return view('formateurs.index', [
            'formateurs' => $formateurs,
            'statsAvis' => $statsAvis,
            'classesParFormateur' => $classesParFormateur,
            'avisParFormateur' => $avisParFormateur,
            'classesAffectables' => $classesAffectables,
            'totalFormateurs' => $this->formateurs()->count(),
            'classesSansFormateur' => Classe::whereNull('formateur_id')->whereNull('fermee_le')->count(),
        ]);
    }

    /**
     * Crée un compte formateur : le rôle est imposé côté serveur (jamais lu dans le formulaire).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            //'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $formateur = app(\App\Services\CompteAdminService::class)->creer([
            'name' => $data['name'],
            'email' => $data['email'],
            'role_id' => $this->roleFormateurId(),
        ]);

        // Compte créé par l'admin : pas de code de vérification à saisir
        $formateur->markEmailAsVerified();

        return redirect()->route('admin.formateurs.index', $formateur)
            ->with('success', 'Formateur créé avec succès. (mot de passe temporaire : '
                    . User::MOT_DE_PASSE_PAR_DEFAUT . ', à changer à sa première connexion). '
                    . 'Vous pouvez maintenant maintenant lui affecter des classes.');
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

        return redirect()->route('admin.formateurs.index')
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
     * Suppression groupée : un formateur qui a encore des classes non fermées est ignoré.
     */
    public function destroyMultiple(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ], [
            'ids.required' => 'Sélectionnez au moins un formateur.',
        ]);

        $supprimes = 0;
        $ignores = [];

        $formateurs = $this->formateurs()
            ->withCount(['classesAnimes as classes_ouvertes_count' => fn ($q) => $q->whereNull('fermee_le')])
            ->whereIn('id', $data['ids'])
            ->get();

        foreach ($formateurs as $formateur) {
            if ($formateur->classes_ouvertes_count > 0) {
                $ignores[] = "{$formateur->name} : {$formateur->classes_ouvertes_count} classe(s) non fermée(s)";
                continue;
            }

            try {
                $formateur->delete();
                $supprimes++;
            } catch (QueryException $e) {
                report($e);
                $ignores[] = "{$formateur->name} : compte lié à d'autres données";
            }
        }

        // back() : on revient sur la liste avec la même recherche
        $retour = back();
        if ($supprimes > 0) {
            $retour->with('success', "{$supprimes} formateur(s) supprimé(s).");
        }
        if ($ignores) {
            $retour->with('error', 'Non supprimé(s) : ' . implode(' ; ', $ignores) . '.');
        }

        return $retour;
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

        return back()->with('success', "La classe « {$classe->cours?->titre} — {$classe->nom} » est maintenant affectée à {$formateur->name}.");
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

        return back()->with('success', "La classe « {$classe->cours?->titre} — {$classe->nom} » n'a plus de formateur.");
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