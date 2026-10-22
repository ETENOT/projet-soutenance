<?php

namespace Database\Seeders;

use App\Models\Classe;
use App\Models\Cours;
use Illuminate\Database\Seeder;

class CalendrierClassesDemoSeeder extends Seeder
{
    /**
     * Crée quatre classes qui partagent leurs dates pour tester le « +N ».
     * Les dates relatives rendent le scénario visible quel que soit le jour du seed.
     */
    public function run(): void
    {
        $sessions = [
            ['Microsoft Word', 'TEST calendrier +N - Word'],
            ['Microsoft Excel', 'TEST calendrier +N - Excel'],
            ['Microsoft PowerPoint', 'TEST calendrier +N - PowerPoint'],
            ['Gestion de projet', 'TEST calendrier +N - Gestion de projet'],
        ];

        $debut = today()->addDays(2);
        $fin = today()->addDays(5);

        foreach ($sessions as [$titreCours, $nomClasse]) {
            $cours = Cours::where('titre', $titreCours)->firstOrFail();

            // Toutes les sessions commencent et finissent le même jour :
            // avec la limite de deux repères, le calendrier propose « +N ».
            Classe::updateOrCreate(
                ['nom' => $nomClasse, 'cours_id' => $cours->id],
                [
                    'capacite_max' => 8,
                    'date_debut' => $debut,
                    'date_fin' => $fin,
                    'lieu' => 'Libreville',
                ]
            );
        }
    }
}
