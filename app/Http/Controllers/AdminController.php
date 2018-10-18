<?php namespace App\Http\Controllers;

use App\Form;
use App\FormGroup;
use App\Http\Controllers\Auth\RegisterController;
use App\Preference;
use App\Project;
use App\ProjectGroup;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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
        ['name' => 'form_custom', 'backup' => 'SaveFormCustomTable'],
        ['name' => 'form_groups', 'backup' => 'SaveFormGroupsTable'],
        ['name' => 'form_group_user', 'backup' => 'SaveFormGroupUsersTable'],
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
        ['name' => 'preferences', 'backup' => 'SavePreferencesTable'],
        ['name' => 'project_custom', 'backup' => 'SaveProjectCustomTable'],
        ['name' => 'project_groups', 'backup' => 'SaveProjectGroupsTable'],
        ['name' => 'project_group_user', 'backup' => 'SaveProjectGroupUsersTable'],
        ['name' => 'projects', 'backup' => 'SaveProjectsTable'],
        ['name' => 'project_token', 'backup' => 'SaveProjectTokensTable'],
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
    public function users(Request $request) {
        $usersAz = User::orderBy('first_name')->get();
        $usersZa = User::orderBy('first_name', 'desc')->get();
        $usersNto = User::latest()->get();
        $usersOtn = User::orderBy('created_at')->get();

        $notification = array(
          'message' => '',
          'description' => '',
          'warning' => false,
          'static' => false
        );
        $prevUrlArray = $request->session()->get('_previous');
        $prevUrl = reset($prevUrlArray);
        $profChangesArray = $request->session()->get('user_changes');
        if ($profChangesArray) $profChanges = reset($profChangesArray);
        if ($prevUrl !== url()->current()) {
          $session = $request->session()->get('k3_global_success');

          if ($session == 'user_updated' && $profChanges == 'password')
            $notification['message'] = 'Password Successfully Updated!';
          else if ($session == 'user_updated')
            $notification['message'] = 'User Successfully Updated!';
        } else if ($request->session()->get('k3_global_success') == 'batch_users') {
          $notification['message'] = 'User(s) Successfully Invited!';
        }

        return view('admin.users', compact('usersAz', 'usersZa', 'usersNto', 'usersOtn', 'notification'));
    }

    /**
     * Updates information and/or password for a individual user.
     *
     * @param  Request $request
     * @return Redirect
     */
    public function update(Request $request) {
        $message = array();
        $user = User::where('id', '=', $request->id)->first();
        $newFirstName = $request->first_name;
        $newLastName = $request->last_name;
        $newProfilePic = $request->profile;
        $newOrganization = $request->organization;
        $newLanguage = $request->language;
        $newPass = $request->password;
        $confirm = $request->password_confirmation;

        // Look for changes, update what was changed
        if (!empty($newFirstName) && $newFirstName != $user->first_name) {
          $user->first_name = $newFirstName;
          array_push($message, "first_name");
        }

        if (!empty($newLastName) && $newLastName != $user->last_name) {
          $user->last_name = $newLastName;
          array_push($message, "last_name");
        }

        if (!empty($newProfilePic)) {
          $user->profile = $newProfilePic;
          array_push($message, "profile");
        }

        if (!empty($newOrganization) && $newOrganization != $user->organization) {
          $user->organization = $newOrganization;
          array_push($message, "organization");
        }

        // Need to test comparing language code vs language name (en vs English)
        if (!empty($newLanguage) && $newLanguage != $user->language) {
          $user->language = $newLanguage;
          array_push($message, "language");
        }

        // Handle password change cases.
        if(!empty($newPass) || !empty($confirm)) {
            // If passwords don't match.
            if($newPass != $confirm)
                return response()->json(["status" => false, "message" => "passwords_unmatched"], 200);
                //return redirect('user/'.$user->id.'/edit')->with('k3_global_error', 'passwords_unmatched');

            // If password is less than 6 chars
            if(strlen($newPass)<6)
                return response()->json(["status" => false, "message" => "password_minimum"], 200);
                //return redirect('user/'.$user->id.'/edit')->with('k3_global_error', 'password_minimum');

            // If password contains spaces
            if(preg_match('/\s/',$newPass))
                return response()->json(["status" => false, "message" => "password_whitespaces"], 200);
                //return redirect('user/'.$user->id.'/edit')->with('k3_global_error', 'password_whitespaces');

            $user->password = bcrypt($newPass);
            array_push($message,"password");
        }

        $user->save();
        //return response()->json(["status" => true, "message" => $message], 200);
        return redirect('admin/users')->with('k3_global_success', 'user_updated')->with('user_changes', $message);
    }

    /**
     * Deletes a user from the system.
     *
     * @param  int $id - The ID of user to be deleted
     * @return JsonResponse - User deleted
     */
    public function deleteUser($id) {
        if(!\Auth::user()->admin) {
            return response()->json(["status" => false, "message" => "not_admin"], 200);
        }

        if ($id == 1) {
            return response()->json(["status" => false, "message" => "attempt to delete root admin"], 200);
        }

        $user = User::where('id', '=', $id)->first();
        $user->delete();

        return response()->json(["status" => true, "message" => "user_deleted"], 200);
    }

     /**
      * Updates admin and activation status of a user
      * Adds or removes access to projects, forms, and groups
      *
      * @param  int $id - The ID of user to be updated
      * @return JsonResponse - User admin toggled
      */
	  
      public function updateStatus(Request $request) {
        if ($request->id == 1) {
          return response()->json(["status" => false, "message" => "root_admin_error"], 200);
        }
		
        $user = User::where('id', '=', $request->id)->first();
		
        $message = array();
		
        if ($request->status == "admin") {
          // Updating admin status
          $action = "admin";

          if ($user->admin) {
            // Revoking admin status
            $user->admin = 0;

            //Build the list of project groups they are a part of
            $guPairs = DB::table("project_group_user")->where('user_id', '=', $user->id)->get();
			
			$user_project_group_ids = array();
			foreach($guPairs as $gu)
			{
				array_push($user_project_group_ids, $gu->project_group_id);
			}
			
			$safe_pids = array();
			$safe_pids_assoc = array();
			$pids_data = ProjectGroup::whereIn('id', $user_project_group_ids)->get();
			foreach($pids_data as $project) // json -> array
			{
				array_push($safe_pids, $project->pid);
				$safe_pids_assoc[$project->pid] = true;
			}
			
			$safe_pids = array_unique($safe_pids);

            //Build the list of form groups they are a part of
            $guPairs = DB::table("form_group_user")->where("user_id", "=", $user->id)->get();
			
			$user_form_group_ids = array();
			foreach($guPairs as $gu)
			{
				array_push($user_form_group_ids, $gu->form_group_id);
			}
			
			$safe_fids = array();
			$safe_fids_assoc = array();
			$fids_data = FormGroup::whereIn('id', $user_form_group_ids)->get();
			foreach($fids_data as $group) // json -> array
			{
				array_push($safe_fids, $group->fid);
				$safe_fids_assoc[$group->fid] = true;
			}
			
            $safe_fids = array_unique($safe_fids);

            //If the user isn't a part of the project group, we want to remove their custom access to it
            $projects = Project::all();
			$pids_to_remove = array();
            foreach($projects as $project) {
                if(!array_key_exists($project->pid, $safe_pids_assoc))
				{
                    //$user->removeCustomProject($project->pid);
					array_push($pids_to_remove, $project->pid);
				}
            }
			$user->bulkRemoveCustomProjects($pids_to_remove);
			
            //If the user isn't a part of the form group, we want to remove their custom access to it
            $forms = Form::all();
			$fids_to_remove = array();
            foreach($forms as $form) {
                if(!array_key_exists($form->fid, $safe_fids_assoc))
				{
                    //$user->removeCustomForm($form->fid);
					array_push($fids_to_remove, $form->fid);
				}
            }
			$user->bulkRemoveCustomForms($fids_to_remove);

            array_push($message, "not_admin");
          } else {
            // User granted admin status
            $user->admin = 1;

			$user->addNewAdminToAllCustomProjects();
			$user->addNewAdminToAllCustomForms();
			
            array_push($message, "admin");
          }
        } else {
          // Updating activation status
          $action = "activation";

          if ($user->active) {
            // User already active, need to deactivate
            $user->active = 0;

            // We need to give them a new regtoken so they can't use the old one to reactivate
            $user->regtoken = RegisterController::makeRegToken();
          } else {
            // User not active, need to activate
            $user->active = 1;
          }
        }
		
        $user->save(); // insignificant
		
        return response()->json(["status" => true, "message" => $message, "action" => $action], 200);
      }

    /**
     * Batch invites users to Kora3 using list of emails.
     *
     * @param  Request $request
     * @return View
     */
    public function batch(Request $request) {
        $emails = str_replace(',', ' ', $request->emails);
        $emails = preg_replace('!\s+!', ' ', $emails);
        $emails = array_unique(explode(' ', $emails));
        $personal_message = $request->message;

        $notification = array(
            'message' => '',
            'description' => '',
            'warning' => false,
            'static' => false
        );

        // The user hasn't entered anything.
        if($emails[0] == "") {
            return redirect('admin/users')->with('k3_global_error', 'batch_no_data');
        } else {
            $skipped = 0;
            $created = 0;
			$user_ids = array();
			
            foreach ($emails as $email) {
				if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
					$username = explode('@', $email)[0];
                    $i = 1;
                    $username_array = array();
                    $username_array[0] = $username;

                    // Increment a count while the username exists.
                    while (self::usernameExists($username)) {
                        $username_array[1] = $i;
                        $username = implode($username_array);
                        $i++;
                    }
					
					if(!self::emailExists($email)) {
                        //
                        // Create the new user.
                        //
                        $user = new User();
                        $user->username = $username;
                        $user->email = $email;
                        $password = self::passwordGen();
                        $user->password = bcrypt($password);
                        $user->language = 'en';
                        $token = RegisterController::makeRegToken();
                        $user->regtoken = $token;
                        $user->save();
						array_push($user_ids, $user->id);
						
                        //
                        // Assign the new user a default set of preferences.
                        //
                        $preference = new Preference;
                        $preference->user_id = $user->id;
                        $preference->created_at = Carbon::now();
                        $preference->use_dashboard = 1;
                        $preference->logo_target = 1;
                        $preference->proj_page_tab_selection = 3;
                        $preference->single_proj_page_tab_selection = 3;
                        $preference->save();

                        //
                        // Send a confirmation email.
                        //
                        try {
                            Mail::send('emails.batch-activation', compact('token', 'password', 'username', 'personal_message'), function ($message) use ($email) {
                                $message->from(config('mail.from.address'));
                                $message->to($email);
                                $message->subject('Kora Account Activation');
                            });
                        } catch(\Swift_TransportException $e) {
                            $notification['warning'] = true;
                            $notification['static'] = true;
                            $notification['message'] = 'Emails failed to send!';
                            $notification['description'] = 'Please check your mailing configuration and try again.';
                            //Log for now
                            Log::info('Batch invite email failed');
                        }
                        $created++;
                    } else {
                        if (isset($request->return_user_ids)) { // return user id of existing user
							$user = User::where('email', '=', $email)->first();
							array_push($user_ids, $user->id);
						}
						$skipped++;
                    }
				}
            }

			if (isset($request->return_user_ids))
				return $user_ids;
			else
				return redirect('admin/users')->with('k3_global_success', 'batch_users')->with('batch_users_created', $created)->with('batch_users_skipped', $skipped)->with('notification', $notification);
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
