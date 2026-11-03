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

            // "Noté sur" du quiz de CE chapitre = nombre de questions tirées au hasard
            // parmi les questions rattachées au chapitre. NULL = toutes les questions du chapitre.
            $table->unsignedSmallInteger('quiz_note_sur')->nullable();

            // Le chapitre appartient à un cours
            $table->foreignId('cours_id')
                ->constrained('cours')
                ->cascadeOnDelete();

            $table->timestamps();
        });

        // ------------------------------------------------------------------
        // Quiz de chapitre + quiz final (parcours type Cisco)
        // Placé ICI, et pas dans create_questions_table : la clé étrangère
        // questions.chapitre_id exige que la table "chapitres" existe déjà,
        // or create_questions_table (31/08) s'exécute avant cette migration (30/09).
        // ------------------------------------------------------------------

        // Une question de la banque du cours peut être rattachée à UN chapitre (facultatif).
        // NULL = question "générale" : elle sert au quiz du cours comme avant,
        // mais pas aux quiz de chapitre ni au quiz final.
        // nullOnDelete : supprimer un chapitre remet ses questions dans la banque générale.
        Schema::table('questions', function (Blueprint $table) {
            $table->foreignId('chapitre_id')
                ->nullable()
                ->after('cours_id')
                ->constrained('chapitres')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        // Ordre inverse de up() : d'abord ce qui dépend de "chapitres", puis "chapitres"
        Schema::dropIfExists('reponses_evaluation');
        Schema::dropIfExists('tentatives_evaluation');

        Schema::table('questions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('chapitre_id');
        });
    }
};