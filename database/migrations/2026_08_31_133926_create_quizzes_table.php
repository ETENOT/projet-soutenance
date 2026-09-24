<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();

            // "date" = la date à laquelle la tentative a eu lieu (mise à "aujourd'hui" au démarrage)
            $table->date('date');

            // "heure_debut" = l'heure exacte à laquelle le particulier a démarré sa tentative
            $table->time('heure_debut');

            // "heure_fin" = heure_debut + 1h, calculée et figée dès le démarrage
            // (c'est cette valeur qui sert de "deadline" pour la clôture automatique)
            $table->time('heure_fin');

            // Barème choisi par celui qui crée la tentative (ex: /20, /100...)
            // default(20) : valeur utilisée tant qu'on ne laisse pas le choix à l'utilisateur
            $table->unsignedSmallInteger('bareme')->default(20);

            $table->foreignId('cours_id')
                ->constrained('cours')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    // Supprime la table "quizzes" si elle existe.
    // Cette méthode est appelée lorsque l'on annule (rollback)
    // la migration avec : php artisan migrate:rollback
    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};