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
     * Changes the user's password.
     *
     * @param Request $request
     * @return Response
     */
    public function update(Request $request)
    {
        if (!empty($request)) {
            $id = $request->users;
            $new_pass = $request->new_password;
            $confirm_pass = $request->confirm;
        }

        if (! ($new_pass == $confirm_pass)) {
            flash()->overlay('Passwords do not match, try again please.');
            return redirect('admin/users');
        }

        $user = User::where('id','=', $id)->first();
        $user->password = bcrypt($new_pass);
        $user->save();

        flash()->overlay('Password updated!');
        return redirect('admin/users');
    }


}
