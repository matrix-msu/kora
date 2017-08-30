<?php namespace App\Http\Controllers;

use App\Form;
use App\FormGroup;
use App\Project;
use App\ProjectGroup;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Support\Facades\Redirect;
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
     * @var array - The data tables. Admin functions will use for both deletion, and backup/restore processes
     */
    public $DATA_TABLES = [
        ['name' => 'associations', 'backup' => 'SaveAssociationsTable'],
        ['name' => 'associator_fields', 'backup' => 'SaveAssociatorFieldsTable'],
        ['name' => 'associator_support', 'backup' => 'SaveAssociatorSupportTable'],
        ['name' => 'combo_list_fields', 'backup' => 'SaveComboListFieldsTable'],
        ['name' => 'combo_support', 'backup' => 'SaveComboSupportTable'],
        ['name' => 'dashboard_blocks', 'backup' => 'SaveDashboardBlocksTable'],
        ['name' => 'dashboard_sections', 'backup' => 'SaveDashboardSectionsTable'],
        ['name' => 'date_fields', 'backup' => 'SaveDateFieldsTable'],
        ['name' => 'documents_fields', 'backup' => 'SaveDocumentsFieldsTable'],
        ['name' => 'fields', 'backup' => 'SaveFieldsTable'],
        ['name' => 'form_group_user', 'backup' => 'SaveFormGroupUsersTable'],
        ['name' => 'form_groups', 'backup' => 'SaveFormGroupsTable'],
        ['name' => 'forms', 'backup' => 'SaveFormsTable'],
        ['name' => 'gallery_fields', 'backup' => 'SaveGalleryFieldsTable'],
        ['name' => 'generated_list_fields', 'backup' => 'SaveGeneratedListFieldsTable'],
        ['name' => 'geolocator_fields', 'backup' => 'SaveGeolocatorFieldsTable'],
        ['name' => 'geolocator_support', 'backup' => 'SaveGeolocatorSupportTable'],
        ['name' => 'list_fields', 'backup' => 'SaveListFieldTable'],
        ['name' => 'metadatas', 'backup' => 'SaveMetadatasTable'],
        ['name' => 'model_fields', 'backup' => 'SaveModelFieldsTable'],
        ['name' => 'multi_select_list_fields', 'backup' => 'SaveMultiSelectListFieldsTable'],
        ['name' => 'number_fields', 'backup' => 'SaveNumberFieldsTable'],
        ['name' => 'option_presets', 'backup' => 'SaveOptionPresetsTable'],
        ['name' => 'pages', 'backup' => 'SavePagesTable'],
        ['name' => 'playlist_fields', 'backup' => 'SavePlaylistFieldsTable'],
        ['name' => 'plugins', 'backup' => 'SavePluginsTable'],
        ['name' => 'plugin_menus', 'backup' => 'SavePluginMenusTable'],
        ['name' => 'plugin_settings', 'backup' => 'SavePluginSettingsTable'],
        ['name' => 'plugin_users', 'backup' => 'SavePluginUsersTable'],
        ['name' => 'project_group_user', 'backup' => 'SaveProjectGroupUsersTable'],
        ['name' => 'project_groups', 'backup' => 'SaveProjectGroupsTable'],
        ['name' => 'project_token', 'backup' => 'SaveProjectTokensTable'],
        ['name' => 'projects', 'backup' => 'SaveProjectsTable'],
        ['name' => 'record_presets', 'backup' => 'SaveRecordPresetsTable'],
        ['name' => 'records', 'backup' => 'SaveRecordsTable'],
        ['name' => 'revisions', 'backup' => 'SaveRevisionsTable'],
        ['name' => 'rich_text_fields', 'backup' => 'SaveRichTextFieldsTable'],
        ['name' => 'schedule_fields', 'backup' => 'SaveScheduleFieldsTable'],
        ['name' => 'schedule_support', 'backup' => 'SaveScheduleSupportTable'],
        ['name' => 'text_fields', 'backup' => 'SaveTextFieldsTable'],
        ['name' => 'tokens', 'backup' => 'SaveTokensTable'],
        ['name' => 'video_fields', 'backup' => 'SaveVideoFieldsTable'],
    ];

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
     * @return Redirect
     */
    public function update(Request $request) {
        $message = array();
        $user = User::where('id', '=', $request->users)->first();
        $newPass = $request->new_password;
        $confirm = $request->confirm;

        // Has the user been given admin rights?
        if(!is_null($request->admin)) {
            $user->admin = 1;

            $projects = Project::all();
            foreach($projects as $project) {
                $user->addCustomProject($project->pid);
            }

            $forms = Form::all();
            foreach($forms as $form) {
                $user->addCustomForm($form->fid);
            }

            array_push($message,"admin");
        } else {
            $user->admin = 0;

            //Build the list of project groups they are a part of
            $safePids = array();
            $guPairs = DB::table("project_group_user")->where("user_id", "=", $user->id);
            foreach($guPairs as $gu) {
                $group = ProjectGroup::where("id","=",$gu->project_group_id);
                array_push($safePids,$group->pid);
            }
            $safePids = array_unique($safePids);

            //Build the list of form groups they are a part of
            $safeFids = array();
            $guPairs = DB::table("form_group_user")->where("user_id", "=", $user->id);
            foreach($guPairs as $gu) {
                $group = FormGroup::where("id","=",$gu->form_group_id);
                array_push($safeFids,$group->fid);
            }
            $safeFids = array_unique($safeFids);

            //If the user isnt apart of the project group, we want to remove their custom access to it
            $projects = Project::all();
            foreach($projects as $project) {
                if(!in_array($project->pid,$safePids))
                    $user->removeCustomProject($project->pid);
            }

            //If the user isnt apart of the form group, we want to remove their custom access to it
            $forms = Form::all();
            foreach($forms as $form) {
                if(!in_array($form->fid,$safeFids))
                    $user->removeCustomFid($form->fid);
            }

            array_push($message,"not_admin");
        }

        // Has the user been activated?
        if(!is_null($request->active)) {
            $user->active = 1;
            array_push($message,"active");
        } else {
            $user->active = 0;
            //We need to give them a new regtoken so they can't use the old one to reactivate
            $user->regtoken = AuthenticatesAndRegistersUsers::makeRegToken();
            array_push($message,"not_active");
        }

        // Handle password change cases.
        if(!empty($newPass) || !empty($confirm)) {
            // If passwords don't match.
            if($newPass != $confirm)
                return redirect('admin/users')->with('k3_global_error', 'passwords_unmatched');

            // If password is less than 6 chars
            if(strlen($newPass)<6)
                return redirect('admin/users')->with('k3_global_error', 'password_minimum');

            // If password contains spaces
            if(preg_match('/\s/',$newPass))
                return redirect('admin/users')->with('k3_global_error', 'password_whitespaces');

            $user->password = bcrypt($newPass);
            array_push($message,"password");
        }

        $user->save();
        return redirect('admin/users')->with('k3_global_success', 'user_updated')->with('user_changes', $message);
    }

    /**
     * Deletes a user from the system.
     *
     * @param  int $id - The ID of user to be deleted
     * @return JsonResponse - User deleted
     */
    public function deleteUser($id) {
        $user = User::where('id', '=', $id)->first();
        $user->delete();

        return response()->json(["status"=>true,"message"=>"user_deleted"],200);
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
            return redirect('admin/users')->with('k3_global_error', 'batch_no_data');
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

            return redirect('admin/users')->with('k3_global_success', 'batch_users')->with('batch_users_created', $created)->with('batch_users_skipped', $skipped);
        }
    }

    /**
     * Deletes all information from Kora3, except the root user. Only the root user can use this function.
     *
     * @return string - Success message
     */
    public function deleteData() {
        if(Auth::check()) {
            if(Auth::user()->id != 1)
                return response()->json(["status"=>false,"message"=>"delete_all_not_root"],500);
        }

        try {
            foreach(User::all() as $User) {
                if($User->id == 1) //Do not delete the default admin user
                    continue;
                else
                    $User->delete();
            }

            foreach($this->DATA_TABLES as $table)
                DB::table($table["name"])->delete();

        } catch(\Exception $e) {
            return response()->json(["status"=>false,"message"=>"delete_all_db_fail"],500);
        }

        return response()->json(["status"=>true,"message"=>"delete_all_success"],200);
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
