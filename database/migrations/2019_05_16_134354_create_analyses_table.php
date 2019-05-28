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
            $table->string('name',45);
            $table->string('representation_type',25);
            $table->boolean('shared');
            $table->uuid('owner_uuid');
            $table->mediumText('description');
            $table->string('comment');
            $table->enum('visibility',['admin_only','job_referent','worker','all']);
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
