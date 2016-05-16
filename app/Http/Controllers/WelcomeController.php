<?php namespace App\Http\Controllers;

use \Illuminate\Support\Facades\App;
Use \Illuminate\Support\Facades\Request;
use \Illuminate\Support\Facades\Session;
use \Illuminate\Support\Facades\Config;
use \Illuminate\Support\Facades\Artisan;

class WelcomeController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Welcome Controller
	|--------------------------------------------------------------------------
	|
	| This controller renders the "marketing page" for the application and
	| is configured to only allow guests. Like most of the other sample
	| controllers, you are free to modify or remove it as you desire.
	|
	*/

    //Constructor causing a redirect loop error, it is solved by commenting it out.
    //Error occurs only when a user is logged in so I assumed it was caused by
    //this middleware redirecting to 'guest' middleware. -Ian

//
//	/**
//	 * Create a new controller instance.
//	 *
//	 * @return void
//	 */
//	public function __construct()
//	{
//		$this->middleware('guest');
//	}

	/**
	 * Show the application welcome screen to the user.
	 *
	 * @return Response
	 */
	public function index(Request $request)
	{

		$languages_available = Config::get('app.locales_supported');
		$not_installed = true;
		if(!file_exists("../.env")){
			return view('welcome',compact('languages_available','not_installed'));
		}
		else{
			return view('welcome',compact('languages_available'));
		}
	}

    public  function setTemporaryLanguage(Request $request){
        $language = Request::input('templanguage');
        Session::put('guest_user_language',$language);
        return(trans('controller_welcome.visitor').$language);
    }
}