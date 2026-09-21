<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notification envoyée à l'inscription (et à chaque "Renvoyer le code").
 *
 * Elle remplace Illuminate\Auth\Notifications\VerifyEmail, la notification
 * native de Laravel qui envoie un LIEN signé à cliquer. Ici on envoie un CODE
 * à 6 chiffres à taper, donc on a besoin de notre propre classe : voir
 * User::sendEmailVerificationNotification() qui l'instancie.
 */
class VerifyEmailWithCode extends Notification
{
    use Queueable;

    /**
     * $code est injecté au moment de la création de la notification
     * (cf. User::generateVerificationCode() qui le génère juste avant).
     */
    public function __construct(protected string $code)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Construit et retourne l'email de vérification avec le code
     *
     * @param object $notifiable L'entité recevant la notification (ex: User)
     * @return MailMessage L'email configuré avec le sujet et la vue personnalisée
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Votre code de vérification – Formation NéoVision')
            ->view('emails.verification-code', [
                'code' => $this->code,
                'name' => $notifiable->name,
            ]);
    }
}