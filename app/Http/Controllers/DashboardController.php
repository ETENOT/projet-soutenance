<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Auth::user() renvoie l'utilisateur connecté.
        // ->load('role') précharge la relation "role".
        $utilisateur = Auth::user()->load('role');

        // match() compare le rôle de l'utilisateur
        // et appelle la méthode correspondant à ce rôle.
        return match ($utilisateur->role->nom) {
            'admin' => $this->admin($utilisateur),
            'entreprise' => $this->entreprise($utilisateur),
            'particulier' => $this->particulier($utilisateur),

            default => abort(403, 'Rôle non reconnu.'),
        };
    }


    // =========================================================
    // ESPACE PARTICULIER
    // =========================================================

    private function particulier($utilisateur)
    {
        // Nombre total de sessions auxquelles l'utilisateur est inscrit.
        $sessionsInscrites = $utilisateur->inscriptions()->count();


        // =====================================================
        // SESSIONS EN COURS
        // =====================================================

        // Une session est en cours lorsque :
        // date_debut <= aujourd'hui
        // ET
        // date_fin >= aujourd'hui
        //
        // On vérifie également que la session est payée.
        $SessionEnCours = $utilisateur->inscriptions()
            ->whereHas('paiement')
            ->whereHas('classe', function ($q) {
                $q->where('date_debut', '<=', now())
                  ->where('date_fin', '>=', now());
            })
            ->count();


        // =====================================================
        // PROCHAINE SESSION
        // =====================================================

        // Recherche la prochaine classe à venir
        // parmi celles auxquelles l'utilisateur est inscrit.
        $prochaineClasse = \App\Models\Classe::whereHas('inscriptions', function ($q) use ($utilisateur) {
                $q->where('user_id', $utilisateur->id);
            })
            ->where('date_debut', '>', now())
            ->orderBy('date_debut')
            ->first();


        // =====================================================
        // ANCIENS COURS
        // =====================================================

        // On recherche les inscriptions :
        //
        // 1. appartenant à l'utilisateur connecté ;
        // 2. ayant un paiement ;
        // 3. dont la classe est terminée ;
        // 4. avec le cours correspondant.
        //
        // Chaîne :
        // Utilisateur
        //      ↓
        // Inscription
        //      ↓
        // Classe
        //      ↓
        // Cours
        $anciensCours = $utilisateur->inscriptions()
            ->whereHas('paiement')
            ->whereHas('classe', function ($q) {
                $q->where('date_fin', '<', now());
            })
            ->with('classe.cours')
            ->get()
            ->map(function ($inscription) {

                // On récupère le cours lié à la classe.
                return $inscription->classe->cours;
            })
            ->filter()
            ->unique('id')
            ->values();


        // =====================================================
        // MOYENNE DES QUIZ
        // =====================================================

        // Moyenne des scores obtenus aux quiz.
        $moyenneQuiz = $utilisateur->resultatsQuiz()->avg('score');


        // =====================================================
        // NOTIFICATIONS
        // =====================================================

        // Nombre de notifications qui n'ont pas encore été lues.
        $notificationsNonLues = $utilisateur->notifications()
            ->where('est_lue', false)
            ->count();


        // =====================================================
        // STATUT DES PAIEMENTS
        // =====================================================

        // Une inscription est considérée comme payée
        // lorsqu'elle possède un paiement associé.
        $sessionsPayees = $utilisateur->inscriptions()
            ->whereHas('paiement')
            ->count();

        // Inscriptions sans paiement.
        $sessionsImpayees = $utilisateur->inscriptions()
            ->whereDoesntHave('paiement')
            ->count();


        // =====================================================
        // VUE DU PARTICULIER
        // =====================================================

        return view('dashboards.particulier', [
            'utilisateur' => $utilisateur,

            'sessionsInscrites' => $sessionsInscrites,

            'SessionEnCours' => $SessionEnCours,

            'prochaineClasse' => $prochaineClasse
                ? \Carbon\Carbon::parse($prochaineClasse->date_debut)->format('d/m/Y')
                : 'Aucune session prévue',

            'moyenneQuiz' => $moyenneQuiz !== null
                ? round($moyenneQuiz, 1)
                : null,

            'notificationsNonLues' => $notificationsNonLues,

            'sessionsPayees' => $sessionsPayees,

            'sessionsImpayees' => $sessionsImpayees,

            // Nouveaux anciens cours.
            'anciensCours' => $anciensCours,
        ]);
    }


    // =========================================================
    // ESPACE ENTREPRISE
    // =========================================================

    private function entreprise($utilisateur)
    {
        $entreprise = $utilisateur->entreprise;

        // Garde-fou si aucun compte entreprise n'est associé.
        if (!$entreprise) {
            return view('dashboards.entreprise', [
                'utilisateur' => $utilisateur,
                'devisEnCours' => 0,
                'employesInscrits' => 0,
                'sessionsReservees' => 0,
                'budgetFormation' => 0,
            ]);
        }


        // Devis en attente de traitement.
        $devisEnCours = $entreprise->devis()
            ->where('statut', 'en_attente')
            ->count();


        // Sessions réservées par les employés de l'entreprise.
        $sessionsReservees = \App\Models\Inscription::whereHas('user', function ($q) use ($entreprise) {
            $q->where('entreprise_id', $entreprise->id);
        })
        ->count();


        // Total dépensé en formation par l'entreprise.
        $budgetFormation = \App\Models\Paiement::whereHas('inscription.user', function ($q) use ($entreprise) {
            $q->where('entreprise_id', $entreprise->id);
        })
        ->sum('montant');


        return view('dashboards.entreprise', [
            'utilisateur' => $utilisateur,
            'devisEnCours' => $devisEnCours,
            'sessionsReservees' => $sessionsReservees,
            'budgetFormation' => $budgetFormation,
        ]);
    }


    // =========================================================
    // ESPACE ADMIN
    // =========================================================

    private function admin($utilisateur)
    {
        // Compteurs du tableau de bord administrateur.
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

