<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Feuille de présence : UNE ligne = un stagiaire, pour UNE journée de formation d'une classe.
        Schema::create('presences', function (Blueprint $table) {
            $table->id();

            // Classe concernée (supprimée avec elle)
            $table->foreignId('classe_id')
                ->constrained('classes')
                ->cascadeOnDelete();

            // Stagiaire concerné
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Jour de la séance (date seule : pas d'heure, une séance = une journée)
            $table->date('date_seance');

            // true = présent, false = absent
            $table->boolean('present')->default(false);

            $table->timestamps();

            // Un stagiaire ne peut avoir qu'une seule ligne par jour et par classe :
            // enregistrer deux fois la même journée met à jour la ligne au lieu d'en créer une 2e.
            $table->unique(['classe_id', 'user_id', 'date_seance']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presences');
    }
};