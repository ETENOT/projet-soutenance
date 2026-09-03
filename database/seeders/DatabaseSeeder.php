<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Point d'entrée unique de tous les seeders.
     * Appelé automatiquement par : php artisan migrate:fresh --seed
     */
    public function run(): void
    {
        // L'ORDRE COMPTE : RoleSeeder doit tourner avant UserSeeder,
        // car UserSeeder a besoin qu'un rôle "admin" existe déjà en base
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
        ]);
    }
}