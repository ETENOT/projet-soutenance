<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Une session de formation = occurrence planifiée regroupant plusieurs classes (de cours éventuellement
// différents), créée par l'administrateur pour UNE SEULE entreprise.
// (Table nommée « sessions_formation » pour ne pas entrer en conflit avec la table « sessions » de Laravel.)
// Doit être créée AVANT la table « classes », qui possède une clé étrangère vers elle.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sessions_formation', function (Blueprint $table) {
            $table->id();

            $table->string('nom');
            $table->text('description')->nullable();

            // Une session appartient toujours à une seule entreprise.
            // restrictOnDelete : une entreprise ayant des sessions ne peut pas être supprimée par accident.
            $table->foreignId('entreprise_id')
                ->constrained('entreprises')
                ->restrictOnDelete();

            // Dates DÉDUITES des classes (début de la 1re classe, fin de la dernière) :
            // elles ne sont jamais saisies à la main, SessionFormation::recalculerDates() les tient à jour.
            // NULL tant que la session ne contient aucune classe.
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions_formation');
    }
};