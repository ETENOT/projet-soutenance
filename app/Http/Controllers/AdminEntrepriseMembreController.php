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
use Illuminate\Validation\Rule;

// Gestion, par l'administrateur, des MEMBRES d'une entreprise :
//  - ajouter des particuliers déjà inscrits (recherche + sélection) ;
//  - créer directement un nouveau particulier, ajouté automatiquement comme membre ;
//  - modifier un membre ; le retirer de l'entreprise (son compte est conservé).
// Un particulier n'est membre que d'UNE entreprise : l'ajouter à une autre le transfère.
class AdminEntrepriseMembreController extends Controller
{
    use NormaliseTelephones;

    private const REGEX_TELEPHONE = '/^\+[1-9]\d{6,14}$/';

    public function __construct(private CompteAdminService $comptes)
    {
    }

    // Le membre doit appartenir à l'entreprise de l'URL (sinon 404 : on ne manipule pas le membre d'une autre)
    private function verifierMembre(Entreprise $entreprise, User $user): Particulier
    {
        $profil = $user->particulier;
        abort_unless($profil && $profil->entreprise_id === $entreprise->id, 404);

        return $profil;
    }

    // Page « Ajouter des membres » : recherche + création
    public function create(Entreprise $entreprise)
    {
        return view('entreprises.membres.create', ['entreprise' => $entreprise]);
    }

    // Recherche JSON des particuliers inscrits (nom ou e-mail)
    public function rechercher(Request $request, Entreprise $entreprise)
    {
        $terme = trim((string) $request->input('q'));
        if (mb_strlen($terme) < 2) {
            return response()->json([]);
        }

        // % et _ sont cherchés comme de simples caractères
        $motif = '%' . addcslashes($terme, '%_\\') . '%';

        $utilisateurs = User::with('particulier.entreprise')
            ->whereHas('role', fn ($q) => $q->where('nom', 'particulier'))
            ->whereNotNull('particulier_id') // sans profil particulier, impossible de le rattacher
            ->where(fn ($q) => $q->where('name', 'like', $motif)->orWhere('email', 'like', $motif))
            ->orderBy('name')
            ->limit(10)
            ->get();

        return response()->json($utilisateurs->map(function (User $u) use ($entreprise) {
            $actuelle = $u->particulier?->entreprise;

            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                // Déjà membre de CETTE entreprise : non sélectionnable
                'deja' => $actuelle?->id === $entreprise->id,
                // Membre d'une AUTRE entreprise : sélectionnable, il sera transféré
                'autre_entreprise' => ($actuelle && $actuelle->id !== $entreprise->id) ? $actuelle->raison_sociale : null,
            ];
        }));
    }

    // Ajoute à l'entreprise les particuliers sélectionnés (déjà inscrits sur la plateforme)
    public function store(Request $request, Entreprise $entreprise)
    {
        $data = $request->validate([
            'user_ids' => ['required', 'array', 'min:1', 'max:50'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ], [
            'user_ids.required' => 'Sélectionnez au moins un utilisateur à ajouter.',
            'user_ids.min' => 'Sélectionnez au moins un utilisateur à ajouter.',
        ]);

        $ajoutes = 0;
        $transferes = 0;
        $ignores = 0;

        DB::transaction(function () use ($data, $entreprise, &$ajoutes, &$transferes, &$ignores) {
            foreach (User::with(['role', 'particulier'])->whereIn('id', $data['user_ids'])->get() as $utilisateur) {
                $profil = $utilisateur->particulier;

                // Seuls les particuliers (avec leur profil) peuvent être membres ; déjà membre ici = rien à faire
                if ($utilisateur->role?->nom !== 'particulier' || ! $profil || $profil->entreprise_id === $entreprise->id) {
                    $ignores++;
                    continue;
                }

                if ($profil->entreprise_id !== null) {
                    $transferes++; // il quitte son ancienne entreprise
                }

                $profil->update(['entreprise_id' => $entreprise->id]);
                $ajoutes++;
            }
        });

        if ($ajoutes === 0) {
            return back()->with('error', 'Aucun membre ajouté : les utilisateurs choisis sont déjà membres ou ne sont pas des particuliers.');
        }

        $message = "{$ajoutes} membre(s) ajouté(s) à « {$entreprise->raison_sociale} ».";
        if ($transferes > 0) {
            $message .= " {$transferes} d’entre eux ont quitté leur ancienne entreprise.";
        }
        if ($ignores > 0) {
            $message .= " {$ignores} utilisateur(s) ignoré(s).";
        }

        return redirect()->route('admin.entreprises.show', $entreprise)->with('success', $message);
    }

    // Crée un NOUVEAU particulier, automatiquement membre de l'entreprise
    // (e-mail vérifié d'office, mot de passe par défaut à changer à la première connexion).
    public function storeNouveau(Request $request, Entreprise $entreprise)
    {
        $this->normaliserTelephones($request, ['telephone']);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'telephone' => ['required', 'string', 'max:20', 'regex:' . self::REGEX_TELEPHONE],
            'date_de_naissance' => ['nullable', 'date', 'before_or_equal:today'],
        ], $this->messages());

        DB::transaction(function () use ($data, $entreprise) {
            $profil = Particulier::create([
                'telephone' => $data['telephone'],
                'date_de_naissance' => $data['date_de_naissance'] ?? null,
                'entreprise_id' => $entreprise->id,
            ]);

            $this->comptes->creer([
                'name' => $data['name'],
                'email' => $data['email'],
                'role_id' => Role::where('nom', 'particulier')->value('id'),
                'particulier_id' => $profil->id,
            ]);
        });

        return redirect()->route('admin.entreprises.show', $entreprise)
            ->with('success', "{$data['name']} a été créé et ajouté comme membre. Mot de passe temporaire : "
                . User::MOT_DE_PASSE_PAR_DEFAUT . ' (à changer à sa première connexion).');
    }

    public function edit(Entreprise $entreprise, User $user)
    {
        $this->verifierMembre($entreprise, $user);

        return view('entreprises.membres.edit', [
            'entreprise' => $entreprise,
            'membre' => $user->load('particulier'),
        ]);
    }

    public function update(Request $request, Entreprise $entreprise, User $user)
    {
        $profil = $this->verifierMembre($entreprise, $user);
        $this->normaliserTelephones($request, ['telephone']);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'telephone' => ['required', 'string', 'max:20', 'regex:' . self::REGEX_TELEPHONE],
            'date_de_naissance' => ['nullable', 'date', 'before_or_equal:today'],
        ], $this->messages());

        DB::transaction(function () use ($user, $profil, $data) {
            $user->update(['name' => $data['name'], 'email' => $data['email']]);
            $profil->update([
                'telephone' => $data['telephone'],
                'date_de_naissance' => $data['date_de_naissance'] ?? null,
            ]);
        });

        return redirect()->route('admin.entreprises.show', $entreprise)
            ->with('success', 'Membre modifié avec succès.');
    }

    // Retire le membre de l'entreprise : son compte et ses inscriptions sont conservés
    public function destroy(Entreprise $entreprise, User $user)
    {
        $this->verifierMembre($entreprise, $user)->update(['entreprise_id' => null]);

        return redirect()->route('admin.entreprises.show', $entreprise)
            ->with('success', "{$user->name} a été retiré de l’entreprise (son compte est conservé).");
    }

    // Retire plusieurs membres cochés dans la liste
    public function destroyMultiple(Request $request, Entreprise $entreprise)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:users,id'],
        ], [
            'ids.required' => 'Sélectionnez au moins un membre.',
        ]);

        // Seuls les membres de CETTE entreprise sont concernés, quoi que contienne la requête
        $retires = Particulier::where('entreprise_id', $entreprise->id)
            ->whereHas('user', fn ($q) => $q->whereIn('id', $data['ids']))
            ->update(['entreprise_id' => null]);

        return redirect()->route('admin.entreprises.show', $entreprise)
            ->with('success', "{$retires} membre(s) retiré(s) de l’entreprise (leurs comptes sont conservés).");
    }

    private function messages(): array
    {
        return [
            'name.required' => 'Le nom est obligatoire.',
            'email.required' => 'L’adresse e-mail est obligatoire.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'telephone.required' => 'Le téléphone est obligatoire pour un particulier.',
            'telephone.regex' => 'Le téléphone doit être au format international, par exemple +24177123456.',
            'date_de_naissance.before_or_equal' => 'La date de naissance ne peut pas être dans le futur.',
        ];
    }
}