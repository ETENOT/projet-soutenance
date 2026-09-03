<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Ce seeder crée un compte administrateur de test.
     * Remplace l'ancien User::create() qui était (à tort) dans la migration.
     * À ne pas utiliser en production : c'est un compte de test avec un mot de passe simple.
     */
    public function run(): void
    {
        // On récupère le rôle "admin" créé par RoleSeeder (doit être exécuté avant celui-ci)
        // firstOrFail() : si le rôle n'existe pas, on arrête tout de suite avec une erreur claire
        // plutôt que de planter plus loin avec un message confus
        $roleAdmin = Role::where('nom', 'admin')->firstOrFail();

        User::create([
            'name' => 'admin',
            'email' => 'admin@themesbrand.com',
            // Hash::make() : on ne stocke JAMAIS un mot de passe en clair dans la base
            'password' => Hash::make('12345678'),
            // On considère l'email déjà vérifié pour ce compte de test
            'email_verified_at' => now(),
            'role_id' => $roleAdmin->id,
        ]);
    }
}