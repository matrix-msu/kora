<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTextfieldsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('textfields', function(Blueprint $table)
		{
            $table->increment('id');
            $table->primary('id');

            $table->integer('rid')->unsigned();
            $table->integer('flid')->unsigned();
            $table->mediumText('text');
			$table->timestamps();

            $table->foreign('rid')->references('rid')->on('records')->onDelete('cascade');
            $table->foreign('flid')->references('flid')->on('fields')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('textfields');
	}

}
