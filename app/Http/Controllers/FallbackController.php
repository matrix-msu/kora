<?php namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class FallbackController extends Controller { //TODO::CASTLE
    /**
     * Constructs controller and makes sure user is authenticated.
     */
    public function __construct() {
        $this->middleware('auth');
        $this->middleware('active');
    }
	
	public function routeNotFound(Request $request) {
		$install_admin = User::where('id','=',1)->first();
		return response()->view('errors.404', ['install_admin_email' => $install_admin->email], 404);
	}
	

}
