<?php

namespace Database\Seeders;

use App\Models\Categorie;
use App\Models\Cours;
use Illuminate\Database\Seeder;

class CoursSeeder extends Seeder
{
    public function run(): void
    {
        $formations = [
            ['Microsoft Word', 'Bureautique', 'Apprendre à créer et mettre en forme des documents professionnels.', 25000, 40000],
            ['Microsoft Excel', 'Bureautique', 'Apprendre à utiliser les tableaux, les formules et les graphiques.', 25000, 40000],
            ['Microsoft PowerPoint', 'Bureautique', 'Apprendre à créer des présentations professionnelles.', 25000, 40000],
            ['Developpement Web - HTML, CSS, JavaScript', 'Développement web', 'Apprendre les bases de la création de sites web.', 150000, 250000],
            ['Comptabilite generale', 'Comptabilité', 'Découvrir les principes essentiels de la comptabilité.', 100000, 180000],
            ['Gestion de projet', 'Gestion de projet', 'Apprendre à organiser et piloter un projet efficacement.', 130000, 220000],
            ['Anglais professionnel', 'Langues', 'Développer son anglais dans un contexte professionnel.', 90000, 150000],
            ['Initiation a l\'informatique', 'Bureautique', 'Découvrir l’ordinateur, les fichiers, internet et les outils de base.', 25000, 40000],
            ['Gestion du temps et productivité', 'Développement personnel', 'Organiser son travail et mieux gérer ses priorités au quotidien.', 45000, 75000],
            ['Marketing numérique', 'Marketing', 'Construire une présence en ligne et mesurer ses résultats.', 70000, 120000],
            ['Initiation à la cybersécurité', 'Informatique', 'Adopter les bons réflexes pour protéger ses comptes et ses données.', 55000, 90000],
            ['Gestion des ressources humaines', 'Ressources humaines', 'Découvrir les pratiques essentielles de gestion des équipes.', 85000, 145000],
            ['Entrepreneuriat et création d’entreprise', 'Entrepreneuriat', 'Structurer une idée et préparer les premières étapes d’un projet.', 65000, 110000],
            ['Communication professionnelle', 'Communication', 'Améliorer ses échanges écrits et oraux en milieu professionnel.', 50000, 85000],
            ['Service client et relation commerciale', 'Commerce', 'Accueillir les clients, comprendre leurs besoins et suivre leurs demandes.', 60000, 100000],
            ['Analyse de données avec Excel', 'Bureautique', 'Préparer, analyser et présenter des données avec Excel.', 75000, 125000],
            ['Comptabilité et gestion de trésorerie', 'Comptabilité', 'Suivre les opérations courantes et anticiper les besoins de trésorerie.', 80000, 135000],
            ['Design graphique avec Canva', 'Communication visuelle', 'Créer des visuels cohérents pour des supports numériques et imprimés.', 50000, 85000],
        ];

        foreach ($formations as [$titre, $nomCategorie, $description, $prixParticulier, $prixEntreprise]) {
            $categorie = Categorie::where('nom', $nomCategorie)->firstOrFail();

            Cours::updateOrCreate(
                ['titre' => $titre],
                [
                    'categorie_id' => $categorie->id,
                    'description' => $description,
                    'prix_particulier' => $prixParticulier,
                    'prix_entreprise' => $prixEntreprise,
                ]
            );
        }
    }
}
