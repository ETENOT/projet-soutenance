<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->text('message');
            $table->boolean('est_lue')->default(false);

            $table->foreignId('utilisateur_id')
                ->constrained('utilisateurs')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    // Supprime la table "notifications" si elle existe.
    // Cette méthode est appelée lorsque l'on annule (rollback)
    // la migration avec : php artisan migrate:rollback
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};