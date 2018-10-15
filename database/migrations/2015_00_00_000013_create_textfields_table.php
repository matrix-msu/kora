<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateTextfieldsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('text_fields', function(Blueprint $table)
		{
			$table->engine = 'MyISAM';

            $table->increments('id');

            $table->integer('rid')->unsigned();
			$table->integer('fid')->unsigned();
            $table->integer('flid')->unsigned();
            $table->text('text');
			$table->timestamps();

            $table->foreign('rid')->references('rid')->on('records')->onDelete('cascade');
            $table->foreign('flid')->references('flid')->on('fields')->onDelete('cascade');
		});

		DB::statement("ALTER TABLE ". config('database.connections.mysql.prefix') ."text_fields ADD FULLTEXT search(`text`)");
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table("text_fields", function($table) {
			$table->dropIndex("search");
		});
		Schema::drop('text_fields');
	}

}
