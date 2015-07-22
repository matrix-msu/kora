<?php namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class UserController extends Controller {

    /*
    |--------------------------------------------------------------------------
    | User Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles ...
    |
    */

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['activate', 'activator', 'activateshow']]);
        $this->middleware('active', ['except' => ['activate', 'activator', 'activateshow']]);
    }

    /**
     * Show the application welcome screen to the user.
     *
     *
     *
     * @return Response
     */
    public function index()
    {
        $languages_available = Config::get('app.locales_supported');
        return view('user/profile',compact('languages_available'));
    }

    /**
     * @param Request $request
     * @return Response
     */

    public function changeprofile(Request $request){
        $user = Auth::user();
        $type = $request->input("type");

        if($type == "lang"){
            $lang = $request->input("field");

            if(empty($lang)){
                flash()->overlay('You must select a language','Whoops.');
                //return redirect('user/profile');
            }
            else{
                $user->language = $lang;
                $user->save();
                flash()->overlay("Your language preference has been updated","Success!");
               // return redirect('user/profile');
            }
        }
        elseif($type == "name"){
            $realname = $request->input("field");

            if(empty($realname)){
                flash()->overlay('You must enter a name','Whoops.');
                //return redirect('user/profile');
            }
            else{
                $user->name = $realname;
                $user->save();
                flash()->overlay("Your real name preference has been updated","Success!");
                //return redirect('user/profile');
            }
        }
        elseif($type == "org"){
            $organization = $request->input("field");

            if(empty($organization)){
                flash()->overlay('You must enter an organization','Whoops.');
                //return redirect('user/profile');
            }
            else{
                $user->organization = $organization;
                $user->save();
                flash()->overlay("Your organization preference has been updated","Success!");
               // return redirect('user/profile');
            }

        }
        else{

        }

    }
    public function changepw(Request $request)
    {
        $user = Auth::user();
        $new_pass = $request->new_password;
        $confirm = $request->confirm;

        if (empty($new_pass) && empty($confirm)){
            flash()->overlay('Please fill both password fields before submitting.', 'Whoops.');
            return redirect('user/profile');
        }

        elseif($new_pass != $confirm){
            flash()->overlay('Passwords do not match, please try again.', 'Whoops.');
            return redirect('user/profile');
        }

        else{
            $user->password = bcrypt($new_pass);
            $user->save();

            flash()->overlay('Your password has been changed!', 'Success!');
            return redirect('user/profile');
        }
    }

    /**
     * @return Response
     */
    public function activateshow()
    {
        return view('auth.activate');
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function activator(Request $request)
    {
        $user = User::where('username', '=', $request->user)->first();
        $token = trim($request->token);

        if ($user->regtoken == $token){
            $user->active = 1;
            $user->save();
            flash()->overlay('You have been activated!', 'Success!');

            \Auth::login($user);

            return redirect('/');
        }
        else{
            flash()->overlay('That token does not match that user.', 'Whoops.');
            return redirect('auth/activate');
        }
    }

    /**
     * Activates the user with a link that is emailed to them.
     *
     * @param token
     * @return Response
     */
    public function activate($token)
    {
        $user = User::where('regtoken', '=', $token)->first();

        \Auth::login($user);

        if ($token != $user->regtoken)
        {
            flash()->overlay('That token was invalid, try again.', 'Whoops.');
            return redirect('/');
        }
        else
        {
            $user->active = 1;
            $user->save();

            flash()->overlay('Your account is now active!', 'Success!');
            return redirect('/');
        }


    }
}
