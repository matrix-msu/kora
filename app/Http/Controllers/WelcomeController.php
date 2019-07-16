<?php namespace App\Http\Controllers;

use App\Http\Controllers\Auth\UserController;
Use \Illuminate\Support\Facades\Request;
use \Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class WelcomeController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Welcome Controller
	|--------------------------------------------------------------------------
	|
	| This controller renders the Home pages for the application and
	| is configured to only allow guests.
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
			if($session == 'We have e-mailed your password reset link!') {
				$notification['message'] = 'Check your email!';
				$notification['description'] ='The password reset link has successfully been sent!';
				$notification['static'] = true;
			} else if($session == 'user_activate_resent') {
				$notification['message'] = 'Another email has been sent!';
				$notification['static'] = true;
			} else if($session == 'activation_email_sent') {
				$notification['message'] = 'Registration successful! Activation email sent.';
				$notification['static'] = true;
			} else if($session == 'activation_email_failed') {
				$notification['message'] ='Registration successful, but activation email failed.';
				$notification['description'] ='Have the activation email resent, or contact your kora administrator for help.';
				$notification['warning'] = true;
				$notification['static'] = true;
			}

			return view('/welcome', compact('notification'));
		} else if (!\Auth::user()->active) {
			$notification = array(
				'message' => '',
				'description' => '',
				'warning' => false,
				'static' => false
			);

			$session = session()->get('status');
			if($session == 'activation_email_sent') {
				$notification['message'] = 'Registration successful! Activation email sent.';
				$notification['static'] = false;
			} else if($session == 'activation_email_failed') {
				$notification['message'] ='Registration successful, but activation email failed.';
				$notification['description'] ='Have the activation email resent, or contact your kora administrator for help.';
				$notification['warning'] = true;
				$notification['static'] = true;
			} else if($session == 'user_activate_resent') {
				$notification['message'] = 'Another email has been sent!';
				$notification['static'] = false;
			} else if($session == 'bad_activation_token') {
				$notification['message'] = 'Invalid activation token provided!';
				$notification['warning'] = true;
				$notification['static'] = false;
			}

			return view('/auth/activate', compact('notification'));
		} else {
			if(UserController::returnUserPrefs('logo_target') == 1 && UserController::returnUserPrefs('use_dashboard'))
				return redirect('/dashboard');
			else
				return redirect('/projects');
		}
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
