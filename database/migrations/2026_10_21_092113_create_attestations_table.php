<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Une attestation = UN document délivré à UN stagiaire pour UNE inscription payée
        // dont le taux de présence a atteint le seuil (voir ClasseClotureService).
        //
        // Les informations affichées sur le PDF (nom, cours, dates, lieu, formateur...) sont
        // COPIÉES ici au moment de la délivrance : si l'admin renomme un cours ou change le
        // formateur de la classe plus tard, l'attestation déjà délivrée ne change pas.
        Schema::create('attestations', function (Blueprint $table) {
            $table->id();

            // Une seule attestation par inscription (unique = pas de doublon)
            $table->foreignId('inscription_id')
                ->unique()
                ->constrained('inscriptions')
                ->cascadeOnDelete();

            // Raccourcis pour filtrer sans passer par l'inscription
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('classe_id')->constrained('classes')->cascadeOnDelete();

            // Numéro lisible imprimé sur le document, ex. ATT-2026-C12-U45
            $table->string('numero')->unique();

            // Copie figée des informations du document
            $table->string('beneficiaire');
            $table->string('intitule');
            $table->date('date_debut');
            $table->date('date_fin');
            $table->string('lieu')->nullable();
            $table->text('contenu')->nullable();
            $table->string('formateur')->nullable();
            $table->decimal('taux_presence', 5, 2);

            // Chemin du PDF sur le disque "local" (storage/app), privé : jamais servi directement
            $table->string('chemin_pdf')->nullable();

            // dateTime() et non timestamp() : évite le décalage d'1h avec now() (Gabon = UTC+1)
            $table->dateTime('envoyee_le')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attestations');
    }
};