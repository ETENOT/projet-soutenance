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
    }
}