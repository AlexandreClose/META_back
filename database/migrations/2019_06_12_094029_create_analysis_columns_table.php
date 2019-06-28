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
            $table->bigInteger('column_id');
            $table->bigInteger('analysis_id');
            $table->string('color_code');
            $table->string('usage');
            $table->timestamps();
            $table->primary(['column_id', 'analysis_id']);
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
