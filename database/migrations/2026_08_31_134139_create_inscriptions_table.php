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

            // Date à laquelle l'utilisateur s'est inscrit
            $table->date('date_inscription');

            // État de l'inscription
            $table->string('statut')->default('en_attente');

            // Renseignés UNIQUEMENT à la fermeture de la classe (voir ClasseClotureService) :
            // - taux_presence : pourcentage de séances où le stagiaire était présent
            // - attestation_eligible : false si taux_presence < 50 %
            // NULL tant que la classe n'est pas fermée.
            $table->decimal('taux_presence', 5, 2)->nullable();
            $table->boolean('attestation_eligible')->nullable();

            // Utilisateur inscrit
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Classe choisie
            $table->foreignId('classe_id')
                ->constrained('classes')
                ->cascadeOnDelete();

            $table->timestamps();

            // Un utilisateur ne peut pas s'inscrire
            // deux fois à la même classe.
            $table->unique(['user_id', 'classe_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inscriptions');
    }
};