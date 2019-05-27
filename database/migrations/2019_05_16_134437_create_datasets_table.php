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
            $table->string('name',45);
            $table->boolean('validated');
            $table->string('datastore',45);
            $table->string('description');
            $table->string('creator',45);
            $table->string('contributor',45);
            $table->string('license',45);
            $table->dateTime('created_date');
            $table->dateTime('updated_date');
            $table->boolean('conf_ready')->default(false);
            $table->boolean('upload_ready')->default(false);
            $table->enum('visibility',['admin_only','job_referent','worker','all']);
            $table->string('user',45);
            $table->string('producer',45);
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
