<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateSchedulefieldsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('schedule_fields', function(Blueprint $table)
		{
			$table->engine = 'MyISAM';

			$table->increments('id');

			$table->integer('rid')->unsigned();
			$table->integer('fid')->unsigned();
			$table->integer('flid')->unsigned();
			$table->timestamps();

			$table->foreign('rid')->references('rid')->on('records')->onDelete('cascade');
			$table->foreign('flid')->references('flid')->on('fields')->onDelete('cascade');
		});

		Schema::create('schedule_support', function(Blueprint $table)
		{
			$table->engine = "MyISAM";

			$table->increments('id');
			$table->integer('fid')->unsigned();
			$table->integer('rid')->unsigned();
			$table->integer('flid')->unsigned();
			$table->dateTime('begin');
			$table->dateTime('end');
			$table->boolean('allday');
			$table->text('desc');
			$table->timestamps();
		});

		DB::statement("ALTER TABLE ". config('database.connections.mysql.prefix') ."schedule_support ADD FULLTEXT search_supp(`desc`)");
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('schedule_fields');
		Schema::drop('schedule_support');
	}

}
