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
            $table->foreignId('categorie_id')->constrained('categories')->restrictOnDelete();
            $table->text('description')->nullable();

            $table->string('image')->nullable();

            // "Noté sur" du quiz du cours = nombre de questions tirées au hasard dans la banque.
            // NULL = pas de limite : on pose toute la banque de questions.
            $table->unsignedSmallInteger('note_sur')->nullable();

             // Crée une colonne "prix" de type décimal.
            // 10 = nombre total de chiffres maximum.
            // 2 = nombre de chiffres après la virgule.
            // Exemple : 150000.50            
            $table->decimal('prix_entreprise', 10, 2);
            $table->decimal('prix_particulier', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cours');
    }
};