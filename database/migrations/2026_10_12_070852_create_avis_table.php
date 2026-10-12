<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécuter les migrations.
     */
    public function up(): void
    {
        Schema::create('avis', function (Blueprint $table) {
            $table->id();

            // Utilisateur qui publie l'avis
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Formation concernée par l'avis
            $table->foreignId('cours_id')
                ->constrained('cours')
                ->cascadeOnDelete();

            // Note attribuée : de 1 à 5
            $table->unsignedTinyInteger('note');

            // Commentaire facultatif
            $table->text('commentaire')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Annuler les migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('avis');
    }
};
