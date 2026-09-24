<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resultats_quiz', function (Blueprint $table) {
            $table->id();
            $table->decimal('score', 5, 2)->nullable();

            $table->foreignId('quiz_id')
                ->constrained('quizzes')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();
        });

        Schema::create('resultat_quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resultat_quiz_id')
                ->constrained('resultats_quiz')
                ->cascadeOnDelete();
            $table->foreignId('question_id')
                ->constrained('questions')
                ->restrictOnDelete();
            $table->unsignedInteger('ordre');
            $table->timestamps();

            $table->unique(['resultat_quiz_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resultat_quiz_questions');
        Schema::dropIfExists('resultats_quiz');
    }
};