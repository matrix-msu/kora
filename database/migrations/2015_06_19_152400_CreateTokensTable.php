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
            $table->increments('id');
            $table->string('token');
            $table->string('type');
            $table->timestamps();
        });

        //Project token pivot table.
        Schema::create('project_token', function(Blueprint $table)
        {
            $table->integer('project_id')->unsigned()->index();
            $table->foreign('project_id')->references('pid')->on('projects')->onDelete('cascade');

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
		Schema::drop('tokens');

        Schema::drop('project_token');
	}
}