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
            $table->decimal('montant', 10, 2);
            

            // Moyen de paiement : 'direct' (comptoir, enregistré par l'admin),
            // 'airtel_money' ou 'moov_money' (paiement en ligne, simulé pour l'instant).
            $table->string('mode')->default('direct');

            // Référence de transaction : générée pour un paiement en ligne, vide pour un
            // paiement direct. Unique : deux paiements ne peuvent pas partager la même référence.
            $table->string('reference')->nullable()->unique();

            // Données de confirmation conservées pour les paiements SingPay.
            $table->string('singpay_transaction_id')->nullable()->unique();
            $table->string('singpay_status')->nullable();
            $table->string('singpay_result')->nullable();

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