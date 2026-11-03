<?php

namespace Database\Seeders;

use App\Models\Chapitre;
use App\Models\Cours;
use App\Models\Question;
use App\Models\ReponseEvaluation;
use App\Models\TentativeEvaluation;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;

/**
 * Tentatives de démonstration sur les QUIZ DE CHAPITRE et les QUIZ FINAUX,
 * pour voir tous les états du parcours dès « migrate:fresh --seed » :
 *
 *  Initiation à l'informatique (classe EN COURS, commencée il y a 2 jours) :
 *   - Alice  : chapitre 1 (2 fois, en progrès) et 2 terminés, chapitre 3 EN COURS (« Continuer »),
 *              chapitre 4 et quiz final VERROUILLÉS ;
 *   - Brice  : chapitre 1 terminé (note sous la moyenne), chapitre 2 débloqué ;
 *   - Carine, David : rien encore (seul le chapitre 1 est débloqué).
 *
 *  Microsoft Word (classe TERMINÉE) :
 *   - Alice  : les 5 chapitres + quiz final réussi (repassé une 2e fois) ;
 *   - Brice  : chapitres 1 à 3 terminés, chapitre 4 débloqué, final verrouillé ;
 *   - Carine : les 5 chapitres + quiz final sous la moyenne.
 *
 *  particulier@test.com n'est inscrit à aucune classe : il voit les cadenas.
 *
 * À lancer APRÈS QuestionChapitreSeeder et FormateurSeeder (stagiaires et inscriptions).
 */
class TentativeEvaluationSeeder extends Seeder
{
    public function run(): void
    {
        // Tirages et réponses reproductibles d'une exécution à l'autre
        mt_srand(2027);

        $alice  = User::where('email', 'stagiaire1@test.com')->firstOrFail();
        $brice  = User::where('email', 'stagiaire2@test.com')->firstOrFail();
        $carine = User::where('email', 'stagiaire3@test.com')->firstOrFail();

        $initiation = Cours::where('titre', 'Initiation a l\'informatique')->firstOrFail();
        $word       = Cours::where('titre', 'Microsoft Word')->firstOrFail();

        // ----- Initiation à l'informatique : classe en cours (tentatives des 2 derniers jours) -----
        $i = $this->chapitres($initiation);
        $hier = now()->subDay()->setTime(9, 0);

        $this->passer($alice, $initiation, $i[1], $hier->copy()->subDay(), 1 / 3);   // 1re tentative ratée…
        $this->passer($alice, $initiation, $i[1], $hier, 1.0);                       // … repassée et réussie
        $this->passer($alice, $initiation, $i[2], $hier->copy()->addHour(), 2 / 3);
        $this->passer($alice, $initiation, $i[3], now()->subHour(), 2 / 3, terminee: false);   // en cours

        $this->passer($brice, $initiation, $i[1], $hier->copy()->addHours(2), 1 / 3);

        // ----- Microsoft Word : classe terminée (tentatives réparties pendant la classe) -----
        $w = $this->chapitres($word);
        $debut = now()->subDays(20)->setTime(10, 0);   // début de la classe (voir FormateurSeeder)

        foreach ($w as $ordre => $chapitre) {
            $jour = $debut->copy()->addDays($ordre);
            $this->passer($alice, $word, $chapitre, $jour, 1.0);
            $this->passer($carine, $word, $chapitre, $jour->copy()->addHour(), 1 / 3);
            if ($ordre <= 3) {
                $this->passer($brice, $word, $chapitre, $jour->copy()->addHours(2), 2 / 3);
            }
        }

        // Quiz finaux (questions de tous les chapitres)
        $this->passer($alice, $word, null, $debut->copy()->addDays(7), 0.7);
        $this->passer($alice, $word, null, $debut->copy()->addDays(8), 0.9);    // repassé pour faire mieux
        $this->passer($carine, $word, null, $debut->copy()->addDays(8), 0.3);
    }

    // Chapitres d'un cours indexés par leur numéro d'ordre : [1 => Chapitre, 2 => Chapitre, ...]
    private function chapitres(Cours $cours): array
    {
        return Chapitre::where('cours_id', $cours->id)->orderBy('ordre')->get()->keyBy('ordre')->all();
    }

    /**
     * Crée une tentative comme le ferait ParcoursEvaluationService::reprendreOuDemarrer(),
     * mais à une date passée et avec un taux de bonnes réponses choisi.
     *  $chapitre null = quiz final ; $taux = part des questions répondues juste (0 à 1) ;
     *  $terminee false = tentative laissée en cours (la dernière question reste sans réponse).
     */
    private function passer(User $user, Cours $cours, ?Chapitre $chapitre, Carbon $date, float $taux, bool $terminee = true): void
    {
        // Même tirage que l'application : questions du chapitre, ou de tous les chapitres pour le final
        $tirage = $chapitre
            ? Question::where('cours_id', $cours->id)->where('chapitre_id', $chapitre->id)
            : Question::where('cours_id', $cours->id)->whereIn('chapitre_id', Chapitre::where('cours_id', $cours->id)->select('id'));

        $noteSur = $chapitre ? $chapitre->quiz_note_sur : $cours->note_sur_final;

        // Mélange en PHP (reproductible grâce à mt_srand) puis limite au « noté sur »
        $liste = $tirage->with('options')->get()->all();
        shuffle($liste);   // shuffle() de PHP : suit mt_srand(), donc même tirage à chaque exécution
        $questions = collect($liste);
        if ($noteSur) {
            $questions = $questions->take($noteSur);
        }
        if ($questions->isEmpty()) {
            return;
        }

        $nbBonnes = (int) round($questions->count() * $taux);

        $tentative = TentativeEvaluation::create([
            'user_id'     => $user->id,
            'cours_id'    => $cours->id,
            'chapitre_id' => $chapitre?->id,
            'bareme'      => $questions->count(),
            'score'       => $terminee ? $nbBonnes : null,
            'commence_le' => $date,
            'termine_le'  => $terminee ? $date->copy()->addMinutes(12) : null,
        ]);

        foreach ($questions->values() as $ordre => $question) {
            $bonne = $question->options->firstWhere('est_correct', true);
            $fausse = $question->options->firstWhere('est_correct', false);

            if (!$terminee && $ordre === $questions->count() - 1) {
                $option = null;                          // tentative en cours : dernière question pas encore répondue
            } else {
                $option = $ordre < $nbBonnes ? $bonne : $fausse;   // les premières justes, les suivantes fausses
            }

            ReponseEvaluation::create([
                'tentative_evaluation_id' => $tentative->id,
                'question_id'             => $question->id,
                'option_id'               => $option?->id,
                'ordre'                   => $ordre,
            ]);
        }
    }
}