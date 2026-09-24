<?php

namespace Database\Seeders;

use App\Models\Option;
use App\Models\Question;
use Illuminate\Database\Seeder;

class OptionSeeder extends Seeder
{
    public function run(): void
    {
        $optionsParQuestion = [
            'Quel logiciel permet de créer des documents texte ?' => [
                ['libelle' => 'Microsoft Word', 'est_correct' => true],
                ['libelle' => 'Microsoft Excel', 'est_correct' => false],
                ['libelle' => 'PowerPoint', 'est_correct' => false],
                ['libelle' => 'Photoshop', 'est_correct' => false],
            ],

            'Quelle fonctionnalité permet de mettre un texte en évidence ?' => [
                ['libelle' => 'Le surlignage', 'est_correct' => true],
                ['libelle' => 'Le tri', 'est_correct' => false],
                ['libelle' => 'La validation des données', 'est_correct' => false],
                ['libelle' => 'Le publipostage', 'est_correct' => false],
            ],

            'Quel symbole commence généralement une formule Excel ?' => [
                ['libelle' => '=', 'est_correct' => true],
                ['libelle' => '#', 'est_correct' => false],
                ['libelle' => '@', 'est_correct' => false],
                ['libelle' => '$', 'est_correct' => false],
            ],

            'Quelle fonction permet d additionner plusieurs cellules ?' => [
                ['libelle' => 'SOMME', 'est_correct' => true],
                ['libelle' => 'MOYENNE', 'est_correct' => false],
                ['libelle' => 'NB', 'est_correct' => false],
                ['libelle' => 'SI', 'est_correct' => false],
            ],

            'Quel élément compose principalement une présentation PowerPoint ?' => [
                ['libelle' => 'Des diapositives', 'est_correct' => true],
                ['libelle' => 'Des feuilles de calcul', 'est_correct' => false],
                ['libelle' => 'Des tables SQL', 'est_correct' => false],
                ['libelle' => 'Des cellules', 'est_correct' => false],
            ],

            'Quelle fonctionnalité permet de changer le passage entre deux diapositives ?' => [
                ['libelle' => 'Transition', 'est_correct' => true],
                ['libelle' => 'Filtre', 'est_correct' => false],
                ['libelle' => 'Formule', 'est_correct' => false],
                ['libelle' => 'Publipostage', 'est_correct' => false],
            ],

            'Quel langage structure une page web ?' => [
                ['libelle' => 'HTML', 'est_correct' => true],
                ['libelle' => 'CSS', 'est_correct' => false],
                ['libelle' => 'SQL', 'est_correct' => false],
                ['libelle' => 'PHP uniquement', 'est_correct' => false],
            ],

            'Quel langage est utilisé pour styliser une page web ?' => [
                ['libelle' => 'CSS', 'est_correct' => true],
                ['libelle' => 'HTML', 'est_correct' => false],
                ['libelle' => 'JavaScript', 'est_correct' => false],
                ['libelle' => 'JSON', 'est_correct' => false],
            ],

            'Quel document présente les actifs et les passifs d une entreprise ?' => [
                ['libelle' => 'Le bilan', 'est_correct' => true],
                ['libelle' => 'Le devis', 'est_correct' => false],
                ['libelle' => 'Le contrat', 'est_correct' => false],
                ['libelle' => 'Le planning', 'est_correct' => false],
            ],

            'Que représente une charge en comptabilité ?' => [
                ['libelle' => 'Une dépense supportée par l entreprise', 'est_correct' => true],
                ['libelle' => 'Une recette', 'est_correct' => false],
                ['libelle' => 'Un bénéfice', 'est_correct' => false],
                ['libelle' => 'Un investissement personnel', 'est_correct' => false],
            ],

            'Quel outil permet de représenter les tâches dans le temps ?' => [
                ['libelle' => 'Le diagramme de Gantt', 'est_correct' => true],
                ['libelle' => 'Le bilan comptable', 'est_correct' => false],
                ['libelle' => 'Le formulaire HTML', 'est_correct' => false],
                ['libelle' => 'Le journal de caisse', 'est_correct' => false],
            ],

            'Quelle étape consiste à définir les objectifs du projet ?' => [
                ['libelle' => 'La planification', 'est_correct' => true],
                ['libelle' => 'La clôture uniquement', 'est_correct' => false],
                ['libelle' => 'La suppression', 'est_correct' => false],
                ['libelle' => 'La facturation', 'est_correct' => false],
            ],

            'Que signifie "meeting" en anglais professionnel ?' => [
                ['libelle' => 'Réunion', 'est_correct' => true],
                ['libelle' => 'Facture', 'est_correct' => false],
                ['libelle' => 'Congé', 'est_correct' => false],
                ['libelle' => 'Contrat', 'est_correct' => false],
            ],

            'Quelle formule peut commencer un email professionnel ?' => [
                ['libelle' => 'Dear Sir or Madam', 'est_correct' => true],
                ['libelle' => 'See you yesterday', 'est_correct' => false],
                ['libelle' => 'Good night meeting', 'est_correct' => false],
                ['libelle' => 'Tomorrow yesterday', 'est_correct' => false],
            ],

            'Quel périphérique permet de saisir du texte ?' => [
                ['libelle' => 'Le clavier', 'est_correct' => true],
                ['libelle' => 'L écran', 'est_correct' => false],
                ['libelle' => 'Les haut-parleurs', 'est_correct' => false],
                ['libelle' => 'Le projecteur', 'est_correct' => false],
            ],

            'Quel outil permet de naviguer sur Internet ?' => [
                ['libelle' => 'Un navigateur web', 'est_correct' => true],
                ['libelle' => 'Un tableur', 'est_correct' => false],
                ['libelle' => 'Un traitement de texte', 'est_correct' => false],
                ['libelle' => 'Une calculatrice', 'est_correct' => false],
            ],
        ];

        foreach ($optionsParQuestion as $enonce => $options) {
            $question = Question::where('enonce', $enonce)->firstOrFail();

            foreach ($options as $option) {
                Option::create([
                    'libelle' => $option['libelle'],
                    'est_correct' => $option['est_correct'],
                    'question_id' => $question->id,
                ]);
            }
        }
    }
}