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
     *
     * @param Request $request
     * @return Response
     */
    public function update(Request $request)
    {
        $user = User::where('id', '=', $request->users)->first();
        $new_pass = $request->new_password;
        $confirm = $request->confirm;

        if(!is_null($request->admin)){
            if(empty($new_pass) && empty($confirm)){ //User did not want to update password
                $user->admin=1;
                $user->save();

                flash()->overlay('User is now admin!', 'Success!');
                return redirect('admin/users');
            }

            elseif($new_pass != $confirm){
                flash()->overlay('Passwords do not match, please try again.', 'Whoops.');
                return redirect('admin/users');
            }

            else{
                $user->admin=1;
                $user->password = bcrypt($new_pass);
                $user->save();

                flash()->overlay('User made admin and password has been updated!', 'Success!');
                return redirect('admin/users');
            }
        }

        else{ //User does not want to give admin rights
            if(empty($new_pass) && empty($confirm)){ //User did not want to update password
                $user->admin=0;
                $user->save();

                flash()->overlay('User is no longer an admin!', 'Success!');
                return redirect('admin/users');
            }

            elseif($new_pass != $confirm){
                flash()->overlay('Passwords do not match, please try again.', 'Whoops.');
                return redirect('admin/users');
            }

            else{
                $user->admin=0;
                $user->password = bcrypt($new_pass);
                $user->save();

                flash()->overlay('Password has been updated, user is no longer an admin!', 'Success!');
                return redirect('admin/users');
            }
        }
    }
}
