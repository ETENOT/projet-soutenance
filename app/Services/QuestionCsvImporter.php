<?php

namespace App\Services;

use App\Models\Cours;
use App\Models\Option;
use App\Models\Question;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// Importe des questions (QCM) dans la banque de questions à partir d'un fichier CSV.
//
// Format attendu (1ère ligne = en-têtes, séparateur ; , ou tabulation détecté automatiquement) :
//   cours ; question ; option_1 ; option_2 ; ... (jusqu'à option_6) ; bonne_reponse
//
// - "cours" est optionnel si un cours par défaut est choisi dans le formulaire
//   (titre exact du cours, ou son id)
// - "bonne_reponse" = numéro de l'option (1, 2, ...), sa lettre (A, B, ...) ou son texte exact
//
// Principe "tout ou rien" : si UNE seule ligne est invalide, rien n'est enregistré
// et on renvoie la liste des erreurs avec leur numéro de ligne.
class QuestionCsvImporter
{
    public const MIN_OPTIONS = 2;
    public const MAX_OPTIONS = 6;

    /**
     * @return array{crees:int, ignores:int, erreurs:array<int,string>}
     */
    public function importer(string $chemin, ?int $coursParDefautId = null): array
    {
        $analyse = $this->analyser(
            $this->lireLignes($chemin),
            $this->indexCours(),
            $coursParDefautId,
            $this->questionsExistantes()
        );

        if ($analyse['erreurs']) {
            return ['crees' => 0, 'ignores' => $analyse['ignores'], 'erreurs' => $analyse['erreurs']];
        }

        // Transaction : si une insertion échoue, tout est annulé (pas d'import à moitié fait)
        DB::transaction(function () use ($analyse) {
            $maintenant = now();
            foreach ($analyse['questions'] as $q) {
                $question = Question::create(['enonce' => $q['enonce'], 'cours_id' => $q['cours_id']]);

                // insert() en un seul appel par question : insert() ne remplit pas
                // created_at/updated_at tout seul, d'où les deux dates ci-dessous
                Option::insert(array_map(fn ($o) => [
                    'libelle'     => $o['libelle'],
                    'est_correct' => $o['est_correct'],
                    'question_id' => $question->id,
                    'created_at'  => $maintenant,
                    'updated_at'  => $maintenant,
                ], $q['options']));
            }
        });

        return ['crees' => count($analyse['questions']), 'ignores' => $analyse['ignores'], 'erreurs' => []];
    }

    /**
     * Partie "pure" (sans base de données) : transforme les lignes lues en questions valides
     * et en erreurs. Séparée de importer() pour pouvoir être testée facilement.
     *
     * @param array<int,array<int,?string>> $lignes       lignes du CSV (la 1ère = en-têtes)
     * @param array<string,int>             $indexCours   titre normalisé => id, et id => id
     * @param array<string,bool>            $existantes   clés "coursId|énoncé normalisé" déjà en base
     */
    public function analyser(array $lignes, array $indexCours, ?int $coursParDefautId, array $existantes = []): array
    {
        $resultat = ['questions' => [], 'ignores' => 0, 'erreurs' => []];

        // On ignore les lignes totalement vides (ex: lignes vides en fin de fichier Excel)
        $lignes = array_filter($lignes, fn ($l) => collect($l)->contains(fn ($c) => trim((string) $c) !== ''));
        if (!$lignes) {
            $resultat['erreurs'][] = 'Le fichier est vide.';
            return $resultat;
        }

        // On retire l'en-tête SANS réindexer (array_shift renumérote) pour garder les vrais numéros de ligne
        $numeroEntete = array_key_first($lignes);
        $colonnes = $this->lireEntetes($lignes[$numeroEntete]);
        unset($lignes[$numeroEntete]);

        if (!isset($colonnes['question'])) {
            $resultat['erreurs'][] = 'Colonne « question » introuvable dans la première ligne.';
        }
        if (!isset($colonnes['bonne'])) {
            $resultat['erreurs'][] = 'Colonne « bonne_reponse » introuvable dans la première ligne.';
        }
        if (count($colonnes['options'] ?? []) < self::MIN_OPTIONS) {
            $resultat['erreurs'][] = 'Il faut au moins les colonnes « option_1 » et « option_2 ».';
        }
        if ($resultat['erreurs']) {
            return $resultat;
        }

        $vusDansLeFichier = [];

        foreach ($lignes as $numero => $ligne) {
            // $numero commence à 0 pour la 1ère ligne du fichier -> on affiche +1 comme dans Excel
            $ligneAffichee = $numero + 1;
            // Fermeture classique avec "&" : une fn fléchée copierait $resultat et perdrait les erreurs
            $erreur = function (string $m) use (&$resultat, $ligneAffichee) {
                $resultat['erreurs'][] = "Ligne {$ligneAffichee} : {$m}";
            };

            // --- Cours ---
            $coursId = $coursParDefautId;
            $texteCours = isset($colonnes['cours']) ? trim((string) ($ligne[$colonnes['cours']] ?? '')) : '';
            if ($texteCours !== '') {
                $coursId = $indexCours[$this->cle($texteCours)] ?? null;
                if (!$coursId) {
                    $erreur("cours « {$texteCours} » introuvable.");
                    continue;
                }
            } elseif (!$coursId) {
                $erreur('aucun cours indiqué (colonne « cours » vide et pas de cours par défaut choisi).');
                continue;
            }

            // --- Énoncé ---
            $enonce = trim((string) ($ligne[$colonnes['question']] ?? ''));
            if ($enonce === '') {
                $erreur('la question est vide.');
                continue;
            }
            if (mb_strlen($enonce) > 2000) {
                $erreur('la question dépasse 2000 caractères.');
                continue;
            }

            // --- Options : on garde la position de colonne (option_3 vide reste "la 3e") ---
            $options = [];
            foreach ($colonnes['options'] as $position => $indexColonne) {
                $texte = trim((string) ($ligne[$indexColonne] ?? ''));
                if ($texte !== '') {
                    $options[$position] = $texte;
                }
            }
            if (count($options) < self::MIN_OPTIONS) {
                $erreur('il faut au moins ' . self::MIN_OPTIONS . ' options renseignées.');
                continue;
            }
            if (count(array_unique(array_map(fn ($o) => $this->normaliserTexte($o), $options))) !== count($options)) {
                $erreur('deux options ont le même texte.');
                continue;
            }
            if (collect($options)->contains(fn ($o) => mb_strlen($o) > 255)) {
                $erreur('une option dépasse 255 caractères.');
                continue;
            }

            // --- Bonne réponse ---
            $positionBonne = $this->trouverBonneReponse(trim((string) ($ligne[$colonnes['bonne']] ?? '')), $options);
            if ($positionBonne === null) {
                $erreur('« bonne_reponse » ne correspond à aucune option renseignée (utilisez 1, 2, ... ou A, B, ... ou le texte exact).');
                continue;
            }

            // --- Doublons (déjà en base ou déjà vu plus haut dans le fichier) ---
            $cleDoublon = $coursId . '|' . $this->normaliserTexte($enonce);
            if (isset($existantes[$cleDoublon]) || isset($vusDansLeFichier[$cleDoublon])) {
                $resultat['ignores']++;
                continue;
            }
            $vusDansLeFichier[$cleDoublon] = true;

            $resultat['questions'][] = [
                'cours_id' => $coursId,
                'enonce'   => $enonce,
                'options'  => collect($options)->map(fn ($libelle, $pos) => [
                    'libelle'     => $libelle,
                    'est_correct' => $pos === $positionBonne,
                ])->values()->all(),
            ];
        }

        return $resultat;
    }

    // Renvoie la position (1-6) de la bonne option, ou null si la valeur est invalide
    private function trouverBonneReponse(string $valeur, array $options): ?int
    {
        if ($valeur === '') {
            return null;
        }

        // 1) Numéro : "2"
        if (ctype_digit($valeur) && isset($options[(int) $valeur])) {
            return (int) $valeur;
        }

        // 2) Lettre : "B"
        if (preg_match('/^[a-f]$/i', $valeur)) {
            $position = ord(strtoupper($valeur)) - ord('A') + 1;
            if (isset($options[$position])) {
                return $position;
            }
        }

        // 3) Texte exact de l'option
        foreach ($options as $position => $texte) {
            if ($this->normaliserTexte($texte) === $this->normaliserTexte($valeur)) {
                return $position;
            }
        }

        return null;
    }

    // Associe chaque en-tête reconnu à son numéro de colonne
    private function lireEntetes(array $entetes): array
    {
        $colonnes = ['options' => []];

        foreach ($entetes as $index => $entete) {
            $nom = $this->cle((string) $entete);

            if ($nom === 'cours') {
                $colonnes['cours'] = $index;
            } elseif (in_array($nom, ['question', 'enonce'], true)) {
                $colonnes['question'] = $index;
            } elseif (in_array($nom, ['bonne_reponse', 'reponse_correcte', 'correcte', 'bonne_option'], true)) {
                $colonnes['bonne'] = $index;
            } elseif (preg_match('/^(?:option|reponse|choix)_([1-6])$/', $nom, $m)) {
                $colonnes['options'][(int) $m[1]] = $index;
            }
        }

        ksort($colonnes['options']);

        return $colonnes;
    }

    // Lit le fichier : gère le BOM, l'encodage (UTF-8 ou Windows-1252 d'Excel) et le séparateur
    private function lireLignes(string $chemin): array
    {
        $contenu = (string) file_get_contents($chemin);
        $contenu = preg_replace('/^\xEF\xBB\xBF/', '', $contenu);

        if (!mb_check_encoding($contenu, 'UTF-8')) {
            $contenu = mb_convert_encoding($contenu, 'UTF-8', 'Windows-1252');
        }

        // Séparateur = celui qui apparaît le plus dans la première ligne
        $premiere = strtok($contenu, "\r\n") ?: '';
        $separateurs = [';' => substr_count($premiere, ';'), ',' => substr_count($premiere, ','), "\t" => substr_count($premiere, "\t")];
        arsort($separateurs);
        $separateur = array_key_first($separateurs);

        // fgetcsv sur un flux mémoire : gère les champs entre guillemets sur plusieurs lignes.
        // Le dernier paramètre ('') désactive le caractère d'échappement (obsolète en PHP 8.4+)
        $flux = fopen('php://temp', 'r+');
        fwrite($flux, $contenu);
        rewind($flux);

        $lignes = [];
        while (($ligne = fgetcsv($flux, 0, $separateur, '"', '')) !== false) {
            $lignes[] = $ligne;
        }
        fclose($flux);

        return $lignes;
    }

    // titre normalisé (sans accents, minuscules) => id du cours ; accepte aussi l'id directement
    private function indexCours(): array
    {
        $index = [];
        foreach (Cours::select('id', 'titre')->get() as $cours) {
            $index[$this->cle($cours->titre)] ??= $cours->id;
            $index[(string) $cours->id] ??= $cours->id;
        }
        return $index;
    }

    // Questions déjà en base, pour détecter les doublons
    private function questionsExistantes(): array
    {
        $existantes = [];
        foreach (Question::select('cours_id', 'enonce')->cursor() as $q) {
            $existantes[$q->cours_id . '|' . $this->normaliserTexte($q->enonce)] = true;
        }
        return $existantes;
    }

    // "Éléments de Base" -> "elements_de_base" (pour comparer en-têtes et titres de cours)
    private function cle(string $texte): string
    {
        return trim(preg_replace('/[^a-z0-9]+/', '_', strtolower(Str::ascii($texte))), '_');
    }

    // Minuscules + espaces multiples réduits (pour détecter les doublons d'énoncés)
    private function normaliserTexte(string $texte): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $texte)));
    }
}