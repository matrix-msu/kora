<?php namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class FallbackController extends Controller {

    /*
    |--------------------------------------------------------------------------
    | Fallback Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles routing of unknown routes to a custom 404 page
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
     * Bounces user to unknown page if route is invalid.
     */
	public function routeNotFound(Request $request) {
		$install_admin = User::where('id','=',1)->first();
		return response()->view('errors.404', ['install_admin_email' => $install_admin->email], 404);
	}
}
