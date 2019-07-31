<?php namespace App\Http\Controllers;

use App\Commands\UserEmails;
use App\Form;
use App\FormGroup;
use App\Http\Controllers\Auth\RegisterController;
use App\Project;
use App\ProjectGroup;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class AdminController extends Controller {

    /*
    |--------------------------------------------------------------------------
    | Admin Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles administrative functions for kora
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
    public function users(Request $request) {
        $users = User::all();
        $userNameSort = [];

        foreach($users as $user) {
            $userNameSort[$user->preferences['first_name']] = $user;
        }

        ksort($userNameSort);
        $usersAz = $userNameSort;
        ksort($userNameSort,SORT_DESC);
        $usersZa = $userNameSort;
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
        if($profChangesArray) $profChanges = reset($profChangesArray);
        if($prevUrl !== url()->current()) {
          $session = $request->session()->get('k3_global_success');

          if($session == 'user_updated' && isset($profChanges) && $profChanges == 'password')
            $notification['message'] = 'Password Successfully Updated!';
          else if($session == 'user_updated')
            $notification['message'] = 'User Successfully Updated!';
        } else if($request->session()->get('k3_global_success') == 'batch_users') {
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
        $user = User::where('id', '=', $request->uid)->first();
        $newUsername = $request->username;
        $newEmail = $request->email;
        $newFirstName = $request->first_name;
        $newLastName = $request->last_name;
        $newProfilePic = $request->profile;
        $newOrganization = $request->organization;
        $newPass = $request->password;
        $confirm = $request->password_confirmation;

        $userPrefs = $user->preferences; // doesn't access property directly, uses __get

        $user->username = $newUsername;
        $user->email = $newEmail;

        // Look for changes, update what was changed
        if(!empty($newFirstName) && $newFirstName != $user->preferences['first_name']) {
            $userPrefs['first_name'] = $newFirstName;
            array_push($message, "first_name");
        }

        if(!empty($newLastName) && $newLastName != $user->preferences['last_name']) {
            $userPrefs['last_name'] = $newLastName;
            array_push($message, "last_name");
        }

        if(!empty($newOrganization) && $newOrganization != $user->preferences['organization']) {
            $userPrefs['organization'] = $newOrganization;
            array_push($message, "organization");
        }

        // Handle password change cases.
        if(!empty($newPass) || !empty($confirm)) {
            // If passwords don't match.
            if($newPass != $confirm)
                return response()->json(["status" => false, "message" => "passwords_unmatched"], 200);

            // If password is less than 6 chars
            if(strlen($newPass)<6)
                return response()->json(["status" => false, "message" => "password_minimum"], 200);

            // If password contains spaces
            if(preg_match('/\s/',$newPass))
                return response()->json(["status" => false, "message" => "password_whitespaces"], 200);

            $user->password = bcrypt($newPass);
            array_push($message,"password");
        }

        $user->preferences = $userPrefs; // __set
        $user->save();

        if(!empty($newProfilePic)) {
            $changePicResponse = json_decode($this->changepicture($request, $user), true);
            if($changePicResponse['status'])
                array_push($message, $changePicResponse['message']);
        }

        return redirect('admin/users')->with('k3_global_success', 'user_updated')->with('user_changes', $message);
    }

    /**
     * Deletes a user from the system.
     *
     * @param  int $id - The ID of user to be deleted
     * @return JsonResponse - User deleted
     */
    public function deleteUser($id) {
        if(!\Auth::user()->admin)
            return response()->json(["status" => false, "message" => "not_admin"], 200);

        if($id == 1)
            return response()->json(["status" => false, "message" => "attempt to delete root admin"], 200);

        $user = User::where('id', '=', $id)->first();
        $user->delete();

        return response()->json(["status" => true, "message" => "user_deleted"], 200);
    }

     /**
      * Updates admin and activation status of a user.
      * Adds or removes access to projects, forms, and groups.
      *
      * @param  int $id - The ID of user to be updated
      * @return JsonResponse - User admin toggled
      */
      public function updateStatus(Request $request) {
        if($request->id == 1)
          return response()->json(["status" => false, "message" => "root_admin_error"], 200);
		
        $user = User::where('id', '=', $request->id)->first();
		
        $message = array();
		
        if($request->status == "admin") {
          // Updating admin status
          $action = "admin";

          if($user->admin) {
            // Revoking admin status
            $user->admin = 0;

            //Build the list of project groups they are a part of
            $guPairs = DB::table("project_group_user")->where('user_id', '=', $user->id)->get();
			
			$user_project_group_ids = array();
			foreach($guPairs as $gu) {
				array_push($user_project_group_ids, $gu->project_group_id);
			}
			
			$safe_pids_assoc = array();
			$pids_data = ProjectGroup::whereIn('id', $user_project_group_ids)->get();
			foreach($pids_data as $project) {// json -> array
				$safe_pids_assoc[$project->project_id] = true;
			}

            //Build the list of form groups they are a part of
            $guPairs = DB::table("form_group_user")->where("user_id", "=", $user->id)->get();
			
			$user_form_group_ids = array();
			foreach($guPairs as $gu) {
				array_push($user_form_group_ids, $gu->form_group_id);
			}
			
			$safe_fids_assoc = array();
			$fids_data = FormGroup::whereIn('id', $user_form_group_ids)->get();
			foreach($fids_data as $group) { // json -> array
				$safe_fids_assoc[$group->form_id] = true;
			}

            //If the user isn't a part of the project group, we want to remove their custom access to it
            $projects = Project::all();
			$pids_to_remove = array();
            foreach($projects as $project) {
                if(!array_key_exists($project->project_id, $safe_pids_assoc))
					array_push($pids_to_remove, $project->project_id);
            }
			$user->bulkRemoveCustomProjects($pids_to_remove);
			
            //If the user isn't a part of the form group, we want to remove their custom access to it
            $forms = Form::all();
			$fids_to_remove = array();
            foreach($forms as $form) {
                if(!array_key_exists($form->form_id, $safe_fids_assoc))
					array_push($fids_to_remove, $form->form_id);
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

          if($user->active) {
            // User already active, need to deactivate
            $user->active = 0;

            // We need to give them a new regtoken so they can't use the old one to reactivate
            $user->regtoken = RegisterController::makeRegToken();
          } else {
            // User not active, need to activate
            $user->active = 1;
          }
        }
		
        $user->save();
		
        return response()->json(["status" => true, "message" => $message, "action" => $action], 200);
      }
      
    /**
      * Checks whether the email is already taken.
      *
      * @param  string $email - Email to compare
      * @return bool - The result of its existence
      */
    public function validateEmails(Request $request) {
        $emails = str_replace(',', ' ', $request->emails);
        $emails = preg_replace('!\s+!', ' ', $emails);
        $emails = array_unique(explode(' ', $emails));

        $existingEmails = array();
        foreach($emails as $email) {
            if(self::emailExists($email))
                array_push($existingEmails, $email);
        }

        // return json response of all emails that already exist
        return response()->json(["status" => true, "message" => $existingEmails], 200);
    }

    /**
     * Batch invites users to kora using list of emails. Creates users in the db if they don't exist.
     *
     * @param  Request $request
     * @return View
     */
    public function batch(Request $request) {
        $emails = str_replace(',', ' ', $request->emails);
        $emails = preg_replace('!\s+!', ' ', $emails);
        $emails = array_unique(explode(' ', $emails));
        $personal_message = $request->message;

        if(isset($request->projectGroup)) {
            $projectGroup = ProjectGroup::where('id', '=', $request->projectGroup)->first();
            $project = Project::where('id','=',$projectGroup->project_id)->first();
        } else {
            $projectGroup = null;
            $project = null;
        }

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

            foreach($emails as $email) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
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

                    if(!self::emailExists($email)) {
                        //
                        // Create the new user.
                        //
                        $user = new User;
                        $user->username = $username;
                        $user->email = $email;
                        $password = uniqid();
                        $user->password = bcrypt($password);
                        $token = RegisterController::makeRegToken();
                        $user->regtoken = $token;

                        $preferences = [];
                        $preferences['created_at'] = Carbon::now();
                        $preferences['language'] = 'en';
                        $preferences['first_name'] = 'New';
                        $preferences['last_name'] = 'User';
                        $preferences['logo_target'] = 2;
                        $preferences['profile_pic'] = '';
                        $preferences['organization'] = 'None';
                        $preferences['onboarding'] = 1;
                        $preferences['use_dashboard'] = 1;
                        $preferences['form_tab_selection'] = 2;
                        $preferences['proj_tab_selection'] = 2;

                        $user->preferences = $preferences;
                        $user->save();
                        array_push($user_ids, $user->id);

                        //
                        // Send a confirmation email.
                        //
                        $emailOptions = [
                            'token' => $token, 'password' => $password, 'username' => $username,
                            'personal_message' => $personal_message, 'sender' => Auth::User(), 'project' => $project,
                            'projectGroup' => $projectGroup, 'email' => $email
                        ];
                        $job = new UserEmails('BatchUserInvite', $emailOptions);
                        $job->handle();
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

			if(isset($request->return_user_ids))
				return $user_ids;
			else
				return redirect('admin/users')->with('k3_global_success', 'batch_users')->with('batch_users_created', $created)->with('batch_users_skipped', $skipped)->with('notification', $notification);
        }
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
}
