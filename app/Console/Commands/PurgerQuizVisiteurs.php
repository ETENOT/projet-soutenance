<?php

namespace App\Console\Commands;

use App\Models\Quiz;
use Illuminate\Console\Command;

// Supprime les quiz passés par des visiteurs qui ne se sont jamais inscrits
class PurgerQuizVisiteurs extends Command
{
    protected $signature = 'quiz:purger-visiteurs {--jours=7 : Ancienneté minimale en jours}';

    protected $description = "Supprime les quiz anonymes (et leurs réponses/résultats) plus anciens que N jours, jamais rattachés à un compte.";

    public function handle(): int
    {
        // user_id NULL = jamais rattaché à un compte.
        // La suppression d'un quiz supprime ses réponses et ses résultats (cascadeOnDelete).
        $nombre = Quiz::whereNull('user_id')
            ->where('created_at', '<', now()->subDays((int) $this->option('jours')))
            ->delete();

        $this->info("{$nombre} quiz anonyme(s) supprimé(s).");

        return self::SUCCESS;
    }
}