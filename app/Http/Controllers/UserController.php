<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Affiche la liste des utilisateurs avec leur rôle.
     */
    public function index(Request $request)
    {
        $query = User::with('role')->withCount('inscriptions');

        if ($recherche = trim((string) $request->input('q'))) {
            $query->where(function ($q) use ($recherche) {
                $q->where('name', 'like', "%{$recherche}%")
                ->orWhere('email', 'like', "%{$recherche}%");
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('role', fn ($q) => $q->where('nom', $request->input('role')));
        }

        match ($request->input('statut')) {
            'verifie'     => $query->whereNotNull('email_verified_at'),
            'non_verifie' => $query->whereNull('email_verified_at'),
            default       => null,
        };

        $compter = fn (string $role) => User::whereHas('role', fn ($q) => $q->where('nom', $role))->count();

        return view('users.index', [
            'utilisateurs' => $query->orderByDesc('created_at')->paginate(10)->withQueryString(),
            'roles' => Role::orderBy('nom')->get(),
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
     * Affiche le formulaire de création d'un utilisateur.
     */
    public function create()
    {
        return view('users.create', [
            'roles' => Role::orderBy('nom')->get(),
        ]);
    }

    /**
     * Valide et enregistre un nouvel utilisateur depuis l'espace admin.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        // Le mot de passe est hache avant d'etre enregistre en base.
        $data['password'] = Hash::make($data['password']);

        // Crée l'utilisateur depuis l'interface d'administration.
        $user = User::create($data);

        // L'adresse e-mail est considérée comme vérifiée immédiatement.
        // L'utilisateur n'a donc pas besoin de saisir le code de vérification.
        $user->markEmailAsVerified();

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur cree avec succes.');
    }

    /**
     * Affiche le formulaire de modification d'un utilisateur.
     */
    public function edit(User $user)
    {
        return view('users.edit', [
            'user' => $user->load('role'),
            'roles' => Role::orderBy('nom')->get(),
        ]);
    }

    /**
     * Met a jour les informations modifiables d'un utilisateur.
     */
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        // Un compte admin ne peut pas etre transforme en autre role depuis cette page.
        if ($user->role?->nom === 'admin') {
            $data['role_id'] = $user->role_id;
        }

        if (blank($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'Utilisateur modifie avec succes.');
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
}