<?php

namespace Database\Seeders;

use App\Models\Classe;
use App\Models\Cours;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Classes de TEST pour vérifier à l'écran :
 *  - le lieu cliquable (lien Google Maps, photo, ou recherche automatique) ;
 *  - la capacité minimale ;
 *  - la fermeture des inscriptions (à partir de la date de début).
 *
 * Les dates sont calculées par rapport à AUJOURD'HUI : le scénario reste valable
 * quel que soit le jour où l'on relance le seeder.
 *
 * Tout est rattaché à UN SEUL cours (« Microsoft Word ») pour tout voir sur la même page.
 * Relancer seul : php artisan db:seed --class=ClasseScenariosSeeder
 */
class ClasseScenariosSeeder extends Seeder
{
    public function run(): void
    {
        $cours = Cours::where('titre', 'Microsoft Word')->firstOrFail();

        // Photos de lieu : on copie quelques images du template dans le disque public
        // (le même emplacement que celui où ClasseController range les vraies photos).
        $photo = fn (string $source, string $nom) => $this->copierPhoto($source, $nom);

        $scenarios = [
            // Terminée : inscriptions closes
            [
                'nom' => 'Session de test 1',
                'debut' => -20, 'fin' => -16,
                'lieu' => 'Palais des Sports de Libreville',
                'lien' => 'https://www.google.com/maps/search/?api=1&query=Palais+des+Sports+Libreville',
                'image' => $photo('img-1.jpg', 'test-palais-des-sports.jpg'),
            ],
            // Commencée depuis 2 jours : inscriptions closes
            [
                'nom' => 'Session de test 2',
                'debut' => -2, 'fin' => 3,
                'lieu' => 'Université Omar Bongo',
                'lien' => 'https://www.google.com/maps/search/?api=1&query=Universite+Omar+Bongo+Libreville',
                'image' => $photo('img-2.jpg', 'test-uob.jpg'),
            ],
            // Commence AUJOURD'HUI : inscriptions closes (la date de début est atteinte)
            [
                'nom' => 'Session de test 3',
                'debut' => 0, 'fin' => 4,
                'lieu' => 'Aéroport Léon Mba',
                'lien' => 'https://www.google.com/maps/search/?api=1&query=Aeroport+Leon+Mba+Libreville',
                'image' => $photo('img-3.jpg', 'test-aeroport.jpg'),
            ],
            // Commence DEMAIN : encore ouverte, lien + photo
            [
                'nom' => 'Session de test 4',
                'debut' => 1, 'fin' => 5,
                'lieu' => 'Marché du Mont-Bouët',
                'lien' => 'https://www.google.com/maps/search/?api=1&query=Marche+Mont-Bouet+Libreville',
                'image' => $photo('img-4.jpg', 'test-mont-bouet.jpg'),
            ],
            // Ouverte, SANS lien ni photo : le nom du lieu ouvre une recherche Google Maps
            [
                'nom' => 'Session de test 5',
                'debut' => 10, 'fin' => 14,
                'lieu' => 'Port-Gentil',
                'lien' => null,
                'image' => null,
            ],
            // Ouverte, lien SANS photo
            [
                'nom' => 'Session de test 6',
                'debut' => 20, 'fin' => 24,
                'lieu' => 'Pointe-Denis',
                'lien' => 'https://www.google.com/maps/search/?api=1&query=Pointe-Denis+Gabon',
                'image' => null,
            ],
        ];

        foreach ($scenarios as $s) {
            // updateOrCreate : relancer le seeder met à jour les dates sans créer de doublons
            Classe::updateOrCreate(
                ['nom' => $s['nom'], 'cours_id' => $cours->id],
                [
                    'capacite_max' => 8,
                    'date_debut' => today()->addDays($s['debut']),
                    'date_fin' => today()->addDays($s['fin']),
                    'lieu' => $s['lieu'],
                    'lieu_lien' => $s['lien'],
                    'lieu_image' => $s['image'],
                ]
            );
        }
    }

    /**
     * Copie une image du template vers storage/app/public/classes/lieux/.
     * Renvoie le chemin relatif à stocker en base, ou null si l'image source n'existe pas
     * (la classe est alors créée sans photo, sans faire échouer le seeder).
     */
    private function copierPhoto(string $source, string $nom): ?string
    {
        $chemin = resource_path('images/small/' . $source);

        if (! File::exists($chemin)) {
            return null;
        }

        $destination = 'classes/lieux/' . $nom;
        Storage::disk('public')->put($destination, File::get($chemin));

        return $destination;
    }
}