<?php

namespace Database\Seeders;

use App\Models\Avis;
use App\Models\Cours;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AvisSeeder extends Seeder
{
    /** Proportion du catalogue gardée sans avis pour les captures de démonstration. */
    private const POURCENTAGE_COURS_SANS_AVIS = 10;

    /**
     * Génère des avis de démonstration pour les cours du catalogue.
     *
     * À exécuter après RoleSeeder, UserSeeder et les seeders de cours.
     * Chaque exécution remplace les avis pour éviter les doublons.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            // Remplacer l'échantillon existant rend le seeder relançable.
            Avis::query()->delete();

            // La factory fournit des utilisateurs distincts pour les avis.
            $utilisateursManquants = 15 - User::count();
            if ($utilisateursManquants > 0) {
                $roleParticulier = Role::where('nom', 'particulier')->firstOrFail();
                User::factory()
                    ->count($utilisateursManquants)
                    ->create(['role_id' => $roleParticulier->id]);
            }

            $utilisateurs = User::query()->get();
            $commentaires = [
                1 => 'Le contenu ne correspondait pas à mes attentes.',
                2 => 'Quelques notions utiles, mais les explications pourraient être plus approfondies.',
                3 => 'Formation correcte et claire, malgré un manque de pratique.',
                4 => 'Très bonne formation, claire et bien structurée.',
                5 => 'Excellente formation, très complète et immédiatement utile.',
            ];

            $cours = Cours::query()->get();
            $coursSansAvis = collect();

            // Une sélection globale garantit qu'une part visible du catalogue reste sans note.
            if ($cours->isNotEmpty() && self::POURCENTAGE_COURS_SANS_AVIS > 0) {
                $nombreCoursSansAvis = min(
                    $cours->count(),
                    max(1, (int) round($cours->count() * self::POURCENTAGE_COURS_SANS_AVIS / 100))
                );
                $coursSansAvis = $cours->random($nombreCoursSansAvis)->pluck('id');
            }

            foreach ($cours as $cours) {
                if ($coursSansAvis->contains($cours->id)) {
                    continue;
                }

                // Chaque cours a une qualité propre; ses notes sont tirées autour de cette valeur.
                $qualite = $this->tirerQualite();
                $nombreAvis = random_int(3, 12);
                $avis = [];

                foreach ($utilisateurs->random($nombreAvis) as $utilisateur) {
                    $note = $this->tirerNote($qualite);
                    $avis[] = [
                        'user_id' => $utilisateur->id,
                        'cours_id' => $cours->id,
                        'note' => $note,
                        'commentaire' => $commentaires[$note],
                        'created_at' => now()->subDays(random_int(0, 90)),
                        'updated_at' => now(),
                    ];
                }

                Avis::query()->insert($avis);
            }
        });
    }

    private function tirerQualite(): float
    {
        // La majorité des cours sont bons, avec quelques cours moyens ou excellents.
        $repartition = random_int(1, 100);

        if ($repartition <= 15) {
            return random_int(28, 35) / 10;
        }

        if ($repartition <= 80) {
            return random_int(36, 44) / 10;
        }

        return random_int(45, 49) / 10;
    }

    private function tirerNote(float $qualite): int
    {
        // Pondération en cloche : une note proche de la qualité est plus probable.
        $poids = [];
        foreach (range(1, 5) as $note) {
            $ecart = $note - $qualite;
            $poids[$note] = exp(-($ecart ** 2) / (2 * (0.85 ** 2)));
        }

        $tirage = random_int(0, 1_000_000) / 1_000_000 * array_sum($poids);
        foreach ($poids as $note => $poidsNote) {
            $tirage -= $poidsNote;
            if ($tirage <= 0) {
                return $note;
            }
        }

        return 5;
    }
}
