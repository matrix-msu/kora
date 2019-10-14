<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
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
        $this->middleware('install');
        $this->middleware('guest', ['except' => 'logout']);
    }

    /**
     * Redirect the user to the Gitlab authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToGitlab() {
        return Socialite::driver('gitlab')->scopes(['read_user'])->redirect();
    }

    /**
     * Obtain the user information from Gitlab.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleGitlabCallback() {
        $user = Socialite::driver('gitlab')->user();

        //Check to see if user exists
        $koraUserByEmail = User::where('email','=',$user->email)->first();

        if( !is_null($koraUserByEmail) && Hash::check($user->token, $koraUserByEmail->gitlab_token) ) {
            //Found a user, and token matched a user in the DB
            Auth::login($koraUserByEmail);

            return redirect('/home');
        } else {
            $koraUserByName  = User::where('username','=',$user->nickname)->first();

            if(!is_null($koraUserByEmail)) {
                dd("user with email already exists!!!");
                //User has same email as another user that existed previously
                //TODO
            } else if(!is_null($koraUserByName)) {
                dd("user with username already exists!!!");
                //User has same username as another user that existed previously
                //TODO
            } else {
                //Create user and then force them to edit user page
                $newKoraUser = $this->createNewUserFromOAuth($user->nickname,$user->email,'gitlab_token',$user->token);

                Auth::login($newKoraUser);

                return redirect('/user/'.$newKoraUser->id.'/edit')->with('k3_global_success', 'gitlab_user_created');
            }
        }
    }

    /**
     * Create user and assign OAuth token value to it.
     *
     * @param  string $username - Username from OAuth account
     * @param  string $email - Email from OAuth account
     * @param  string $client - OAuth database column to assign to
     * @param  string $token - Authentication token from OAuth account
     * @return User - The new user
     */
    private function createNewUserFromOAuth($username, $email, $client, $token) {
        $user = new User;
        $user->username = $username;
        $user->email = $email;
        $user->active = 1;
        $password = uniqid();
        $user->password = bcrypt($password);
        $user->{$client} = Hash::make($token);
        $regtoken = RegisterController::makeRegToken();
        $user->regtoken = $regtoken;

        $preferences = [];
        $preferences['created_at'] = Carbon::now();
        $preferences['language'] = 'en';
        $preferences['first_name'] = 'New';
        $preferences['last_name'] = 'User';
        $preferences['logo_target'] = 2;
        $preferences['profile_pic'] = '';
        $preferences['organization'] = 'None';
        $preferences['onboarding'] = 1;
        $preferences['use_dashboard'] = 1;
        $preferences['form_tab_selection'] = 2;
        $preferences['proj_tab_selection'] = 2;

        $user->preferences = $preferences;
        $user->save();

        return $user;
    }
}
