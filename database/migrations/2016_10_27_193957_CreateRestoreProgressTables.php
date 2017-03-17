<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRestoreProgressTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('restore_overall_progress', function(Blueprint $table)
        {
            $table->engine = 'MyISAM';

            $table->increments('id');
            $table->integer('progress');
            $table->integer('overall');
            $table->dateTime('start');
            $table->timestamps();

        });

        Schema::create('restore_partial_progress', function(Blueprint $table)
        {
            $table->engine = 'MyISAM';

            $table->increments('id');
            $table->string('name');
            $table->integer('progress');
            $table->integer('overall');
            $table->integer('restore_id')->unsigned();
            $table->dateTime('start');
            $table->timestamps();

            $table->foreign('restore_id')->references('id')->on('restore_overall_progress')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('restore_partial_progress');
        Schema::drop('restore_overall_progress');
    }
}
