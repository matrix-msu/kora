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

        Schema::create('reverse_associator_cache', function(Blueprint $table)
        {
            $table->string('associated_kid');
            $table->string('source_kid');
            $table->string('source_flid',60);
            $table->integer('source_form_id')->unsigned();
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
		Schema::drop('reverse_associator_cache');
	}

}
