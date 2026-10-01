<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chapitres', function (Blueprint $table) {
            $table->id();

            // Nom du chapitre
            $table->string('titre');

            // Contenu pédagogique du chapitre
            $table->longText('contenu')->nullable();

            // Permet de définir l'ordre des chapitres
            $table->unsignedInteger('ordre')->default(1);

            // Le chapitre appartient à un cours
            $table->foreignId('cours_id')
                ->constrained('cours')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chapitres');
    }
};