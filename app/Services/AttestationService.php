<?php

// app/Services/AttestationService.php

namespace App\Services;

use App\Models\Attestation;
use App\Models\Classe;
use App\Models\Cours;
use App\Models\Inscription;
use App\Models\Notification;
use App\Notifications\AttestationDisponible;
use Barryvdh\DomPDF\Facade\Pdf;
use DomainException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

// Centralise TOUT ce qui concerne les attestations : création de la ligne en base,
// génération du PDF, stockage, envoi par e-mail.
//
// Qui a droit à une attestation ? Les stagiaires dont l'inscription est payée ET dont
// `attestation_eligible` vaut true. Ce champ est figé par ClasseClotureService à la fermeture
// de la classe, à partir de la feuille de présence émargée par le formateur
// (taux de présence >= Classe::SEUIL_ATTESTATION). Ce service ne recalcule donc rien.
class AttestationService
{
    // Disque PRIVÉ (storage/app) : le PDF n'est jamais accessible par une URL publique,
    // il passe obligatoirement par AttestationController (vérification du propriétaire).
    private const DISQUE = 'local';

    /**
     * Génère et envoie les attestations de TOUS les stagiaires éligibles d'une classe.
     * Appelée juste après la fermeture de la classe. Retourne le nombre d'attestations traitées.
     */
    public function genererPourClasse(Classe $classe): int
    {
        $inscriptions = $classe->inscriptions()
            ->payees()
            ->where('attestation_eligible', true)
            ->with('user', 'classe.cours', 'classe.formateur')
            ->get();

        $nombre = 0;

        foreach ($inscriptions as $inscription) {
            try {
                $this->envoyer($this->generer($inscription));
                $nombre++;
            } catch (Throwable $e) {
                // Un problème sur UN stagiaire (mail invalide...) ne doit pas bloquer les autres.
                report($e);
            }
        }

        return $nombre;
    }

    /**
     * Crée (une seule fois) l'attestation d'une inscription et son PDF.
     * Idempotent : l'appeler deux fois renvoie la même attestation sans doublon.
     *
     * @throws DomainException si le stagiaire n'est pas éligible
     */
    public function generer(Inscription $inscription): Attestation
    {
        if (! $inscription->attestation_eligible) {
            throw new DomainException('Ce stagiaire n\'est pas éligible à l\'attestation (présence insuffisante ou classe non fermée).');
        }

        $attestation = Attestation::firstOrCreate(
            ['inscription_id' => $inscription->id],
            $this->donnees($inscription)
        );

        $this->assurerPdf($attestation);

        // Notification dans l'application (cloche) : uniquement à la toute première création
        if ($attestation->wasRecentlyCreated) {
            Notification::create([
                'user_id' => $attestation->user_id,
                'message' => 'Votre attestation de formation « ' . $attestation->intitule
                    . ' » est disponible : téléchargez-la dans « Mes classes », onglet Terminées.',
            ]);
        }

        return $attestation;
    }

    /**
     * Envoie l'attestation par e-mail (PDF en pièce jointe). Une seule fois, sauf $forcer.
     * Si l'envoi échoue, `envoyee_le` reste vide : la commande `attestations:generer`
     * pourra réessayer. Le stagiaire peut de toute façon la télécharger sur la plateforme.
     */
    public function envoyer(Attestation $attestation, bool $forcer = false): void
    {
        if ($attestation->envoyee_le && ! $forcer) {
            return;
        }

        try {
            $attestation->user->notify(new AttestationDisponible($attestation));
            $attestation->update(['envoyee_le' => now()]);
        } catch (Throwable $e) {
            report($e);
        }
    }

    // Contenu binaire du PDF (régénéré à partir des données figées si le fichier a disparu)
    public function octetsPdf(Attestation $attestation): string
    {
        $this->assurerPdf($attestation);

        return Storage::disk(self::DISQUE)->get($attestation->chemin_pdf);
    }

    // -----------------------------------------------------------------
    // Détails internes
    // -----------------------------------------------------------------

    // Copie figée des informations du document (cours, classe, utilisateur, formateur)
    private function donnees(Inscription $inscription): array
    {
        $classe = $inscription->classe;
        $user = $inscription->user;
        $cours = $classe->cours;

        return [
            'user_id' => $user->id,
            'classe_id' => $classe->id,
            // Numéro déterministe : une inscription = un numéro, lisible et unique
            'numero' => sprintf('ATT-%s-C%d-U%d', $classe->date_fin->format('Y'), $classe->id, $user->id),
            'beneficiaire' => mb_strtoupper($user->name),
            'intitule' => $cours->titre,
            'date_debut' => $classe->date_debut,
            'date_fin' => $classe->date_fin,
            'lieu' => $classe->lieu,
            'contenu' => $this->contenuDuCours($cours),
            'formateur' => $classe->formateur?->name,
            'taux_presence' => (float) $inscription->taux_presence,
        ];
    }

    // « Contenu : Word 1 – Excel 1 » : les modules du programme (lignes « ## ») ou, à défaut,
    // les titres des chapitres du cours.
    private function contenuDuCours(Cours $cours): ?string
    {
        $titres = collect($cours->plan_sections)->pluck('titre');

        if ($titres->isEmpty()) {
            $titres = $cours->chapitres()->pluck('titre');
        }

        return $titres->isEmpty() ? null : Str::limit($titres->implode(' – '), 250);
    }

    // Écrit le PDF sur le disque s'il n'existe pas encore (ou plus)
    private function assurerPdf(Attestation $attestation): void
    {
        if ($attestation->pdfExiste()) {
            return;
        }

        $chemin = 'attestations/' . $attestation->numero . '.pdf';

        Storage::disk(self::DISQUE)->put($chemin, $this->rendrePdf($attestation));

        $attestation->update(['chemin_pdf' => $chemin]);
    }

    // Rend la vue resources/views/attestations/pdf.blade.php en PDF (A4 paysage, comme le modèle)
    private function rendrePdf(Attestation $attestation): string
    {
        return Pdf::loadView('attestations.pdf', [
            'attestation' => $attestation,
            'img' => [
                'fond' => $this->imageEnBase64('fond.png'),
                'logo' => $this->imageEnBase64('logo.png'),
                'medaille' => $this->imageEnBase64('medaille.png'),
                'signature' => $this->imageEnBase64('signature.jpg'),
            ],
        ])->setPaper('a4', 'landscape')->output();
    }

    // Les images sont intégrées au PDF en base64 : aucune requête réseau, aucun souci de chemin
    private function imageEnBase64(string $fichier): string
    {
        $chemin = public_path('images/attestation/' . $fichier);

        return 'data:image/png;base64,' . base64_encode(file_get_contents($chemin));
    }
}