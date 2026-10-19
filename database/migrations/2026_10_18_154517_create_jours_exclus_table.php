<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Jours d'une classe à NE PAS prendre en compte dans le taux de présence
        // (jour férié, formateur absent, panne de courant, séance annulée...).
        // UNE ligne = un jour exclu pour une classe.
        Schema::create('jours_exclus', function (Blueprint $table) {
            $table->id();

            // Classe concernée (les jours exclus disparaissent avec elle)
            $table->foreignId('classe_id')
                ->constrained('classes')
                ->cascadeOnDelete();

            // Le jour exclu (date seule)
            $table->date('date');

            $table->timestamps();

            // Un même jour ne peut être exclu qu'une seule fois par classe
            $table->unique(['classe_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jours_exclus');
    }
};