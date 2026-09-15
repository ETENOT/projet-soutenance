<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVerificationCodesTable extends Migration
{
    public function up()
    {
        // Même structure que password_resets (déjà dans ce projet) : table
        // "technique" indépendante de users, hors du diagramme de classe UML —
        // même statut que password_resets, jamais remise en question là-bas.
        Schema::create('verification_codes', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('code');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('verification_codes');
    }
}