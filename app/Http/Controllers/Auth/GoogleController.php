<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Entreprise;
use App\Models\Particulier;
use App\Models\Role;
use App\Models\User;
use App\Services\QuizFinalisationService;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

/**
 * « Continuer avec Google » (Laravel Socialite).
 *
 * Parcours :
 *  1. redirect()  : envoie l'utilisateur sur la page de connexion de Google ;
 *  2. callback()  : Google renvoie l'utilisateur ici avec son identité (nom, e-mail, id Google).
 *       - un compte NéoVision existe déjà (même id Google, ou même e-mail) -> connexion directe,
 *         le compte Google y est rattaché ;
 *       - aucun compte -> page « Finaliser votre inscription » : comme sur la page d'inscription,
 *         l'utilisateur choisit son rôle (Particulier ou Entreprise) et complète ce que Google
 *         ne fournit pas (téléphone pour un particulier ; raison sociale, siège, contact pour une entreprise).
 *  3. completer() / store() : création du compte PARTICULIER ou ENTREPRISE, e-mail déjà vérifié
 *     par Google (pas de code à 6 chiffres), puis connexion.
 *
 * Exception (même règle que l'inscription classique) : un visiteur qui a passé un quiz avec cette
 * adresse et attend son résultat est forcément inscrit comme particulier.
 */
class GoogleController extends Controller
{
    // Clé de session qui garde l'identité Google entre le retour de Google et la finalisation
    private const SESSION = 'google_inscription';

    /**
     * Étape 1 : redirection vers Google.
     */
    public function redirect(): RedirectResponse
    {
        // Configuration absente du .env : message clair plutôt qu'une erreur de Google
        if (! config('services.google.client_id') || ! config('services.google.client_secret')) {
            return redirect()->route('login')
                ->with('error', 'La connexion avec Google n\'est pas encore configurée.');
        }

        return Socialite::driver('google')
            // Toujours proposer le choix du compte Google (utile sur un poste partagé)
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    /**
     * Étape 2 : retour de Google.
     */
    public function callback(Request $request): RedirectResponse
    {
        // L'utilisateur a refusé sur l'écran de Google (ou Google a renvoyé une erreur)
        if ($request->filled('error')) {
            return redirect()->route('login')->with('error', 'Connexion avec Google annulée.');
        }

        try {
            $google = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            // Jeton expiré, paramètre "state" invalide (onglet ouvert trop longtemps)…
            report($e);

            return redirect()->route('login')
                ->with('error', 'La connexion avec Google a échoué. Veuillez réessayer.');
        }

        $email = Str::lower((string) $google->getEmail());

        if ($email === '') {
            return redirect()->route('login')
                ->with('error', 'Votre compte Google ne communique pas d\'adresse e-mail.');
        }

        // Compte existant : d'abord par l'identifiant Google, sinon par l'adresse e-mail
        $user = User::where('google_id', $google->getId())->first()
            ?? User::where('email', $email)->first();

        if ($user) {
            return $this->connecter($request, $user, $google->getId());
        }

        // Nouveau venu : on garde son identité Google en session le temps qu'il complète son profil
        $request->session()->put(self::SESSION, [
            'google_id' => $google->getId(),
            'name' => $google->getName() ?: Str::before($email, '@'),
            'email' => $email,
        ]);

        return redirect()->route('google.completer');
    }

    /**
     * Étape 3a : formulaire « Finaliser votre inscription » (téléphone, date de naissance).
     */
    public function completer(Request $request): View|RedirectResponse
    {
        $google = $request->session()->get(self::SESSION);

        // Page ouverte directement, sans passer par Google
        if (! $google) {
            return redirect()->route('login');
        }

        return view('auth.google-completer', [
            'google' => $google,
            // Quiz visiteur en attente avec cette adresse : rôle bloqué sur « Particulier »
            'roleVerrouille' => $this->quizVisiteurEnAttente($request, $google['email']),
        ]);
    }

    /**
     * Étape 3b : création du compte (particulier ou entreprise) puis connexion.
     */
    public function store(Request $request): RedirectResponse
    {
        $google = $request->session()->get(self::SESSION);

        if (! $google) {
            return redirect()->route('login')
                ->with('error', 'Votre session Google a expiré. Cliquez à nouveau sur « Continuer avec Google ».');
        }

        // Quiz visiteur en attente : rôle imposé par le serveur, quoi qu'envoie le navigateur
        if ($this->quizVisiteurEnAttente($request, $google['email'])) {
            $request->merge(['role' => 'particulier']);
        }

        // Mêmes règles que l'inscription classique (RegisteredUserController), selon le rôle choisi.
        // L'e-mail ne vient PAS du formulaire : il vient de Google (session), il est donc fiable.
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'role' => ['required', 'in:particulier,entreprise'],

            // Particulier
            'telephone' => ['nullable', 'required_if:role,particulier', 'string', 'max:20', 'regex:/^\+[1-9]\d{6,14}$/'],
            'date_de_naissance' => ['nullable', 'date', 'before:today'],

            // Entreprise
            'raison_sociale' => ['nullable', 'required_if:role,entreprise', 'string', 'max:255'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'contact_principal' => ['nullable', 'required_if:role,entreprise', 'string', 'max:255'],
            'secteur_activite' => ['nullable', 'string', 'max:255'],
        ], [
            'name.required' => 'Le nom est obligatoire.',
            'role.required' => 'Veuillez choisir si vous êtes un particulier ou une entreprise.',
            'role.in' => 'Veuillez choisir si vous êtes un particulier ou une entreprise.',
            'telephone.required_if' => 'Le numéro de téléphone est obligatoire.',
            'telephone.regex' => 'Veuillez entrer un numéro de téléphone valide pour le pays sélectionné.',
            'date_de_naissance.before' => 'La date de naissance doit être dans le passé.',
            'raison_sociale.required_if' => 'La raison sociale est obligatoire.',
            'contact_principal.required_if' => 'Le contact principal est obligatoire.',
        ]);

        // Sécurité : un compte a pu être créé avec cet e-mail entre-temps (autre onglet…)
        if ($existant = User::where('email', $google['email'])->first()) {
            $request->session()->forget(self::SESSION);

            return $this->connecter($request, $existant, $google['google_id']);
        }

        // Particulier (ou entreprise) + utilisateur créés ensemble, ou pas du tout
        $user = DB::transaction(function () use ($data, $google) {
            $userData = [
                'name' => $data['name'],
                'email' => $google['email'],
                // Mot de passe aléatoire inconnu de tous : le compte se connecte avec Google.
                // L'utilisateur peut en choisir un plus tard via « Mot de passe oublié ».
                'password' => Hash::make(Str::random(40)),
                'google_id' => $google['google_id'],
            ];

            if ($data['role'] === 'particulier') {
                $particulier = Particulier::create([
                    'telephone' => $data['telephone'],
                    'date_de_naissance' => $data['date_de_naissance'] ?? null,
                ]);

                $userData['role_id'] = Role::where('nom', 'particulier')->firstOrFail()->id;
                $userData['particulier_id'] = $particulier->id;
            } else {
                $entreprise = Entreprise::create([
                    'raison_sociale' => $data['raison_sociale'],
                    'adresse' => $data['adresse'] ?? null,
                    'contact_principal' => $data['contact_principal'],
                    'secteur_activite' => ($data['secteur_activite'] ?? null) ?: null,
                ]);

                $userData['role_id'] = Role::where('nom', 'entreprise')->firstOrFail()->id;
                $userData['entreprise_id'] = $entreprise->id;
            }

            $user = User::create($userData);

            // Adresse déjà vérifiée par Google : pas de code de vérification à 6 chiffres.
            // forceFill car email_verified_at n'est pas dans $fillable.
            $user->forceFill(['email_verified_at' => now()])->save();

            return $user;
        });

        $request->session()->forget(self::SESSION);

        // Même événement qu'après le code à 6 chiffres : déclenche l'e-mail de bienvenue
        event(new Verified($user));

        $this->rattacherQuizVisiteur($request, $user);

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false))
            ->with('success', 'Bienvenue ' . $user->name . ' : votre compte a été créé avec Google.');
    }

    /**
     * Abandon de la finalisation : on oublie l'identité Google.
     */
    public function annuler(Request $request): RedirectResponse
    {
        $request->session()->forget(self::SESSION);

        return redirect()->route('login');
    }

    // -----------------------------------------------------------------
    // Outils internes
    // -----------------------------------------------------------------

    /**
     * Connecte un utilisateur existant et lui rattache son compte Google.
     */
    private function connecter(Request $request, User $user, string $googleId): RedirectResponse
    {
        // Ce compte est déjà lié à un AUTRE compte Google : on refuse plutôt que d'écraser
        if ($user->google_id && $user->google_id !== $googleId) {
            return redirect()->route('login')
                ->with('error', 'Cette adresse e-mail est déjà liée à un autre compte Google.');
        }

        $user->google_id = $googleId;

        // Inscription classique jamais vérifiée (code à 6 chiffres non saisi) : Google prouve
        // que l'adresse appartient bien à cette personne, on la marque donc comme vérifiée.
        $vientDEtreVerifie = false;
        if (! $user->hasVerifiedEmail()) {
            $user->email_verified_at = now();
            $vientDEtreVerifie = true;
        }

        $user->save();

        if ($vientDEtreVerifie) {
            $user->clearVerificationCode();
            event(new Verified($user));   // e-mail de bienvenue, comme après le code
        }

        $this->rattacherQuizVisiteur($request, $user);

        // true = « Se souvenir de moi »
        Auth::login($user, true);
        $request->session()->regenerate();

        // Les comptes créés par l'admin restent obligés de changer leur mot de passe par défaut :
        // le middleware ForcePasswordChange s'en charge sur la page suivante.
        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Vrai si un visiteur anonyme a passé un quiz avec cette adresse et attend son résultat :
     * il doit alors devenir particulier pour retrouver ce quiz dans son historique.
     */
    private function quizVisiteurEnAttente(Request $request, string $email): bool
    {
        $token = $request->session()->get('visiteur_token');

        return $token
            && Str::lower((string) $request->session()->get('visiteur_email')) === Str::lower($email)
            && app(QuizFinalisationService::class)->visiteurAResultatEnAttente($token);
    }

    /**
     * Quiz passé en visiteur anonyme : rattaché au compte si l'e-mail saisi avant le quiz
     * est celui du compte Google (même règle que l'inscription classique).
     */
    private function rattacherQuizVisiteur(Request $request, User $user): void
    {
        $token = $request->session()->get('visiteur_token');
        $email = Str::lower((string) $request->session()->get('visiteur_email'));
        $service = app(QuizFinalisationService::class);

        if (! $token || $email !== $user->email || ! $service->visiteurAResultatEnAttente($token)) {
            return;
        }

        $service->rattacherVisiteur($user, $token);
        $request->session()->forget(['visiteur_email', 'visiteur_token']);

        // Comme après l'inscription classique : on atterrit sur l'historique du quiz
        $dernierCours = $user->resultatsQuiz()->with('quiz')->latest()->first()?->quiz?->cours_id;
        if ($dernierCours) {
            $request->session()->put('url.intended', route('quiz.historique', $dernierCours));
        }
    }
}
