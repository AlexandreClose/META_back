<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDatasetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('datasets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->unique();
            $table->string('databaseName', 48)->unique();
            $table->boolean('validated');
            $table->longText('description');
            $table->string('creator',45);
            $table->string('contributor',45);
            $table->string('license',45);
            $table->dateTime('created_date');
            $table->dateTime('updated_date');
            $table->boolean('realtime');
            $table->boolean('conf_ready')->default(false);
            $table->boolean('upload_ready')->default(false);
            $table->boolean('open_data')->default(false);
            $table->string('visibility');
            $table->string('user',45);
            $table->boolean('JSON')->default(false);
            $table->boolean('GEOJSON')->default(false);
            //$table->boolean('util')->default(false);
            $table->string('producer',45);
            $table->string('themeName');
            $table->string('update_frequency')->default("Jamais");
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
        Schema::dropIfExists('datasets');
    }
}
