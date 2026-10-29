<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('particuliers', function (Blueprint $table) {
            $table->id();
            $table->string('telephone');
            $table->date('date_de_naissance')->nullable();

             // Entreprise dont le particulier est membre. Une seule colonne = un particulier ne peut être
            // membre que d'UNE entreprise à la fois (aucun statut : c'est l'administrateur qui gère les membres).
            $table->foreignId('entreprise_id')
                ->nullable()
                ->constrained('entreprises')
                // Entreprise supprimée : ses membres restent des particuliers, simplement sans entreprise.
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    // Supprime la table "particuliers" si elle existe.
    // Cette méthode est appelée lorsque l'on annule (rollback)
    // la migration avec : php artisan migrate:rollback
    public function down(): void
    {
        Schema::dropIfExists('particuliers');
    }
};