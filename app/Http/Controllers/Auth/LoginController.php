<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
        $this->middleware('databaseConnected');
        $this->middleware('guest', ['except' => [
            'logout',
            'redirectToGitlab',
            'handleGitlabCallback'
        ]]);
    }

    /**
     * Override of function in the use class above, AuthenticatesUsers. Filters login results to allow login with either username or email
     *
     * @param  Request $request
     * @return array - The filtered credentials
     */
    protected function credentials(Request $request)
    {
        $credentials = $request->only($this->username(), 'password');

        if(strpos($credentials['email'], '@') == false) {
            //logging in with username not email, so change the column-name
            $credentials['username'] = $credentials['email'];
            unset($credentials['email']);
        }

        return $credentials;
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        if ($response = $this->loggedOut($request)) {
            return $response;
        }

        return $request->wantsJson()
            ? new JsonResponse([], 204)
            : redirect('/home');
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

        if(Auth::guest()) {
            //Check to see if user exists
            $koraUsersByHash = User::whereNotNull('gitlab_token')->get();
            foreach($koraUsersByHash as $hashUser) {
                //Found the user
                if(Hash::check($user->token, $hashUser->gitlab_token)) {
                    Auth::login($hashUser);

                    //This handles intended url redirects after login
                    return redirect()->intended($this->redirectPath());
                }
            }

            //Didn't find anybody so let's see if we can make account automatically
            $koraUserByEmail = User::where('email', '=', $user->email)->first();
            $koraUserByName = User::where('username', '=', $user->nickname)->first();

            if(!is_null($koraUserByEmail) | !is_null($koraUserByName)) {
                //Gitlab user has same email or username as another kora user that exists
                return redirect('/home')->with('status', 'oauth_user_conflict');
            } else {
                //Create user and then send them to profile page
                $newKoraUser = $this->createNewUserFromOAuth($user->nickname, $user->email, 'gitlab_token', $user->token);

                Auth::login($newKoraUser);

                return redirect('/user/' . $newKoraUser->id)->with('k3_global_success', 'gitlab_user_created');
            }
        } else {
            //We are logged in, so assign gitlab to current user
            $currentUser = Auth::user();

            if(is_null($currentUser->gitlab_token)) {
                //Make sure this account isn't assigned to another
                $koraUsersByHash = User::whereNotNull('gitlab_token')->get();
                foreach($koraUsersByHash as $hashUser) {
                    if(Hash::check($user->token, $hashUser->gitlab_token))
                        return redirect('/user/' . $currentUser->id)->with('k3_global_success', 'gitlab_user_used');
                }

                //Gitlab account not in use so assign it!
                $currentUser->gitlab_token = Hash::make($user->token);
                $currentUser->save();
                return redirect('/user/' . $currentUser->id)->with('k3_global_success', 'gitlab_user_assigned');
            } else {
                //Already has a gitlab account
                return redirect('/user/' . $currentUser->id)->with('k3_global_success', 'gitlab_user_exists');
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
