<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paiements', function (Blueprint $table) {
            $table->id();

            // Montant réellement VERSÉ par le stagiaire (paiement complet ou partiel)
            $table->decimal('montant', 10, 2);

            // Montant TOTAL dû pour l'inscription (prix de la formation au moment du paiement).
            // Figé ici pour que le "reste à payer" ne change pas si le prix du cours est modifié ensuite.
            // reste à payer = montant_total - montant
            $table->decimal('montant_total', 10, 2);


        // Moyen de paiement : 'direct' (comptoir, enregistré par l'admin),
            // 'airtel_money' ou 'moov_money' (paiement en ligne, simulé pour l'instant).
            $table->string('mode');

        // Référence de transaction : générée pour un paiement en ligne, vide pour un
            // paiement direct. Unique : deux paiements ne peuvent pas partager la même référence.            
            $table->string('reference')->nullable()->unique();
            // Statut du paiement :
            //  - 'valide'  : paiement reçu en totalité (reste à payer = 0) ;
            //  - 'partiel' : paiement partiel (reste à payer > 0) -> accès au cours, mais pas à l'attestation.
            // Une inscription SANS ligne de paiement est "en attente".
            $table->string('statut')->default('en_attente');

            // Informations retournées par l'opérateur Mobile Money (Airtel Money)
            // pour un paiement en ligne ; NULL pour un paiement au comptoir.
            $table->string('operateur_transaction_id')->nullable(); // identifiant Airtel Money de l'opération
            $table->string('operateur_statut')->nullable();         // code final (ex. TS = réussi)
            $table->text('operateur_resultat')->nullable();         // message renvoyé par l'opérateur

            $table->foreignId('inscription_id')
                ->constrained('inscriptions')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    // Supprime la table "paiements" si elle existe.
    // Cette méthode est appelée lorsque l'on annule (rollback)
    // la migration avec : php artisan migrate:rollback
    public function down(): void
    {
        Schema::dropIfExists('paiements');
    }
};