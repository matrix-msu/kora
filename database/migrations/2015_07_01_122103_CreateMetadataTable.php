<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMetadataTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('metadatas', function(Blueprint $table)
        {
			$table->engine = 'MyISAM';

           // $table->increments('mid');
            $table->integer('flid')->unsigned();

            $table->integer('pid')->unsigned();
            $table->integer('fid')->unsigned();

            $table->string('name');
            $table->timestamps();

            $table->foreign('flid')->references('flid')->on('fields')->onDelete('cascade');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('metadatas');
	}
}