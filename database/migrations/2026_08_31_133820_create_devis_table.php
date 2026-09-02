<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devis', function (Blueprint $table) {
            $table->id();
            $table->date('date_demande');
            $table->string('statut')->default('en_attente');

            $table->foreignId('entreprise_id')
                ->constrained('entreprises')
                ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    // Supprime la table "devis" si elle existe.
    // Cette méthode est appelée lorsque l'on annule (rollback)
    // la migration avec : php artisan migrate:rollback
    public function down(): void
    {
        Schema::dropIfExists('devis');
    }
};