<?php

namespace Database\Seeders;

use App\Models\Classe;
use App\Models\Cours;
use App\Models\Particulier;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AvisSeeder extends Seeder
{
    /**
     * Crée des particuliers de test, les inscrit (paiement validé) à une classe
     * du premier cours et leur fait laisser un avis, pour tester la moyenne.
     * À lancer après UserSeeder, CoursSeeder et ClasseSeeder.
     */
    public function run(): void
    {
        $roleParticulier = Role::where('nom', 'particulier')->firstOrFail();

        $cours  = Cours::firstOrFail();
        $classe = Classe::where('cours_id', $cours->id)->firstOrFail();

        // [nom, email, note, commentaire]
        $jeux = [
            ['Sasuke',  'sasuke@test.com',  5, 'Formation très complète, formateur excellent.'],
            ['Sakura',  'sakura@test.com',  4, 'Bon contenu, rythme agréable.'],
            ['Messi',   'messi@test.com',   5, null],
            ['Neymar',  'neymar@test.com',  3, 'Correct, mais un peu court.'],
            ['Mbappé',  'mbappe@test.com',  4, 'Les exercices pratiques sont utiles.'],
            ['Kakashi', 'kakashi@test.com', 2, 'Je m\'attendais à plus de pratique.'],
        ];
        // Moyenne attendue : (5+4+5+3+4+2) / 6 = 3,8 (6 avis)

        foreach ($jeux as [$nom, $email, $note, $commentaire]) {
            $particulier = Particulier::create([
                'telephone'         => '074123456',
                'date_de_naissance' => '1995-06-15',
            ]);

            $user = User::create([
                'name'              => $nom,
                'email'             => $email,
                'password'          => Hash::make('12345678'),
                'email_verified_at' => now(),
                'role_id'           => $roleParticulier->id,
                'particulier_id'    => $particulier->id,
            ]);

            // Inscription à une classe du cours
            $inscriptionId = DB::table('inscriptions')->insertGetId([
                'date_inscription' => now()->toDateString(),
                'statut'           => 'confirmee',
                'user_id'          => $user->id,
                'classe_id'        => $classe->id,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            // Paiement validé (condition pour pouvoir donner un avis)
            DB::table('paiements')->insert([
                'montant'        => $cours->prix_particulier ?? 0,
                'mode'           => 'direct',
                'reference'      => null,
                'statut'         => 'valide',
                'inscription_id' => $inscriptionId,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            // L'avis
            DB::table('avis')->insert([
                'user_id'     => $user->id,
                'cours_id'    => $cours->id,
                'note'        => $note,
                'commentaire' => $commentaire,
                'created_at'  => now()->subDays(rand(0, 20)),
                'updated_at'  => now(),
            ]);
        }
    }
}