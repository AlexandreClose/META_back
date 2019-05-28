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
            $table->integer('analysis_id');
            $table->uuid('user_uuid');
            $table->primary(['analysis_id','user_uuid']);
            $table->boolean('displayed');
            $table->integer('position');
            $table->integer('size')->nullable();
            $table->timestamps();
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
