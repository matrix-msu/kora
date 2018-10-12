<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateDocumentsfieldsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('documents_fields', function(Blueprint $table)
		{
			$table->engine = 'MyISAM';

			$table->increments('id');

			$table->integer('rid')->unsigned();
			$table->integer('fid')->unsigned();
			$table->integer('flid')->unsigned();
			$table->mediumText('documents');
			$table->timestamps();

			$table->foreign('rid')->references('rid')->on('records')->onDelete('cascade');
			$table->foreign('flid')->references('flid')->on('fields')->onDelete('cascade');
		});

		DB::statement("ALTER TABLE ". config('database.connections.mysql.prefix') ."documents_fields ADD FULLTEXT search_doc(`documents`)");
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table("documents_fields", function($table) {
			$table->dropIndex("search_doc");
		});

		Schema::drop('documents_fields');
	}

}
