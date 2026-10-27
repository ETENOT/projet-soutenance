<?php

namespace Database\Seeders;

use App\Models\Chapitre;
use App\Models\Cours;
use Illuminate\Database\Seeder;

class ChapitreSeeder extends Seeder
{
    public function run(): void
    {
        $chapitres = [
            'Microsoft Word' => [
                ['Découverte de Word', 'Ruban, onglets et barre d’accès rapide; création, enregistrement et navigation dans un document.'],
                ['Mise en forme', 'Polices, styles, alignement, retraits, interlignes et listes.'],
                ['Tableaux et mise en page', 'Insertion de tableaux et d’images; marges, orientation et sauts de page.'],
                ['Documents avancés', 'Publipostage, table des matières, en-têtes, pieds de page et numérotation.'],
                ['Révision et impression', 'Suivi des modifications, commentaires, export PDF et paramètres d’impression.'],
            ],
            'Microsoft Excel' => [
                ['Prise en main des feuilles de calcul', 'Interface, classeurs, feuilles, saisie et mise en forme des données.'],
                ['Formules et fonctions', 'Opérations de base, références relatives et absolues, fonctions courantes et gestion des erreurs.'],
                ['Mise en forme et impression', 'Mise en forme conditionnelle, formats de nombres et préparation de la mise en page.'],
                ['Tableaux croisés dynamiques', 'Création, filtrage, regroupement et actualisation des tableaux croisés dynamiques.'],
                ['Graphiques et tableaux de bord', 'Choix d’un graphique et construction d’un tableau de bord simple.'],
            ],
            'Microsoft PowerPoint' => [
                ['Structurer une présentation', 'Définir le message, le plan, les masques et les sections.'],
                ['Mettre en forme les diapositives', 'Choisir des thèmes, polices et couleurs; organiser la hiérarchie visuelle.'],
                ['Images, schémas et animations', 'Insérer des images et des formes, créer des schémas et utiliser des animations sobres.'],
                ['Présenter et exporter', 'Utiliser le mode présentateur, gérer le minutage et exporter en PDF ou vidéo.'],
            ],
            'Developpement Web - HTML, CSS, JavaScript' => [
                ['Structure HTML', 'Balises, hiérarchie du document, liens, images, listes et formulaires.'],
                ['Mise en forme avec CSS', 'Sélecteurs, modèle de boîte, couleurs, polices et espacements.'],
                ['Mise en page responsive', 'Flexbox, Grid et media queries pour les écrans mobiles et ordinateurs.'],
                ['Bases de JavaScript', 'Variables, fonctions, conditions, manipulation du DOM et événements.'],
                ['Projet de site web', 'Structurer un projet, intégrer HTML/CSS/JavaScript et mettre le site en ligne.'],
            ],
            'Comptabilite generale' => [
                ['Principes fondamentaux', 'Rôle et objectifs de la comptabilité, principes comptables et vocabulaire essentiel.'],
                ['Bilan et compte de résultat', 'Structure des documents comptables et liens entre bilan et résultat.'],
                ['Enregistrement des opérations', 'Partie double, journal, grand livre et écritures courantes.'],
                ['Travaux de fin d’exercice', 'Amortissements, provisions, régularisations et clôture comptable.'],
                ['Analyse financière', 'Indicateurs financiers, lecture d’un bilan et premières analyses.'],
            ],
            'Gestion de projet' => [
                ['Cadrage du projet', 'Identifier les besoins et parties prenantes, définir les objectifs et rédiger une note de cadrage.'],
                ['Planification et suivi', 'Découper les tâches, établir un calendrier et suivre le budget.'],
                ['Gestion des risques', 'Identifier, évaluer et prioriser les risques; préparer des mesures de réduction.'],
                ['Pilotage d’équipe', 'Répartir les rôles, organiser les réunions et communiquer avec les parties prenantes.'],
                ['Clôture du projet', 'Livrer, valider, faire le bilan et capitaliser les retours d’expérience.'],
            ],
            'Anglais professionnel' => [
                ['Vocabulaire professionnel', 'Vocabulaire de l’entreprise, présentations et expressions courantes au bureau.'],
                ['Rédiger des courriels', 'Structure d’un message professionnel, formules de politesse et mises en situation.'],
                ['Participer à une réunion', 'Vocabulaire de réunion, argumentation et gestion des échanges.'],
                ['Présenter et négocier', 'Préparer une présentation commerciale et conduire une négociation.'],
                ['Entretien et expression orale', 'Préparer un entretien et s’entraîner à répondre aux questions fréquentes.'],
            ],
            'Initiation a l\'informatique' => [
                ['Ordinateur et système', 'Composants, système d’exploitation, souris, clavier et raccourcis.'],
                ['Fichiers et dossiers', 'Créer, renommer, déplacer et organiser des fichiers et dossiers.'],
                ['Internet et messagerie', 'Utiliser un navigateur, rechercher des informations et gérer une messagerie.'],
                ['Premiers outils bureautiques', 'Découvrir Word et Excel, enregistrer, organiser et imprimer des documents.'],
            ],
            'Gestion du temps et productivité' => [
                ['Organiser son activité', 'Définir ses priorités et planifier sa semaine en fonction de ses objectifs.'],
                ['Gagner en efficacité', 'Réduire les interruptions et suivre régulièrement ses résultats.'],
            ],
            'Marketing numérique' => [
                ['Définir sa stratégie', 'Identifier son public cible et choisir les canaux adaptés.'],
                ['Déployer ses campagnes', 'Créer des contenus cohérents et mesurer les résultats des actions.'],
            ],
            'Initiation à la cybersécurité' => [
                ['Comprendre les risques', 'Reconnaître les menaces courantes et protéger ses mots de passe.'],
                ['Sécuriser ses usages', 'Vérifier les liens et pièces jointes et sauvegarder ses données.'],
            ],
            'Gestion des ressources humaines' => [
                ['Recruter et intégrer', 'Préparer un recrutement et réussir l’accueil d’un nouveau collaborateur.'],
                ['Accompagner les équipes', 'Fixer des objectifs et conduire un entretien de suivi.'],
            ],
            'Entrepreneuriat et création d’entreprise' => [
                ['Valider son idée', 'Étudier son marché et définir une offre adaptée.'],
                ['Lancer son activité', 'Construire un budget prévisionnel et préparer un plan d’action.'],
            ],
            'Communication professionnelle' => [
                ['Communiquer clairement', 'Structurer un message et adapter son ton à son interlocuteur.'],
                ['Échanger efficacement', 'Écouter, reformuler et préparer une présentation courte.'],
            ],
            'Service client et relation commerciale' => [
                ['Accueillir et conseiller', 'Poser les bonnes questions et présenter une solution adaptée.'],
                ['Fidéliser ses clients', 'Traiter une réclamation et assurer le suivi après-vente.'],
            ],
            'Analyse de données avec Excel' => [
                ['Préparer les données', 'Nettoyer un tableau et utiliser les tris et filtres.'],
                ['Analyser et présenter', 'Créer un tableau croisé dynamique et choisir un graphique adapté.'],
            ],
            'Comptabilité et gestion de trésorerie' => [
                ['Suivre les opérations', 'Lire les documents comptables et enregistrer les mouvements courants.'],
                ['Piloter la trésorerie', 'Construire un plan de trésorerie, repérer les écarts et décider des actions.'],
            ],
            'Design graphique avec Canva' => [
                ['Concevoir un visuel', 'Choisir une mise en page et harmoniser couleurs et typographies.'],
                ['Décliner ses supports', 'Adapter un format aux réseaux sociaux et exporter pour le web ou l’impression.'],
            ],
        ];

        foreach ($chapitres as $titreCours => $liste) {
            $cours = Cours::where('titre', $titreCours)->firstOrFail();
            $cours->chapitres()->delete();

            foreach ($liste as $index => [$titre, $contenu]) {
                Chapitre::create([
                    'cours_id' => $cours->id,
                    'titre' => $titre,
                    'contenu' => $contenu,
                    'ordre' => $index + 1,
                ]);
            }
        }
    }
}
