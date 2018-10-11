<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateComboListFieldsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('combo_list_fields', function(Blueprint $table)
		{
			$table->engine = 'MyISAM';

			$table->increments('id');

			$table->integer('rid')->unsigned();
			$table->integer('fid')->unsigned();
			$table->integer('flid')->unsigned();
			$table->timestamps();

			$table->foreign('rid')->references('rid')->on('records')->onDelete('cascade');
			$table->foreign('flid')->references('flid')->on('fields')->onDelete('cascade');
		});

		Schema::create('combo_support', function(Blueprint $table) {
			$table->engine = 'MyISAM';

			$table->increments('id');
			$table->integer('fid')->unsigned();
			$table->integer('rid')->unsigned();
			$table->integer('flid')->unsigned();
			$table->integer('list_index')->unsigned();
			$table->integer('field_num')->unsigned();
			$table->mediumText('data')->nullable();
			$table->decimal('number', 65, 30)->nullable(); // Max possible decimal value.
			$table->timestamps();
		});

		DB::statement("ALTER TABLE " . config('database.connections.mysql.prefix') . "combo_support ADD FULLTEXT search_sup(`data`)");
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('combo_list_fields');
		Schema::drop('combo_support');
	}

}
