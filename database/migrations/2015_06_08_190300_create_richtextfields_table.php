<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRichtextfieldsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('rich_text_fields', function(Blueprint $table)
		{
            $table->increments('id');

            $table->integer('rid')->unsigned();
            $table->integer('flid')->unsigned();
            $table->longText('rawtext');
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
		Schema::drop('richtext_fields');
	}

}
