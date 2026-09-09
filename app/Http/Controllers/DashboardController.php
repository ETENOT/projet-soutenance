<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request){
        // Auth::user() renvoie l'utilisateur connecté (peu importe le nom de sa classe,
        // ->load('role') précharge la relation "role" tout de suite (1 seule requête SQL),
        $utilisateur = Auth::user()->load('role');

         // match() compare $utilisateur->role->nom à chaque cas dans l'ordre.
         //$utilisateur->role->nom fonctionne car : role_id sur "users" pointe vers "roles",
         return match ($utilisateur->role->nom) {      
            'admin' => $this->admin($utilisateur) ,
            'entreprise' => $this->entreprise($utilisateur),
            'particulier' => $this->particulier($utilisateur),

            default => abort(403, 'Rôle non reconnu.'),
        };
    }

     // Chaque méthode privée retourne la vue Blade correspondant au rôle,
    // en lui passant l'utilisateur connecté pour que la vue puisse afficher son nom, etc.
    private function particulier($utilisateur)
{
    // Nombre total de sessions auxquelles l'utilisateur est inscrit
    $sessionsInscrites = $utilisateur->inscriptions()->count();

    // Sessions dont la classe est actuellement en cours (date_debut <= aujourd'hui <= date_fin)
    $coursEnCours = $utilisateur->inscriptions()
        ->whereHas('classe', function ($q) {
            $q->where('date_debut', '<=', now())
              ->where('date_fin', '>=', now());
        })->count();

    // La prochaine classe à venir (date_debut la plus proche dans le futur)
    $prochaineClasse = \App\Models\Classe::whereHas('inscriptions', function ($q) use ($utilisateur) {
            $q->where('user_id', $utilisateur->id);
        })
        ->where('date_debut', '>', now())
        ->orderBy('date_debut')
        ->first();

    // Moyenne des scores sur tous les quiz passés (null si aucun quiz encore fait)
    $moyenneQuiz = $utilisateur->resultatsQuiz()->avg('score');

    // Notifications non encore lues
    $notificationsNonLues = $utilisateur->notifications()->where('est_lue', false)->count();

    // Sessions payées vs impayées (une Inscription est "payée" si elle a un Paiement lié)
    $sessionsPayees = $utilisateur->inscriptions()->whereHas('paiement')->count();
    $sessionsImpayees = $utilisateur->inscriptions()->whereDoesntHave('paiement')->count();

    return view('dashboards.particulier', [
        'utilisateur' => $utilisateur,
        'sessionsInscrites' => $sessionsInscrites,
        'coursEnCours' => $coursEnCours,
        'prochaineSession' => $prochaineClasse
            ? \Carbon\Carbon::parse($prochaineClasse->date_debut)->format('d/m/Y')
            : 'Aucune session prévue',
        'moyenneQuiz' => $moyenneQuiz !== null ? round($moyenneQuiz, 1) : null,
        'notificationsNonLues' => $notificationsNonLues,
        'sessionsPayees' => $sessionsPayees,
        'sessionsImpayees' => $sessionsImpayees,
    ]);
}

    private function entreprise($utilisateur)
{
    $entreprise = $utilisateur->entreprise;

    // Garde-fou : un compte "entreprise" sans entreprise_id renseigné ne devrait
    // normalement pas arriver, mais on évite un crash si ça se produit.
    if (!$entreprise) {
        return view('dashboards.entreprise', [
            'utilisateur' => $utilisateur,
            'devisEnCours' => 0,
            'employesInscrits' => 0,
            'sessionsReservees' => 0,
            'budgetFormation' => 0,
        ]);
    }

    // Devis en attente de traitement
    $devisEnCours = $entreprise->devis()->where('statut', 'en_attente')->count();

    // Sessions réservées par n'importe quel employé de cette entreprise
    $sessionsReservees = \App\Models\Inscription::whereHas('user', function ($q) use ($entreprise) {
        $q->where('entreprise_id', $entreprise->id);
    })->count();

    // Total dépensé en formation : somme des paiements liés aux inscriptions
    // de tous les employés de cette entreprise
    $budgetFormation = \App\Models\Paiement::whereHas('inscription.user', function ($q) use ($entreprise) {
        $q->where('entreprise_id', $entreprise->id);
    })->sum('montant');

    return view('dashboards.entreprise', [
        'utilisateur' => $utilisateur,
        'devisEnCours' => $devisEnCours,
        'sessionsReservees' => $sessionsReservees,
        'budgetFormation' => $budgetFormation,
    ]);
}

    private function admin($utilisateur)
    {
        // Compteurs simples : contexte du volume à gérer, pas des indicateurs business
        $totalUtilisateurs = User::count();
        $totalCours = \App\Models\Cours::count();
        $totalClasses = \App\Models\Classe::count();
        $totalQuiz = \App\Models\Quiz::count();

        return view('dashboards.admin', [
            'utilisateur' => $utilisateur,
            'totalUtilisateurs' => $totalUtilisateurs,
            'totalCours' => $totalCours,
            'totalClasses' => $totalClasses,
            'totalQuiz' => $totalQuiz,
        ]);
    }
}   
