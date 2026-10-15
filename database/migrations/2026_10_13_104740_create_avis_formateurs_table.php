<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Avis d'un stagiaire sur le formateur d'une classe, à la fin de la formation.
     * Les 3 critères viennent du bloc « Le formateur » de la fiche d'évaluation NEO VISION,
     * notés en étoiles (1 à 5) comme les avis de cours. Visible par l'admin et par le formateur.
     * Le formateur n'est pas stocké ici : on le retrouve par classes.formateur_id.
     */
    public function up(): void
    {
        Schema::create('avis_formateurs', function (Blueprint $table) {
            $table->id();

            // Classe suivie (l'avis est supprimé avec elle)
            $table->foreignId('classe_id')
                ->constrained('classes')
                ->cascadeOnDelete();

            // Stagiaire qui donne l'avis
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Les 3 critères, de 1 à 5 étoiles
            $table->unsignedTinyInteger('note_maitrise_sujet');
            $table->unsignedTinyInteger('note_qualites_pedagogiques');
            $table->unsignedTinyInteger('note_comprehension_problemes');

            // Commentaire facultatif
            $table->text('commentaire')->nullable();

            $table->timestamps();

            // Un stagiaire ne donne qu'un seul avis par classe
            $table->unique(['classe_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avis_formateurs');
    }
};