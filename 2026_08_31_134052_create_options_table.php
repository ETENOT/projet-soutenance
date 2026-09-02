<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('options', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->boolean('est_correct')->default(false);

            $table->foreignId('question_id')
                ->constrained('questions')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    // Supprime la table "options" si elle existe.
    // Cette méthode est appelée lorsque l'on annule (rollback)
    // la migration avec : php artisan migrate:rollback
    public function down(): void
    {
        Schema::dropIfExists('options');
    }
};