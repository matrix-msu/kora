<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecordsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up() {}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down() {}

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
        });
    }

    public function removeFormRecordsTable($fid) {
        Schema::drop("records_$fid");
    }

    //TODO::NEWFIELD
    public function addTextColumn($fid, $slug) {
        Schema::table("records_$fid", function(Blueprint $table) use ($slug) {
            $table->text($slug)->nullable();
        });
    }

    public function renameColumn($fid, $slug, $newSlug) {
        Schema::table("records_$fid", function (Blueprint $table) use ($slug, $newSlug) {
            $table->renameColumn($slug,$newSlug);
        });
    }

    public function dropColumn($fid, $slug) {
        Schema::table("records_$fid", function (Blueprint $table) use ($slug) {
            $table->dropColumn($slug);
        });
    }
}
