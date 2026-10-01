<?php

namespace App\Http\Controllers;

use App\Models\Cours;
use App\Models\Quiz;
use App\Models\ReponseQuiz;
use App\Models\ResultatQuiz;
use App\Services\QuizFinalisationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Str;

// Gère le passage d'un quiz d'auto-évaluation côté particulier :
// démarrage/reprise d'une tentative, enregistrement des réponses au fil de l'eau,
// clôture (manuelle ou automatique au bout d'1h), et affichage du résultat/correction.
class QuizAttemptController extends Controller
{
    // Durée maximale d'une tentative, en minutes. Sert à calculer "heure_fin"
    // au moment du démarrage (voir demarrer()).
    private const DUREE_MAX_MINUTES = 60;

    // Laravel injecte automatiquement une instance du service dans $this->finalisation
    // (injection de dépendances) : pas besoin d'écrire "new QuizFinalisationService()".
    // Ce service centralise le calcul de la deadline et du score, pour que la logique
    // soit identique que la clôture vienne d'ici ou de la commande planifiée
    // (voir app/Console/Commands/FinaliserQuizExpires.php).
    public function __construct(private QuizFinalisationService $finalisation)
    {
    }

    /**
     * Affiche la page de passage du quiz pour un cours donné.
     * - S'il existe déjà une tentative en cours (non clôturée) pour ce user+cours, on la reprend.
     * - Si elle existe mais que le temps est dépassé, on la clôture avant d'afficher quoi que ce soit.
     * - Sinon, on en démarre une toute nouvelle.
     */
    public function show(Request $request, Cours $cours)
{
    $user  = $request->user();
    $token = $request->session()->get('visiteur_token');
    $email = $request->session()->get('visiteur_email');

    // Visiteur sans e-mail enregistré : on le demande avant de commencer
    if (!$user && (!$email || !$token)) {
        return redirect()->route('quiz.visiteur.email', $cours);
    }

    $quiz = Quiz::where('cours_id', $cours->id)
        ->when(
            $user,
            fn ($q) => $q->where('user_id', $user->id),
            fn ($q) => $q->whereNull('user_id')->where('visiteur_token', $token)
        )
        ->whereDoesntHave('resultats')
        ->latest('id')
        ->first();

    if ($quiz && $this->finalisation->estExpire($quiz)) {
        $resultat = $this->finalisation->finaliser($quiz);
        return redirect($this->urlApresFin($request, $quiz, $resultat))
            ->with('info', 'Le temps était écoulé, votre quiz a été envoyé automatiquement.');
    }

    if (!$quiz) {
        $autre = $this->finalisation->tentativeEnCours($user, null, $token);
        if ($autre) {
            return redirect()->route('cours.show', $cours)->with('quiz_en_cours', [
                'cours' => $autre->cours->titre,
                'url'   => route('quiz.tentative.show', $autre->cours),
            ]);
        }
        $quiz = $this->demarrer($cours, $user, $email, $token);
    }

        // orderBy('ordre') : réaffiche toujours les questions dans le même ordre que
        // lors du tirage initial, même si l'utilisateur quitte la page et revient plus tard.
        
        $reponses = $quiz->reponses()->with(['question.options'])->orderBy('ordre')->get();

        // Temps restant = deadline (heure_debut + 1h, figée au démarrage) - maintenant.
        // On soustrait deux timestamps : pas de piège de signe ni de décimales avec Carbon 3.
        $secondesRestantes = max(
            0,
            $this->finalisation->limite($quiz)->getTimestamp() - now()->getTimestamp()
        );

        return view('quiz.passer', compact('quiz', 'reponses', 'secondesRestantes'));
    }

    /**
     * Crée une nouvelle tentative : une ligne "quizzes" représentant la session,
     * puis une ligne "reponses_quiz" par question tirée (avec option_id encore vide).
     */
    private function demarrer(Cours $cours, $user, $email, $token): Quiz
    {
        // DB::transaction : si une des insertions plante en cours de route
        // (ex. coupure réseau avec la base), tout est annulé plutôt que de laisser
        // une tentative à moitié créée (quiz sans ses reponses_quiz, par exemple).
        return DB::transaction(function () use ($cours, $user, $email, $token) {
            // On tire TOUTE la banque de questions du cours, juste mélangée
            // (inRandomOrder), pas un sous-ensemble.
            // On la récupère AVANT de créer la ligne "quizzes" car le barème
            // (voir plus bas) dépend directement du nombre de questions tirées.
            $questionIds = $cours->questions()->inRandomOrder()->pluck('id');

            $quiz = Quiz::create([
                'cours_id' => $cours->id,
                'user_id'        => $user?->id,
                'email_visiteur' => $user ? null : $email,
                'visiteur_token' => $user ? null : $token,
                'date' => now()->toDateString(),
                'heure_debut' => now()->format('H:i:s'),
                // Deadline calculée et figée dès le démarrage (et non recalculée
                // à chaque requête) : c'est elle qui fait foi pour détecter l'expiration.
                'heure_fin' => now()->addMinutes(self::DUREE_MAX_MINUTES)->format('H:i:s'),
                'cours_id' => $cours->id,
                'user_id'        => $user?->id,
                'email_visiteur' => $user ? null : $email,
                'visiteur_token' => $user ? null : $token,
                // Chaque question vaut 1 point -> le barème (le "noté sur X")
                // est simplement le nombre de questions posées pour cette tentative.
                // Ex : 30 questions tirées -> bareme = 30 -> quiz noté sur 30.
                'bareme' => $questionIds->count(),
            ]);

            // Une ligne reponses_quiz par question tirée, avec option_id absent
            // (reste NULL par défaut) : elle sera renseignée au fil de l'eau
            // par repondre(). "ordre" mémorise la position du tirage pour un
            // réaffichage stable en cas de reprise.
            foreach ($questionIds as $ordre => $questionId) {
                ReponseQuiz::create([
                    'quiz_id' => $quiz->id,
                    'question_id' => $questionId,
                    'ordre' => $ordre,
                ]);
            }

            return $quiz;
        });
    }

    /**
     * Enregistre la réponse à UNE question, appelée en AJAX à chaque clic
     * sur une option (voir le fetch() dans passer.blade.php). Ne recharge pas la page,
     * ce qui permet de garder l'état à jour même si l'utilisateur ferme l'onglet
     * juste après sans cliquer sur "Terminer".
     */
    public function repondre(Request $request, Quiz $quiz)
    {
        // Empêche un particulier de modifier la tentative d'un autre utilisateur
        // (protection contre la manipulation de l'URL/des paramètres).
        abort_unless($this->estProprietaire($request, $quiz), 403);

        // Une tentative déjà notée (résultat existant) ne doit plus pouvoir être modifiée.
        abort_if($quiz->resultats()->exists(), 409);

        // Vérification défensive côté serveur : même si le minuteur JS a un décalage
        // ou un bug, c'est ici que la vraie limite est appliquée. Si le temps est
        // dépassé, on clôture immédiatement au lieu d'enregistrer la réponse.
        if ($this->finalisation->estExpire($quiz)) {
            $resultat = $this->finalisation->finaliser($quiz);
            return response()->json(['ok' => false, 'expire' => true, 'redirect' => $this->urlApresFin($request, $quiz, $resultat)]);
        }

        $data = $request->validate([
            'question_id' => 'required|exists:questions,id',
            'option_id' => 'required|exists:options,id',
        ]);

        // firstOrFail() : si question_id ne correspond à aucune ligne reponses_quiz
        // de CETTE tentative (valeur manipulée/invalide), on obtient une 404
        // plutôt que d'enregistrer n'importe quoi.
        $quiz->reponses()->where('question_id', $data['question_id'])
            ->firstOrFail()
            ->update(['option_id' => $data['option_id']]);

        return response()->json(['ok' => true]);
    }

    /**
     * Clôture la tentative : soit un clic volontaire sur "Terminer le quiz",
     * soit une soumission automatique du formulaire par le JS quand le minuteur atteint 0.
     */
    public function terminer(Request $request, Quiz $quiz)
    {
        abort_unless($this->estProprietaire($request, $quiz), 403);

        // Si un résultat existe déjà (ex. double clic, ou double soumission du formulaire
        // au moment exact où le minuteur atteint 0), on ne le recrée pas — on réutilise
        // celui qui existe déjà plutôt que de générer un doublon.
        $resultat = $quiz->resultats()->first() ?? $this->finalisation->finaliser($quiz);

        return redirect($this->urlApresFin($request, $quiz, $resultat));
    }

    /**
     * Affiche la note obtenue ainsi que la correction détaillée
     * (bonne réponse en vert, mauvaise réponse choisie en rouge).
     */
    public function resultat(Request $request, ResultatQuiz $resultatQuiz)
    {
        // Un particulier ne doit voir que ses propres résultats, jamais ceux
        // d'un autre utilisateur même en devinant l'id dans l'URL.
        abort_unless($resultatQuiz->user_id === $request->user()->id, 403);

        $reponses = $resultatQuiz->quiz->reponses()->with(['question.options'])->orderBy('ordre')->get();

        return view('quiz.resultat', compact('resultatQuiz', 'reponses'));
    }

    public function annuler(Request $request, Quiz $quiz)
    {
        // Refuse l'annulation si cette tentative n'appartient pas à l'utilisateur connecté.
        abort_unless($this->estProprietaire($request, $quiz), 403);
        // Conserve le cours associé pour rediriger l'utilisateur vers sa page après l'annulation.
        $cours = $quiz->cours;

        if ($quiz->resultats()->exists()) {
            return redirect()->route('cours.show', $cours)
                ->with('error', 'Ce quiz est déjà terminé, il ne peut plus être annulé.');
        }

        $quiz->delete(); // reponses_quiz part en cascade (clé étrangère)

        return redirect()->route('cours.show', $cours)->with('success', 'Le quiz a été annulé.');
    }

    public function historique(Request $request, Cours $cours)
    {
        $resultats = ResultatQuiz::where('user_id', $request->user()->id)
            ->whereHas('quiz', fn ($q) => $q->where('cours_id', $cours->id))
            ->with('quiz')
            ->latest()
            ->get();

        return view('quiz.historique', compact('cours', 'resultats'));
    }

    // Propriétaire = utilisateur connecté, OU visiteur dont le token de session correspond
    private function estProprietaire(Request $request, Quiz $quiz): bool
    {
        $user = $request->user();
        if ($user) {
            return $quiz->user_id === $user->id;
        }

        $token = (string) $request->session()->get('visiteur_token');
        return $quiz->user_id === null
            && $quiz->visiteur_token !== null
            && $token !== ''
            && hash_equals($quiz->visiteur_token, $token);
    }

    // Où aller une fois le quiz fini : résultat pour un connecté, invitation à s'inscrire pour un visiteur
    private function urlApresFin(Request $request, Quiz $quiz, ResultatQuiz $resultat): string
    {
        return $request->user()
            ? route('quiz.resultat', $resultat)
            : route('quiz.visiteur.termine', $quiz);
    }

    // Étape 1 visiteur : saisie obligatoire de l'e-mail avant de commencer
    public function emailVisiteur(Request $request, Cours $cours)
    {
        if ($request->user()) {
            return redirect()->route('quiz.tentative.show', $cours);
        }
        return view('quiz.visiteur-email', compact('cours'));
    }

    public function enregistrerEmailVisiteur(Request $request, Cours $cours)
    {
        if ($request->user()) {
            return redirect()->route('quiz.tentative.show', $cours);
        }

        $data  = $request->validate(['email' => ['required', 'string', 'email', 'max:255']]);
        $email = mb_strtolower(trim($data['email']));

        // Adresse déjà inscrite : inutile de passer en anonyme, on l'envoie se connecter
        if (User::where('email', $email)->exists()) {
            return redirect()->route('login')
                ->with('status', 'Un compte existe déjà avec cette adresse e-mail. Connectez-vous pour passer le quiz.');
        }

        // Nouvelle adresse => nouvelle identité de visiteur (les anciennes tentatives sont abandonnées)
        if ($request->session()->get('visiteur_email') !== $email || !$request->session()->has('visiteur_token')) {
            $request->session()->put('visiteur_token', Str::random(40));
        }
        $request->session()->put('visiteur_email', $email);

        return redirect()->route('quiz.tentative.show', $cours);
    }

    // Étape finale visiteur : quiz terminé, résultat masqué tant qu'il n'est pas inscrit
    public function termineVisiteur(Request $request, Quiz $quiz)
    {
        if ($request->user()) {
            return redirect()->route('cours.show', $quiz->cours);
        }
        abort_unless($this->estProprietaire($request, $quiz), 403);

        if (!$quiz->resultats()->exists()) {
            return redirect()->route('quiz.tentative.show', $quiz->cours);
        }

        return view('quiz.visiteur-termine', [
            'cours' => $quiz->cours,
            'email' => $quiz->email_visiteur,
        ]);
    }
}