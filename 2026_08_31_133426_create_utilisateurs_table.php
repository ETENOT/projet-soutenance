<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('utilisateurs', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('email')->unique();
            $table->string('mot_de_passe');

            $table->foreignId('role_id')
                ->constrained('roles')
                // Empêche la suppression d'un rôle s'il est encore utilisé par un utilisateur dans cette table.
                ->restrictOnDelete();

            // 1-1 : un particulier ne peut être rattaché qu'à un seul utilisateur
            $table->foreignId('particulier_id')
                ->nullable()
                ->unique()
                ->constrained('particuliers')
                // Si le particulier est supprimé, "particulier_id" sera automatiquement mis à NULL.
                ->nullOnDelete();

            // 1-n : plusieurs utilisateurs (contacts) peuvent représenter la même entreprise
            $table->foreignId('entreprise_id')
                ->nullable()
                ->constrained('entreprises')
                // Si le entreprise est supprimé, "entreprise_id" sera automatiquement mis à NULL.
                ->nullOnDelete();

            $table->timestamps();
        });
    }


    // Supprime la table "utilisateurs" si elle existe.
    // Cette méthode est appelée lorsque l'on annule (rollback)
    // la migration avec : php artisan migrate:rollback
    public function down(): void
    {
        Schema::dropIfExists('utilisateurs');
    }
};