<?php namespace App\Http\Controllers;

use App\User;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;

class AdminController extends Controller {

    /**
     * User must be logged in and admin to access views in this controller.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('active');
        $this->middleware('admin');
    }

    /**
     * Method for the manage users page.
     *
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
        $message = trans('controller_admin.changed');
        $user = User::where('id', '=', $request->users)->first();
        $new_pass = $request->new_password;
        $confirm = $request->confirm;

        // Has the user been given admin rights?
        if (!is_null($request->admin)){
            $user->admin = 1;
            $message .= trans('controller_admin.admin');
        }
        else{
            $user->admin = 0;
            $message .= trans('controller_admin.notadmin');
        }

        // Has the user been activated?
        if (!is_null($request->active)){
            $user->active = 1;
            $message .= trans('controller_admin.active');
        }
        else{
            $user->active = 0;
            $message .= trans('controller_admin.inactive');
        }

        // Handle password change cases.
        if (!empty($new_pass) || !empty($confirm)){

            // If passwords don't match.
            if ($new_pass != $confirm){
                flash()->overlay(trans('controller_admin.nomatch'), trans('controller_admin.whoops'));
                return redirect('admin/users');
            }

            // If password is less than 6 chars
            if(strlen($new_pass)<6){
                flash()->overlay(trans('controller_admin.short'), trans('controller_admin.whoops'));
                return redirect('admin/users');
            }

            // If password contains spaces
            if ( preg_match('/\s/',$new_pass) ){
                flash()->overlay(trans('controller_admin.spaces'), trans('controller_admin.whoops'));
                return redirect('admin/users');
            }

            $user->password = bcrypt($new_pass);
            $message .= trans('controller_admin.passchange');
        }

        $user->save();
        flash()->overlay($message, trans('controller_admin.success'));
        return redirect('admin/users');
    }

    /**
     * Deletes a user.
     *
     * @param $id, the user's id.
     */
    public function deleteUser($id)
    {
        $user = User::where('id', '=', $id)->first();
        $user->delete();

        flash()->overlay(trans('controller_admin.delete'), trans('controller_admin.success'));
    }

    /**
     * Takes in comma or space separated (or a combination of the two)
     * e-mails and creates new users based on the emails.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function batch(Request $request)
    {
        $emails = str_replace(',', ' ', $request['emails']);
        $emails = preg_replace('!\s+!', ' ', $emails);
        $emails = array_unique(explode(' ', $emails));

        // The user hasn't entered anything.
        if ($emails[0] == "") {
            flash()->overlay(trans('controller_admin.enter'), trans('controller_admin.whoops'));
            return redirect('admin/users');
        }
        else {
            $skipped = 0;
            $created = 0;

            foreach ($emails as $email) {
                if (!AdminController::emailExists($email)) {
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $username = explode('@', $email)[0];
                        $i = 1;
                        $username_array = array();
                        $username_array[0] = $username;

                        // Increment a count while the username exists.
                        while (AdminController::usernameExists($username)) {
                            $username_array[1] = $i;
                            $username = implode($username_array);
                            $i++;
                        }

                        //
                        // Create the new user.
                        //
                        $user = new User();
                        $user->username = $username;
                        $user->email = $email;
                        $password = AdminController::passwordGen();
                        $user->password = bcrypt($password);
                        $token = AuthenticatesAndRegistersUsers::makeRegToken();
                        $user->regtoken = $token;
                        $user->save();

                        //
                        // Send a confirmation email.
                        //
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
                } else {
                    $skipped++;
                }
            }
            if ($skipped)
                flash()->overlay($skipped . trans('controller_admin.skipped') . $created . trans('controller_admin.created'), trans('controller_admin.success'));
            else
                flash()->overlay($created . trans('controller_admin.created'), trans('controller_admin.success'));
            return redirect('admin/users');
        }
    }

    public function deleteData(){
        if(Auth::check()){
            if(Auth::user()->id != 1){
                flash()->overlay("There can only be one highlander!","Get out!");
                return redirect("/projects")->send();
            }
        }

        try {
            foreach (User::all() as $User) {
                if ($User->id == 1) { //Do not delete the default admin user
                    continue;
                } else {
                    $User->delete();
                }
            }
            DB::table('projects')->delete();
            DB::table('forms')->delete();
            DB::table('fields')->delete();
            DB::table('records')->delete();
            DB::table('metadatas')->delete();
            DB::table('tokens')->delete();
            DB::table('project_token')->delete();
            DB::table('revisions')->delete();
            DB::table('date_fields')->delete();
            DB::table('form_groups')->delete();
            DB::table('form_group_user')->delete();
            DB::table('generated_list_fields')->delete();
            DB::table('geolocator_fields')->delete();
            DB::table('list_fields')->delete();
            DB::table('multi_select_list_fields')->delete();
            DB::table('number_fields')->delete();
            DB::table('project_groups')->delete();
            DB::table('project_group_user')->delete();
            DB::table('rich_text_fields')->delete();
            DB::table('schedule_fields')->delete();
            DB::table('text_fields')->delete();
            DB::table('documents_fields')->delete();
            DB::table('model_fields')->delete();
            DB::table('gallery_fields')->delete();
            DB::table('video_fields')->delete();
            DB::table('playlist_fields')->delete();
            DB::table('combo_list_fields')->delete();
            DB::table('associator_fields')->delete();
            DB::table('option_presets')->delete();
            DB::table('record_presets')->delete();
            DB::table('plugins')->delete();
            DB::table('plugin_menus')->delete();
            DB::table('plugin_settings')->delete();
            DB::table('plugin_users')->delete();
            DB::table('associations')->delete();
            DB::table('combo_support')->delete();
            DB::table('geolocator_support')->delete();
            DB::table('schedule_support')->delete();
            DB::table('associator_support')->delete();
            DB::table('pages')->delete();

        }catch(\Exception $e){
            $this->ajaxResponse(false, "Error removing from database");
        }

        return "The force is strong with this one :)";
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
     * Checks if an email is in use.
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
