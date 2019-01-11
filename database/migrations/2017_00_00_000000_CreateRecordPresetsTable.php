<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecordpresetsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('record_presets', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('form_id')->unsigned();
            $table->string('record_kid',20);
			$table->jsonb('preset');
			$table->timestamps();

            $table->foreign('form_id')->references('id')->on('forms')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('record_presets');
	}
}
