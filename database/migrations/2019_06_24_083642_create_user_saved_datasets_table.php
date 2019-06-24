<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserSavedDatasetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_saved_datasets', function (Blueprint $table) {
            $table->bigInteger('id');
            $table->uuid('uuid');
            $table->boolean('favorite');
            $table->timestamps();
            $table->primary(['id', 'uuid']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_saved_datasets');
    }
}
