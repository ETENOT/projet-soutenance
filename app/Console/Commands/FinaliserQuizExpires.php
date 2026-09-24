<?php

namespace App\Console\Commands;

use App\Models\Quiz;
use App\Services\QuizFinalisationService;
use Illuminate\Console\Command;

// Commande artisan appelée automatiquement par le planificateur (voir Kernel.php)
// pour clôturer les tentatives que personne n'est venu terminer soi-même
class FinaliserQuizExpires extends Command
{
    // Nom utilisé pour l'appeler : php artisan quiz:finaliser-expires
    protected $signature = 'quiz:finaliser-expires';

    protected $description = "Clôture automatiquement les tentatives dont l'heure de fin est dépassée et génère leur résultat à partir des questions déjà répondues.";

    public function handle(QuizFinalisationService $finalisation): int
    {
        // whereDoesntHave('resultats') : uniquement les tentatives pas encore notées
        // ->get()->filter(...) : le filtre sur l'heure se fait en PHP plutôt qu'en SQL
        // ici, car "date" + "heure_fin" sont deux colonnes séparées à combiner
        $tentatives = Quiz::whereDoesntHave('resultats')
            ->get()
            ->filter(fn (Quiz $quiz) => $finalisation->estExpire($quiz));

        // Génère un résultat pour chaque tentative expirée trouvée
        $tentatives->each(fn (Quiz $quiz) => $finalisation->finaliser($quiz));

        // Affiché dans les logs/la console quand la commande tourne
        $this->info("{$tentatives->count()} tentative(s) abandonnée(s) clôturée(s) automatiquement.");

        return self::SUCCESS;
    }
}