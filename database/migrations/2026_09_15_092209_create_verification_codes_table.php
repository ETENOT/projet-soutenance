<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVerificationCodesTable extends Migration
{
    public function up()
    {
        // Table "technique", volontairement indépendante de la table users :
        // même structure et même rôle que password_resets (déjà présente dans
        // ce projet). Comme password_resets, elle n'apparaît pas dans le
        // diagramme de classe UML — ce sont toutes les deux des tables
        // d'infrastructure, pas des entités métier.
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