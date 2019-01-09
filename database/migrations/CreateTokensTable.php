<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTokensTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('tokens', function(Blueprint $table)
        {
            $table->engine = 'MyISAM';

            $table->increments('id');
            $table->string('token');
            $table->string('title');
            $table->boolean('search');
            $table->boolean('create');
            $table->boolean('edit');
            $table->boolean('delete');
            $table->timestamps();
        });

        //Project token pivot table.
        Schema::create('project_token', function(Blueprint $table)
        {
            $table->engine = 'MyISAM';

            $table->integer('project_pid')->unsigned()->index();
            $table->foreign('project_pid')->references('pid')->on('projects')->onDelete('cascade');

            $table->integer('token_id')->unsigned()->index();
            $table->foreign('token_id')->references('id')->on('tokens')->onDelete('cascade');
        });
}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::drop('project_token');
		Schema::drop('tokens');
	}
}