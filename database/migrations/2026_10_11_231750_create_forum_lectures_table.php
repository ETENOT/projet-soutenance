<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Jusqu'où chaque utilisateur a lu chaque forum.
        // Un forum a des "nouveaux messages" pour un utilisateur s'il contient un message
        // d'un AUTRE auteur dont l'id est supérieur à dernier_message_lu_id.
        Schema::create('forum_lectures', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('classe_id')->constrained('classes')->cascadeOnDelete();

            // Id du dernier message vu. Simple nombre (pas de clé étrangère) : le message
            // peut être supprimé plus tard sans que cette valeur ne perde son sens.
            $table->unsignedBigInteger('dernier_message_lu_id')->default(0);

            $table->timestamps();

            // Une seule ligne par utilisateur et par forum
            $table->unique(['user_id', 'classe_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_lectures');
    }
};