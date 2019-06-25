<?php namespace App\Http\Controllers\Auth;

use App\Commands\UserEmails;
use App\Form;
use App\Http\Requests\UserRequest;
use App\Project;
use App\ProjectGroup;
use App\Http\Controllers\Controller;
use App\Record;
use App\Revision;
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

class UserController extends Controller {

    /*
    |--------------------------------------------------------------------------
    | User Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles User based functions
    |
    */

    // Logo Target Options
    const DASHBOARD = 1;
    const PROJECTS = 2;

    protected static $logoTargetOptions = array(
        self::DASHBOARD => 'Dashboard',
        self::PROJECTS  => 'Projects',
    );

    // Projects Page Tab Selection Options
    const CUSTOM = 1;
    const ALPHABETICAL = 2;

    protected static $projPageTabSelOptions = array(
        self::CUSTOM  => 'Custom',
        self::ALPHABETICAL => 'Alphabetical'
    );

    // Single Project Page Tab Selection
    const SINGLE_CUSTOM = 1;
    const SINGLE_ALPHABETICAL = 2;

    protected static $singleProjTabSelOptions = array(
        self::SINGLE_CUSTOM  => 'Custom',
        self::SINGLE_ALPHABETICAL => 'Alphabetical'
    );

    public static function logoTargetOptions() {
        return static::$logoTargetOptions;
    }

    public static function projPageTabSelOptions() {
        return static::$projPageTabSelOptions;
    }

    public static function singleProjTabSelOptions() {
        return static::$singleProjTabSelOptions;
    }

    /**
     * Constructs the controller and checks if user is authenticated and activated.
     */
    public function __construct() {
        $this->middleware('auth', ['except' => ['activate', 'activator', 'activateshow', 'activateFromInvite']]);
        $this->middleware('active', ['except' => ['activate', 'resendActivation', 'activator', 'activateshow', 'activateFromInvite', 'validateUserFields', 'updateFromEmail']]);
    }

    /**
     * Quick link to get to the profile of the logged in user.
     *
     * @return Redirect
     */
    public function redirect() {
        return redirect('user/'.Auth::user()->id);
    }

    /**
     * Gets info for profile and returns profile view. Also gathers records and systems permission sets.
     *
     * @return View
     */
    public function index(Request $request, $uid, $section = '') {
        if(!\Auth::user()->admin && \Auth::user()->id != $request->uid)
            return redirect('user')->with('k3_global_error', 'cannot_edit_profile');

        $section = (($section && in_array($section, ['permissions', 'history'])) ? $section : 'profile');

        $user = User::where('id',$uid)->get()->first();

        $admin = $user->admin;

        $notification = array(
          'message' => '',
          'description' => '',
          'warning' => false,
          'static' => false
        );

        $prevUrlArray = $request->session()->get('_previous');
        $prevUrl = reset($prevUrlArray);

        if($prevUrl !== url()->current()) {
          $session = $request->session()->get('k3_global_success');
          $changes = $request->session()->get('user_changes');

          if($session == 'user_updated') {
            if(in_array('password', $changes)) {
              $notification['message'] = 'Password Successfully Changed!';
              $notification['static'] = true;
            } else {
              $notification['message'] = 'Profile Successfully Updated!';
            }
          }
        }

        if($section == 'permissions') {
            if($admin) {
                return view('user/profile-permissions',compact('user', 'admin',  'section', 'notification'));
            } else {
                $projects = self::buildProjectsArray($user);
                $forms = self::buildFormsArray($user);
                return view('user/profile-permissions',compact('user', 'admin', 'projects', 'forms', 'section', 'notification'));
            }
        } else if ($section == 'history') {
            // Record History revisions
            $sec = $request->input('sec') === null ? 'rm' : $request->input('sec');
            $pageCount = $request->input('page-count') === null ? 10 : app('request')->input('page-count');
            $page = $request->input('page') === null ? 1 : app('request')->input('page');
            // Recently Modified Order
            $rm_order = $request->input('rm-order') === null ? 'lmd' : app('request')->input('rm-order');
            $rm_order_type = substr($rm_order, 0, 2) === "lm" ? "created_at" : "id";
            $rm_order_direction = substr($rm_order, 2, 3) === "a" ? "asc" : "desc";
            // My Created Records Order
            $mcr_order = $request->input('mcr-order') === null ? 'lmd' : app('request')->input('mcr-order');
            $mcr_order_type = substr($mcr_order, 0, 2) === "lm" ? "created_at" : "kid";
            $mcr_order_direction = substr($mcr_order, 2, 3) === "a" ? "asc" : "desc";

            //Get all the revisions and records for user in every form they have access to
            //We have to basically joins these tables together for each forms record table, and then we can sort properly below
            //ALso did custom pagination since the unions break laravel's pagination system
            $first = true;
            $userRevisions = null;
            $userCreatedRecords = null;
            foreach($user->allowedProjects() as $project) {
                foreach($user->allowedForms($project->id) as $form) {
                    $fid = $form->id;
                    $recMod = new Record(array(),$fid);

                    if($first) {
                        $userRevisions = Revision::leftJoin("records_$fid", "revisions.record_kid", "=", "records_$fid.kid")
                            ->select("revisions.*", "records_$fid.kid", "records_$fid.project_id")
                            ->where("revisions.owner", "=", $user->username)
                            ->whereNotNull("kid");

                        $userCreatedRecords = $recMod->newQuery()->select('kid','created_at')->where('owner', '=', $user->id);

                        $first = false;
                    } else {
                        $userRevisions = $userRevisions->union(
                            Revision::leftJoin("records_$fid", "revisions.record_kid", "=", "records_$fid.kid")
                                ->select("revisions.*", "records_$fid.kid", "records_$fid.project_id")
                                ->where("revisions.owner", "=", $user->username)
                                ->whereNotNull("kid")
                        );

                        $userCreatedRecords = $userCreatedRecords->union(
                            $recMod->newQuery()->select('kid','created_at')->where('owner', '=', $user->id)
                        );
                    }
                }
            }

            $rmCount = $userRevisions->get()->count();
            $mcrCount = $userCreatedRecords->get()->count();
            $userRevisions = $userRevisions->orderBy($rm_order_type, $rm_order_direction)->skip(($page-1)*$pageCount)->take($pageCount)->get();
            $userCreatedRecords = $userCreatedRecords->orderBy($mcr_order_type, $mcr_order_direction)->skip(($page-1)*$pageCount)->take($pageCount)->pluck('kid')->toArray();

            return view('user/profile-record-history',compact('user', 'admin', 'userRevisions',
                'userCreatedRecords', 'section', 'sec', 'notification', 'page', 'pageCount', 'rmCount', 'mcrCount'));
        } else {
            return view('user/profile',compact('user', 'admin', 'section', 'notification'));
        }
    }

    /**
     * Returns the edit profile view.
     *
     * @param  Request $request
     * @return View
     */
    public function editProfile(Request $request) {
        if(!\Auth::user()->admin && \Auth::user()->id!=$request->uid)
            return redirect('user')->with('k3_global_error', 'cannot_edit_profile');

        if(\Auth::user()->admin)
          $user = User::where('id', '=', $request->uid)->first();
        else
          $user = \Auth::user();

        return view('user/edit', compact('user'));
    }

    /**
     * User updating profile information.
     *
     * @param  Request $request
     * @return Redirect
     */
    public function update(Request $request) {
      if(!\Auth::user()->admin && \Auth::user()->id != $request->uid)
          return response()->json(["status" => false, "message" => "cannot_update_user"], 200);

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
      if(!empty($newEmail) && $newEmail != $user->email) {
          $user->email = $newEmail;
          array_push($message, 'email');
      }
      
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

      return redirect('user/'.Auth::user()->id)->with('k3_global_success', 'user_updated')->with('user_changes', $message);
    }

    /**
     * Create account from email invite
     * Since we 'create' the account when we invite the user, we are updating their things rather than creating them
     * Can't use the 'Update' function above since we need this function to send the activation email
     *
     * What to return here?
     */
    public function updateFromEmail(Request $request) {
      if(!\Auth::user()->admin && \Auth::user()->id != $request->uid)
        return response()->json(["status" => false, "message" => "cannot_update_user"], 200);

      $message = array();
      $user = User::where('id', '=', $request->uid)->first();
      $newFirstName = $request->first_name;
      $newLastName = $request->last_name;
      $newUsername = $request->username;
      $newProfilePic = $request->profile;
      $newOrganization = $request->organization;
      $newPass = $request->password;
      $confirm = $request->password_confirmation;

      $userPrefs = $user->preferences; // doesn't access property directly, uses __get

      $user->username = $newUsername;

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

        $user->preferences = $userPrefs; // __set
        $user->save();

      if(!empty($newProfilePic)) {
        $changePicResponse = json_decode($this->changepicture($request, $user), true);
        if($changePicResponse['status'])
          array_push($message, $changePicResponse['message']);
      }

      // Send email
        $job = new UserEmails('UserActivationRequest', ['token' => null, 'email' => \Auth::user()->email]);
        $job->handle();

      return redirect('user/'.Auth::user()->id)->with('k3_global_success', 'user_updated')->with('user_changes', $message);
    }

    /**
      * User deleting own account
      */
    public function delete(Request $request) {
        if(!\Auth::user()->admin && \Auth::user()->id != $request->uid)
          return redirect('user/'.\Auth::user()->id)->with('k3_global_error', 'cannot_delete_profile');

        if($request->uid == 1)
          return redirect('user/'.\Auth::user()->id)->with('k3_global_error', 'cannot_delete_root_admin');

        $user = User::where('id', '=', $request->uid)->first();
        $selfDelete = (\Auth::user()->id == $request->uid);
        $user->delete();

        if($selfDelete)
            return redirect('/')->with('k3_global_success', 'account_deleted');
        else if (\Auth::user()->admin)
            return redirect('admin/users')->with('k3_global_success', 'user_deleted');
        else
            return redirect('/')->with('k3_global_success', 'account_deleted');
    }

    /**
     * Editing a user's preferences.
     *
     * @param  int $uid - User's Id
     * @return View
     */
    public function preferences($uid) {
        if(\Auth::user()->id != $uid)
            return redirect('user')->with('k3_global_error', 'cannot_edit_preferences');

        $user = \Auth::user();
        $logoTargetOptions = self::logoTargetOptions();
        $projPageTabSelOptions = self::projPageTabSelOptions();
        $singleProjTabSelOptions = self::singleProjTabSelOptions();

        $notification = array(
            'message' => '',
            'description' => '',
            'warning' => false,
            'static' => false
        );

        return view('user.preferences', compact('user', 'logoTargetOptions', 'projPageTabSelOptions', 'singleProjTabSelOptions', 'sideMenuOptions', 'notification'));
    }

    /**
     * Actually updates the user's profile.
     *
     * @param  int $uid - User's Id
     * @param  Request $request
     * @return View
     */
    public function updatePreferences($uid, Request $request) {
        if(\Auth::user()->id != $uid)
            return redirect('user/'.\Auth::user()->id.'/preferences')->with('k3_global_error', 'cannot_edit_preferences');

        $user = \Auth::user();
        $userPref = $user->preferences;

        $userPref['use_dashboard'] = ($request->useDashboard == "true" ? 1 : 0);
        $userPref['logo_target'] = $request->logoTarget;
        $userPref['proj_tab_selection'] = $request->projPageTabSel;
        $userPref['form_tab_selection'] = $request->formPageTabSel;

        $user->preferences = $userPref;
        $user->save();

        $logoTargetOptions = self::logoTargetOptions();
        $projPageTabSelOptions = self::projPageTabSelOptions();
        $singleProjTabSelOptions = self::singleProjTabSelOptions();

        $notification = array(
            'message' => 'Preferences Successfully Updated!',
            'description' => '',
            'warning' => false,
            'static' => false
        );

        return view('user.preferences', compact('user', 'logoTargetOptions', 'projPageTabSelOptions', 'singleProjTabSelOptions', 'sideMenuOptions', 'notification'));
    }

	 // triggered from onboarding.js and from 'replay kora intro' button on user preferences page
    public function toggleOnboarding () {
		$user = \Auth::user();
		$userPrefs = $user->preferences;

        if ($userPrefs['onboarding'] == 1) {
            $userPrefs['onboarding'] = 0;
            $user->preferences = $userPrefs;
			$user->save();
        } else {
            $userPrefs['onboarding'] = 1;
            $user->preferences = $userPrefs;
			$user->save();
            return redirect('/');
        }
    }

    /**
     * Return a specific user preference
     *
     * @param  string $pref - The requested preference
     * @return array - The preference value
     */
    public static function returnUserPrefs($pref) {
        if(\Auth::user()) {
            $user = \Auth::user();
            return $user->preferences[$pref];
        } else if(\Auth::guest()) {
            // if user is guest, create default set of preferences
            $preferences = array();
            $preferences['use_dashboard'] = 1;
            $preferences['logo_target'] = 2;
            $preferences['proj_tab_selection'] = 2;
            $preferences['form_tab_selection'] = 2;
			$preferences['onboarding'] = 1;

            return $preferences[$pref];
        }
    }

	public static function getOnboardingProjects (User $user) {
        $all_projects = Project::all()->sortBy("name", SORT_NATURAL|SORT_FLAG_CASE);
        $projects = array();
        $requestableProjects = array();
        foreach ($all_projects as $project) {
            if ($project->active) {
                if ($user->admin || $user->inAProjectGroup($project)) {
                    array_push($projects, $project->name);
                } else {
                    $requestableProjects[$project->id] = $project->name;
                }
            }
        }
        return array($projects, $requestableProjects);
    }

    /**
     * Validate a user model.
     *
     * @param  UserRequest $request
     * @return JsonResponse
     */
    public function validateUserFields(UserRequest $request) {
        return response()->json(["status"=>true, "message"=>"User Valid", 200]);
    }

    /**
     * Changes the user profile picture and returns the pic URI.
     *
     * @param  Request $request
     * @return JsonResponse
     */
    public function changepicture(Request $request, $user) {
        $file = $request->profile;
        $pDir = storage_path('app/profiles/'.$user->id.'/');
        $pURL = url('app/profiles/'.$user->id).'/';

        //remove old pic
        $oldFile = $pDir.$user->preferences['profile_pic'];
        if(file_exists($oldFile))
            unlink($oldFile);

        //set new pic to db
        $newFilename = $file->getClientOriginalName();

        $prefs = $user->preferences;
        $prefs['profile_pic'] = $newFilename;
        $user->preferences = $prefs;
        $user->save();

        //move photo and return new path
        $file->move($pDir, $newFilename);

        return json_encode(["status"=>true,"message"=>"profile_pic_updated","pic_url"=>$pURL.$newFilename],200);
    }

    /**
     * Returns the view for the user activation page.
     *
     * @return View
     */
    public function activateshow() {
        if(is_null(\Auth::user()))
            return redirect('register');
        else if(!\Auth::user()->active) {
			$notification = array(
				'message' => '',
				'description' => '',
				'warning' => false,
				'static' => false
			);
			return view('auth.activate', compact('notification'));
        } else
            return redirect('projects');
    }

    /**
     * Returns the view for the user activation page.
     *
     * @return Redirect
     */
    public function resendActivation() {
        $token = \Auth::user()->token;

        //Send email
        $job = new UserEmails('UserActivationRequest', ['token' => $token, 'email' => \Auth::user()->email]);
        $job->handle();

        return redirect('/')->with('status', 'user_activate_resent');
    }

    /**
     * Validates registration token to activate a user.
     *
     * @param  Request $request
     * @return Redirect
     */
    public function activator(Request $request) {
        $user = User::where('username', '=', \Auth::user()->username)->first();
        if($user==null)
            return redirect('auth/activate')->with('k3_global_error', 'user_doesnt_exist');

        $token = trim($request->activationtoken);

        if(!empty($user->regtoken) && strcmp($user->regtoken, $token) == 0 && !($user->active == 1)) {
            $user->active = 1;
            $user->save();

            \Auth::login($user);

            $this->makeDefaultProject($user);

            return redirect('/');
        } else {
            return redirect('/')->with('status', 'bad_activation_token');
        }
    }

    /**
     * Handles activation from an email link.
     *
     * @param  String $token - Token user will register with
     * @return Redirect
     */
    public function activate($token) {
        //Since we are coming from an email client or otherwise, we need to make sure that no one on the browser is already
        // logged in.
        if(!is_null(\Auth::user()))
            \Auth::logout(\Auth::user()->id);

        $user = User::where('regtoken', '=', $token)->first();

        \Auth::login($user);

        if($token != $user->regtoken) {
            return redirect('/')->with('status', 'bad_activation_token');
        } else {
            $user->active = 1;
            $user->save();

            $this->makeDefaultProject($user);

            return redirect('/');
        }
    }

    /**
     * Account registration from email invitation.
     *
     * @param String $token - Token to register and identify user with
     * @return View
     */
    public function activateFromInvite ($token) {
        // coming from email client or otherwise, need to make sure no one on the browser is already logged in.
        if(!is_null(\Auth::user()))
            \Auth::logout(\Auth::user()->id);

        $user = User::where('regtoken', '=', $token)->first();
        \Auth::login($user);

        if($token != $user->regtoken)
            return redirect('/')->with('status', 'bad_activation_token');

        return view('auth.invited-register', compact('user'));
    }

    /**
     * Creates a default project for the new user. Kept private because this should only happen on activation by user.
     *
     * @param  User $user - User to make default project
     */
    private function makeDefaultProject($user) {
        $default = new Project();

        $default->name = "Test Project for ".$user->username;
        $default->description = "Test project for user, ".$user->username;
        $default->save();

        $adminGroup = ProjectGroup::makeAdminGroup($default);
        ProjectGroup::makeDefaultGroup($default);
        $default->adminGroup_id = $adminGroup->id;
        $default->active = 1;
        $default->internal_name = str_replace(" ","_", $default->name).'_'.$default->id.'_';
        $default->save();
    }

    /**
     * Updates the users list of custom projects.
     *
     * @param  Request $request
     */
    public function saveProjectCustomOrder(Request $request) {
        $pids = $request->pids;
        $user = Auth::user();

        //We are going to delete the old ones, and just rebuild the list entirely for the user
        $check = DB::table("project_custom")->where("user_id", "=", $user->id)->first();
        $time = Carbon::now();

        //Create or edit custom project list for user
        if(is_null($check)) {
            DB::table('project_custom')->insert(
                ['user_id' => $user->id, 'organization' => json_encode($pids),
                    "created_at" => $time,
                    "updated_at" => $time]
            );
        } else {
            DB::table('project_custom')->where("id", "=", $check->id)->update(
                ['organization' => json_encode($pids),
                    "updated_at" => $time]
            );
        }
    }

    /**
     * Updates the users list of custom forms for a project.
     *
     * @param  int $pid - Project ID
     * @param  Request $request
     */
    public function saveFormCustomOrder($pid, Request $request) {
        $fids = $request->fids;
        $user = Auth::user();

        //We are going to delete the old ones, and just rebuild the list entirely for the user
        $check = DB::table("form_custom")->where("user_id", "=", $user->id)
            ->where("project_id", "=", $pid)->first();
        $time = Carbon::now();

        //Create or edit custom form list for user
        if(is_null($check)) {
            DB::table('form_custom')->insert(
                ['user_id' => $user->id, 'project_id' => $pid,'organization' => json_encode($fids),
                    "created_at" => $time,
                    "updated_at" => $time]
            );
        } else {
            DB::table('form_custom')->where("id", "=", $check->id)->update(
                ['organization' => json_encode($fids),
                    "updated_at" => $time]
            );
        }
    }

    /**
     * Build permission set array of all the users projects
     *
     * @param  User $user - User to get information for
     * @return array - Project permission set information
     */
    public static function buildProjectsArray(User $user) {
        $all_projects = Project::all();
        $projects = array();
        $i=0;
        foreach($all_projects as $project) {
            if($user->inAProjectGroup($project)) {
                $permissions = '';
                $projects[$i]['id'] = $project->id;
                $projects[$i]['name'] = $project->name;
                $projects[$i]['group'] = $user->getProjectGroup($project);

                if($user->isProjectAdmin($project)) {
                    $projects[$i]['permissions'] = 'Admin';
                } else {
                    // Get Permissions
                    if($user->canCreateForms($project))
                        $permissions .= 'Create Forms | ';
                    if($user->canEditForms($project))
                        $permissions .= 'Edit Forms | ';
                    if($user->canDeleteForms($project))
                        $permissions .= 'Delete Forms | ';
                    if($permissions == '')
                        $permissions .= 'Read Only';

                    $projects[$i]['permissions'] = self::buildPermissionsString(rtrim($permissions, '| '));
                }
            }
            $i++;
        }
        return $projects;
    }

    /**
     * Build permission set array of all the users forms
     *
     * @param  User $user - User to get information for
     * @return array - Form permission set information
     */
    public static function buildFormsArray(User $user) {
        $i=0;
        $all_forms = Form::all();
        $forms = array();
        foreach($all_forms as $form) {
            if($user->inAFormGroup($form)) {
                $permissions = '';
                $forms[$i]['id'] = $form->id;
                $forms[$i]['project_id'] = $form->project_id;
                $forms[$i]['name'] = $form->name;
                $forms[$i]['group'] = $user->getFormGroup($form);

                if($user->isFormAdmin($form))
                    $forms[$i]['permissions'] = 'Admin';
                else {
                    if($user->canCreateFields($form))
                        $permissions .= 'Create Fields | ';
                    if($user->canEditFields($form))
                        $permissions .= 'Edit Fields | ';
                    if($user->canDeleteFields($form))
                        $permissions .= 'Delete Fields | ';
                    if($user->canIngestRecords($form))
                        $permissions .= 'Create Records | ';
                    if($user->canModifyRecords($form))
                        $permissions .= 'Edit Records | ';
                    if($user->canDestroyRecords($form))
                        $permissions .= 'Delete Records | ';
                    if($permissions == '')
                        $permissions .= 'Read Only ';
                    $forms[$i]['permissions'] = self::buildPermissionsString(rtrim($permissions, '| '));
                }
            }
            $i++;
        }
        return $forms;
    }

    /**
     * Builds the string that represents a User's permissions for saving.
     *
     * @param  string $permissions - Pre-formatted permissions
     * @return string - The formatted string
     */
    public static function buildPermissionsString($permissions) {
        $permissionsArray = explode(" | ", $permissions);
        if(count($permissionsArray) == 1) {
            return $permissionsArray[0];
        } else if(count($permissionsArray) == 2) {
            return implode(' and ', $permissionsArray);
        } else if(count($permissionsArray) > 2) {
            $lastIndex = count($permissionsArray) - 1;
            $permissionsArray[$lastIndex] = 'and ' . $permissionsArray[$lastIndex];
            return implode(', ', $permissionsArray);
        }
    }
}
