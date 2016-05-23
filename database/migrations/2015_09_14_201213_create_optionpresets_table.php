<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOptionpresetsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('option_presets', function(Blueprint $table)
        {
			$table->engine = 'MyISAM';

            $table->increments('id');
            $table->integer('pid')->unsigned()->nullable();
            $table->string('type');
            $table->string('name');
            $table->mediumText('preset');
            $table->boolean('shared');
            $table->timestamps();

            $table->foreign('pid')->references('pid')->on('projects')->onDelete('cascade');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		//
        Schema::drop('option_presets');
	}

}
