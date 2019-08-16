<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAnalysisColumnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('analysis_columns', function (Blueprint $table) {
            $table->bigInteger('analysis_id');
            $table->string('field');
            $table->string('databaseName');
            $table->string('color_code')->nullable;
            $table->string('usage');
            $table->timestamps();
            $table->primary(['field', 'analysis_id', 'databaseName']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('analysis_columns');
    }
}
