<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateRecordsTable extends Migration {

    public function __construct(array $arguments = array()) {
        $this->tablePrefix = "records_";
        if (!empty($arguments)) {
            foreach ($arguments as $property => $argument) {
                $this->{$property} = $argument;
            }
        }
    }

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
        Schema::create($this->tablePrefix . $fid, function(Blueprint $table)
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

    // TODO::ANDREW add drop for this
    public function createComboListTable($fid) {
        Schema::create($this->tablePrefix . $fid, function(Blueprint $table)
        {
            $table->increments('record_id');
        });
    }

    public function removeFormRecordsTable($fid) {
        Schema::drop($this->tablePrefix . $fid);
    }

    //TODO::NEWFIELD
    public function addTextColumn($fid, $slug) {
        Schema::table($this->tablePrefix . $fid, function(Blueprint $table) use ($slug) {
            $table->text($slug)->nullable();
        });
    }

    public function addIntegerColumn($fid, $slug) {
        Schema::table($this->tablePrefix . $fid, function(Blueprint $table) use ($slug) {
            $table->integer($slug)->nullable();
        });
    }

    // TODO::ANDREW add drop foreign key column
    public function addForeignKeyColumn($fid, $slug, $fTable, $fField) {
        Schema::table($this->tablePrefix . $fid, function(Blueprint $table) use ($fid, $slug, $fTable, $fField) {
            $table->unsignedInteger($slug);

            $table->foreign($slug)->references($fField)->on($fTable . $fid);
        });
    }

    /**
     * Creates a column with type double
     *
     * @param int $precision - total digits
     * @param int $scale - decimal digits
     */
    public function addDoubleColumn($fid, $slug, $precision = 15, $scale = 8) {
        Schema::table($this->tablePrefix . $fid, function(Blueprint $table) use ($slug, $precision, $scale) {
            $table->double($slug, $precision, $scale)->nullable();
        });
    }

    public function addJSONColumn($fid, $slug) {
        Schema::table($this->tablePrefix . $fid, function(Blueprint $table) use ($slug) {
            $table->jsonb($slug)->nullable();
        });
    }

    public function addMediumTextColumn($fid, $slug) {
        Schema::table($this->tablePrefix . $fid, function(Blueprint $table) use ($slug) {
            $table->mediumText($slug)->nullable();
        });
    }

    public function addEnumColumn($fid, $slug, $list = ['Please Modify List Values']) {
        DB::statement(
            'alter table ' . DB::getTablePrefix() . $this->tablePrefix . $fid . ' add ' . $slug . ' enum("' . implode('","', $list) . '")'
        );
    }

    public function renameColumn($fid, $slug, $newSlug) {
        // Workaround for enums
        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        Schema::table($this->tablePrefix . $fid, function (Blueprint $table) use ($slug, $newSlug) {
            $table->renameColumn($slug,$newSlug);
        });
    }

    public function renameEnumColumn($fid, $slug, $newSlug) {
        Schema::table($this->tablePrefix . $fid, function (Blueprint $table) use ($slug, $newSlug) {
            $table->renameColumn($slug,$newSlug);
        });
    }

    public function dropColumn($fid, $slug) {
        Schema::table($this->tablePrefix . $fid, function (Blueprint $table) use ($slug) {
            $table->dropColumn($slug);
        });
    }

    public function dropForeignKeyColumn($fid, $slug) {
        Schema::table($this->tablePrefix . $fid, function (Blueprint $table) use ($slug) {
            $table->dropForeign([$slug]);
        });
    }

    public function updateEnum($fid, $slug, $list) {
        DB::statement(
            'alter table ' . DB::getTablePrefix() . $this->tablePrefix . $fid . ' modify column ' . $slug . ' enum("' . implode('","', $list) . '")'
        );
    }
}
