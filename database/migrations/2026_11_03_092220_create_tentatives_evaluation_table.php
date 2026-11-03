<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // UNE ligne = une tentative d'un particulier sur le quiz d'un chapitre OU sur le quiz final.
        Schema::create('tentatives_evaluation', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('cours_id')->constrained('cours')->cascadeOnDelete();

            // Chapitre évalué ; NULL = quiz final du cours
            $table->foreignId('chapitre_id')->nullable()->constrained('chapitres')->cascadeOnDelete();

            // Barème = nombre de questions tirées (1 point par bonne réponse), figé au démarrage
            $table->unsignedSmallInteger('bareme');

            // Score = nombre de bonnes réponses ; NULL tant que la tentative n'est pas terminée
            $table->unsignedSmallInteger('score')->nullable();

            // dateTime (et non timestamp) : évite le décalage d'1h dû à la conversion UTC de MySQL
            $table->dateTime('commence_le');
            $table->dateTime('termine_le')->nullable();   // NULL = tentative en cours

            $table->timestamps();

            $table->index(['user_id', 'cours_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tentatives_evaluation');
    }
};
