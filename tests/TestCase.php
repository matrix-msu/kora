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
		Artisan::call('migrate:refresh');
	}

	/**
	 * Clean up memory.
	 */
	public function tearDown()
	{
		Artisan::call('migrate:reset');
		parent::tearDown();
	}

}
