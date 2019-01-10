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
			$table->integer('data_form')->unsigned();
			$table->integer('assoc_form')->unsigned();
			$table->timestamps();

			$table->foreign('data_form')->references('id')->on('forms')->onDelete('cascade');
			$table->foreign('assoc_form')->references('id')->on('forms')->onDelete('cascade');
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
