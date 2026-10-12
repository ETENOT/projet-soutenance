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
                'total'        => User::count(),
            ],
        ]);
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

        User::create($data);

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