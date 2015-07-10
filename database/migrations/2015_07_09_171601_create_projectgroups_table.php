<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectgroupsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('projectgroups', function(Blueprint $table)
		{
			$table->increments('id');
            $table->string('name');
            $table->integer('pid')->unsigned();
            $table->boolean('create');
            $table->boolean('edit');
            $table->boolean('delete');
			$table->timestamps();

            $table->foreign('pid')->references('pid')->on('projects')->onDelete('cascade');
		});

        Schema::create('projectgroup_user', function(Blueprint $table)
        {
            $table->integer('projectgroup_id')->unsigned()->index();
            $table->foreign('projectgroup_id')->references('id')->on('projectgroups')->onDelete('cascade');

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
		Schema::drop('projectgroups');

        Schema::drop('projectgroup_user');
	}
}
