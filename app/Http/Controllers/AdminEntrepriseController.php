<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\NormaliseTelephones;
use App\Models\Entreprise;
use App\Models\Particulier;
use App\Models\Role;
use App\Models\User;
use App\Services\CompteAdminService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Gestion des entreprises par l'administrateur (liste, fiche, création, modification, suppression).
// Les MEMBRES d'une entreprise sont gérés dans AdminEntrepriseMembreController.
class AdminEntrepriseController extends Controller
{
    use NormaliseTelephones;

    private const REGEX_TELEPHONE = '/^\+[1-9]\d{6,14}$/';

    public function __construct(private CompteAdminService $comptes)
    {
    }

    /**
     * Liste des entreprises : recherche, filtre « avec / sans membres », statistiques.
     */
    public function index(Request $request)
    {
        $query = Entreprise::query()->with('users')->withCount('membres');

        if ($recherche = trim((string) $request->input('q'))) {
            $query->where(function ($q) use ($recherche) {
                $q->where('raison_sociale', 'like', "%{$recherche}%")
                    ->orWhere('secteur_activite', 'like', "%{$recherche}%")
                    ->orWhere('adresse', 'like', "%{$recherche}%")
                    // On retrouve aussi une entreprise par le nom ou l'e-mail de son responsable
                    ->orWhereHas('users', fn ($u) => $u->where('name', 'like', "%{$recherche}%")
                        ->orWhere('email', 'like', "%{$recherche}%"));
            });
        }

        // Cocher les deux cases revient à ne pas filtrer
        $avecMembres = $request->boolean('avec_membres');
        $sansMembre = $request->boolean('sans_membre');
        if ($avecMembres && ! $sansMembre) {
            $query->has('membres');
        } elseif ($sansMembre && ! $avecMembres) {
            $query->doesntHave('membres');
        }

        return view('entreprises.index', [
            'entreprises' => $query->orderByDesc('created_at')->orderByDesc('id')->paginate(10)->withQueryString(),
            'avecMembres' => $avecMembres,
            'sansMembre' => $sansMembre,
            'stats' => [
                'entreprises' => Entreprise::count(),
                'membres' => Particulier::whereNotNull('entreprise_id')->count(),
                'particuliers_libres' => User::whereHas('role', fn ($q) => $q->where('nom', 'particulier'))
                    ->whereHas('particulier', fn ($q) => $q->whereNull('entreprise_id'))->count(),
            ],
        ]);
    }

    public function create()
    {
        return view('entreprises.create');
    }

    /**
     * Crée l'entreprise ET son compte de connexion (rôle « entreprise »),
     * avec le mot de passe par défaut à changer à la première connexion.
     */
    public function store(Request $request)
    {
        $this->normaliserTelephones($request, ['contact_principal']);

        $data = $request->validate([
            'raison_sociale' => ['required', 'string', 'max:255'],
            'secteur_activite' => ['nullable', 'string', 'max:255'],
            'adresse' => ['required', 'string', 'max:255'],
            'contact_principal' => ['required', 'string', 'max:20', 'regex:' . self::REGEX_TELEPHONE],
            // Compte de connexion de l'entreprise
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
        ], $this->messages());

        // Tout ou rien : pas d'entreprise sans compte si la création du compte échoue
        $entreprise = DB::transaction(function () use ($data) {
            $entreprise = Entreprise::create([
                'raison_sociale' => $data['raison_sociale'],
                'secteur_activite' => $data['secteur_activite'] ?? null,
                'adresse' => $data['adresse'],
                'contact_principal' => $data['contact_principal'],
            ]);

            $this->comptes->creer([
                'name' => $data['name'],
                'email' => $data['email'],
                'role_id' => Role::where('nom', 'entreprise')->value('id'),
                'entreprise_id' => $entreprise->id,
            ]);

            return $entreprise;
        });

        return redirect()->route('admin.entreprises.show', $entreprise)
            ->with('success', 'Entreprise créée. Mot de passe temporaire du compte : '
                . User::MOT_DE_PASSE_PAR_DEFAUT . ' (à changer à la première connexion).');
    }

    /**
     * Fiche de l'entreprise : informations, comptes de connexion et liste des membres.
     */
    public function show(Request $request, Entreprise $entreprise)
    {
        $membres = $entreprise->membres()->with('particulier');

        if ($recherche = trim((string) $request->input('q'))) {
            $membres->where(function ($q) use ($recherche) {
                $q->where('users.name', 'like', "%{$recherche}%")
                    ->orWhere('users.email', 'like', "%{$recherche}%")
                    ->orWhere('particuliers.telephone', 'like', "%{$recherche}%");
            });
        }

        return view('entreprises.show', [
            'entreprise' => $entreprise->load('users'),
            'membres' => $membres->orderBy('users.name')->paginate(10)->withQueryString(),
            'totalMembres' => $entreprise->membres()->count(),
        ]);
    }

    public function edit(Entreprise $entreprise)
    {
        return view('entreprises.edit', ['entreprise' => $entreprise]);
    }

    public function update(Request $request, Entreprise $entreprise)
    {
        $this->normaliserTelephones($request, ['contact_principal']);

        $data = $request->validate([
            'raison_sociale' => ['required', 'string', 'max:255'],
            'secteur_activite' => ['nullable', 'string', 'max:255'],
            'adresse' => ['required', 'string', 'max:255'],
            'contact_principal' => ['required', 'string', 'max:20', 'regex:' . self::REGEX_TELEPHONE],
        ], $this->messages());

        $entreprise->update($data);

        return redirect()->route('admin.entreprises.show', $entreprise)
            ->with('success', 'Entreprise modifiée avec succès.');
    }

    /**
     * Supprime l'entreprise, ses comptes de connexion et ses devis.
     * Ses membres NE sont PAS supprimés : ils redeviennent de simples particuliers.
     */
    public function destroy(Entreprise $entreprise)
    {
        if ($raison = $this->obstacleSuppression($entreprise)) {
            return redirect()->route('admin.entreprises.index')->with('error', $raison);
        }

        $nom = $entreprise->raison_sociale;
        $this->supprimer($entreprise);

        return redirect()->route('admin.entreprises.index')
            ->with('success', "L’entreprise « {$nom} » a été supprimée (ses membres sont conservés comme particuliers).");
    }

    /**
     * Suppression multiple : chaque entreprise est traitée séparément,
     * celles qui ne peuvent pas être supprimées sont expliquées dans le message.
     */
    public function destroyMultiple(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:entreprises,id'],
        ], [
            'ids.required' => 'Sélectionnez au moins une entreprise.',
        ]);

        $supprimees = 0;
        $ignorees = [];

        foreach (Entreprise::whereIn('id', $data['ids'])->get() as $entreprise) {
            if ($raison = $this->obstacleSuppression($entreprise)) {
                $ignorees[] = $raison;
                continue;
            }
            $this->supprimer($entreprise);
            $supprimees++;
        }

        $retour = back();
        if ($supprimees > 0) {
            $retour->with('success', "{$supprimees} entreprise(s) supprimée(s).");
        }
        if ($ignorees) {
            $retour->with('error', 'Non supprimée(s) : ' . implode(' ; ', $ignorees));
        }

        return $retour;
    }

    // Même protection que pour les utilisateurs : un compte ayant déjà payé une inscription n'est pas supprimé
    // en un clic (ses inscriptions et paiements seraient supprimés avec lui).
    private function obstacleSuppression(Entreprise $entreprise): ?string
    {
        foreach ($entreprise->users as $compte) {
            if ($compte->inscriptions()->has('paiement')->exists()) {
                return "« {$entreprise->raison_sociale} » : son compte {$compte->email} a des inscriptions payées "
                    . '(supprimez-le depuis la gestion des utilisateurs, en connaissance de cause)';
            }
        }

        return null;
    }

    private function supprimer(Entreprise $entreprise): void
    {
        DB::transaction(function () use ($entreprise) {
            // Comptes de connexion de l'entreprise (rôle « entreprise »), puis l'entreprise elle-même.
            // Les particuliers membres sont détachés automatiquement (clé étrangère en « null on delete »).
            $entreprise->users->each->delete();
            $entreprise->delete();
        });
    }

    private function messages(): array
    {
        return [
            'raison_sociale.required' => 'La raison sociale est obligatoire.',
            'adresse.required' => 'Le siège social est obligatoire.',
            'contact_principal.required' => 'Le contact principal est obligatoire.',
            'contact_principal.regex' => 'Le contact principal doit être un numéro au format international, par exemple +24177123456.',
            'name.required' => 'Le nom du responsable du compte est obligatoire.',
            'email.required' => 'L’adresse e-mail du compte est obligatoire.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
        ];
    }
}