<?php

namespace Database\Seeders;

use App\Models\EvaluationFormation;
use App\Models\AvisFormateur;
use App\Models\Classe;
use App\Models\Cours;
use App\Models\Inscription;
use App\Models\Paiement;
use App\Models\Particulier;
use App\Models\Presence;
use App\Models\Quiz;
use App\Models\ResultatQuiz;
use App\Models\Role;
use App\Models\User;
use App\Models\MessageForum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FormateurSeeder extends Seeder
{
    /**
     * Données de test pour l'espace formateur.
     * À lancer APRÈS RoleSeeder, CoursSeeder et ClasseSeeder (voir DatabaseSeeder).
     *
     * Connexion de test : formateur@test.com / 12345678
     *
     * Les dates sont RELATIVES à aujourd'hui : les trois états (à venir, en cours, terminée)
     * restent testables quel que soit le jour où on lance "php artisan migrate:fresh --seed".
     */
    public function run(): void
    {
        $roleFormateur = Role::where('nom', 'formateur')->firstOrFail();
        $roleParticulier = Role::where('nom', 'particulier')->firstOrFail();

        // ---------------------------------------------------------------
        // 1. Le compte formateur
        // ---------------------------------------------------------------
        $formateur = User::where('email', 'formateur@test.com')->firstOrFail();

        // ---------------------------------------------------------------
        // 2. Les classes du formateur (un cas par état)
        // ---------------------------------------------------------------
        $initiation = Cours::where('titre', 'Initiation a l\'informatique')->firstOrFail();
        $word = Cours::where('titre', 'Microsoft Word')->firstOrFail();
        $excel = Cours::where('titre', 'Microsoft Excel')->firstOrFail();

        // EN COURS : on réutilise la classe créée par ClasseSeeder (commencée il y a 2 jours)
        $enCours = Classe::where('nom', 'Initiation - Session en cours')->firstOrFail();
        $enCours->update(['formateur_id' => $formateur->id]);

        // TERMINÉE (par les dates, mais pas encore fermée : on peut tester la fermeture)
        $terminee = Classe::create([
            'nom' => 'Microsoft Word - Session terminée (test formateur)',
            'capacite_max' => 10,
            'date_debut' => now()->subDays(20),
            'date_fin' => now()->subDays(10),
            'lieu' => 'Libreville',
            'cours_id' => $word->id,
            'formateur_id' => $formateur->id,
        ]);

        // À VENIR
        $aVenir = Classe::create([
            'nom' => 'Microsoft Excel - Session à venir (test formateur)',
            'capacite_max' => 8,
            'date_debut' => now()->addDays(10),
            'date_fin' => now()->addDays(14),
            'lieu' => 'Port-Gentil',
            'cours_id' => $excel->id,
            'formateur_id' => $formateur->id,
        ]);

        // ---------------------------------------------------------------
        // 3. Les stagiaires de test (rôle particulier)
        // ---------------------------------------------------------------
        $stagiaires = collect(['Alice Mba', 'Brice Obiang', 'Carine Nze', 'David Ondo'])
            ->map(function ($nom, $i) use ($roleParticulier) {
                $particulier = Particulier::create([
                    'telephone' => '07400000' . $i,
                    'date_de_naissance' => '1998-01-0' . ($i + 1),
                ]);

                return User::create([
                    'name' => $nom,
                    'email' => 'stagiaire' . ($i + 1) . '@test.com',
                    'password' => Hash::make('12345678'),
                    'email_verified_at' => now(),
                    'role_id' => $roleParticulier->id,
                    'particulier_id' => $particulier->id,
                ]);
            });

        [$alice, $brice, $carine, $david] = $stagiaires->all();

        // ---------------------------------------------------------------
        // 4. Inscriptions : Alice, Brice, Carine ont PAYÉ ; David est inscrit mais N'A PAS PAYÉ
        //    (il ne doit apparaître nulle part dans l'espace formateur : cas de test du filtre)
        // ---------------------------------------------------------------
        foreach ([$enCours, $terminee] as $classe) {
            foreach ([$alice, $brice, $carine] as $stagiaire) {
                $this->inscrireEtPayer($classe, $stagiaire, $classe->cours->prix_particulier);
            }
            // Inscrit sans paiement
            Inscription::create([
                'user_id' => $david->id,
                'classe_id' => $classe->id,
                'date_inscription' => now()->subDays(25),
            ]);
        }

        // Classe à venir : seulement Alice, payée
        $this->inscrireEtPayer($aVenir, $alice, $excel->prix_particulier);

        // ---------------------------------------------------------------
        // 6. Présences de la classe TERMINÉE (pour tester la fermeture et les attestations)
        //    Alice : présente à tous les jours      -> 100 %  -> attestation
        //    Brice : présent un jour sur deux       -> ~50 %  -> attestation (seuil atteint)
        //    Carine : présente un seul jour         -> < 50 % -> PAS d'attestation
        // ---------------------------------------------------------------
        $jours = $terminee->joursDeFormation();

        foreach ($jours as $index => $jour) {
            $this->marquer($terminee, $alice, $jour, true);
            $this->marquer($terminee, $brice, $jour, $index % 2 === 0);
            $this->marquer($terminee, $carine, $jour, $index === 0);
        }

        // Quelques présences pour la classe en cours (uniquement les jours déjà passés)
        foreach ($enCours->joursRenseignables() as $jour) {
            $this->marquer($enCours, $alice, $jour, true);
            $this->marquer($enCours, $brice, $jour, false);
            $this->marquer($enCours, $carine, $jour, true);
        }

        // ---------------------------------------------------------------
        // 6 bis. Forum de la classe en cours : quelques messages (dont une réponse)
        // ---------------------------------------------------------------
        $accueil = MessageForum::create([
            'classe_id' => $enCours->id, 'user_id' => $formateur->id,
            'contenu' => 'Bienvenue dans le forum de la classe ! Partagez ici vos photos de la formation.',
        ]);
        MessageForum::create([
            'classe_id' => $enCours->id, 'user_id' => $alice->id, 'parent_id' => $accueil->id,
            'contenu' => 'Merci ! Hâte de voir la suite du cours.',
        ]);
        MessageForum::create([
            'classe_id' => $enCours->id, 'user_id' => $brice->id,
            'contenu' => 'Quelqu\'un a les notes du module 1 ?',
        ]);

                // ---------------------------------------------------------------
        // 6 ter. Avis sur le formateur pour la classe TERMINÉE
        //    Alice et Brice ont donné leur avis ; Carine NON (cas de test du bouton « Noter le formateur »)
        // ---------------------------------------------------------------
               AvisFormateur::create([
            'classe_id' => $terminee->id, 'user_id' => $alice->id,
            'note_maitrise_sujet' => 5, 'note_qualites_pedagogiques' => 5, 'note_comprehension_problemes' => 4,
            'commentaire' => 'Formateur très clair, avec des exemples concrets.',
        ]);
        AvisFormateur::create([
            'classe_id' => $terminee->id, 'user_id' => $brice->id,
            'note_maitrise_sujet' => 4, 'note_qualites_pedagogiques' => 3, 'note_comprehension_problemes' => 3,
            'commentaire' => 'Bon formateur mais le rythme était parfois trop rapide.',
        ]);

        // Évaluation de la formation (critères + remarques) des mêmes stagiaires
        EvaluationFormation::create([
            'classe_id' => $terminee->id, 'user_id' => $alice->id,
            'note_materiel' => 5, 'note_support_cours' => 4,
            'note_info_prealable' => 4, 'note_contenu_adapte' => 5, 'note_contenu_conforme' => 5,
            'note_groupe_homogene' => 4, 'note_correspond_besoin' => 5,
            'points_forts' => 'Exemples concrets, bonne ambiance.',
            'observations' => 'Très satisfait de la formation.',
            'autre_formation' => 'Une formation plus approfondie.',
        ]);
        EvaluationFormation::create([
            'classe_id' => $terminee->id, 'user_id' => $brice->id,
            'note_materiel' => 3, 'note_support_cours' => 3,
            'note_info_prealable' => 3, 'note_contenu_adapte' => 4, 'note_contenu_conforme' => 4,
            'note_groupe_homogene' => 2, 'note_correspond_besoin' => 4,
            'points_faibles' => 'Niveau du groupe trop différent.',
        ]);

        // ---------------------------------------------------------------
        // 7. Support de cours : volontairement aucun (le formateur en dépose depuis l'interface).
        // ---------------------------------------------------------------
    }

    // Crée l'inscription d'un stagiaire à une classe + un paiement VALIDE (= inscription payée).
    private function inscrireEtPayer(Classe $classe, User $stagiaire, $montant): void
    {
        $inscription = Inscription::create([
            'user_id' => $stagiaire->id,
            'classe_id' => $classe->id,
            'date_inscription' => now()->subDays(25),
        ]);

        Paiement::create([
            'inscription_id' => $inscription->id,
            'montant' => $montant,
            'mode' => 'direct',
            'statut' => 'valide', // la définition de "payé" utilisée dans tout le projet
        ]);
    }

    // Enregistre la présence (ou l'absence) d'un stagiaire pour un jour donné.
    private function marquer(Classe $classe, User $stagiaire, $jour, bool $present): void
    {
        Presence::create([
            'classe_id' => $classe->id,
            'user_id' => $stagiaire->id,
            'date_seance' => $jour->toDateString(),
            'present' => $present,
        ]);
    }
}