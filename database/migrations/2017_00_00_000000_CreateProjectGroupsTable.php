<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectGroupsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('project_groups', function(Blueprint $table)
		{
			$table->increments('id');
            $table->string('name',80);
            $table->integer('project_id')->unsigned();
            $table->boolean('create');
            $table->boolean('edit');
            $table->boolean('delete');
			$table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
		});

        Schema::create('project_group_user', function(Blueprint $table)
        {
			$table->integer('project_group_id')->unsigned()->index();
            $table->foreign('project_group_id')->references('id')->on('project_groups')->onDelete('cascade');

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
        Schema::drop('project_group_user');
		Schema::drop('project_groups');
	}
}
