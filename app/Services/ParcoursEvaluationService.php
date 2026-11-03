<?php

namespace App\Services;

use App\Models\Chapitre;
use App\Models\Cours;
use App\Models\Notification;
use App\Models\Question;
use App\Models\ReponseEvaluation;
use App\Models\TentativeEvaluation;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Parcours d'évaluation d'un cours, façon Cisco :
 *   quiz du chapitre 1 → quiz du chapitre 2 → … → quiz final.
 *
 * Règles :
 *  - réservé aux particuliers INSCRITS à une classe du cours (sinon tout est verrouillé : cadenas) ;
 *    les visiteurs anonymes n'y ont jamais accès ;
 *  - le quiz du chapitre N se débloque quand le quiz du chapitre précédent a été TERMINÉ au moins
 *    une fois (quelle que soit la note) ; le quiz final se débloque quand tous les quiz de chapitre
 *    ont été terminés ;
 *  - une fois débloqué, un quiz peut être repassé autant de fois qu'on veut ;
 *  - un chapitre sans question n'a pas de quiz : il ne bloque pas la suite ;
 *  - quiz de chapitre : questions du chapitre ; quiz final : questions de TOUS les chapitres ;
 *  - « noté sur » = nombre de questions tirées au hasard : chapitres.quiz_note_sur pour un chapitre,
 *    cours.note_sur_final pour le final (NULL = toutes). 1 point par bonne réponse.
 *
 * Le quiz du cours existant (QuizAttemptController / QuizFinalisationService) n'est pas concerné.
 */
class ParcoursEvaluationService
{
    // États possibles d'une étape du parcours
    public const VERROUILLE = 'verrouille';   // cadenas
    public const DISPONIBLE = 'disponible';   // débloqué, jamais terminé
    public const FAIT       = 'fait';         // débloqué et terminé au moins une fois (repassable)
    public const VIDE       = 'vide';         // aucune question : pas de quiz pour cette étape

    /**
     * Seul un particulier inscrit à au moins une classe de ce cours a accès au parcours.
     * L'inscription suffit (qu'elle soit personnelle ou faite par son entreprise, payée ou non).
     * Pour exiger le paiement, ajouter ->payees() avant ->exists().
     */
    public function estInscrit(?User $user, Cours $cours): bool
    {
        if (!$user || $user->role?->nom !== 'particulier') {
            return false;
        }

        return $user->inscriptions()
            ->whereHas('classe', fn ($q) => $q->where('cours_id', $cours->id))
            ->exists();
    }

    /**
     * Les étapes du parcours, dans l'ordre (chapitres par « ordre », puis quiz final),
     * avec leur état pour l'utilisateur donné (null = visiteur : tout est verrouillé).
     *
     * Chaque étape est un tableau :
     *  type ('chapitre'|'final'), chapitre (Chapitre|null), titre, nb_questions (taille de la banque),
     *  nb_posees (questions par tentative), etat, raison (texte du cadenas),
     *  en_cours (tentative ouverte|null), nb_tentatives, derniere, meilleure (tentatives terminées|null).
     */
    public function etapes(Cours $cours, ?User $user): Collection
    {
        $inscrit = $this->estInscrit($user, $cours);

        // Chapitres du cours avec le nombre de questions qui leur sont rattachées
        $chapitres = $cours->chapitres()
            ->withCount(['questions' => fn ($q) => $q->where('cours_id', $cours->id)])
            ->get()
            ->sortBy([['ordre', 'asc'], ['id', 'asc']])
            ->values();

        // Toutes les tentatives du particulier sur ce cours, en UNE requête, rangées par étape
        // (clé '' = quiz final, car chapitre_id est NULL)
        $tentatives = $inscrit
            ? TentativeEvaluation::where('user_id', $user->id)
                ->where('cours_id', $cours->id)
                ->orderBy('commence_le')->orderBy('id')
                ->get()
                ->groupBy(fn ($t) => (string) $t->chapitre_id)
            : collect();

        $raisonNonInscrit = $user
            ? 'Inscrivez-vous à une classe de ce cours pour débloquer les quiz des chapitres.'
            : 'Connectez-vous avec un compte particulier inscrit à une classe de ce cours.';

        $etapes = collect();
        $precedentFait = true;     // le premier quiz est débloqué d'office
        $dernierBloquant = null;   // titre du dernier chapitre dont le quiz n'est pas encore terminé

        foreach ($chapitres as $chapitre) {
            $etape = $this->etape(
                'chapitre',
                $chapitre,
                'Chapitre ' . $chapitre->ordre . ' — ' . $chapitre->titre,
                $chapitre->questions_count,
                $chapitre->quiz_note_sur,
                $tentatives->get((string) $chapitre->id, collect())
            );

            if ($etape['nb_questions'] === 0) {
                // Pas de question = pas de quiz : l'étape est sautée et ne bloque pas la suite
                $etape['etat'] = self::VIDE;
            } elseif (!$inscrit) {
                $etape['etat'] = self::VERROUILLE;
                $etape['raison'] = $raisonNonInscrit;
            } elseif (!$precedentFait) {
                $etape['etat'] = self::VERROUILLE;
                $etape['raison'] = 'Terminez d\'abord le quiz du « ' . $dernierBloquant . ' ».';
            } else {
                $etape['etat'] = $etape['nb_tentatives'] > 0 ? self::FAIT : self::DISPONIBLE;
            }

            // La chaîne continue seulement si ce quiz a été terminé au moins une fois
            if ($etape['nb_questions'] > 0 && $etape['nb_tentatives'] === 0) {
                $precedentFait = false;
                $dernierBloquant ??= $etape['titre'];
            }

            $etapes->push($etape);
        }

        // ----- Quiz final : questions de tous les chapitres -----
        $final = $this->etape(
            'final',
            null,
            'Quiz final',
            $chapitres->sum('questions_count'),
            $cours->note_sur_final,
            $tentatives->get('', collect())
        );

        if ($final['nb_questions'] === 0) {
            $final['etat'] = self::VIDE;
        } elseif (!$inscrit) {
            $final['etat'] = self::VERROUILLE;
            $final['raison'] = $raisonNonInscrit;
        } elseif (!$precedentFait) {
            $final['etat'] = self::VERROUILLE;
            $final['raison'] = 'Terminez d\'abord les quiz de tous les chapitres.';
        } else {
            $final['etat'] = $final['nb_tentatives'] > 0 ? self::FAIT : self::DISPONIBLE;
        }

        return $etapes->push($final);
    }

    /**
     * L'étape d'un chapitre précis (ou du quiz final si $chapitre est null), avec son état.
     */
    public function etapePour(Cours $cours, ?User $user, ?Chapitre $chapitre): ?array
    {
        return $this->etapes($cours, $user)->first(
            fn ($e) => $chapitre ? (int) $e['chapitre']?->id === (int) $chapitre->id : $e['type'] === 'final'
        );
    }

    // true si le quiz de cette étape peut être passé (débloqué et contient des questions)
    public function estAccessible(?array $etape): bool
    {
        return $etape !== null && in_array($etape['etat'], [self::DISPONIBLE, self::FAIT], true);
    }

    /**
     * Reprend la tentative en cours de cette étape, ou en démarre une nouvelle.
     * À n'appeler qu'après avoir vérifié estAccessible().
     */
    public function reprendreOuDemarrer(User $user, Cours $cours, ?Chapitre $chapitre): TentativeEvaluation
    {
        return DB::transaction(function () use ($user, $cours, $chapitre) {
            // Verrou sur la ligne de l'utilisateur : deux clics rapides ne créent pas deux tentatives
            User::whereKey($user->id)->lockForUpdate()->first();

            $enCours = TentativeEvaluation::where('user_id', $user->id)
                ->where('cours_id', $cours->id)
                ->pourEtape($chapitre?->id)
                ->enCours()
                ->latest('id')
                ->first();

            if ($enCours) {
                return $enCours;
            }

            // Tirage au hasard, chaque question une seule fois (une ligne "questions" = une question)
            if ($chapitre) {
                // Quiz du chapitre : questions rattachées à CE chapitre
                $tirage = Question::where('cours_id', $cours->id)->where('chapitre_id', $chapitre->id);
                $noteSur = $chapitre->quiz_note_sur;
            } else {
                // Quiz final : questions rattachées à N'IMPORTE QUEL chapitre du cours
                $tirage = Question::where('cours_id', $cours->id)
                    ->whereIn('chapitre_id', Chapitre::where('cours_id', $cours->id)->select('id'));
                $noteSur = $cours->note_sur_final;
            }

            $tirage->inRandomOrder();
            if ($noteSur) {
                // Banque plus petite que le "noté sur" : on prend tout, le barème suit
                $tirage->limit($noteSur);
            }
            $questionIds = $tirage->pluck('id');

            $tentative = TentativeEvaluation::create([
                'user_id'     => $user->id,
                'cours_id'    => $cours->id,
                'chapitre_id' => $chapitre?->id,
                // 1 point par question : le barème est le nombre de questions tirées
                'bareme'      => $questionIds->count(),
                'commence_le' => now(),
            ]);

            // Une ligne par question tirée (option encore vide) ; "ordre" fige l'ordre d'affichage
            foreach ($questionIds as $ordre => $questionId) {
                ReponseEvaluation::create([
                    'tentative_evaluation_id' => $tentative->id,
                    'question_id'             => $questionId,
                    'ordre'                   => $ordre,
                ]);
            }

            return $tentative;
        });
    }

    /**
     * Calcule la note (1 point par bonne réponse, 0 pour une réponse fausse ou absente)
     * et clôture la tentative. Sans effet si elle est déjà terminée (double clic).
     */
    public function terminer(TentativeEvaluation $tentative): TentativeEvaluation
    {
        return DB::transaction(function () use ($tentative) {
            // Verrou + relecture : si une autre requête l'a déjà terminée, on ne recalcule rien
            $tentative = TentativeEvaluation::whereKey($tentative->id)->lockForUpdate()->firstOrFail();
            if ($tentative->estTerminee()) {
                return $tentative;
            }

            $score = $tentative->reponses()->with('question.options')->get()
                ->filter(fn (ReponseEvaluation $r) => $r->estCorrecte())
                ->count();

            $tentative->update(['score' => $score, 'termine_le' => now()]);
            $tentative->load('cours', 'chapitre');

            // Notification dans l'application, comme pour le quiz du cours
            Notification::create([
                'user_id' => $tentative->user_id,
                'message' => $tentative->titre() . ' (« ' . $tentative->cours->titre . ' ») terminé. Votre note : '
                    . $score . ' / ' . $tentative->bareme . '.',
            ]);

            return $tentative;
        });
    }

    // Construit le tableau d'une étape à partir de ses tentatives (état calculé ensuite)
    private function etape(string $type, ?Chapitre $chapitre, string $titre, int $nbQuestions, ?int $noteSur, Collection $tentatives): array
    {
        $terminees = $tentatives->filter->estTerminee()->values();

        return [
            'type'          => $type,
            'chapitre'      => $chapitre,
            'titre'         => $titre,
            'nb_questions'  => $nbQuestions,
            // Questions posées à chaque tentative : le "noté sur", plafonné à la taille de la banque
            'nb_posees'     => $noteSur ? min($noteSur, $nbQuestions) : $nbQuestions,
            'etat'          => self::VERROUILLE,
            'raison'        => null,
            'en_cours'      => $tentatives->reject->estTerminee()->last(),
            'nb_tentatives' => $terminees->count(),
            'derniere'      => $terminees->last(),
            // Meilleure note en pourcentage (le barème peut varier d'une tentative à l'autre)
            'meilleure'     => $terminees->sortByDesc(fn ($t) => $t->pourcentage())->first(),
        ];
    }
}