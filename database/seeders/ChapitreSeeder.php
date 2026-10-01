<?php

namespace Database\Seeders;

use App\Models\Cours;
use App\Models\Chapitre;
use Illuminate\Database\Seeder;

class ChapitreSeeder extends Seeder
{
    /**
     * Transforme le programme d'un cours en chapitres.
     *
     * Exemple :
     *
     * ## Module 1
     * - Point A
     * - Point B
     *
     * devient :
     *
     * Chapitre :
     * titre = Module 1
     * contenu = Point A
     *            Point B
     */
    public function run(): void
    {
        Cours::all()->each(function (Cours $cours) {

            if (!$cours->programme) {
                return;
            }

            $lignes = preg_split('/\r\n|\r|\n/', trim($cours->programme));

            $titreChapitre = null;
            $contenuChapitre = [];
            $ordre = 1;

            foreach ($lignes as $ligne) {

                $ligne = trim($ligne);

                if ($ligne === '') {
                    continue;
                }

                // Nouvelle section = nouveau chapitre
                if (str_starts_with($ligne, '## ')) {

                    // On sauvegarde le chapitre précédent
                    if ($titreChapitre !== null) {
                        Chapitre::create([
                            'cours_id' => $cours->id,
                            'titre' => $titreChapitre,
                            'contenu' => implode("\n", $contenuChapitre),
                            'ordre' => $ordre,
                        ]);

                        $ordre++;
                    }

                    $titreChapitre = trim(substr($ligne, 3));
                    $contenuChapitre = [];

                    continue;
                }

                // Les lignes "- " deviennent du contenu
                if (str_starts_with($ligne, '- ')) {
                    $contenuChapitre[] = trim(substr($ligne, 2));
                }
            }

            // Enregistre le dernier chapitre
            if ($titreChapitre !== null) {

                Chapitre::create([
                    'cours_id' => $cours->id,
                    'titre' => $titreChapitre,
                    'contenu' => implode("\n", $contenuChapitre),
                    'ordre' => $ordre,
                ]);
            }
        });
    }
}