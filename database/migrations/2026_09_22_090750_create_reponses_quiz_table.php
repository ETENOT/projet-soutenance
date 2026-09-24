<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cette table sert deux rôles à la fois :
        // 1) mémoriser quelles questions ont été tirées pour une tentative donnée
        //    (une ligne est créée pour CHAQUE question, dès le démarrage, avec option_id à null)
        // 2) mémoriser la réponse choisie au fur et à mesure (option_id mis à jour à chaque clic)
        // -> c'est ce qui permet de reprendre une tentative en cours ET d'afficher la correction plus tard
        Schema::create('reponses_quiz', function (Blueprint $table) {
            $table->id();

            // La tentative concernée (une ligne "quizzes" = une tentative précise)
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();

            // La question tirée pour cette tentative
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();

            // L'option choisie par le particulier.
            // nullable() : tant qu'il n'a pas répondu à cette question, la valeur reste null
            $table->foreignId('option_id')->nullable()->constrained('options')->nullOnDelete();

            // Ordre de tirage, pour réafficher les questions toujours dans le même ordre
            // quand il quitte puis revient (sinon inRandomOrder() donnerait un ordre différent à chaque fois)
            $table->unsignedSmallInteger('ordre');

            $table->timestamps();

            // Empêche d'avoir deux lignes de réponse pour la même question dans la même tentative
            $table->unique(['quiz_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reponses_quiz');
    }
};