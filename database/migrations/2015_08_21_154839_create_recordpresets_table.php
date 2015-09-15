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
            $table->integer('fid')->unsigned();
            $table->integer('rid')->unsigned();
            $table->string('name');
			$table->timestamps();

            $table->foreign('fid')->references('fid')->on('forms')->onDelete('cascade');
            $table->foreign('rid')->references('rid')->on('records')->onDelete('cascade');
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
