<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->string('email_visiteur')->nullable()->after('user_id');
            //visiteur_token est un identifiant aléatoire temporaire qui représente un visiteur non connecté
            $table->string('visiteur_token', 64)->nullable()->index()->after('email_visiteur');
        });

        Schema::table('resultats_quiz', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Impossible de remettre NOT NULL s'il reste des lignes anonymes
        //DB::table('resultats_quiz')->whereNull('user_id')->delete();
        DB::table('quizzes')->whereNull('user_id')->delete();

        Schema::table('resultats_quiz', fn (Blueprint $t) => $t->unsignedBigInteger('user_id')->nullable(false)->change());
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropIndex(['visiteur_token']);
            $table->dropIndex(['email_visiteur']);
            $table->dropColumn(['email_visiteur', 'visiteur_token']);
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }
};
