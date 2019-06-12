<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDashboardTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dashboard_sections', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('order')->unsigned();
            $table->string('title');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('dashboard_blocks', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('section_id')->unsigned();
            $table->string('type');
            $table->integer('order')->unsigned();
            $table->text('options');
            $table->timestamps();

            $table->foreign('section_id')->references('id')->on('dashboard_sections')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('dashboard_blocks');
        Schema::drop('dashboard_sections');
    }
}
