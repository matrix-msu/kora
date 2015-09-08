<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFormGroupsTable extends Migration {

	/**
	 * Run the migrations.
     *
	 * @return void
	 */
	public function up()
	{
		Schema::create('form_groups', function(Blueprint $table)
		{
			$table->increments('id');
            $table->string('name');
            $table->integer('fid')->unsigned();
            $table->boolean('create');
            $table->boolean('edit');
            $table->boolean('delete');
            $table->boolean('ingest');
            $table->boolean('modify');
            $table->boolean('destroy');
			$table->timestamps();

            $table->foreign('fid')->references('fid')->on('forms')->onDelete('cascade');
		});

        Schema::create('form_group_user', function(Blueprint $table)
        {
           $table->integer('form_group_id')->unsigned()->index();
           $table->foreign('form_group_id')->references('id')->on('form_groups')->onDelete('cascade');

           $table->integer('user_id')->unsigned()->index();
           $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::drop('form_group_user');
		Schema::drop('form_groups');


	}
}
