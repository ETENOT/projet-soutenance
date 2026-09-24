<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Définit les tâches planifiées de l'application.
     *
     * everyFiveMinutes() : Laravel vérifiera, à chaque appel de
     * "php artisan schedule:run" (déclenché par le cron du serveur,
     * ou par "php artisan schedule:work" en local), si 5 minutes se
     * sont écoulées depuis la dernière exécution de cette commande.
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('quiz:finaliser-expires')->everyFiveMinutes();
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}