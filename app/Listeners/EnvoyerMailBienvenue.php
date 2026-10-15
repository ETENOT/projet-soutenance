<?php

namespace App\Listeners;

use App\Notifications\BienvenuePlateforme;
use Illuminate\Auth\Events\Verified;

/**
 * Écoute l'événement Verified (déclenché par VerifyEmailController quand le code
 * à 6 chiffres est correct) et envoie l'email de bienvenue.
 *
 * Verified n'est déclenché qu'une fois par utilisateur : le contrôleur s'arrête
 * avant si l'email est déjà vérifié. Le mail de bienvenue n'est donc envoyé
 * qu'une seule fois, et pas à chaque "Renvoyer le code".
 */
class EnvoyerMailBienvenue
{
    public function handle(Verified $event): void
    {
        try {
            $event->user->notify(new BienvenuePlateforme());
        } catch (\Throwable $e) {
            // Un problème d'envoi (serveur mail indisponible, etc.) ne doit pas empêcher
            // l'utilisateur de finaliser son inscription : on journalise l'erreur
            // (storage/logs/laravel.log) et on continue.
            report($e);
        }
    }
}