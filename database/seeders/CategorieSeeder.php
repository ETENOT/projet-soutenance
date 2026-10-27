<?php

namespace Database\Seeders;

use App\Models\Categorie;
use Illuminate\Database\Seeder;

class CategorieSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'Bureautique',
            'Développement web',
            'Comptabilité',
            'Gestion de projet',
            'Langues',
            'Développement personnel',
            'Marketing',
            'Informatique',
            'Ressources humaines',
            'Entrepreneuriat',
            'Communication',
            'Commerce',
            'Communication visuelle',
        ] as $nom) {
            Categorie::firstOrCreate(['nom' => $nom]);
        }
    }
}
