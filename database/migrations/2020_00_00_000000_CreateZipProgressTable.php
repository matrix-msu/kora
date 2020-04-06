<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateZipProgressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zip_progress', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('filename');
            $table->integer('files_finished')->default(0);
            $table->integer('total_files')->default(0);
            $table->boolean('finished')->default(0);
            $table->boolean('failed')->default(0);
            $table->string('message')->default('');
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
        Schema::dropIfExists('zip_progress');
    }
}
