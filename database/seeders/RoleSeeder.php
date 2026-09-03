<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Ce seeder remplit la table "roles" avec les 3 rôles possibles de l'application.
     * Indispensable AVANT de créer le moindre utilisateur, car la colonne
     * "role_id" dans la table "users" est obligatoire (pas nullable).
     */
    public function run(): void
    {
        // insert() plutôt que create() : plus rapide, on insère les 3 lignes
        // en une seule requête SQL au lieu de 3 requêtes séparées
        Role::insert([
            ['nom' => 'particulier', 'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'entreprise',  'created_at' => now(), 'updated_at' => now()],
            ['nom' => 'admin',       'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}