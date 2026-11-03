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
         // UNE ligne = une question tirée pour une tentative, avec l'option choisie (NULL = pas encore répondu)
        Schema::create('reponses_evaluation', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tentative_evaluation_id')->constrained('tentatives_evaluation')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->foreignId('option_id')->nullable()->constrained('options')->nullOnDelete();

            // Ordre du tirage : les questions réapparaissent dans le même ordre si on reprend la tentative
            $table->unsignedSmallInteger('ordre');

            $table->timestamps();

            // Une question n'est jamais posée deux fois dans la même tentative
            $table->unique(['tentative_evaluation_id', 'question_id'], 'reponses_evaluation_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reponses_evaluation');
    }
};
