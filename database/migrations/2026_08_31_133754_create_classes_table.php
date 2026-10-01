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

            // Informations de la session
            $table->string('nom');
            //signifie que la capacité max peut aller jusqu'à 255 et est obligatoirement un entier positif
            $table->unsignedTinyInteger('capacite_max');

            // Dates et horaires
            $table->date('date_debut');
            $table->date('date_fin');
            $table->time('heure_debut')->nullable();
            $table->time('heure_fin')->nullable();

            // Informations pratiques
            $table->string('lieu');
            $table->string('formateur')->nullable();

            // État de la session
            $table->string('statut')->default('a_venir');

            // Cours auquel appartient la classe
            $table->foreignId('cours_id')
                ->constrained('cours')
            
                 // Si un cours est supprimé, toutes les classes
                // qui lui sont associées seront automatiquement supprimées.
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};