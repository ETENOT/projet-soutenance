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
            // Capacité maximale modifiable, avec une valeur initiale pratique.
            $table->unsignedTinyInteger('capacite_max')->default(10);

            // Dates et horaires
            $table->date('date_debut');
            $table->date('date_fin');
            $table->time('heure_debut')->nullable();
            $table->time('heure_fin')->nullable();

            // Informations pratiques
            $table->string('lieu');
            // Lien Google Maps du lieu (facultatif) : posé sur le nom du lieu pour l'itinéraire.
            $table->string('lieu_lien', 500)->nullable();
            // Photo du lieu (facultative), stockée sur le disque public.
            $table->string('lieu_image')->nullable();

            // Formateur (utilisateur au rôle "formateur") chargé d'animer la classe.
            // nullable : une classe peut exister avant qu'un formateur lui soit affecté.
            // nullOnDelete : si le compte du formateur est supprimé, la classe est conservée.
            $table->foreignId('formateur_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // État de la session
            $table->string('statut')->default('a_venir');

            // Fermeture de la classe (action du formateur ou de l'administrateur).
            // NULL = classe encore ouverte. dateTime() et non timestamp() pour éviter le
            // décalage d'1h dû à la conversion UTC de MySQL (voir learnings du projet).
            $table->dateTime('fermee_le')->nullable();

            // Qui a fermé la classe (traçabilité) ; NULL si le compte a été supprimé depuis.
            $table->foreignId('fermee_par')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

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