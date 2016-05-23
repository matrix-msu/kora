<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBackupProgressTables extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('backup_overall_progress', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('progress');
			$table->integer('overall');
			$table->dateTime('start');
			$table->timestamps();

		});

		Schema::create('backup_partial_progress', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name');
			$table->integer('progress');
			$table->integer('overall');
			$table->integer('backup_id')->unsigned();
			$table->dateTime('start');
			$table->timestamps();
			
			$table->foreign('backup_id')->references('id')->on('backup_overall_progress')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('backup_partial_progress');
		Schema::drop('backup_overall_progress');
	}

}
