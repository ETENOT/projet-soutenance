<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Email de bienvenue envoyé UNE SEULE FOIS, quand l'inscription est finalisée,
 * c'est-à-dire quand l'utilisateur a saisi le bon code à 6 chiffres
 * (adresse email vérifiée).
 *
 * Comment cette classe est utilisée :
 *   VerifyEmailController::__invoke() déclenche l'événement Verified ;
 *   le listener EnvoyerMailBienvenue (cf. EventServiceProvider) appelle alors
 *   $user->notify(new BienvenuePlateforme()).
 *
 * À ne pas confondre avec InscriptionEnregistree, qui est l'email envoyé quand
 * un utilisateur s'inscrit à une CLASSE de formation (pas à la plateforme).
 */
class BienvenuePlateforme extends Notification
{
    // Permet de mettre l'envoi en file d'attente plus tard sans changer cette classe
    // (aujourd'hui QUEUE_CONNECTION=sync : l'envoi est immédiat).
    use Queueable;

    /**
     * Canal 'mail' uniquement, comme les autres notifications du projet :
     * le canal 'database' de Laravel écrirait dans la table "notifications"
     * avec ses propres colonnes, incompatibles avec notre table personnalisée.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Construit l'email personnalisé.
     *
     * $notifiable est le User qui vient de valider son adresse email.
     * Les informations affichées dépendent de son rôle :
     *   - particulier : on affiche simplement son nom ;
     *   - entreprise  : on affiche aussi la raison sociale.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Rôle "particulier" ou "entreprise" (nul-safe : ?-> évite une erreur si la relation est vide).
        $role = $notifiable->role?->nom;
        $estEntreprise = $role === 'entreprise';

        return (new MailMessage)
            ->subject('Bienvenue sur Formation NéoVision – votre inscription est confirmée')
            ->view('Emails.bienvenue', [
                'name'          => $notifiable->name,
                'email'         => $notifiable->email,
                'estEntreprise' => $estEntreprise,
                // Raison sociale uniquement pour un compte entreprise.
                'raisonSociale' => $estEntreprise ? $notifiable->entreprise?->raison_sociale : null,
                // Date d'inscription (création du compte), formatée en français : 13/10/2026 à 14:05.
                'dateInscription' => $notifiable->created_at?->format('d/m/Y à H:i'),
            ]);
    }
}