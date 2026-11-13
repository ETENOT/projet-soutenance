<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Demandes de paiement envoyées à Airtel Money (une ligne par tentative).
 *
 * Airtel Money fonctionne par « push USSD » : la plateforme demande le paiement,
 * le client le valide sur son téléphone avec son code PIN, puis Airtel confirme
 * (notification de rappel + consultation du statut). Cette table garde la demande
 * pendant ce temps : le Paiement n'est créé qu'une fois le paiement confirmé.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions_airtel', function (Blueprint $table) {
            $table->id();

            // Identifiant UNIQUE envoyé à Airtel (transaction.id) : sert à retrouver la demande
            $table->string('reference')->unique();

            $table->foreignId('inscription_id')
                ->constrained('inscriptions')
                ->cascadeOnDelete();

            // Somme demandée (prix complet ou paiement partiel)
            $table->decimal('montant', 10, 2);

            // Numéro Airtel Money débité : 8 chiffres sans indicatif ni 0 (ex. 74123456 = 074 12 34 56)
            $table->string('msisdn', 20);

            // en_attente : en attente de la validation sur le téléphone
            // reussie    : Airtel a confirmé -> Paiement créé
            // echouee    : refusée, annulée ou solde insuffisant
            $table->string('statut')->default('en_attente');

            // Retours d'Airtel
            $table->string('airtel_money_id')->nullable(); // identifiant de l'opération chez Airtel
            $table->string('code_statut')->nullable();     // TS, TF, TIP, TA…
            $table->text('message')->nullable();           // message lisible d'Airtel

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions_airtel');
    }
};
