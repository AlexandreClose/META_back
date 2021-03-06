<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAnalysesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('analyses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name',45)->unique();
            $table->string('representation_type',25);
            $table->boolean('shared');
            $table->boolean('isStats');
            $table->uuid('owner_id');
            $table->mediumText('description');
            $table->mediumText('body');
            $table->mediumText('usage');
            $table->string('visibility');
            $table->string('theme_name');
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
        Schema::dropIfExists('analyses');
    }
}
