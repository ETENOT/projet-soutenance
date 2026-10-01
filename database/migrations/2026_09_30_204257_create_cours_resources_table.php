<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cours_resources', function (Blueprint $table) {
            $table->id();

            // cascadeOnDelete : si le cours est supprimé, ses documents le sont aussi
            $table->foreignId('cours_id')->constrained()->cascadeOnDelete();

            // 'fichier' (PDF, Word...) ou 'video' (lien externe YouTube/Vimeo)
            $table->string('type')->default('fichier');

            // Nom lisible affiché à l'utilisateur (distinct du nom réel du fichier sur le disque)
            $table->string('titre');

            // Chemin relatif sur le disque 'public' (ex: cours_resources/3/xxxxx.pdf)
            // stocké tel que retourné par Storage::store(), jamais par l'utilisateur.
            // Nullable : une entrée 'video' n'a pas de fichier physique.
            $table->string('chemin')->nullable();

            // Lien externe, rempli uniquement quand type = 'video'
            $table->string('url')->nullable();

            // Extension d'origine, pour reconstituer le nom de téléchargement et choisir une icône
            // Nullable pour les mêmes raisons que 'chemin'
            $table->string('extension', 10)->nullable();

            // Taille en octets, affichée dans la liste des documents. Nullable pour une vidéo.
            $table->unsignedBigInteger('taille')->nullable();

            $table->timestamps();

            $table->foreignId('chapitre_id')
                ->nullable()
                ->constrained('chapitres')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cours_resources');
    }
};