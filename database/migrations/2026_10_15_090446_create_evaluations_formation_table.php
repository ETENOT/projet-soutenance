<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Évaluation de la formation (blocs « Environnement et moyens pédagogiques » et « La formation »
     * de la fiche NEO VISION + verso), notée en étoiles (1 à 5). Réservée à l'administration.
     * La « satisfaction globale » n'est PAS stockée ici : c'est l'avis public du cours (table avis).
     * UNE ligne = la fiche d'UN stagiaire pour UNE classe.
     */
    public function up(): void
    {
        Schema::create('evaluations_formation', function (Blueprint $table) {
            $table->id();

            $table->foreignId('classe_id')
                ->constrained('classes')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // --- Environnement et moyens pédagogiques ---
            $table->unsignedTinyInteger('note_materiel');
            $table->unsignedTinyInteger('note_support_cours');

            // --- La formation ---
            $table->unsignedTinyInteger('note_info_prealable');
            $table->unsignedTinyInteger('note_contenu_adapte');
            $table->unsignedTinyInteger('note_contenu_conforme');
            $table->unsignedTinyInteger('note_groupe_homogene');
            $table->unsignedTinyInteger('note_correspond_besoin');

            // --- Verso de la fiche : champs libres (facultatifs) ---
            $table->text('points_forts')->nullable();
            $table->text('points_faibles')->nullable();
            $table->text('observations')->nullable();
            $table->text('autre_formation')->nullable();

            $table->timestamps();

            $table->unique(['classe_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluations_formation');
    }
};