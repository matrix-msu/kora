<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

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
			$table->engine = 'MyISAM';

            $table->increments('id');

            $table->integer('rid')->unsigned();
            $table->integer('flid')->unsigned();
			$table->integer('fid')->unsigned();
            $table->longText('rawtext');
			$table->longText('searchable_rawtext');
            $table->timestamps();

            $table->foreign('rid')->references('rid')->on('records')->onDelete('cascade');
            $table->foreign('flid')->references('flid')->on('fields')->onDelete('cascade');
		});

		DB::statement("ALTER TABLE ". config('database.connections.mysql.prefix') ."rich_text_fields ADD FULLTEXT search(searchable_rawtext)");
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table("rich_text_fields", function($table) {
			$table->dropIndex("search");
		});
		Schema::drop('rich_text_fields');
	}

}
