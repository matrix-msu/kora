<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class HelpController extends Controller {

    /*
    |--------------------------------------------------------------------------
    | Help Controller
    |--------------------------------------------------------------------------
    |
    | Will probably use for more, but now just returns the help page for search
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
     * Gets the view for the search help page.
     *
     * @return View
     */
    public function search() {
        return view("help.search");
    }
}