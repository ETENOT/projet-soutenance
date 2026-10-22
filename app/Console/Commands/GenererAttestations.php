<?php

namespace App\Console\Commands;

use App\Models\Classe;
use App\Services\AttestationService;
use Illuminate\Console\Command;

// Filet de sécurité : (re)génère les attestations manquantes et renvoie les e-mails non partis.
//   php artisan attestations:generer            -> toutes les classes fermées
//   php artisan attestations:generer 12         -> la classe n° 12 seulement
// Utile pour les classes fermées AVANT la mise en place des attestations, ou si le serveur
// de mail était en panne au moment de la fermeture.
class GenererAttestations extends Command
{
    protected $signature = 'attestations:generer {classe? : Identifiant de la classe (toutes les classes fermées si omis)}';

    protected $description = 'Génère les attestations manquantes des classes fermées et renvoie les e-mails non envoyés';

    public function handle(AttestationService $service): int
    {
        $classes = Classe::query()
            ->whereNotNull('fermee_le')
            ->when($this->argument('classe'), fn ($q, $id) => $q->whereKey($id))
            ->get();

        $total = 0;

        foreach ($classes as $classe) {
            // Éligibles, payées, et sans attestation OU avec un e-mail jamais parti
            $inscriptions = $classe->inscriptions()
                ->payees()
                ->where('attestation_eligible', true)
                ->where(fn ($q) => $q
                    ->whereDoesntHave('attestation')
                    ->orWhereHas('attestation', fn ($a) => $a->whereNull('envoyee_le')))
                ->with('user', 'classe.cours', 'classe.formateur')
                ->get();

            foreach ($inscriptions as $inscription) {
                $service->envoyer($service->generer($inscription));
                $total++;
            }
        }

        $this->info("{$total} attestation(s) traitée(s).");

        return self::SUCCESS;
    }
}