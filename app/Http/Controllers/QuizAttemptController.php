<?php

namespace App\Http\Controllers;

use App\Models\Cours;
use App\Models\Quiz;
use App\Models\ReponseQuiz;
use App\Models\ResultatQuiz;
use App\Services\QuizFinalisationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $user = $request->user();

        // Une "tentative en cours" = une ligne quizzes pour ce user + ce cours
        // qui n'a pas encore de résultat associé (whereDoesntHave('resultats')).
        // Dès qu'un résultat existe, la tentative est considérée comme terminée
        // et ne doit plus être reprise.
        $quiz = Quiz::where('cours_id', $cours->id)
            ->where('user_id', $user->id)
            ->whereDoesntHave('resultats')
            ->latest('id') // la plus récente, au cas où d'anciennes tentatives abandonnées traîneraient
            ->first();

        // Cas "tentative abandonnée découverte au retour de l'utilisateur" :
        // le temps est écoulé mais personne (ni lui, ni la tâche planifiée) n'a encore
        // généré le résultat -> on le fait maintenant, avant d'afficher la page.
        if ($quiz && $this->finalisation->estExpire($quiz)) {
            $resultat = $this->finalisation->finaliser($quiz);
            return redirect()->route('quiz.resultat', $resultat)
                ->with('info', 'Le temps était écoulé, votre quiz a été envoyé automatiquement.');
        }

        // Aucune tentative en cours trouvée -> on en crée une nouvelle
        // (premier passage, ou tentative précédente déjà terminée -> c'est un "repasser le quiz").
        if (!$quiz) {
            $quiz = $this->demarrer($cours, $user);
        }

        // orderBy('ordre') : réaffiche toujours les questions dans le même ordre que
        // lors du tirage initial, même si l'utilisateur quitte la page et revient plus tard.
        
        $reponses = $quiz->reponses()->with(['question.options'])->orderBy('ordre')->get();

        // Temps restant en secondes, transmis à la vue pour alimenter le minuteur JS.
        // diffInSeconds(..., false) * -1 : on force le signe pour obtenir un nombre positif
        // (la limite est dans le futur par rapport à maintenant).
        //$secondesRestantes = now()->diffInSeconds($this->finalisation->limite($quiz), false) * -1;
        $secondesRestantes = 3600;

        return view('quiz.passer', compact('quiz', 'reponses', 'secondesRestantes'));
    }

    /**
     * Crée une nouvelle tentative : une ligne "quizzes" représentant la session,
     * puis une ligne "reponses_quiz" par question tirée (avec option_id encore vide).
     */
    private function demarrer(Cours $cours, $user): Quiz
    {
        // DB::transaction : si une des insertions plante en cours de route
        // (ex. coupure réseau avec la base), tout est annulé plutôt que de laisser
        // une tentative à moitié créée (quiz sans ses reponses_quiz, par exemple).
        return DB::transaction(function () use ($cours, $user) {
            // On tire TOUTE la banque de questions du cours, juste mélangée
            // (inRandomOrder), pas un sous-ensemble.
            // On la récupère AVANT de créer la ligne "quizzes" car le barème
            // (voir plus bas) dépend directement du nombre de questions tirées.
            $questionIds = $cours->questions()->inRandomOrder()->pluck('id');

            $quiz = Quiz::create([
                'date' => now()->toDateString(),
                'heure_debut' => now()->format('H:i:s'),
                // Deadline calculée et figée dès le démarrage (et non recalculée
                // à chaque requête) : c'est elle qui fait foi pour détecter l'expiration.
                'heure_fin' => now()->addMinutes(self::DUREE_MAX_MINUTES)->format('H:i:s'),
                'cours_id' => $cours->id,
                'user_id' => $user->id,
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
        abort_unless($quiz->user_id === $request->user()->id, 403);

        // Une tentative déjà notée (résultat existant) ne doit plus pouvoir être modifiée.
        abort_if($quiz->resultats()->exists(), 409);

        // Vérification défensive côté serveur : même si le minuteur JS a un décalage
        // ou un bug, c'est ici que la vraie limite est appliquée. Si le temps est
        // dépassé, on clôture immédiatement au lieu d'enregistrer la réponse.
        if ($this->finalisation->estExpire($quiz)) {
            $resultat = $this->finalisation->finaliser($quiz);
            return response()->json(['ok' => false, 'expire' => true, 'redirect' => route('quiz.resultat', $resultat)]);
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
        abort_unless($quiz->user_id === $request->user()->id, 403);

        // Si un résultat existe déjà (ex. double clic, ou double soumission du formulaire
        // au moment exact où le minuteur atteint 0), on ne le recrée pas — on réutilise
        // celui qui existe déjà plutôt que de générer un doublon.
        $resultat = $quiz->resultats()->first() ?? $this->finalisation->finaliser($quiz);

        return redirect()->route('quiz.resultat', $resultat);
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
}