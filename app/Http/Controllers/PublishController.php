<?php namespace App\Http\Controllers;

use Illuminate\View\View;

class PublishController extends Controller {

    /*
    |--------------------------------------------------------------------------
    | Publish Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the Kora one-click publishing platform
    |
    */

    /**
     * Constructs controller and makes sure user is authenticated.
     */
    public function __construct() {
        $this->middleware('auth');
        $this->middleware('active');
    }

    /**
     * Were testing stuff, cause why not
     *
     * @return View
     */
    public function index() {
        return view("publish.index");
    }

}
