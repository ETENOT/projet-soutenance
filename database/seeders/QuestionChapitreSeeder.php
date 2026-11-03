<?php

namespace Database\Seeders;

use App\Models\Chapitre;
use App\Models\Cours;
use App\Models\Option;
use App\Models\Question;
use Illuminate\Database\Seeder;

/**
 * Questions des QUIZ DE CHAPITRE (et donc du QUIZ FINAL, qui pioche dans tous les chapitres).
 *
 * Cours couverts : ceux qui ont des stagiaires de test inscrits (voir FormateurSeeder) :
 *  - Initiation à l'informatique (classe EN COURS : Alice, Brice, Carine, David) ;
 *  - Microsoft Word             (classe TERMINÉE : mêmes stagiaires) ;
 *  - Microsoft Excel            (classe À VENIR : Alice).
 * Les autres cours n'ont pas de questions de chapitre : leur parcours affiche
 * « Pas encore de quiz pour cette étape » (cas de test d'une étape vide).
 *
 * Ces questions sont aussi dans la banque du cours : le quiz du cours (existant) peut les tirer,
 * comme n'importe quelle question du cours. Les questions de QuestionSeeder, elles, restent
 * « générales » (sans chapitre) et ne servent qu'au quiz du cours.
 *
 * À lancer APRÈS ChapitreSeeder (les chapitres doivent exister).
 */
class QuestionChapitreSeeder extends Seeder
{
    public function run(): void
    {
        // Ordre des options mélangé, mais identique à chaque « migrate:fresh --seed »
        // (sinon la bonne réponse serait toujours la première)
        mt_srand(2026);

        // titre du cours => [ ordre du chapitre => [ [énoncé, bonne réponse, mauvaise 1, mauvaise 2, mauvaise 3], ... ] ]
        $questions = [
            'Initiation a l\'informatique' => [
                1 => [ // Ordinateur et système
                    ['Quel composant exécute les instructions des programmes ?', 'Le processeur', 'L’écran', 'Le clavier', 'L’imprimante'],
                    ['Lequel de ces logiciels est un système d’exploitation ?', 'Windows', 'Word', 'Chrome', 'Excel'],
                    ['Quel raccourci permet de copier un élément sélectionné ?', 'Ctrl + C', 'Ctrl + V', 'Ctrl + Z', 'Ctrl + P'],
                ],
                2 => [ // Fichiers et dossiers
                    ['À quoi sert un dossier ?', 'Ranger et regrouper des fichiers', 'Imprimer un document', 'Se connecter à Internet', 'Augmenter la mémoire'],
                    ['Quelle extension correspond le plus souvent à un document Word ?', '.docx', '.xlsx', '.mp3', '.exe'],
                    ['Que contient la corbeille ?', 'Les éléments supprimés, encore récupérables', 'Les fichiers téléchargés', 'Les programmes installés', 'Les mots de passe'],
                ],
                3 => [ // Internet et messagerie
                    ['Quel logiciel sert à consulter des pages web ?', 'Un navigateur', 'Un tableur', 'Un traitement de texte', 'Un antivirus'],
                    ['Dans une adresse e-mail, que sépare le symbole @ ?', 'Le nom d’utilisateur et le domaine', 'Deux destinataires', 'L’objet et le message', 'Le prénom et le nom'],
                    ['Quel champ permet d’envoyer une copie cachée d’un e-mail ?', 'Cci', 'Cc', 'Objet', 'De'],
                ],
                4 => [ // Premiers outils bureautiques
                    ['Quel logiciel est un tableur ?', 'Excel', 'Word', 'PowerPoint', 'Paint'],
                    ['Quel raccourci enregistre le document ouvert ?', 'Ctrl + S', 'Ctrl + O', 'Ctrl + N', 'Ctrl + A'],
                    ['Quel format permet de partager un document sans qu’il soit modifié facilement ?', 'PDF', 'TXT', 'DOCX', 'CSV'],
                ],
            ],

            'Microsoft Word' => [
                1 => [ // Découverte de Word
                    ['Comment s’appelle la bande d’onglets en haut de la fenêtre de Word ?', 'Le ruban', 'La barre d’état', 'Le volet de navigation', 'La règle'],
                    ['Quel onglet contient les commandes de police et de paragraphe ?', 'Accueil', 'Insertion', 'Révision', 'Affichage'],
                    ['Quel raccourci annule la dernière action ?', 'Ctrl + Z', 'Ctrl + Y', 'Ctrl + X', 'Ctrl + B'],
                ],
                2 => [ // Mise en forme
                    ['Quel raccourci met le texte sélectionné en gras ?', 'Ctrl + G', 'Ctrl + I', 'Ctrl + U', 'Ctrl + E'],
                    ['Quel outil applique d’un seul clic une mise en forme cohérente aux titres ?', 'Les styles', 'Le correcteur', 'Le zoom', 'Le publipostage'],
                    ['Quel réglage modifie l’espace entre les lignes d’un paragraphe ?', 'L’interligne', 'La marge', 'Le retrait', 'L’orientation'],
                ],
                3 => [ // Tableaux et mise en page
                    ['Dans quel onglet insère-t-on un tableau ?', 'Insertion', 'Accueil', 'Références', 'Publipostage'],
                    ['Quelle orientation choisir pour une page plus large que haute ?', 'Paysage', 'Portrait', 'Colonne', 'Recto'],
                    ['Quel élément force le texte suivant à commencer sur une nouvelle page ?', 'Un saut de page', 'Un retour à la ligne', 'Une tabulation', 'Un espace insécable'],
                ],
                4 => [ // Documents avancés
                    ['Quelle fonction crée des lettres personnalisées à partir d’une liste de destinataires ?', 'Le publipostage', 'Le suivi des modifications', 'La table des matières', 'Le mode lecture'],
                    ['Sur quoi s’appuie une table des matières automatique ?', 'Les styles de titre', 'Les notes de bas de page', 'Les commentaires', 'Les en-têtes'],
                    ['Où place-t-on le numéro de page pour qu’il apparaisse sur chaque page ?', 'Dans l’en-tête ou le pied de page', 'Dans le corps du texte', 'Dans un commentaire', 'Dans le titre'],
                ],
                5 => [ // Révision et impression
                    ['Quelle fonction garde une trace de toutes les modifications d’un document ?', 'Le suivi des modifications', 'La mise en forme automatique', 'Le zoom', 'Le mode plan'],
                    ['Comment obtenir un PDF à partir d’un document Word ?', 'Fichier > Exporter (ou Enregistrer sous) en PDF', 'Insertion > Image', 'Accueil > Coller', 'Affichage > Règle'],
                    ['Quel raccourci ouvre la fenêtre d’impression ?', 'Ctrl + P', 'Ctrl + I', 'Ctrl + F', 'Ctrl + H'],
                ],
            ],

            'Microsoft Excel' => [
                1 => [ // Prise en main des feuilles de calcul
                    ['Comment s’appelle l’intersection d’une ligne et d’une colonne ?', 'Une cellule', 'Une feuille', 'Un classeur', 'Une plage'],
                    ['Quelle est l’adresse de la cellule située colonne B, ligne 3 ?', 'B3', '3B', 'B:3', 'C2'],
                    ['Comment appelle-t-on un fichier Excel contenant plusieurs feuilles ?', 'Un classeur', 'Un tableau', 'Un dossier', 'Un modèle'],
                ],
                2 => [ // Formules et fonctions
                    ['Par quel symbole commence une formule Excel ?', '=', '+', '#', '@'],
                    ['Quelle fonction calcule la moyenne d’une plage de cellules ?', 'MOYENNE', 'SOMME', 'NB', 'MAX'],
                    ['Dans $A$1, à quoi servent les symboles $ ?', 'Figer la référence lors de la recopie', 'Indiquer une valeur en dollars', 'Masquer la cellule', 'Protéger la feuille'],
                ],
                3 => [ // Mise en forme et impression
                    ['Quel outil colore automatiquement les cellules selon leur valeur ?', 'La mise en forme conditionnelle', 'Le filtre', 'La validation des données', 'Le tri'],
                    ['Quel format affiche 0,25 sous la forme 25 % ?', 'Pourcentage', 'Monétaire', 'Date', 'Texte'],
                    ['Comment imprimer seulement une partie de la feuille ?', 'En définissant une zone d’impression', 'En masquant la feuille', 'En changeant de police', 'En figeant les volets'],
                ],
                4 => [ // Tableaux croisés dynamiques
                    ['À quoi sert un tableau croisé dynamique ?', 'Résumer et analyser rapidement un grand tableau de données', 'Dessiner un graphique', 'Vérifier l’orthographe', 'Protéger un classeur'],
                    ['Que faut-il faire après avoir modifié les données source d’un TCD ?', 'Actualiser le TCD', 'Supprimer le TCD', 'Enregistrer en PDF', 'Fermer le classeur'],
                    ['Dans quelle zone d’un TCD place-t-on les montants à additionner ?', 'Valeurs', 'Filtres', 'Lignes', 'Colonnes'],
                ],
                5 => [ // Graphiques et tableaux de bord
                    ['Quel graphique montre le mieux une évolution dans le temps ?', 'Une courbe', 'Un secteur', 'Un nuage de mots', 'Une jauge'],
                    ['Quel graphique montre le mieux la part de chaque élément dans un total ?', 'Un secteur (camembert)', 'Une courbe', 'Un histogramme groupé', 'Un nuage de points'],
                    ['Quel élément d’un tableau de bord permet de filtrer plusieurs TCD d’un clic ?', 'Un segment', 'Une zone de texte', 'Un commentaire', 'Un lien hypertexte'],
                ],
            ],
        ];

        // « Noté sur » de démonstration (les autres cours gardent NULL = toutes les questions)
        $reglages = [
            // Excel : 2 questions tirées au hasard sur 3 par chapitre, et un quiz final noté sur 8 (sur 15)
            'Microsoft Excel' => ['chapitre' => 2, 'final' => 8],
            // Word : chapitres complets (3 questions), quiz final noté sur 10 (sur 15)
            'Microsoft Word'  => ['chapitre' => null, 'final' => 10],
        ];

        foreach ($questions as $titreCours => $parChapitre) {
            $cours = Cours::where('titre', $titreCours)->firstOrFail();

            foreach ($parChapitre as $ordre => $liste) {
                // Chapitre créé par ChapitreSeeder, retrouvé par son numéro d'ordre
                $chapitre = Chapitre::where('cours_id', $cours->id)->where('ordre', $ordre)->firstOrFail();

                foreach ($liste as [$enonce, $bonne, $faux1, $faux2, $faux3]) {
                    $question = Question::create([
                        'enonce'      => $enonce,
                        'cours_id'    => $cours->id,
                        'chapitre_id' => $chapitre->id,   // => quiz du chapitre + quiz final
                    ]);

                    // 4 options, une seule bonne, dans un ordre mélangé
                    $options = [
                        ['libelle' => $bonne, 'est_correct' => true],
                        ['libelle' => $faux1, 'est_correct' => false],
                        ['libelle' => $faux2, 'est_correct' => false],
                        ['libelle' => $faux3, 'est_correct' => false],
                    ];
                    shuffle($options);

                    foreach ($options as $option) {
                        Option::create($option + ['question_id' => $question->id]);
                    }
                }

                // « Noté sur » du quiz de ce chapitre
                if (isset($reglages[$titreCours])) {
                    $chapitre->update(['quiz_note_sur' => $reglages[$titreCours]['chapitre']]);
                }
            }

            // « Noté sur » du quiz final (cours.note_sur, celui du quiz du cours, n'est pas touché)
            if (isset($reglages[$titreCours])) {
                $cours->update(['note_sur_final' => $reglages[$titreCours]['final']]);
            }
        }
    }
}