<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateMultiselectlistfieldsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('multi_select_list_fields', function(Blueprint $table)
		{
			$table->engine = 'MyISAM';

			$table->increments('id');

			$table->integer('rid')->unsigned();
			$table->integer('fid')->unsigned();
			$table->integer('flid')->unsigned();
			$table->mediumText('options');
			$table->timestamps();

			$table->foreign('rid')->references('rid')->on('records')->onDelete('cascade');
			$table->foreign('flid')->references('flid')->on('fields')->onDelete('cascade');
		});

		DB::statement("ALTER TABLE ". config('database.connections.mysql.prefix') ."multi_select_list_fields ADD FULLTEXT search_msl(`options`)");
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table("multi_select_list_fields", function($table) {
			$table->dropIndex("search_msl");
		});
		Schema::drop('multi_select_list_fields');
	}

}
