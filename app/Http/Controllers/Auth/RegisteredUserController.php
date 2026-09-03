<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Entreprise;
use App\Models\Particulier;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Affiche le formulaire d'inscription (resources/views/auth/register.blade.php).
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Traite la soumission du formulaire d'inscription.
     *
     * Contrairement au RegisteredUserController Breeze de base, celui-ci doit
     * créer AUSSI une ligne dans "particuliers" ou "entreprises" (selon le rôle
     * choisi), car la table "users" a role_id obligatoire (NOT NULL) et
     * particulier_id/entreprise_id qui pointent vers ces tables.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],

            // Verrouille la valeur à l'une des deux seules options du <select>
            'role' => ['required', 'in:particulier,entreprise'],

            // required_if:role,particulier => ces champs ne sont obligatoires
            // QUE si l'utilisateur a choisi "particulier" dans le formulaire.
            // Si "entreprise" est choisi, ils peuvent rester vides sans erreur.
            'telephone' => ['required_if:role,particulier', 'string', 'max:20'],
            'date_de_naissance' => ['required_if:role,particulier', 'date'],

            // Même logique inversée pour les champs "entreprise"
            'raison_sociale' => ['required_if:role,entreprise', 'string', 'max:255'],
            'adresse' => ['required_if:role,entreprise', 'string', 'max:255'],
            'contact_principal' => ['required_if:role,entreprise', 'string', 'max:255'],
            'secteur_activite' => ['required_if:role,entreprise', 'string', 'max:255'],
        ]);

        // Champs communs à tout User, quel que soit le rôle
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ];

        if ($request->role === 'particulier') {
            // 1) On crée d'abord la ligne "particuliers" avec ses champs propres
            $particulier = Particulier::create([
                'telephone' => $request->telephone,
                'date_de_naissance' => $request->date_de_naissance,
            ]);

            // 2) On récupère l'id du rôle "particulier" (créé par RoleSeeder)
            //    firstOrFail() : erreur explicite si les seeders n'ont pas tourné,
            //    plutôt qu'un role_id NULL qui violerait la contrainte NOT NULL en base.
            $userData['role_id'] = Role::where('nom', 'particulier')->firstOrFail()->id;

            // 3) On lie le user au particulier fraîchement créé (relation 1-1,
            //    particulier_id est UNIQUE dans la table users)
            $userData['particulier_id'] = $particulier->id;
        } else {
            // Même logique côté "entreprise", avec ses propres champs
            $entreprise = Entreprise::create([
                'raison_sociale' => $request->raison_sociale,
                'adresse' => $request->adresse,
                'contact_principal' => $request->contact_principal,
                'secteur_activite' => $request->secteur_activite,
            ]);

            $userData['role_id'] = Role::where('nom', 'entreprise')->firstOrFail()->id;

            // Pas de contrainte UNIQUE ici (contrairement à particulier_id) :
            // plusieurs users/contacts peuvent partager la même entreprise_id.
            $userData['entreprise_id'] = $entreprise->id;
        }

        // Création finale du User, une fois qu'on sait quel role_id/particulier_id/
        // entreprise_id lui attribuer
        $user = User::create($userData);

        // Déclenche l'événement Registered (écouté par défaut pour envoyer
        // l'email de vérification d'adresse, si MustVerifyEmail est activé sur User)
        event(new Registered($user));

        // Connecte automatiquement l'utilisateur après inscription
        // (comportement standard Breeze : pas besoin de re-taper ses identifiants)
        Auth::login($user);

        // route('root', ...) et non route('dashboard', ...) : "dashboard" n'existe
        // pas dans ce projet, la vraie page d'accueil protégée s'appelle "root"
        // (cf. routes/web.php -> HomeController@root)
        return redirect(route('root', absolute: false));
    }
}