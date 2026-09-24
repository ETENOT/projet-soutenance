<?php

namespace Database\Seeders;

use App\Models\Cours;
use App\Models\Question;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        $questionsParCours = [
            'Microsoft Word' => [
                'Quel logiciel permet de créer des documents texte ?',
                'Quelle fonctionnalité permet de mettre un texte en évidence ?',
            ],

            'Microsoft Excel' => [
                'Quel symbole commence généralement une formule Excel ?',
                'Quelle fonction permet d additionner plusieurs cellules ?',
            ],

            'Microsoft PowerPoint' => [
                'Quel élément compose principalement une présentation PowerPoint ?',
                'Quelle fonctionnalité permet de changer le passage entre deux diapositives ?',
            ],

            'Developpement Web - HTML, CSS, JavaScript' => [
                'Quel langage structure une page web ?',
                'Quel langage est utilisé pour styliser une page web ?',
            ],

            'Comptabilite generale' => [
                'Quel document présente les actifs et les passifs d une entreprise ?',
                'Que représente une charge en comptabilité ?',
            ],

            'Gestion de projet' => [
                'Quel outil permet de représenter les tâches dans le temps ?',
                'Quelle étape consiste à définir les objectifs du projet ?',
            ],

            'Anglais professionnel' => [
                'Que signifie "meeting" en anglais professionnel ?',
                'Quelle formule peut commencer un email professionnel ?',
            ],

            'Initiation a l\'informatique' => [
                'Quel périphérique permet de saisir du texte ?',
                'Quel outil permet de naviguer sur Internet ?',
            ],
        ];

        foreach ($questionsParCours as $titreCours => $questions) {
            $cours = Cours::where('titre', $titreCours)->firstOrFail();

            foreach ($questions as $enonce) {
                Question::create([
                    'enonce' => $enonce,
                    'cours_id' => $cours->id,
                ]);
            }
        }
    }
}