<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Entreprise;
use App\Models\Particulier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Ce seeder crée des comptes de test pour les 3 rôles.
     * À ne pas utiliser en production : mots de passe simples, données fictives.
     */
    public function run(): void
    {
        // On récupère les 3 rôles créés par RoleSeeder (doit être exécuté avant celui-ci)
        $roleAdmin = Role::where('nom', 'admin')->firstOrFail();
        $roleEntreprise = Role::where('nom', 'entreprise')->firstOrFail();
        $roleParticulier = Role::where('nom', 'particulier')->firstOrFail();

        // --- Compte admin (déjà existant, inchangé) ---
        User::create([
            'name' => 'admin',
            'email' => 'admin@themesbrand.com',
            'password' => Hash::make('12345678'),
            'email_verified_at' => now(),
            'role_id' => $roleAdmin->id,
        ]);

        // --- Compte entreprise de test ---
        // Un compte entreprise a besoin d'une ligne dans "entreprises" en plus
        // de sa ligne dans "users" (relation via entreprise_id)
        $entreprise = Entreprise::create([
            'raison_sociale' => 'BICIG',
            'adresse' => 'Boulevard Triomphal, Libreville',
            'contact_principal' => 'Service Formation',
            'secteur_activite' => 'Banque',
        ]);

        User::create([
            'name' => 'BICIG Test',
            'email' => 'entreprise@test.com',
            'password' => Hash::make('12345678'),
            'email_verified_at' => now(),
            'role_id' => $roleEntreprise->id,
            'entreprise_id' => $entreprise->id,
        ]);

        // --- Compte particulier de test ---
        // Même logique : ligne "particuliers" séparée, reliée via particulier_id
        $particulier = Particulier::create([
            'telephone' => '074123456',
            'date_de_naissance' => '1995-06-15',
        ]);

        User::create([
            'name' => 'Particulier Test',
            'email' => 'particulier@test.com',
            'password' => Hash::make('12345678'),
            'email_verified_at' => now(),
            'role_id' => $roleParticulier->id,
            'particulier_id' => $particulier->id,
        ]);
    }
}