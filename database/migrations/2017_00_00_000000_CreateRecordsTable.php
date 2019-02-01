<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

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
            $table->boolean('is_test')->unsigned();
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

    public function addJSONColumn($fid, $slug) {
        Schema::table("records_$fid", function(Blueprint $table) use ($slug) {
            $table->jsonb($slug)->nullable();
        });
    }

    public function addMediumTextColumn($fid, $slug) {
        Schema::table("records_$fid", function(Blueprint $table) use ($slug) {
            $table->mediumText($slug)->nullable();
        });
    }

    public function addEnumColumn($fid, $slug, $list = ['Please Modify List Values']) {
        Schema::table("records_$fid", function(Blueprint $table) use ($slug, $list) {
            $table->enum($slug, $list)->nullable();
        });
    }

    public function renameColumn($fid, $slug, $newSlug) {
        // Workaround for enums, exists for sets as well.
        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        Schema::table("records_$fid", function (Blueprint $table) use ($slug, $newSlug) {
            $table->renameColumn($slug,$newSlug);
        });
    }

    public function renameEnumColumn($fid, $slug, $newSlug) {
        Schema::table("records_$fid", function (Blueprint $table) use ($slug, $newSlug) {
            $table->renameColumn($slug,$newSlug);
        });
    }

    public function dropColumn($fid, $slug) {
        Schema::table("records_$fid", function (Blueprint $table) use ($slug) {
            $table->dropColumn($slug);
        });
    }

    public function updateEnum($fid, $slug, $list) {
        DB::statement(
            'alter table ' . DB::getTablePrefix() . 'records_' . $fid . ' modify column ' . $slug . ' enum("' . implode('","', $list) . '")'
        );
    }
}
