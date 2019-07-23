<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSavedCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('saved_cards', function (Blueprint $table) {
            $table->Integer('id');
            $table->uuid('uuid');
            $table->tinyInteger('displayed');
            $table->integer('position');
            $table->integer('size')->nullable(true);
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
        Schema::dropIfExists('saved_cards');
    }
}
