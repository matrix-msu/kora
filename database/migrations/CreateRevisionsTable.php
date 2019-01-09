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
			$table->engine = 'MyISAM';

			$table->increments('id');
            $table->integer('fid')->unsigned();
            $table->integer('rid')->unsigned();
            $table->integer('owner')->unsinged();
            $table->string('username');
            $table->string('type');
            $table->binary('data');
            $table->binary('oldData');
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
