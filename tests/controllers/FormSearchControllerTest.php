<?php

use App\Http\Controllers\FormSearchController as FormSearchController;

/**
 * Class FormSearchControllerTest
 * @group search
 */
class FormSearchControllerTest extends TestCase
{
    /**
     * Instead of instantiating it a bunch of times, we'll use just one static
     * FormSearchController for the whole test.
     *
     * @type FormSearchController
     */
    static $controller;

    /**
     * FormSearchControllerTest constructor.
     * Initializes the $controller static object for the test.
     */
    public function __construct() {
        parent::__construct();
        self::$controller = new FormSearchController();
    }

    public function test_keywordRoutine() {

    }

}