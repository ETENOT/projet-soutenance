<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cours', function (Blueprint $table) {
            $table->id();
            $table->string('titre');

            // Crée une colonne "prix" de type décimal.
            // 10 = nombre total de chiffres maximum.
            // 2 = nombre de chiffres après la virgule.
            // Exemple : 150000.50
            $table->decimal('prix', 10, 2);
            $table->timestamps();
        });
    }

    // Supprime la table "cours" si elle existe.
    // Cette méthode est appelée lorsque l'on annule (rollback)
    // la migration avec : php artisan migrate:rollback
    public function down(): void
    {
        Schema::dropIfExists('cours');
    }
};