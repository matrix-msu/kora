<?php

use \Illuminate\Support\Facades\Artisan;

class TestCase extends Illuminate\Foundation\Testing\TestCase {

	/**
	 * @var
	 */
	protected $baseUrl = "http://localhost";

	/**
	 * Creates the application.
	 *
	 * @return \Illuminate\Foundation\Application
	 */
	public function createApplication()
	{
		putenv("DB_DEFAULT=testing"); // Make sure our environment uses the SQLite database.

		$app = require __DIR__.'/../bootstrap/app.php';

		$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

		return $app;
	}

	/**
	 * Set up the database in memory for testing.
	 */
	public function setUp()
	{
		parent::setUp();
		Artisan::call('migrate');
	}

	/**
	 * Clean up memory.
	 */
	public function tearDown()
	{
		Artisan::call('migrate:reset');
		parent::tearDown();
	}

	/**
	 * Creates and returns a dummy project for testing.
	 *
	 * @return \App\Project, the dummy project.
	 */
	static protected function dummyProject() {
		$project = new App\Project();
		$project->name = "Test Project " . self::randomString();
		$project->slug = "ptest " . self::randomString();
		$project->description = "dummy";
		$project->adminGID = 1;
		$project->active = 1;
		$project->save();
		return $project;
	}

	/**
	 * Creates and returns a dummy form for testing.
	 *
	 * @param int $pid, optional project ID for the foreign key reference.
	 * @return \App\Form|null, Form if there is a project in the database, null otherwise.
	 */
	static protected function dummyForm($pid = 0) {
		if ($pid == 0) $anyProject = App\Project::where('pid', '>', 0)->first();
		else $anyProject = App\Project::where('pid', '=', $pid)->first();

		if($anyProject === null) {
			return null; // There must be a project in SQLite before we can make a form due to foreign keys.
		}
		$pid = $anyProject->pid;

		$form = new App\Form();
		$form->pid = $pid;
		$form->adminGID = 1;
		$form->name = "Test Form " . self::randomString();
		$form->slug = "ftest " . self::randomString();
		$form->description = "dummy";
		$form->layout = "<LAYOUT></LAYOUT>";
		$form->preset = 0;
		$form->public_metadata = 0;
		$form->save();
		return $form;
	}

	/**
	 * Creates and returns a dummy field for testing.
	 *
	 * @param string $type, type of the field to be created.
	 * @param int $pid, optional project id.
	 * @param int $fid, optional field id.
	 * @return \App\Field|null, Field if there is a field in the database, null otherwise.
	 */
	public function dummyField($type, $pid = 0, $fid = 0) {
		if ($pid == 0 && $fid == 0) {
			$anyProject = App\Project::where('pid', '>', 0)->first();
			if ($anyProject === null) {
				return null;
			}
			$anyForm = App\Form::where('pid', '=', $anyProject->pid)->first();
		}
		else if ($pid == 0 XOR $fid == 0) {
			return null; // Can't enter just one.
		}
		else {
			$anyProject = App\Project::where('pid', '=', $pid)->first();
			$anyForm = App\Form::where('fid', '=', $fid)->where('pid', '=', $anyProject->pid)->first();
		}

		if ($anyProject === null || $anyForm === null) {
			return null; // Project or form with the specified ID didn't exist.
		}

		if (!self::isValidFieldType($type)) {
			return null; // Must have a valid field type.
		}

		$pid = $anyProject->pid;
		$fid = $anyForm->fid;

		$field = new App\Field();
		$field->pid = $pid;
		$field->fid = $fid;
		$field->order = "";
		$field->type = $type;
		$field->name = "$type Field Name " . self::randomString();
		$field->slug = $type . "test " . self::randomString();
		$field->desc = "dummy";
		$field->required = 0;
		$field->searchable = 1;
		$field->save();

		return $field;
	}

	/**
	 * Creates and returns a dummy record for testing.
	 *
	 * @param int $pid, optional project id.
	 * @param int $fid, optional form id.
	 * @return \App\Record|null, returns Record if there is a project and form in the database already.
	 */
	public function dummyRecord($pid = 0, $fid = 0) {
		if ($pid == 0 && $fid == 0) {
			$anyProject = App\Project::where('pid', '>', 0)->first();
			if ($anyProject === null) {
				return null;
			}
			$anyForm = App\Form::where('pid', '=', $anyProject->pid)->first();
		}
		else if ($pid == 0 XOR $fid == 0) {
			return null; // Can't enter just one.
		}
		else {
			$anyProject = App\Project::where('pid', '=', $pid)->first();
			$anyForm = App\Form::where('fid', '=', $fid)->where('pid', '=', $anyProject->pid)->first();
		}

		if ($anyProject === null || $anyForm === null) {
			return null; // Project or form with the specified ID didn't exist.
		}

		$pid = $anyProject->pid;
		$fid = $anyForm->fid;

		$record = new App\Record();
		$record->pid = $pid;
		$record->fid = $fid;
		$record->kid = ""; // Can't be set yet.
		$record->owner = 1;
		$record->save();
		$record->kid = $record->pid . "-" . $record->fid . "-" . $record->rid;
		$record->save();

		return $record;
	}

	/**
	 * Determines the validity of a field type given.
	 *
	 * @param string $type, must determine validity of this.
	 * @return bool, true if the type was valid.
	 */
	static private function isValidFieldType($type) {
		return in_array($type, App\Field::$ENUM_TYPED_FIELDS);
	}

	/**
	 * Gets a random string to protect from naming collisions.
	 *
	 * @param int $len, optional string length.
	 * @return string
	 */
	static private function randomString($len = 10) {
		$valid = 'abcdefghijklmnopqrstuvwxyz';
		$valid .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$valid .= '0123456789';

		$token = '';
		for ($i = 0; $i < $len; $i++){
			$token .= $valid[( rand() % 62 )];
		}
		return $token;
	}

}
