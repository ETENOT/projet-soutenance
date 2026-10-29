<?php

namespace App\Http\Controllers;

use App\Models\Entreprise;
use App\Models\Particulier;
use App\Models\Role;
use App\Models\User;
use App\Services\CompteAdminService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // Rôles acceptés par le filtre de la liste admin (tout le reste est ignoré)
    private const ROLES = ['particulier', 'entreprise', 'formateur', 'admin'];

    public function __construct(private CompteAdminService $comptes)
    {
    }
    /**
     * Affiche la liste des utilisateurs avec leur rôle.
     */
    public function index(Request $request)
    {
        // Dates et nombres : une valeur invalide renvoie un message d'erreur au lieu d'une page cassée
        $request->validate([
            'date_du' => ['nullable', 'date'],
            'date_au' => array_filter([
                'nullable', 'date',
                $request->filled('date_du') ? 'after_or_equal:date_du' : null,
            ]),
            'inscriptions_min' => ['nullable', 'integer', 'min:0'],
            'inscriptions_max' => array_filter([
                'nullable', 'integer', 'min:0',
                $request->filled('inscriptions_min') ? 'gte:inscriptions_min' : null,
            ]),
        ], [
            'date_du.date'                => 'La date de début de période est invalide.',
            'date_au.date'                => 'La date de fin de période est invalide.',
            'date_au.after_or_equal'      => 'La date de fin de période doit être postérieure ou égale à la date de début.',
            'inscriptions_min.integer'    => 'Le nombre minimum d\'inscriptions doit être un nombre entier.',
            'inscriptions_min.min'        => 'Le nombre minimum d\'inscriptions ne peut pas être négatif.',
            'inscriptions_max.integer'    => 'Le nombre maximum d\'inscriptions doit être un nombre entier.',
            'inscriptions_max.min'        => 'Le nombre maximum d\'inscriptions ne peut pas être négatif.',
            'inscriptions_max.gte'        => 'Le nombre maximum d\'inscriptions doit être supérieur ou égal au minimum.',
        ]);

        $query = User::with(['role', 'particulier', 'entreprise'])->withCount('inscriptions');

        if ($recherche = trim((string) $request->input('q'))) {
            $query->where(function ($q) use ($recherche) {
                $q->where('name', 'like', "%{$recherche}%")
                ->orWhere('email', 'like', "%{$recherche}%");
            });
        }

        // --- Rôles (plusieurs possibles : cocher "Particulier" ET "Entreprise" = l'un OU l'autre) ---
        // (array) : les anciens liens ?role=particulier (simple texte) continuent de fonctionner.
        $roles = array_values(array_intersect((array) $request->input('role', []), self::ROLES));
        if ($roles) {
            $query->whereHas('role', fn ($q) => $q->whereIn('nom', $roles));
        }

        // --- Filtre rapide : au moins une inscription avec paiement ---
        if ($request->boolean('avec_paiement')) {
            $query->whereHas('inscriptions', fn ($q) => $q->has('paiement'));
        }

        // --- Date d'inscription sur la plateforme ---
        if ($request->filled('date_du')) {
            $query->whereDate('created_at', '>=', $request->input('date_du'));
        }
        if ($request->filled('date_au')) {
            $query->whereDate('created_at', '<=', $request->input('date_au'));
        }

        // --- Nombre d'inscriptions aux classes ---
        if ($request->filled('inscriptions_min')) {
            $query->has('inscriptions', '>=', (int) $request->input('inscriptions_min'));
        }
        if ($request->filled('inscriptions_max')) {
            $query->has('inscriptions', '<=', (int) $request->input('inscriptions_max'));
        }

        $compter = fn (string $role) => User::whereHas('role', fn ($q) => $q->where('nom', $role))->count();

        return view('users.index', [
            'utilisateurs' => $query->orderByDesc('created_at')->orderByDesc('id')->paginate(10)->withQueryString(),
            'rolesChoisis' => $roles,
            'roles' => Role::orderBy('nom')->get(),
            // Totaux sur toute la base (pas seulement la page affichée, ni les filtres)
            'stats' => [
                'particuliers' => $compter('particulier'),
                'entreprises'  => $compter('entreprise'),
                'admins'       => $compter('admin'),
                'formateurs'   => $compter('formateur'),
                'total'        => User::count(),
            ],
        ]);
    }

    /**
     * Suppression multiple : supprime les utilisateurs cochés dans la liste.
     * Chaque compte est traité séparément : ceux qui ne peuvent pas être supprimés sont ignorés
     * et expliqués dans le message, les autres sont supprimés normalement.
     */
    public function destroyMultiple(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:users,id'],
        ], [
            'ids.required' => 'Sélectionnez au moins un utilisateur.',
        ]);

        $supprimes = 0;
        $ignores = [];   // explications pour les comptes non supprimés

        foreach (User::with('role')->whereIn('id', $data['ids'])->get() as $utilisateur) {

            // Mêmes protections que le bouton de suppression individuel :
            // ni un compte administrateur, ni son propre compte.
            if ($utilisateur->role?->nom === 'admin' || $utilisateur->id === Auth::id()) {
                $ignores[] = "{$utilisateur->name} : compte protégé (administrateur ou vous-même)";
                continue;
            }

            // Protection supplémentaire propre à la suppression en masse : supprimer un compte supprime
            // aussi ses inscriptions et ses paiements. Un utilisateur qui a déjà payé doit être supprimé
            // individuellement (bouton de sa ligne), en connaissance de cause.
            if ($utilisateur->inscriptions()->has('paiement')->exists()) {
                $ignores[] = "{$utilisateur->name} : a des inscriptions payées";
                continue;
            }

            $utilisateur->delete();
            $supprimes++;
        }

        // back() : on revient sur la liste avec les mêmes filtres
        $retour = back();
        if ($supprimes > 0) {
            $retour->with('success', "{$supprimes} utilisateur(s) supprimé(s).");
        }
        if ($ignores) {
            $retour->with('error', 'Non supprimé(s) : ' . implode(' ; ', $ignores) . '.');
        }

        return $retour;
    }

    /**
     * Valide et enregistre un nouvel utilisateur depuis l'espace admin.
     *
     * Comme à l'inscription publique (RegisteredUserController), un particulier ou une entreprise
     * a AUSSI sa ligne dans "particuliers" ou "entreprises" : sans elle, ses tableaux de bord
     * n'auraient ni téléphone, ni raison sociale. Un formateur ou un administrateur n'en a pas.
     */
    public function store(Request $request)
    {
        [$data, $nomRole] = $this->valider($request);

        // Tout ou rien : si la création du compte échoue, le profil créé juste avant est annulé aussi
        $user = DB::transaction(function () use ($data, $nomRole) {
            $compte = [
                'name' => $data['name'],
                'email' => $data['email'],
                'role_id' => $data['role_id'],
            ];

            if ($nomRole === 'particulier') {
                $compte['particulier_id'] = Particulier::create($this->profilParticulier($data))->id;
            } elseif ($nomRole === 'entreprise') {
                $compte['entreprise_id'] = Entreprise::create($this->profilEntreprise($data))->id;
            }

            return $this->comptes->creer($compte);
        });

        // L'adresse e-mail est considérée comme vérifiée immédiatement.
        // L'utilisateur n'a donc pas besoin de saisir le code de vérification.
        $user->markEmailAsVerified();

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur créé avec succès. Mot de passe temporaire: '
                    . User::MOT_DE_PASSE_PAR_DEFAUT . ' (à changer à sa première connexion).');
    }

    /**
     * Met à jour un utilisateur ET son profil (particulier ou entreprise selon son rôle).
     * Si le compte n'a pas encore de profil pour ce rôle (changement de rôle), il est créé.
     */
    public function update(Request $request, User $user)
    {
        $user->loadMissing('role');
        [$data, $nomRole] = $this->valider($request, $user);

        DB::transaction(function () use ($data, $nomRole, $user) {
            $compte = [
                'name' => $data['name'],
                'email' => $data['email'],
                'role_id' => $data['role_id'],
            ];

            // Mot de passe : modifié seulement s'il est renseigné
            if (filled($data['password'] ?? null)) {
                $compte['password'] = Hash::make($data['password']);
            }

            if ($nomRole === 'particulier') {
                if ($user->particulier_id) {
                    Particulier::whereKey($user->particulier_id)->update($this->profilParticulier($data));
                } else {
                    $compte['particulier_id'] = Particulier::create($this->profilParticulier($data))->id;
                }
            } elseif ($nomRole === 'entreprise') {
                if ($user->entreprise_id) {
                    Entreprise::whereKey($user->entreprise_id)->update($this->profilEntreprise($data));
                } else {
                    $compte['entreprise_id'] = Entreprise::create($this->profilEntreprise($data))->id;
                }
            }

            $user->update($compte);
        });

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur modifié avec succès.');
    }

    /**
     * Supprime un utilisateur depuis l'espace administrateur.
     */
    public function destroy(User $user)
    {
        // Empêche l'administrateur de supprimer son propre compte.
        if ($user->id === Auth::id()) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        // Protège les comptes administrateurs contre une suppression accidentelle.
        if ($user->role?->nom === 'admin') {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'Un compte administrateur ne peut pas être supprimé ici.');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Utilisateur supprimé avec succès.');
    }

    // ------------------------------------------------------------------
    // Outils internes
    // ------------------------------------------------------------------

    /**
     * Nettoie et valide les champs du formulaire (création si $user est null, modification sinon).
     * Retourne [données validées, nom du rôle choisi].
     */
    private function valider(Request $request, ?User $user = null): array
    {
        // Un compte admin ne peut pas être transformé en autre rôle depuis cette page.
        if ($user?->role?->nom === 'admin') {
            $request->merge(['role_id' => $user->role_id]);
        }

        // Nom du rôle choisi (particulier, entreprise, formateur, admin), pour savoir quels champs exiger
        $nomRole = Role::whereKey($request->input('role_id'))->value('nom');

        // Les champs d'un autre rôle sont ignorés (ex. un téléphone envoyé pour un formateur) :
        // le formulaire les désactive déjà, mais le serveur ne doit pas leur faire confiance.
        if ($nomRole !== 'particulier') {
            $request->request->remove('telephone');
            $request->request->remove('date_de_naissance');
        }
        if ($nomRole !== 'entreprise') {
            foreach (['raison_sociale', 'adresse', 'contact_principal', 'secteur_activite'] as $champ) {
                $request->request->remove($champ);
            }
        }

        // Numéros de téléphone : on retire les espaces, points, tirets et parenthèses saisis à la main
        // (« +241 77 12 34 56 » devient « +24177123456 ») avant de valider le format international.
        foreach (['telephone', 'contact_principal'] as $champ) {
            if ($request->filled($champ)) {
                $request->merge([$champ => preg_replace('/[\s.\-()]/', '', $request->input($champ))]);
            }
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'role_id' => ['required', 'exists:roles,id'],

            // Champs du particulier : obligatoires seulement pour ce rôle
            'telephone' => [Rule::requiredIf($nomRole === 'particulier'), 'nullable', 'string', 'max:20', 'regex:/^\+[1-9]\d{6,14}$/'],
            'date_de_naissance' => ['nullable', 'date', 'before_or_equal:today'],

            // Champs de l'entreprise : obligatoires seulement pour ce rôle
            'raison_sociale' => [Rule::requiredIf($nomRole === 'entreprise'), 'nullable', 'string', 'max:255'],
            'adresse' => [Rule::requiredIf($nomRole === 'entreprise'), 'nullable', 'string', 'max:255'],
            'contact_principal' => [Rule::requiredIf($nomRole === 'entreprise'), 'nullable', 'string', 'max:20', 'regex:/^\+[1-9]\d{6,14}$/'],
            'secteur_activite' => ['nullable', 'string', 'max:255'],
        ], [
            'role_id.required' => 'Choisissez un type de compte.',
            'role_id.exists' => 'Ce type de compte n\'existe pas.',
            'telephone.required' => 'Le téléphone est obligatoire pour un particulier.',
            'telephone.regex' => 'Le téléphone doit être au format international, par exemple +24177123456.',
            'date_de_naissance.before_or_equal' => 'La date de naissance ne peut pas être dans le futur.',
            'raison_sociale.required' => 'La raison sociale est obligatoire pour une entreprise.',
            'adresse.required' => 'Le siège social est obligatoire pour une entreprise.',
            'contact_principal.required' => 'Le contact principal est obligatoire pour une entreprise.',
            'contact_principal.regex' => 'Le contact principal doit être un numéro au format international, par exemple +24177123456.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
        ]);

        return [$data, $nomRole];
    }

    private function profilParticulier(array $data): array
    {
        return [
            'telephone' => $data['telephone'],
            'date_de_naissance' => $data['date_de_naissance'] ?? null,
        ];
    }

    private function profilEntreprise(array $data): array
    {
        return [
            'raison_sociale' => $data['raison_sociale'],
            'adresse' => $data['adresse'],
            'contact_principal' => $data['contact_principal'],
            'secteur_activite' => $data['secteur_activite'] ?? null,
        ];
    }
}