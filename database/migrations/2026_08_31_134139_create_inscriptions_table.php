<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inscriptions', function (Blueprint $table) {
            $table->id();
            $table->date('date_inscription');

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('classe_id')
                ->constrained('classes')
                ->cascadeOnDelete();

            $table->timestamps();

            // Empêche un même utilisateur de s'inscrire deux fois à la même classe
            $table->unique(['user_id', 'classe_id']);
        });
    }

    
    public function down(): void
    {
        Schema::dropIfExists('inscriptions');
    }
};