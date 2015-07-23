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
            $table->increments('flid');
            $table->integer('pid')->unsigned();
            $table->integer('fid')->unsigned();
            $table->string('order');
            $table->string('type');
            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('desc')->nullable();
            $table->boolean('required');
            $table->string('default')->nullable();
            $table->string('options')->nullable();
            $table->boolean('recentupdate');
			$table->timestamps();

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
		Schema::drop('fields');
	}

}
