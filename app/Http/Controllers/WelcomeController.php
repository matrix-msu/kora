<?php namespace App\Http\Controllers;

Use \Illuminate\Support\Facades\Request;
use \Illuminate\Support\Facades\Session;
use Illuminate\View\View;

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

    /**
     * Gets the correct view based on current user.
     *
     * @return View
     */
	public function index() {
	    if(!isInstalled())
        	return redirect('/helloworld');
        else if(\Auth::guest()) {
          $notification = array(
            'message' => '',
            'description' => '',
            'warning' => false,
            'static' => false
          );

          $session = session()->get('status');
          if ($session == 'We have e-mailed your password reset link!') {
            $notification['message'] = 'Check your email! The password reset link has succesfully been sent!';
            $notification['static'] = true;
          } else if ($session == 'user_activate_resent') {
            $notification['message'] = 'Another email has been sent!';
            $notification['static'] = true;
          }

          return view('/welcome', compact('notification'));
        }
        else if (!\Auth::user()->active)
          return view('/auth/activate');
        else if(\Auth::user()->dash)
        	return redirect('/dashboard');
        else
        	return redirect('/projects');
	}

    /**
     * Gets the view for successful installation.
     *
     * @return View
     */
	public function installSuccess() {
        if(!isInstalled())
            return redirect('/helloworld');
        else {
          return view('install.success');
        }
    }

    /**
     * Allows guest users to switch language.
     *
     * @return string - Success response
     */
    public function setTemporaryLanguage() {
        $language = Request::input('templanguage');
        Session::put('guest_user_language',$language);

        return response()->json(["status"=>true,"message"=>"global_language_updated","language"=>$language],200);
    }
}
