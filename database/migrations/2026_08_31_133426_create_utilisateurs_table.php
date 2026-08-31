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
                ->restrictOnDelete();

            // 1-1 : un particulier ne peut être rattaché qu'à un seul utilisateur
            $table->foreignId('particulier_id')
                ->nullable()
                ->unique()
                ->constrained('particuliers')
                ->nullOnDelete();

            // 1-n : plusieurs utilisateurs (contacts) peuvent représenter la même entreprise
            $table->foreignId('entreprise_id')
                ->nullable()
                ->constrained('entreprises')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('utilisateurs');
    }
};