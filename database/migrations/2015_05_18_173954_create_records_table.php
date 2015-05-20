<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecordsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('records', function(Blueprint $table)
		{
			$table->increments('rid');
            $table->primary('rid');

            $table->string('kid');
            $table->integer('pid')->unsigned();
            $table->integer('fid')->unsigned();
            $table->string('owner');

            $table->foreign(['pid', 'fid'])->references(['pid', 'fid'])->on('forms')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('records');
	}

}
