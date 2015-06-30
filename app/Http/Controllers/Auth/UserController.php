<?php namespace App\Http\Controllers\Auth;

use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
        $this->middleware('auth');
        $this->middleware('active', ['except' => ['activate', 'activateshow']]);
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
        return view('user/profile');
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function changepw(Request $request)
    {
        $user = \Auth::user();
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
     * Activates the user with a code that is emailed to them.
     *
     * @param Request $request
     * @return Response
     */
    public function activate($token)
    {
        $user = \Auth::user();

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
