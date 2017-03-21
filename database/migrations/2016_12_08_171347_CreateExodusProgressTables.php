<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExodusProgressTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exodus_overall_progress', function(Blueprint $table)
        {
            $table->engine = 'MyISAM';

            $table->increments('id');
            $table->integer('progress');
            $table->integer('overall');
            $table->dateTime('start');
            $table->timestamps();

        });

        Schema::create('exodus_partial_progress', function(Blueprint $table)
        {
            $table->engine = 'MyISAM';

            $table->increments('id');
            $table->string('name');
            $table->integer('progress');
            $table->integer('overall');
            $table->integer('exodus_id')->unsigned();
            $table->dateTime('start');
            $table->timestamps();

            $table->foreign('exodus_id')->references('id')->on('exodus_overall_progress')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('exodus_partial_progress');
        Schema::drop('exodus_overall_progress');
    }
}
