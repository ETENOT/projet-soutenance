<?php

namespace App\Notifications;

use App\Models\Classe;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Email envoyé à l'utilisateur quand son inscription à une classe est enregistrée.
 *
 * Comment cette classe est utilisée :
 *   InscriptionController::store() crée l'inscription et la notification de la cloche dans
 *   une transaction, puis, une fois la transaction terminée, appelle :
 *   $user->notify(new InscriptionEnregistree($classe)).
 *   Laravel appelle alors via() pour connaître le canal, puis toMail() pour construire
 *   le message.
 *
 * Deux systèmes de notification coexistent dans le projet :
 *   - la table "notifications" (modèle App\Models\Notification) : la cloche et l'historique
 *     dans l'application, alimentés à la main avec Notification::create() ;
 *   - cette classe : uniquement l'email, via le système de notifications de Laravel.
 *
 * Même schéma que VerifyEmailWithCode (l'email du code de vérification) et que
 * PaiementConfirme (l'email envoyé après un paiement).
 */
class InscriptionEnregistree extends Notification
{
    // Permet de mettre l'envoi en file d'attente plus tard, sans changer cette classe.
    // Aujourd'hui l'envoi est immédiat (QUEUE_CONNECTION=sync), donc ce trait est inactif.
    use Queueable;

    /**
     * La classe choisie est passée au constructeur puis stockée dans $this->classe
     * (promotion de propriété du constructeur, PHP 8). "protected" : accessible seulement
     * dans cette classe. Le titre du cours et la date de début de l'email sont retrouvés
     * à partir d'elle grâce aux relations Eloquent.
     */
    public function __construct(protected Classe $classe)
    {
    }

    /**
     * Liste les canaux d'envoi.
     *
     * On ne met QUE 'mail', volontairement : le canal 'database' de Laravel écrirait dans
     * la table "notifications" avec ses propres colonnes (id, type, data, read_at...).
     * Notre table personnalisée a d'autres colonnes (message, est_lue, archivee, user_id),
     * donc ce canal provoquerait une erreur.
     * La notification de la cloche est déjà créée dans InscriptionController.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Construit le contenu de l'email.
     *
     * $notifiable est l'utilisateur qui reçoit l'email : c'est l'objet sur lequel on a appelé
     * ->notify(). Laravel envoie l'email à son adresse ($notifiable->email).
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            // Objet de l'email, tel qu'il apparaît dans la boîte de réception.
            ->subject('Inscription enregistrée – Formation NéoVision')

            // Formule d'ouverture personnalisée avec le nom de l'utilisateur.
            ->greeting('Bonjour ' . $notifiable->name . ',')

            // Corps du message : chaque ->line() devient un paragraphe.
            // $this->classe->cours est la relation belongsTo définie dans le modèle Classe :
            // elle donne le titre du cours à partir de la classe.
            ->line('Votre inscription à « ' . $this->classe->cours->titre . ' » (' . $this->classe->nom . ') est enregistrée.')

            // date_debut est convertie en objet Carbon par $casts dans le modèle Classe,
            // ce qui permet d'appeler directement ->format('d/m/Y').
            ->line('Début de la formation : ' . $this->classe->date_debut->format('d/m/Y') . '.')

            // Le paiement n'est pas encore en place : l'inscription est "en attente".
            // Ce message correspond à l'état "impayée" du tableau de bord.
            ->line('Votre inscription sera validée après réception du paiement.')

            // Bouton vers "Mes cours". route() génère l'URL complète à partir de APP_URL
            // du fichier .env : il doit correspondre à l'adresse où tourne l'application.
            ->action('Voir mes cours', route('cours.mes'));
    }
}