<?php
 
namespace Database\Seeders;
 
use App\Models\Cours;
use Illuminate\Database\Seeder;
 
class CoursSeeder extends Seeder
{
    /**
     * Crée des cours de test pour avoir un catalogue à afficher.
     * Doit tourner avant ClasseSeeder (les classes ont besoin d'un cours_id).
     */
    public function run(): void
    {
        Cours::create([
            'titre' => 'Microsoft Word',
            'categorie' => 'Bureautique',
            'description' => 'Apprendre a creer et mettre en forme des documents professionnels.',
            'programme' => "Module 1 - Decouverte de l'interface et des outils de base\nModule 2 - Mise en forme du texte et des paragraphes\nModule 3 - Tableaux, images et mise en page\nModule 4 - Publipostage et documents longs\nModule 5 - Revision, partage et impression",
            'prix_particulier' => 25000,
            'prix_entreprise' => 40000,
        ]);

        Cours::create([
            'titre' => 'Microsoft Excel',
            'categorie' => 'Bureautique',
            'description' => 'Apprendre a utiliser les tableaux, les formules et les graphiques.',
            'programme' => "Module 1 - Prise en main des feuilles de calcul\nModule 2 - Formules et fonctions essentielles\nModule 3 - Mise en forme et mise en page\nModule 4 - Tableaux croises dynamiques\nModule 5 - Graphiques et tableaux de bord",
            'prix_particulier' => 25000,
            'prix_entreprise' => 40000,
        ]);

        Cours::create([
            'titre' => 'Microsoft PowerPoint',
            'categorie' => 'Bureautique',
            'description' => 'Apprendre a creer des presentations professionnelles.',
            'programme' => "Module 1 - Structurer une presentation\nModule 2 - Mise en forme des diapositives\nModule 3 - Images, schemas et animations\nModule 4 - Transitions et minutage\nModule 5 - Presenter et exporter",
            'prix_particulier' => 25000,
            'prix_entreprise' => 40000,
        ]);

        Cours::create([
            'titre' => 'Developpement Web - HTML, CSS, JavaScript',
            'categorie' => 'Developpement Web',
            'description' => 'Apprendre les bases de la creation de sites web.',
            'programme' => "Module 1 - Structure HTML d'une page web\nModule 2 - Mise en forme avec CSS\nModule 3 - Mise en page responsive\nModule 4 - Bases de JavaScript et interactivite\nModule 5 - Projet final : creer un site complet",
            'prix_particulier' => 150000,
            'prix_entreprise' => 250000,
        ]);

        Cours::create([
            'titre' => 'Comptabilite generale',
            'categorie' => 'Comptabilite',
            'description' => 'Decouvrir les principes essentiels de la comptabilite.',
            'programme' => "Module 1 - Principes fondamentaux de la comptabilite\nModule 2 - Le bilan et le compte de resultat\nModule 3 - Enregistrement des operations courantes\nModule 4 - Travaux de fin d'exercice\nModule 5 - Lecture et analyse des etats financiers",
            'prix_particulier' => 100000,
            'prix_entreprise' => 180000,
        ]);

        Cours::create([
            'titre' => 'Gestion de projet',
            'categorie' => 'Gestion de projet',
            'description' => 'Apprendre a organiser et piloter un projet efficacement.',
            'programme' => "Module 1 - Cadrage et definition des objectifs\nModule 2 - Planification et suivi (planning, budget)\nModule 3 - Gestion des risques\nModule 4 - Pilotage d'equipe et communication\nModule 5 - Cloture et bilan de projet",
            'prix_particulier' => 130000,
            'prix_entreprise' => 220000,
        ]);

        Cours::create([
            'titre' => 'Anglais professionnel',
            'categorie' => 'Langues',
            'description' => 'Developper son anglais dans un contexte professionnel.',
            'programme' => "Module 1 - Vocabulaire professionnel de base\nModule 2 - Redaction d'emails professionnels\nModule 3 - Prise de parole en reunion\nModule 4 - Negociation et presentation commerciale\nModule 5 - Entretien et expression orale",
            'prix_particulier' => 90000,
            'prix_entreprise' => 150000,
        ]);

        // Cours de test dont la classe est DEJA COMMENCEE (voir ClasseSeeder) :
        // permet de tester la carte "Cours en cours" du dashboard apres une inscription.
        Cours::create([
            'titre' => 'Initiation a l\'informatique',
            'categorie' => 'Bureautique',
            'description' => 'Decouvrir l\'ordinateur, les fichiers, internet et les outils de base.',
            'programme' => "Module 1 - Decouverte de l'ordinateur et du systeme\nModule 2 - Gestion des fichiers et dossiers\nModule 3 - Navigation internet et messagerie\nModule 4 - Bases de la bureautique",
            'prix_particulier' => 25000,
            'prix_entreprise' => 40000,
        ]);
    }
}