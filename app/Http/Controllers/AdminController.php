<?php namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\View\View;

class AdminController extends Controller {

    /*
    |--------------------------------------------------------------------------
    | Admin Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles administrative functions for Kora 3
    |
    */

    /**
     * Constructs the controller and makes sure active user is an administrator.
     */
    public function __construct() {
        $this->middleware('auth');
        $this->middleware('active');
        $this->middleware('admin');
    }

    /**
     * Returns the view for the user management page.
     *
     * @return View
     */
    public function users() {
        $users = User::all();

        return view('admin.users', compact('users'));
    }

    /**
     * Updates information and/or password for a individual user.
     *
     * @param  Request $request
     * @return View
     */
    public function update(Request $request) {
        $message = trans('controller_admin.changed');
        $user = User::where('id', '=', $request->users)->first();
        $new_pass = $request->new_password;
        $confirm = $request->confirm;

        // Has the user been given admin rights?
        if(!is_null($request->admin)) {
            $user->admin = 1;
            $message .= trans('controller_admin.admin');
        } else {
            $user->admin = 0;
            $message .= trans('controller_admin.notadmin');
        }

        // Has the user been activated?
        if(!is_null($request->active)) {
            $user->active = 1;
            $message .= trans('controller_admin.active');
        } else {
            $user->active = 0;
            //We need to give them a new regtoken so they can't use the old one to reactivate
            $user->regtoken = AuthenticatesAndRegistersUsers::makeRegToken();
            $message .= trans('controller_admin.inactive');
        }

        // Handle password change cases.
        if(!empty($new_pass) || !empty($confirm)) {
            // If passwords don't match.
            if($new_pass != $confirm) {
                flash()->overlay(trans('controller_admin.nomatch'), trans('controller_admin.whoops'));
                return redirect('admin/users');
            }

            // If password is less than 6 chars
            if(strlen($new_pass)<6) {
                flash()->overlay(trans('controller_admin.short'), trans('controller_admin.whoops'));
                return redirect('admin/users');
            }

            // If password contains spaces
            if(preg_match('/\s/',$new_pass)) {
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
     * Deletes a user from the system.
     *
     * @param  int $id - The ID of user to be deleted
     */
    public function deleteUser($id) {
        $user = User::where('id', '=', $id)->first();
        $user->delete();

        flash()->overlay(trans('controller_admin.delete'), trans('controller_admin.success'));
    }

    /**
     * Batch invites users to Kora3 using list of emails.
     *
     * @param  Request $request
     * @return View
     */
    public function batch(Request $request) {
        $emails = str_replace(',', ' ', $request['emails']);
        $emails = preg_replace('!\s+!', ' ', $emails);
        $emails = array_unique(explode(' ', $emails));

        // The user hasn't entered anything.
        if($emails[0] == "") {
            flash()->overlay(trans('controller_admin.enter'), trans('controller_admin.whoops'));
            return redirect('admin/users');
        } else {
            $skipped = 0;
            $created = 0;

            foreach($emails as $email) {
                if(!self::emailExists($email)) {
                    if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $username = explode('@', $email)[0];
                        $i = 1;
                        $username_array = array();
                        $username_array[0] = $username;

                        // Increment a count while the username exists.
                        while(self::usernameExists($username)) {
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
                        $password = self::passwordGen();
                        $user->password = bcrypt($password);
                        $user->language = 'en';
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
                    } else {
                        $skipped++;
                    }
                } else {
                    $skipped++;
                }
            }
            if($skipped)
                flash()->overlay($skipped . trans('controller_admin.skipped') . $created . trans('controller_admin.created'), trans('controller_admin.success'));
            else
                flash()->overlay($created . trans('controller_admin.created'), trans('controller_admin.success'));
            return redirect('admin/users');
        }
    }

    /**
     * Deletes all information from Kora3, except the root user. Only the root user can use this function.
     *
     * @return string - Success message
     */
    public function deleteData() {
        if(Auth::check()) {
            if(Auth::user()->id != 1) {
                flash()->overlay("There can only be one highlander!","Get out!");
                return redirect("/projects")->send();
            }
        }

        try {
            foreach(User::all() as $User) {
                if($User->id == 1) { //Do not delete the default admin user
                    continue;
                } else {
                    $User->delete();
                }
            }
            //TODO::Can we make this more modular?
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

        } catch(\Exception $e) {
            $this->ajaxResponse(false, "Error removing from database");
        }

        return "The force is strong with this one :)";
    }

    /**
     * Checks if username is already taken.
     *
     * @param  string $username - Username to check for
     * @return bool - Username's existence
     */
    private function usernameExists($username) {
        return !is_null(User::where('username', '=', $username)->first());
    }

    /**
     * Checks if email is already taken.
     *
     * @param  string $email - Email to check for
     * @return bool - Email's existence
     */
    private function emailExists($email) {
        return !is_null(User::where('email', '=', $email)->first());
    }

    /**
     * Generates a temporary password of length 10.
     *
     * @return string - Generated password
     */
    private function passwordGen() {
        $valid = 'abcdefghijklmnopqrstuvwxyz';
        $valid .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $valid .= '0123456789';

        $password = '';
        for($i = 0; $i < 10; $i++) {
            $password .= $valid[( rand() % 62 )];
        }
        return $password;
    }
}
