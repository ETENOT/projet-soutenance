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
            'prix_particulier' => 25000,
            'prix_entreprise' => 40000,
        ]);

        Cours::create([
            'titre' => 'Microsoft Excel',
            'categorie' => 'Bureautique',
            'description' => 'Apprendre a utiliser les tableaux, les formules et les graphiques.',
            'prix_particulier' => 25000,
            'prix_entreprise' => 40000,
        ]);

        Cours::create([
            'titre' => 'Microsoft PowerPoint',
            'categorie' => 'Bureautique',
            'description' => 'Apprendre a creer des presentations professionnelles.',
            'prix_particulier' => 25000,
            'prix_entreprise' => 40000,
        ]);

        Cours::create([
            'titre' => 'Developpement Web - HTML, CSS, JavaScript',
            'categorie' => 'Developpement Web',
            'description' => 'Apprendre les bases de la creation de sites web.',
            'prix_particulier' => 150000,
            'prix_entreprise' => 250000,
        ]);

        Cours::create([
            'titre' => 'Comptabilite generale',
            'categorie' => 'Comptabilite',
            'description' => 'Decouvrir les principes essentiels de la comptabilite.',
            'prix_particulier' => 100000,
            'prix_entreprise' => 180000,
        ]);

        Cours::create([
            'titre' => 'Gestion de projet',
            'categorie' => 'Gestion de projet',
            'description' => 'Apprendre a organiser et piloter un projet efficacement.',
            'prix_particulier' => 130000,
            'prix_entreprise' => 220000,
        ]);

        Cours::create([
            'titre' => 'Anglais professionnel',
            'categorie' => 'Langues',
            'description' => 'Developper son anglais dans un contexte professionnel.',
            'prix_particulier' => 90000,
            'prix_entreprise' => 150000,
        ]);

        // Cours de test dont la classe est DEJA COMMENCEE (voir ClasseSeeder) :
        // permet de tester la carte "Cours en cours" du dashboard apres une inscription.
        Cours::create([
            'titre' => 'Initiation a l\'informatique',
            'categorie' => 'Bureautique',
            'description' => 'Decouvrir l\'ordinateur, les fichiers, internet et les outils de base.',
            'prix_particulier' => 25000,
            'prix_entreprise' => 40000,
        ]);
    }
}