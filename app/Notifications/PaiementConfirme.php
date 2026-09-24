<?php

namespace App\Notifications;

use App\Models\Paiement;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Email envoyé à l'utilisateur quand le paiement de son inscription est enregistré.
 *
 * Comment cette classe est utilisée :
 *   PaiementController::enregistrer() crée le Paiement dans une transaction, puis, une fois
 *   la transaction terminée, envoyerEmail() appelle : $user->notify(new PaiementConfirme($paiement)).
 *   Laravel appelle alors via() pour connaître le canal, puis toMail() pour construire le message.
 *   Le même email sert au paiement direct (admin) et au paiement en ligne (particulier).
 *
 * Deux systèmes de notification coexistent dans le projet :
 *   - la table "notifications" (modèle App\Models\Notification) : la cloche et l'historique
 *     dans l'application, alimentés à la main avec Notification::create() ;
 *   - cette classe : uniquement l'email, via le système de notifications de Laravel.
 */
class PaiementConfirme extends Notification
{
    // Permet de mettre l'envoi en file d'attente plus tard, sans changer cette classe.
    // Aujourd'hui l'envoi est immédiat (QUEUE_CONNECTION=sync), donc ce trait est inactif.
    use Queueable;

    /**
     * Le paiement est passé au constructeur puis stocké dans $this->paiement
     * (promotion de propriété du constructeur, PHP 8). "protected" : accessible seulement
     * dans cette classe. Toutes les infos de l'email (montant, moyen, référence, classe, cours,
     * date de début) sont retrouvées à partir de lui grâce aux relations Eloquent.
     */
    public function __construct(protected Paiement $paiement)
    {
    }

    /**
     * Liste les canaux d'envoi.
     *
     * On ne met QUE 'mail', volontairement : le canal 'database' de Laravel écrirait dans
     * la table "notifications" avec ses propres colonnes (id, type, data, read_at...).
     * Notre table personnalisée a d'autres colonnes (message, est_lue, archivee, user_id),
     * donc ce canal provoquerait une erreur.
     * La notification de la cloche est déjà créée dans PaiementController.
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
        // Chaîne de relations : Paiement -> Inscription -> Classe (-> Cours plus bas).
        // Chaque flèche est une requête, tolérable ici puisqu'on envoie un seul email.
        $classe = $this->paiement->inscription->classe;

        $mail = (new MailMessage)
            // Objet de l'email, tel qu'il apparaît dans la boîte de réception.
            ->subject('Paiement confirmé – Formation NéoVision')

            // Formule d'ouverture personnalisée avec le nom de l'utilisateur.
            ->greeting('Bonjour ' . $notifiable->name . ',')

            // Corps du message : chaque ->line() devient un paragraphe.
            // number_format(montant, 0 décimale, ',' séparateur décimal, ' ' séparateur de
            // milliers) affiche 25000 sous la forme "25 000".
            ->line('Nous avons bien reçu votre paiement de '
                . number_format($this->paiement->montant, 0, ',', ' ')
                . ' fcfa pour « ' . $classe->cours->titre . ' » (' . $classe->nom . ').')

            // libelle_mode est l'accesseur défini dans le modèle Paiement
            // (ex. "Airtel Money" ou "Paiement direct (comptoir)").
            ->line('Moyen de paiement : ' . $this->paiement->libelle_mode . '.');

        // La référence n'existe que pour un paiement en ligne (elle est vide au comptoir).
        if ($this->paiement->reference) {
            $mail->line('Référence de la transaction : ' . $this->paiement->reference . '.');
        }

        return $mail
            // date_debut est convertie en objet Carbon par $casts dans le modèle Classe,
            // ce qui permet d'appeler directement ->format('d/m/Y').
            ->line('Votre inscription est validée. Début de la formation : '
                . $classe->date_debut->format('d/m/Y') . '.')

            // Bouton vers "Mes cours". route() génère l'URL complète à partir de APP_URL
            // du fichier .env : il doit correspondre à l'adresse où tourne l'application.
            ->action('Voir mes cours', route('cours.mes'));
    }
}