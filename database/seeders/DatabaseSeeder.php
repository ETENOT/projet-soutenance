<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Point d'entrée unique de tous les seeders.
     *  Appelé automatiquement par : php artisan migrate:fresh --seed
     */
    public function run(): void
    {
        // L'ORDRE COMPTE : RoleSeeder doit tourner avant UserSeeder,
        // car UserSeeder a besoin qu'un rôle "admin" existe déjà en base
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,

            // Création des formations
            CoursSeeder::class,

            // Création des chapitres à partir des programmes des cours
            ChapitreSeeder::class,

            // Ressources liées aux chapitres
            CoursResourceSeeder::class,

            // Sessions de formation
            ClasseSeeder::class,

            // Quiz
            QuestionSeeder::class,
            OptionSeeder::class,
        ]);
    }
}