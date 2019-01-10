<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecordsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{

	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{

	}

    /**
     * Dynamically creates a form's record table.
     *
     * @param
     * @return void
     */
	public function createFormRecordsTable($fid) {
        Schema::create("records_$fid", function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('kid');
            $table->string('legacy_kid')->nullable();
            $table->integer('project_id')->unsigned();
            $table->integer('form_id')->unsigned();
            $table->integer('owner')->unsigned();
            $table->timestamps();

            $table->foreign(['project_id', 'form_id'])->references(['id', 'id'])->on('forms')->onDelete('cascade');
        });
    }
}
