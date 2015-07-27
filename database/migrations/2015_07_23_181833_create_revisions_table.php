<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRevisionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('revisions', function(Blueprint $table)
		{
			$table->increments('id');
            $table->integer('fid')->unsigned();
            $table->integer('rid')->unsigned();
            $table->integer('userId')->unsigned();
            $table->string('type');
            $table->binary('data');
            $table->boolean('rollback');
			$table->timestamps();

            $table->foreign('fid')->references('fid')->on('forms')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('revisions');
	}

}
