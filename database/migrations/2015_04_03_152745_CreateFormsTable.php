<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFormsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('forms', function(Blueprint $table)
		{
			$table->engine = 'MyISAM';

			$table->increments('fid');
			$table->integer('pid')->unsigned();
            $table->integer('adminGID')->unsigned();
			$table->string('name');
			$table->string('slug')->unique();
			$table->string('description');
            $table->text('layout');
            $table->boolean('preset');
            $table->boolean('public_metadata');
            $table->string('lod_resource')->default('');
			$table->timestamps();

            $table->foreign('pid')->references('pid')->on('projects')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('forms');
	}

}
