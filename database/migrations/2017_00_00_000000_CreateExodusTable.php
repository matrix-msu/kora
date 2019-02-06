<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExodusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exodus_overall', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('progress')->unsigned();
            $table->integer('total_forms')->unsigned();
            $table->timestamps();

        });

        Schema::create('exodus_partial', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('name',60);
            $table->integer('progress')->unsigned();
            $table->integer('total_records')->unsigned();
            $table->integer('exodus_id')->unsigned();
            $table->timestamps();

            $table->foreign('exodus_id')->references('id')->on('exodus_overall')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('exodus_partial');
        Schema::drop('exodus_overall');
    }
}
