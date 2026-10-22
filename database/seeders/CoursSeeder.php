<?php
 
namespace Database\Seeders;
 
use App\Models\Cours;
use Illuminate\Database\Seeder;
 
class CoursSeeder extends Seeder
{
    /**
     * Crée des cours de test pour avoir un catalogue à afficher.
     * Doit tourner avant ClasseSeeder (les classes ont besoin d'un cours_id).
     *
     * 'programme' suit la convention "## titre" (grand point) / "- sous-point" :
     * voir Cours::getPlanSectionsAttribute() pour comment elle est interprétée.
     */
    public function run(): void
    {
        Cours::create([
            'titre' => 'Microsoft Word',
            'categorie' => 'Bureautique',
            'description' => 'Apprendre a creer et mettre en forme des documents professionnels.',
            'programme' => "## Decouverte de l'interface et des outils de base\n- Ruban, onglets et barre d'acces rapide\n- Creation, enregistrement et ouverture d'un document\n- Navigation dans un document long\n\n## Mise en forme du texte et des paragraphes\n- Polices, styles et mise en forme du texte\n- Alignement, retraits et interlignes\n- Puces, numerotation et styles de titres\n\n## Tableaux, images et mise en page\n- Insertion et mise en forme de tableaux\n- Insertion d'images et habillage du texte\n- Marges, orientation et sauts de page\n\n## Publipostage et documents longs\n- Creation d'une lettre type avec fusion et publipostage\n- Table des matieres automatique\n- En-tetes, pieds de page et numerotation\n\n## Revision, partage et impression\n- Suivi des modifications et commentaires\n- Export en PDF\n- Parametres d'impression",
            'prix_particulier' => 25000,
            'prix_entreprise' => 40000,
        ]);

        Cours::create([
            'titre' => 'Microsoft Excel',
            'categorie' => 'Bureautique',
            'description' => 'Apprendre a utiliser les tableaux, les formules et les graphiques.',
            'programme' => "## Prise en main des feuilles de calcul\n- Interface, classeurs et feuilles\n- Saisie et mise en forme des donnees\n- Copie, deplacement et references relatives/absolues\n\n## Formules et fonctions essentielles\n- Operations de base et ordre de priorite\n- Fonctions SOMME, MOYENNE, SI, RECHERCHEV\n- Gestion des erreurs courantes\n\n## Mise en forme et mise en page\n- Mise en forme conditionnelle\n- Formats de nombres et de dates\n- Impression et mise en page d'un tableau\n\n## Tableaux croises dynamiques\n- Construction d'un tableau croise dynamique\n- Filtres, segments et regroupements\n- Mise a jour des donnees sources\n\n## Graphiques et tableaux de bord\n- Choisir le bon type de graphique\n- Mise en forme d'un graphique\n- Construction d'un tableau de bord simple",
            'prix_particulier' => 25000,
            'prix_entreprise' => 40000,
        ]);

        Cours::create([
            'titre' => 'Microsoft PowerPoint',
            'categorie' => 'Bureautique',
            'description' => 'Apprendre a creer des presentations professionnelles.',
            'programme' => "## Structurer une presentation\n- Definir le message et le plan\n- Masques de diapositives et mises en page\n- Organiser les sections\n\n## Mise en forme des diapositives\n- Themes, polices et palette de couleurs\n- Alignement et hierarchie visuelle\n- Bonnes pratiques de lisibilite\n\n## Images, schemas et animations\n- Insertion d'images et de formes\n- Creation de schemas (SmartArt)\n- Animations simples et discretes\n\n## Transitions et minutage\n- Transitions entre diapositives\n- Minutage d'une presentation orale\n- Repetition et chronometrage\n\n## Presenter et exporter\n- Mode presentateur\n- Export en PDF et en video\n- Conseils de prise de parole",
            'prix_particulier' => 25000,
            'prix_entreprise' => 40000,
        ]);

        Cours::create([
            'titre' => 'Developpement Web - HTML, CSS, JavaScript',
            'categorie' => 'Developpement Web',
            'description' => 'Apprendre les bases de la creation de sites web.',
            'programme' => "## Structure HTML d'une page web\n- Balises de base et hierarchie du document\n- Liens, images et listes\n- Formulaires HTML\n\n## Mise en forme avec CSS\n- Selecteurs et proprietes de base\n- Modele de boite (box model)\n- Couleurs, polices et espacements\n\n## Mise en page responsive\n- Flexbox et Grid\n- Media queries\n- Adaptation mobile/tablette/desktop\n\n## Bases de JavaScript et interactivite\n- Variables, fonctions et conditions\n- Manipulation du DOM\n- Gestion des evenements\n\n## Projet final : creer un site complet\n- Structuration du projet\n- Integration HTML/CSS/JavaScript\n- Mise en ligne du site",
            'prix_particulier' => 150000,
            'prix_entreprise' => 250000,
        ]);

        Cours::create([
            'titre' => 'Comptabilite generale',
            'categorie' => 'Comptabilite',
            'description' => 'Decouvrir les principes essentiels de la comptabilite.',
            'programme' => "## Principes fondamentaux de la comptabilite\n- Role et objectifs de la comptabilite\n- Principes comptables de base\n- Vocabulaire comptable essentiel\n\n## Le bilan et le compte de resultat\n- Structure du bilan\n- Structure du compte de resultat\n- Lien entre les deux documents\n\n## Enregistrement des operations courantes\n- Principe de la partie double\n- Journal et grand livre\n- Exemples d'ecritures courantes\n\n## Travaux de fin d'exercice\n- Amortissements et provisions\n- Regularisations de fin d'exercice\n- Cloture d'un exercice comptable\n\n## Lecture et analyse des etats financiers\n- Principaux indicateurs financiers\n- Lecture critique d'un bilan\n- Introduction a l'analyse financiere",
            'prix_particulier' => 100000,
            'prix_entreprise' => 180000,
        ]);

        Cours::create([
            'titre' => 'Gestion de projet',
            'categorie' => 'Gestion de projet',
            'description' => 'Apprendre a organiser et piloter un projet efficacement.',
            'programme' => "## Cadrage et definition des objectifs\n- Identification des besoins et des parties prenantes\n- Definition des objectifs SMART\n- Redaction d'une note de cadrage\n\n## Planification et suivi\n- Decoupage en taches (WBS)\n- Planning et diagramme de Gantt\n- Suivi budgetaire simple\n\n## Gestion des risques\n- Identification des risques\n- Evaluation et priorisation\n- Plans de mitigation\n\n## Pilotage d'equipe et communication\n- Repartition des roles\n- Reunions de suivi efficaces\n- Communication avec les parties prenantes\n\n## Cloture et bilan de projet\n- Livraison et recette\n- Bilan de projet\n- Capitalisation des retours d'experience",
            'prix_particulier' => 130000,
            'prix_entreprise' => 220000,
        ]);

        Cours::create([
            'titre' => 'Anglais professionnel',
            'categorie' => 'Langues',
            'description' => 'Developper son anglais dans un contexte professionnel.',
            'programme' => "## Vocabulaire professionnel de base\n- Vocabulaire du monde de l'entreprise\n- Presentations personnelles et professionnelles\n- Expressions courantes au bureau\n\n## Redaction d'emails professionnels\n- Structure d'un email professionnel\n- Formules de politesse en anglais\n- Exemples et mises en situation\n\n## Prise de parole en reunion\n- Vocabulaire de reunion\n- Donner son avis et argumenter\n- Gerer les echanges en anglais\n\n## Negociation et presentation commerciale\n- Vocabulaire de la negociation\n- Structurer une presentation commerciale\n- Mises en situation pratiques\n\n## Entretien et expression orale\n- Preparation a un entretien en anglais\n- Questions frequentes et reponses types\n- Entrainement a l'expression orale",
            'prix_particulier' => 90000,
            'prix_entreprise' => 150000,
        ]);

        // Cours de test dont la classe est DEJA COMMENCEE (voir ClasseSeeder) :
        // permet de tester la carte "Cours en cours" du dashboard apres une inscription.
        Cours::create([
            'titre' => 'Initiation a l\'informatique',
            'categorie' => 'Bureautique',
            'description' => 'Decouvrir l\'ordinateur, les fichiers, internet et les outils de base.',
            'programme' => "## Decouverte de l'ordinateur et du systeme\n- Composants de base d'un ordinateur\n- Prise en main du systeme d'exploitation\n- Souris, clavier et raccourcis essentiels\n\n## Gestion des fichiers et dossiers\n- Creer, renommer, deplacer des fichiers\n- Organiser une arborescence de dossiers\n- Copier-coller et corbeille\n\n## Navigation internet et messagerie\n- Utilisation d'un navigateur web\n- Recherche d'informations en ligne\n- Creation et utilisation d'une messagerie\n\n## Bases de la bureautique\n- Decouverte de Word et Excel\n- Enregistrement et organisation des documents\n- Impression de documents",
            'prix_particulier' => 25000,
            'prix_entreprise' => 40000,
        ]);

        // Dix formations supplémentaires pour tester la pagination du catalogue.
        // updateOrCreate permet de relancer cette partie sans créer de doublons.
        $formationsCatalogue = [
            [
                'titre' => 'Gestion du temps et productivité',
                'categorie' => 'Développement personnel',
                'description' => 'Organiser son travail et mieux gérer ses priorités au quotidien.',
                'programme' => "## Organiser son activité\n- Définir ses priorités\n- Planifier sa semaine\n\n## Gagner en efficacité\n- Réduire les interruptions\n- Suivre ses objectifs",
                'prix_particulier' => 45000,
                'prix_entreprise' => 75000,
            ],
            [
                'titre' => 'Marketing numérique',
                'categorie' => 'Marketing',
                'description' => 'Construire une présence en ligne et mesurer ses résultats.',
                'programme' => "## Définir sa stratégie\n- Identifier son public\n- Choisir ses canaux\n\n## Déployer ses campagnes\n- Créer du contenu\n- Mesurer les résultats",
                'prix_particulier' => 70000,
                'prix_entreprise' => 120000,
            ],
            [
                'titre' => 'Initiation à la cybersécurité',
                'categorie' => 'Informatique',
                'description' => 'Adopter les bons réflexes pour protéger ses comptes et ses données.',
                'programme' => "## Comprendre les risques\n- Reconnaître les menaces courantes\n- Protéger ses mots de passe\n\n## Sécuriser ses usages\n- Vérifier les liens et pièces jointes\n- Sauvegarder ses données",
                'prix_particulier' => 55000,
                'prix_entreprise' => 90000,
            ],
            [
                'titre' => 'Gestion des ressources humaines',
                'categorie' => 'Ressources humaines',
                'description' => 'Découvrir les pratiques essentielles de gestion des équipes.',
                'programme' => "## Recruter et intégrer\n- Préparer un recrutement\n- Réussir l'accueil d'un collaborateur\n\n## Accompagner les équipes\n- Fixer des objectifs\n- Conduire un entretien de suivi",
                'prix_particulier' => 85000,
                'prix_entreprise' => 145000,
            ],
            [
                'titre' => 'Entrepreneuriat et création d’entreprise',
                'categorie' => 'Entrepreneuriat',
                'description' => 'Structurer une idée et préparer les premières étapes d’un projet.',
                'programme' => "## Valider son idée\n- Étudier son marché\n- Définir son offre\n\n## Lancer son activité\n- Construire un budget prévisionnel\n- Préparer un plan d'action",
                'prix_particulier' => 65000,
                'prix_entreprise' => 110000,
            ],
            [
                'titre' => 'Communication professionnelle',
                'categorie' => 'Communication',
                'description' => 'Améliorer ses échanges écrits et oraux en milieu professionnel.',
                'programme' => "## Communiquer clairement\n- Structurer un message\n- Adapter son ton à son interlocuteur\n\n## Échanger efficacement\n- Écouter et reformuler\n- Préparer une présentation courte",
                'prix_particulier' => 50000,
                'prix_entreprise' => 85000,
            ],
            [
                'titre' => 'Service client et relation commerciale',
                'categorie' => 'Commerce',
                'description' => 'Accueillir les clients, comprendre leurs besoins et suivre leurs demandes.',
                'programme' => "## Accueillir et conseiller\n- Poser les bonnes questions\n- Présenter une solution adaptée\n\n## Fidéliser ses clients\n- Traiter une réclamation\n- Assurer le suivi après-vente",
                'prix_particulier' => 60000,
                'prix_entreprise' => 100000,
            ],
            [
                'titre' => 'Analyse de données avec Excel',
                'categorie' => 'Bureautique',
                'description' => 'Préparer, analyser et présenter des données avec Excel.',
                'programme' => "## Préparer les données\n- Nettoyer un tableau\n- Utiliser les tris et filtres\n\n## Analyser et présenter\n- Créer un tableau croisé dynamique\n- Choisir un graphique adapté",
                'prix_particulier' => 75000,
                'prix_entreprise' => 125000,
            ],
            [
                'titre' => 'Comptabilité et gestion de trésorerie',
                'categorie' => 'Comptabilité',
                'description' => 'Suivre les opérations courantes et anticiper les besoins de trésorerie.',
                'programme' => "## Suivre les opérations\n- Lire les documents comptables\n- Enregistrer les mouvements courants\n\n## Piloter la trésorerie\n- Construire un plan de trésorerie\n- Repérer les écarts et agir",
                'prix_particulier' => 80000,
                'prix_entreprise' => 135000,
            ],
            [
                'titre' => 'Design graphique avec Canva',
                'categorie' => 'Communication visuelle',
                'description' => 'Créer des visuels cohérents pour des supports numériques et imprimés.',
                'programme' => "## Concevoir un visuel\n- Choisir une mise en page\n- Harmoniser couleurs et typographies\n\n## Décliner ses supports\n- Adapter un format aux réseaux sociaux\n- Exporter pour le web et l'impression",
                'prix_particulier' => 50000,
                'prix_entreprise' => 85000,
            ],
        ];

        foreach ($formationsCatalogue as $formation) {
            Cours::updateOrCreate(
                ['titre' => $formation['titre']],
                $formation
            );
        }
    }
}