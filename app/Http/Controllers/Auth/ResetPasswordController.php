<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use App\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Contracts\Hashing;
use Illuminate\Support\Facades\Validator;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }
	
	/**
     * Display the password reset view for the given token.
     *
     * If no token is present, display the link request form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string|null  $token
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showResetForm(Request $request, $token = null)
    {
		// find which hashed token in the database can result from the given plaintext token
		$app_hasher = app()['hash'];
		$entries = DB::table('password_resets')->get();
		
		$hashed_token = "";
		foreach ($entries as $entry) {
			$hash_check = $app_hasher->check($token, $entry->token);
			
			if ($hash_check !== null && $hash_check == 1) {
				$request->email = $entry->email;
				break;
			}
		}
		
        return view('auth.passwords.reset')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }
	
	public function sendResetResponse($response)
    {
		$projectCollections = Project::all()->sortBy("name", SORT_NATURAL|SORT_FLAG_CASE);
        $projects = array();
        $inactive = array();
        $custom = array();
        $pSearch = array();
        $hasProjects = false;
        $requestableProjects = array();
        foreach($projectCollections as $project) {
            if(\Auth::user()->admin || \Auth::user()->inAProjectGroup($project)) {
                if($project->active) {
                    array_push($projects, $project);
                    array_push($pSearch, $project);
                    $seq = \Auth::user()->getCustomProjectSequence($project->pid);
                    if($seq == null) {
                        \Auth::user()->addCustomProject($project->pid);
                        $seq = \Auth::user()->getCustomProjectSequence($project->pid);
                    }
                    $custom[$seq] = $project;
                } else {
                    array_push($inactive, $project);
                    array_push($pSearch, $project);
                }
                $hasProjects = true;
            } else if($project->active) {
                $requestableProjects[$project->pid] = $project->name. " (" . $project->slug.")";
            }
        }
		
        //We need to sort the custom array
        ksort($custom);
        $notification = array(
          'message' => 'Password Successfully Reset!',
          'description' => '',
          'warning' => false,
          'static' => false
        );

        return view('projects.index', compact('projects', 'inactive', 'custom', 'pSearch', 'hasProjects', 'requestableProjects', 'notification'));
    }
	
    public function checkEmailInDB(Request $request)
    {
        $validator = Validator::make($request->all(), [
           'email' => 'required|email',
        ]);
        
        if ($validator->fails()) {
            return "Invalid";
        }
		
		$user = User::where('email', '=', $request->email)->first();
		if ($user !== null) {
			return "Exists";
		} else {
			return "Not Exists";
		}
    }
	
}
