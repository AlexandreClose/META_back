<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAnalysisHasColumnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('analysis_has_columns', function (Blueprint $table) {
            $table->integer('analysis_id');
            $table->integer('column_id');
            $table->string('usage',45);
            $table->integer('used_size');
            $table->string('map_filter',45)->nullable();
            $table->boolean('map_overlaps')->nullable();
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
        Schema::dropIfExists('analysis_has_columns');
    }
}
