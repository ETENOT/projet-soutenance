<?php

namespace App\Notifications;

use App\Models\Classe;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// Email envoyé à l'utilisateur quand son inscription à une classe est enregistrée.
// Même schéma que VerifyEmailWithCode : canal "mail" uniquement, car la table
// "notifications" est personnalisée (message, est_lue, user_id) et n'est pas
// compatible avec le canal "database" de Laravel.
class InscriptionEnregistree extends Notification
{
    use Queueable;

    public function __construct(protected Classe $classe)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Inscription enregistrée – Formation NéoVision')
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Votre inscription à « ' . $this->classe->cours->titre . ' » (' . $this->classe->nom . ') est enregistrée.')
            ->line('Début de la formation : ' . $this->classe->date_debut->format('d/m/Y') . '.')
            ->line('Votre inscription sera validée après réception du paiement.')
            ->action('Voir mes cours', route('cours.mes'));
    }
}