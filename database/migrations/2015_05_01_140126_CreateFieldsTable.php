<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFieldsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('fields', function(Blueprint $table)
		{
			$table->engine = 'MyISAM';

            $table->increments('flid');
            $table->integer('pid')->unsigned();
            $table->integer('fid')->unsigned();
            $table->integer('page_id')->unsigned();
            $table->integer('sequence')->unsigned();
            $table->string('type');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('desc');
            $table->boolean('required');
			$table->boolean('searchable');
			$table->boolean('extsearch');
			$table->boolean('viewable');
			$table->boolean('viewresults');
			$table->boolean('extview');
            $table->text('default')->nullable();
            $table->text('options')->nullable();
			$table->timestamps();

            $table->foreign(['pid', 'fid'])->references(['pid', 'fid'])->on('forms')->onDelete('cascade');
            $table->foreign('page_id')->references('id')->on('pages')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('fields');
	}

}
