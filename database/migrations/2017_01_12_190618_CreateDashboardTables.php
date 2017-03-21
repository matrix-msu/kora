<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDashboardTables extends Migration
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
            $table->engine = 'MyISAM';

            $table->increments('id');
            $table->integer('uid');
            $table->integer('order');
            $table->string('title');
            $table->timestamps();

            $table->foreign('uid')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('dashboard_blocks', function(Blueprint $table)
        {
            $table->engine = 'MyISAM';

            $table->increments('id');
            $table->integer('bid');
            $table->string('type');
            $table->integer('order');
            $table->text('options');
            $table->timestamps();

            $table->foreign('bid')->references('id')->on('dashboard_sections')->onDelete('cascade');
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
