<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            //pour enregistrer la date et l'heure auxquelles l'adresse e-mail d'un utilisateur a été vérifiée.
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->text('avatar')->nullable();
            $table->foreignId('role_id')
                ->constrained('roles')
                // Empêche la suppression d'un rôle s'il est encore utilisé par un utilisateur dans cette table.
                ->restrictOnDelete();

            // 1-1 : un particulier ne peut être rattaché qu'à un seul utilisateur
            $table->foreignId('particulier_id')
                ->nullable()
                ->unique()
                ->constrained('particuliers')
                // Si le particulier est supprimé, "particulier_id" sera automatiquement mis à NULL.
                ->nullOnDelete();

            // 1-n : plusieurs utilisateurs (contacts) peuvent représenter la même entreprise
            $table->foreignId('entreprise_id')
                ->nullable()
                ->constrained('entreprises')
                // Si le entreprise est supprimé, "entreprise_id" sera automatiquement mis à NULL.
                ->nullOnDelete();

            //elle sert à mémoriser la connexion d'un utilisateur.
            $table->rememberToken();
            $table->timestamps();
        });
        //à effacer lors de la mise en production, juste pour les tests
        User::create(['name' => 'admin','email' => 'admin@themesbrand.com','password' => Hash::make('12345678'),'email_verified_at'=>'2022-01-02 17:04:58','avatar' => 'avatar-1.jpg','created_at' => now(), 'role_id' => '1']);
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    // Supprime la table "users" si elle existe.
    // Cette méthode est appelée lorsque l'on annule (rollback)
    // la migration avec : php artisan migrate:rollback
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
