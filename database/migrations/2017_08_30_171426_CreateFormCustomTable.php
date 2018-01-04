<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFormCustomTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('form_custom', function(Blueprint $table)
        {
            $table->engine = 'MyISAM';

            $table->increments('id');
            $table->integer('uid')->unsigned();
            $table->foreign('uid')->references('id')->on('users')->onDelete('cascade');
            $table->integer('pid')->unsigned();
            $table->foreign('pid')->references('pid')->on('projects')->onDelete('cascade');
            $table->integer('fid')->unsigned();
            $table->foreign('fid')->references('fid')->on('forms')->onDelete('cascade');
            $table->integer('sequence')->unsigned();
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
        Schema::drop('form_custom');
    }
}
