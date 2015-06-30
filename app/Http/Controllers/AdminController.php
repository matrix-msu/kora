<?php namespace App\Http\Controllers;

use App\User;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminController extends Controller {

    /**
     * User must be logged in to access views in this controller.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('active');
    }

    /**
     * @return Response
     */
    public function users()
    {
        $users = User::all();

        return view('admin.users', compact('users'));
    }

    /**
     * Changes the user's password and/or makes user admin.
     * Builds up a message as it moves through if statements.
     *
     * @param Request $request
     * @return Response
     */
    public function update(Request $request)
    {
        $message = "Here's what you changed (or kept the same):";
        $user = User::where('id', '=', $request->users)->first();
        $new_pass = $request->new_password;
        $confirm = $request->confirm;

        if (!is_null($request->admin)){
            $user->admin = 1;
            $message .= " User is admin.";
        }
        else{
            $user->admin = 0;
            $message .= " User is not admin.";
        }

        if (!is_null($request->active)){
            $user->active = 1;
            $message .= " User is active.";
        }
        else{
            $user->active = 0;
            $message .= " User is not active.";
        }

        if (!empty($new_pass) || !empty($confirm)){
            if ($new_pass != $confirm){
                flash()->overlay('Passwords do not match, please try again.', 'Whoops.');
                return redirect('admin/users');
            }
            else{
                $user->password = bcrypt($new_pass);
                $message .= " User password changed. \n";
            }
        }

        $user->save();
        flash()->overlay($message, 'Success!');
        return redirect('admin/users');
    }
}
