<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pages', function(Blueprint $table)
        {
            $table->engine = 'MyISAM';

            $table->increments('id');
            $table->integer('fid')->unsigned();
            $table->string('title');
            $table->integer('sequence')->unsigned();
            $table->timestamps();

            $table->foreign('fid')->references('fid')->on('forms')->onDelete('cascade');
        });

        //Project token pivot table.
        Schema::create('page_field', function(Blueprint $table)
        {
            $table->engine = 'MyISAM';

            $table->integer('page_id')->unsigned()->index();
            $table->foreign('page_id')->references('id')->on('pages')->onDelete('cascade');

            $table->integer('flid')->unsigned()->index();
            $table->foreign('flid')->references('flid')->on('fields')->onDelete('cascade');

            $table->integer('sequence')->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('page_field');
        Schema::drop('pages');
    }
}
