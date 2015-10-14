<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAssociationsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('associations', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('dataForm')->unsigned();
			$table->integer('assocForm')->unsigned();
			$table->timestamps();

			$table->foreign('dataForm')->references('fid')->on('forms')->onDelete('cascade');
			$table->foreign('assocForm')->references('fid')->on('forms')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('associations');
	}

}
