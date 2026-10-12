<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Messages du forum de discussion. Un forum = une classe (pas de table "forums" :
        // les membres sont déduits automatiquement des inscriptions payées de la classe).
        Schema::create('messages_forum', function (Blueprint $table) {
            $table->id();

            // Forum (= classe) dans lequel le message est posté ; supprimé avec la classe
            $table->foreignId('classe_id')
                ->constrained('classes')
                ->cascadeOnDelete();

            // Auteur du message ; ses messages disparaissent si le compte est supprimé
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Message auquel on répond (NULL = message normal).
            // Si le message d'origine est supprimé, la réponse reste (parent_id repasse à NULL).
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('messages_forum')
                ->nullOnDelete();

            // Texte (facultatif si le message contient une photo)
            $table->text('contenu')->nullable();

            // Chemin de la photo sur le disque privé (jamais une URL publique : voir ForumController::photo)
            $table->string('photo')->nullable();

            $table->timestamps();

            // Accélère le chargement des derniers messages d'une classe
            $table->index(['classe_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages_forum');
    }
};