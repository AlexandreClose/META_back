<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuthUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //Table de liaison entre datasets et user permettant a un utilisateur précis d'avoir acces a un dataset
        Schema::create('auth_users', function (Blueprint $table) {
            $table->uuid('uuid');
            $table->integer('id');
            $table->timestamps();
            $table->primary(['uuid', 'id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('auth_users');
    }
}
