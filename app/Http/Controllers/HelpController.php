<?php namespace App\Http\Controllers;

use Illuminate\View\View;

class HelpController extends Controller {

    /*
    |--------------------------------------------------------------------------
    | Help Controller
    |--------------------------------------------------------------------------
    |
    | Will probably use for more, but now just returns the help page for search
    |
    */ //TODO::Is this class used, and do we need it? If so, add to the tech doc

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

    /**
     * This is for testing email layouts. Return view instead if you want to test. Also, I recommend creating a test
     * view that extends the 'email' view, for proper testing.
     *
     * @return View
     */
    public function emailTest() {
        return redirect('/');

        //return view("email");
    }
}