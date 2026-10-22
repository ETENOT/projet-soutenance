<?php

namespace App\Notifications;

use App\Models\Attestation;
use App\Services\AttestationService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * E-mail envoyé au stagiaire quand son attestation est prête (PDF en pièce jointe).
 *
 * Même principe que PaiementConfirme : canal 'mail' uniquement, la notification de la cloche
 * est créée à la main par AttestationService (la table "notifications" est personnalisée).
 */
class AttestationDisponible extends Notification
{
    use Queueable;

    // On passe le modèle (pas le PDF) : si l'envoi passe un jour en file d'attente,
    // on ne sérialise pas un gros fichier binaire dans la file.
    public function __construct(protected Attestation $attestation)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $a = $this->attestation;

        return (new MailMessage)
            ->subject('Votre attestation de formation – Formation NéoVision')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Félicitations ! Vous avez suivi avec succès la formation « ' . $a->intitule . ' », '
                . 'du ' . $a->date_debut->format('d/m/Y') . ' au ' . $a->date_fin->format('d/m/Y') . '.')
            ->line('Votre attestation (n° ' . $a->numero . ') est jointe à cet e-mail au format PDF. '
                . 'Vous pouvez aussi la télécharger ou la prévisualiser à tout moment depuis la plateforme (téléchargez-la dans « Mes classes », onglet Terminées.).')
            ->action('Télécharger mon attestation', route('attestations.telecharger', $a))
            // PDF en pièce jointe, lu depuis le disque (régénéré s'il manque)
            ->attachData(
                app(AttestationService::class)->octetsPdf($a),
                $a->nomFichier(),
                ['mime' => 'application/pdf']
            );
    }
}