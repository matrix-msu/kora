<?php namespace App\Http\Controllers\Auth;

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
    }

    /**
     * Show the application welcome screen to the user.
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
        dd($request);

//        $id = \Auth::user()->id;
//        $user = User::where('id', '=', $id)->first();
//
//        $new_pass = $request->new_pass;
//        $confirm = $request->confirm;
//
//        if (empty($new_pass) && empty($confirm)){
//            flash()->overlay('Please fill the fields before submitting.', 'Whoops.');
//            return redirect('user/profile');
//        }
//
//        elseif($new_pass != $confirm){
//            flash()->overlay('Passwords do not match, please try again.', 'Whoops.');
//            return redirect('user/profile');
//        }
//
//        else{
//            $user->password = bcrypt($new_pass);
//            $user->save();
//
//            flash()->overlay('Your password has been changed!', 'Success!');
//            return redirect('user/profile');
//        }


    }


}
