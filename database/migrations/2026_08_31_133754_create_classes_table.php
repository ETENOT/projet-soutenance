<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            //signifie que la capacité max peut aller jusqu'à 255 et est obligatoirement un entier positif
            $table->unsignedTinyInteger('capacite_max');
            $table->date('date_debut');
            $table->date('date_fin');

            $table->foreignId('cours_id')
                ->constrained('cours')

                // Si un cours est supprimé, toutes les classes
                // qui lui sont associées seront automatiquement supprimées.
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    // Supprime la table "classes" si elle existe.
    // Cette méthode est appelée lorsque l'on annule (rollback)
    // la migration avec : php artisan migrate:rollback
    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};