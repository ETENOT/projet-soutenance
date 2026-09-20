<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
{
    $this->middleware('auth')->except(['root']);
}
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        if (view()->exists($request->path())) {
            return view($request->path());
        }
        return abort(404);
    }

  public function root()
{
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return view('accueil');
}

    /*Language Translation*/
    public function lang($locale)
    {
        if ($locale) {
            App::setLocale($locale);
            Session::put('lang', $locale);
            Session::save();
            return redirect()->back()->with('locale', $locale);
        } else {
            return redirect()->back();
        }
    }

    public function updateProfile(Request $request, $id)
    {
        $user = Auth::user()->load(['particulier', 'entreprise', 'role']);

        // Les champs complémentaires dépendent du profil métier de l'utilisateur.
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:1024'],
        ];

        // Règles supplémentaires selon le rôle, cohérentes avec celles
        // de l'inscription (RegisteredUserController)
        if ($user->role->nom === 'particulier') {
            $rules['telephone'] = ['required', 'string', 'max:20'];
            $rules['date_de_naissance'] = ['required', 'date'];
        } elseif ($user->role->nom === 'entreprise') {
            $rules['raison_sociale'] = ['required', 'string', 'max:255'];
            $rules['adresse'] = ['required', 'string', 'max:255'];
            $rules['contact_principal'] = ['required', 'string', 'max:255'];
            $rules['secteur_activite'] = ['required', 'string', 'max:255'];
        }

        $request->validate($rules);

        $user->name = $request->get('name');
        $user->email = $request->get('email');

        if ($request->file('avatar')) {
            $avatar = $request->file('avatar');
            $avatarName = time() . '.' . $avatar->getClientOriginalExtension();
            $avatarPath = public_path('/images/');
            $avatar->move($avatarPath, $avatarName);
            $user->avatar = $avatarName;
        }

        $user->update();

        // Met à jour la fiche particulier/entreprise liée, en plus du User
        if ($user->role->nom === 'particulier' && $user->particulier) {
            $user->particulier->update([
                'telephone' => $request->get('telephone'),
                'date_de_naissance' => $request->get('date_de_naissance'),
            ]);
        } elseif ($user->role->nom === 'entreprise' && $user->entreprise) {
            $user->entreprise->update([
                'raison_sociale' => $request->get('raison_sociale'),
                'adresse' => $request->get('adresse'),
                'contact_principal' => $request->get('contact_principal'),
                'secteur_activite' => $request->get('secteur_activite'),
            ]);
        }

        Session::flash('message', 'Profil mis à jour avec succès !');
        Session::flash('alert-class', 'alert-success');
        return redirect()->back();
    }

    public function updatePassword(Request $request, $id)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        if (!(Hash::check($request->get('current_password'), Auth::user()->password))) {
            return response()->json([
                'isSuccess' => false,
                'Message' => "Votre mot de passe actuel ne correspond pas."
            ], 200);
        }

        // On ne change pas le mot de passe tout de suite : on le garde en attente
        // en session (déjà haché), et on envoie un code de vérification pour
        // confirmer que c'est bien le propriétaire du compte qui agit.
        // Le nouveau mot de passe reste en attente jusqu'à la validation du code email.
        session(['pending_password' => Hash::make($request->get('password'))]);

        Auth::user()->sendEmailVerificationNotification();

        return response()->json([
            'isSuccess' => true,
            'requiresCode' => true,
            'Message' => "Un code de vérification a été envoyé à votre adresse email. Entrez-le pour confirmer le changement."
        ], 200);
    }

    /**
     * Deuxième étape : l'utilisateur soumet le code reçu par email.
     * Si valide, on applique le mot de passe qui était en attente en session.
     */
    public function confirmPasswordChange(Request $request)
    {
        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        if (!session()->has('pending_password')) {
            return response()->json([
                'isSuccess' => false,
                'Message' => "Aucun changement de mot de passe en attente. Recommencez."
            ], 200);
        }

        $status = Auth::user()->verificationCodeStatus($request->code);

        if ($status === 'expired') {
            return response()->json([
                'isSuccess' => false,
                'Message' => "Ce code a expiré. Relancez le changement de mot de passe."
            ], 200);
        }

        if ($status === 'invalid') {
            return response()->json([
                'isSuccess' => false,
                'Message' => "Ce code est incorrect."
            ], 200);
        }

        $user = Auth::user();
        $user->password = session('pending_password');
        $user->update();

        Auth::user()->clearVerificationCode();
        session()->forget('pending_password');

        return response()->json([
            'isSuccess' => true,
            'Message' => "Mot de passe modifié avec succès !"
        ], 200);
    }
}
