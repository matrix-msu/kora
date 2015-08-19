<?php namespace App\Http\Controllers;

use App\User;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;

class AdminController extends Controller {

    /**
     * User must be logged in to access views in this controller.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('active');
        $this->middleware('admin');
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
                $message .= " User password changed.";
            }
        }

        $user->save();
        flash()->overlay($message, 'Success!');
        return redirect('admin/users');
    }

    /**
     * Deletes a user.
     *
     * @param $id
     */
    public function deleteUser($id)
    {
        $user = User::where('id', '=', $id)->first();
        $user->delete();

        flash()->overlay('User Deleted.', 'Success!');
    }

    /**
     * Takes in comma separated or space separated (or a combination of the two)
     * e-mails and creates new users based on the emails.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function batch(Request $request)
    {
        $emails = str_replace(',', ' ', $request['emails']);
        $emails = str_replace('  ', ' ', $emails);
        $emails = array_unique(explode(' ', $emails));

        $skipped = 0;
        $created = 0;

        foreach($emails as $email)
        {
            if(!AdminController::emailExists($email)) {
                $username = explode('@', $email)[0];
                $len = strlen($username);
                $i = 1;
                $username_array = array();
                $username_array[0] = $username;
                while (AdminController::usernameExists($username)) {
                    $username_array[1] = $i;
                    $username = implode($username_array);
                    $i++;
                }

                $user = new User();
                $user->username = $username;
                $user->email = $email;
                $password = AdminController::passwordGen();
                $user->password = bcrypt($password);
                $token = AuthenticatesAndRegistersUsers::makeRegToken();
                $user->regtoken = $token;
                $user->save();

                Mail::send('emails.batch-activation', compact('token', 'password', 'username'), function ($message) use ($email) {
                    $message->from(env('MAIL_FROM_ADDRESS'));
                    $message->to($email);
                    $message->subject('Kora Account Activation');
                });
                $created++;
            }
            else {
                $skipped++;
            }
        }
        if($skipped)
            flash()->overlay($skipped.' e-mail(s) were in use, '.$created.' user(s) created.', 'Success');
        else
            flash()->overlay($created. ' user(s) created.', 'Success');
        return redirect('admin/users');
    }

    /**
     * Checks if a username is in use.
     *
     * @param $username
     * @return bool
     */
    private function usernameExists($username)
    {
        return !is_null(User::where('username', '=', $username)->first());
    }

    /**
     * Checks if a email is in use.
     *
     * @param $email
     * @return bool
     */
    private function emailExists($email)
    {
        return !is_null(User::where('email', '=', $email)->first());
    }


    /**
     * Generates a random temporary password.
     *
     * @return string
     */
    private function passwordGen()
    {
        $valid = 'abcdefghijklmnopqrstuvwxyz';
        $valid .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $valid .= '0123456789';

        $password = '';
        for ($i = 0; $i < 10; $i++){
            $password .= $valid[( rand() % 62 )];
        }
        return $password;
    }
}
