<?php namespace App\Http\Controllers\Auth;

use App\Form;
use App\Http\Requests\UserRequest;
use App\Preference;
use App\Project;
use App\ProjectGroup;
use App\Record;
use App\Revision;
use App\Http\Controllers\Controller;
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

    /**
     * Constructs the controller and checks if user is authenticated and activated.
     */
    public function __construct() {
        $this->middleware('auth', ['except' => ['activate', 'activator', 'activateshow']]);
        $this->middleware('active', ['except' => ['activate', 'resendActivation', 'activator', 'activateshow']]);
    }

    /**
     * Quick link to get to the profile of the logged in user
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
        if (!\Auth::user()->admin && \Auth::user()->id != $request->uid)
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

        if ($prevUrl !== url()->current()) {
          $session = $request->session()->get('k3_global_success');
          $changes = $request->session()->get('user_changes');

          if ($session == 'user_updated') {
            if (in_array('password', $changes)) {
              $notification['message'] = 'Password Successfully Reset!';
              $notification['static'] = true;
            } else {
              $notification['message'] = 'Profile Successfully Updated!';
            }
          }
        }

        if ($section == 'permissions') {
            if($admin) {
                return view('user/profile-permissions',compact('user', 'admin',  'section', 'notification'));
            } else {
                $projects = self::buildProjectsArray($user);
                $forms = self::buildFormsArray($user);
                return view('user/profile-permissions',compact('user', 'admin', 'projects', 'forms', 'section', 'notification'));
            }
        } elseif ($section == 'history') {
            // Record History revisions
            $sec = $request->input('sec') === null ? 'rm' : $request->input('sec');
            $pagination = $request->input('page-count') === null ? 10 : app('request')->input('page-count');
            // Recently Modified Order
            $rm_order = $request->input('rm-order') === null ? 'lmd' : app('request')->input('rm-order');
            $rm_order_type = substr($rm_order, 0, 2) === "lm" ? "revisions.created_at" : "revisions.id";
            $rm_order_direction = substr($rm_order, 2, 3) === "a" ? "asc" : "desc";
            // My Created Records Order
            $mcr_order = $request->input('mcr-order') === null ? 'lmd' : app('request')->input('mcr-order');
            $mcr_order_type = substr($mcr_order, 0, 2) === "lm" ? "records.created_at" : "records.rid";
            $mcr_order_direction = substr($mcr_order, 2, 3) === "a" ? "asc" : "desc";
            $userRevisions = Revision::leftJoin('records', 'revisions.rid', '=', 'records.rid')
                ->leftJoin('users', 'revisions.owner', '=', 'users.id')
                ->select('revisions.*', 'records.kid', 'records.pid', 'users.username as ownerUsername')
                ->where('revisions.username', '=', $user->username)
                ->whereNotNull('kid')
                ->orderBy($rm_order_type, $rm_order_direction)
                ->paginate($pagination);
            $userCreatedRecords = Record::where('owner', '=', $user->id)
                ->whereNotNull('kid')
                ->orderBy($mcr_order_type, $mcr_order_direction)
                ->paginate($pagination);

            return view('user/profile-record-history',compact('user', 'admin', 'userRevisions', 'userOwnedRevisions', 'userCreatedRecords', 'section', 'sec', 'notification'));
        } else {
            return view('user/profile',compact('user', 'admin', 'section', 'notification'));
        }
    }

    public function editProfile(Request $request) {
        if (!\Auth::user()->admin && \Auth::user()->id!=$request->uid)
            return redirect('user')->with('k3_global_error', 'cannot_edit_profile');

        if (\Auth::user()->admin) {
          $user = User::where('id', '=', $request->uid)->first();
        } else {
          $user = \Auth::user();
        }

        return view('user/edit', compact('user'));
    }

    /**
      * User updating profile information
      */
    public function update(Request $request) {
      if (!\Auth::user()->admin && \Auth::user()->id != $request->uid) {
        return response()->json(["status" => false, "message" => "cannot_update_user"], 200);
      }

      $message = array();
      $user = User::where('id', '=', $request->uid)->first();
      $newFirstName = $request->first_name;
      $newLastName = $request->last_name;
      $newProfilePic = $request->profile;
      $newOrganization = $request->organization;
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

      if (!empty($newOrganization) && $newOrganization != $user->organization) {
        $user->organization = $newOrganization;
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

      $user->save();

      if (!empty($newProfilePic)) {
        $changePicResponse = json_decode($this->changepicture($request, $user), true);
        if ($changePicResponse['status']) {
          array_push($message, $changePicResponse['message']);
        }
      }

      //return response()->json(["status" => true, "message" => $message], 200);
      return redirect('user/'.Auth::user()->id)->with('k3_global_success', 'user_updated')->with('user_changes', $message);
    }

    /**
      * User deleting own account
      */
    public function delete(Request $request) {
        if (!\Auth::user()->admin && \Auth::user()->id != $request->uid) {
          return redirect('user/'.\Auth::user()->id)->with('k3_global_error', 'cannot_delete_profile');
        }

        if ($request->uid == 1) {
          return redirect('user/'.\Auth::user()->id)->with('k3_global_error', 'cannot_delete_root_admin');
        }

        $user = User::where('id', '=', $request->uid)->first();
        $selfDelete = (\Auth::user()->id == $request->uid);
        $user->delete();

        if ($selfDelete) {
            return redirect('/')->with('k3_global_success', 'account_deleted');
        } elseif (\Auth::user()->admin) {
            return redirect('admin/users')->with('k3_global_success', 'user_deleted');
        } else {
            return redirect('/')->with('k3_global_success', 'account_deleted');
        }
    }

    /**
     * Editing a user's preferences
     *
     * @param $uid User's Id
     * @return View User prefernce view
     */
    public function preferences($uid) {
        if (\Auth::user()->id != $uid)
            return redirect('user')->with('k3_global_error', 'cannot_edit_preferences');

        $user = \Auth::user();
        $preference = Preference::where('user_id', '=' ,$user->id)->first();
        $logoTargetOptions = Preference::logoTargetOptions();
        $projPageTabSelOptions = Preference::projPageTabSelOptions();
        $singleProjTabSelOptions = Preference::singleProjTabSelOptions();

        if (is_null($preference)) {
            // Must create user preference
            $preference = new Preference;
            $preference->user_id = $user->id;
            $preference->use_dashboard = 1;
            $preference->logo_target = 1;
            $preference->proj_page_tab_selection = 3;
            $preference->single_proj_page_tab_selection = 3;
            $preference->created_at = Carbon::now();
            $preference->save();
        }

        $notification = array(
            'message' => '',
            'description' => '',
            'warning' => false,
            'static' => false
        );

        return view('user.preferences', compact('user', 'preference', 'logoTargetOptions', 'projPageTabSelOptions', 'singleProjTabSelOptions', 'sideMenuOptions', 'notification'));
    }

    /**
     * @param $uid User's Id
     * @param Request $request Form inputs
     * @return Redirect to user's preferences
     */
    public function updatePreferences($uid, Request $request) {
        if (\Auth::user()->id != $uid)
            return redirect('user/'.\Auth::user()->id.'/preferences')->with('k3_global_error', 'cannot_edit_preferences');

        $user = \Auth::user();

        $preference = Preference::where('user_id', '=', $user->id)->first();

        if (is_null($preference)) {
            // Must create user preference
            $preference = new Preference;
            $preference->user_id = $user->id;
            $preference->created_at = Carbon::now();
        }

        $preference->use_dashboard = ($request->useDashboard == "true" ? 1 : 0);
        $preference->logo_target = $request->logoTarget;
        $preference->proj_page_tab_selection = $request->projPageTabSel;
        $preference->single_proj_page_tab_selection = $request->singleProjPageTabSel;

        $preference->save();

        $logoTargetOptions = Preference::logoTargetOptions();
        $projPageTabSelOptions = Preference::projPageTabSelOptions();
        $singleProjTabSelOptions = Preference::singleProjTabSelOptions();

        $notification = array(
            'message' => 'Preferences Successfully Updated!',
            'description' => '',
            'warning' => false,
            'static' => false
        );

        return view('user.preferences', compact('user', 'preference', 'logoTargetOptions', 'projPageTabSelOptions', 'singleProjTabSelOptions', 'sideMenuOptions', 'notification'));
    }

    public static function returnUserPrefs ($pref) {
        if (\Auth::user()) {
            $user = \Auth::user();
            $preference = Preference::where('user_id', '=', $user->id)->first();

            if (is_null($preference)) {
                $preference = new Preference();
                $preference->use_dashboard = 1;
                $preference->logo_target = 1;
                $preference->proj_page_tab_selection = 3;
                $preference->single_proj_page_tab_selection = 3;
            }

            $preference = $preference->$pref;

            // use_dashboard :: 0 or 1
            // logo_target :: 1 or 2
            // proj_page_tab_selection :: 1, 2, or 3 :: archived//custom//alphabetical
            // single_proj_page_tab_selection :: 2 or 3 :: custom//alphabetical

            return $preference;
        } else if (\Auth::guest()) {
            // if user is guest, create default set of preferences
            $preference = new Preference;
            $preference->use_dashboard = 1;
            $preference->logo_target = 1;
            $preference->proj_page_tab_selection = 3;
            $preference->single_proj_page_tab_selection = 3;

            $preference = $preference->$pref;

            return $preference;
        }
    }

    public function validateUserFields(UserRequest $request) {
        return response()->json(["status"=>true, "message"=>"User Valid", 200]);
    }

    /**
     * Changes the user profile picture and returns the pic URI.
     *
     * @param  Request $request
     * @return JsonResponse - URI of pic
     */
    public function changepicture(Request $request, $user) {
        $file = $request->profile;
        $pDir = storage_path('app/profiles/'.$user->id.'/');
        $pURL = url('app/profiles/'.$user->id).'/';

        //remove old pic
        $oldFile = $pDir.$user->profile;
        if(file_exists($oldFile))
            unlink($oldFile);

        //set new pic to db
        $newFilename = $file->getClientOriginalName();

        $user->profile = $newFilename;
        $user->save();

        //move photo and return new path
        $file->move($pDir, $newFilename);

        return json_encode(["status"=>true,"message"=>"profile_pic_updated","pic_url"=>$pURL.$newFilename],200);
    }

    /**
     * Validates and changes user password.
     *
     * @param  Request $request
     * @return Redirect
     */
    public function changepw(Request $request) {
        $user = Auth::user();
        $new_pass = $request->new_password;
        $confirm = $request->confirm;

        if(empty($new_pass) && empty($confirm)) {
            return redirect('user/profile')->with('k3_global_error', 'fill_both_passwords');
        } else if(strlen($new_pass) < 6) {
            return redirect('user/profile')->with('k3_global_error', 'password_minimum');
        } else if($new_pass != $confirm) {
            return redirect('user/profile')->with('k3_global_error', 'passwords_unmatched');
        } else {
            $user->password = bcrypt($new_pass);
            $user->save();

            return redirect('user/profile')->with('k3_global_success', 'password_change_success');
        }
    }

    /**
     * Returns the view for the user activation page.
     *
     * @return View
     */
    public function activateshow() {
        $notification = array(
            'message' => '',
            'description' => '',
            'warning' => false,
            'static' => false
        );
		return view('auth.activate', compact('notification'));
		
        if(is_null(\Auth::user()))
            return redirect('register');
        elseif (!\Auth::user()->active)
            return view('auth.activate');
        else
            return redirect('projects');
    }

    /**
     * Returns the view for the user activation page.
     *
     * @return View
     */
    public function resendActivation() {
        $token = \Auth::user()->token;

        //Send email
        try {
            Mail::send('emails.activation', compact('token'), function($message)
            {
                $message->from(env('MAIL_FROM_ADDRESS'));
                $message->to(\Auth::user()->email);
                $message->subject('Kora Account Activation');
            });
        } catch(\Swift_TransportException $e) {
            //Log for now
            Log::info('Resend activation email failed');
            return redirect('/')->with('status', 'activation_email_failed');
        }

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
     * Creates a default project for the new user. Kept private because this should only happen on activation by user.
     *
     * @param  User $user - User to make default project
     */
    private function makeDefaultProject($user) {
        $default = new Project();

        $default->name = "ZZTest ".$user->username;
        $slugUser = preg_replace('/[^A-Za-z0-9_]/', '_', $user->username);
        $default->slug = "ZZTest_".$slugUser;
        $default->description = "Test project for user, ".$user->username;
        $default->save();

        $adminGroup = ProjectGroup::makeAdminGroup($default);
        ProjectGroup::makeDefaultGroup($default);
        $default->adminGID = $adminGroup->id;
        $default->active = 1;
        $default->save();
    }

    /**
     * Updates the users list of custom projects.
     *
     * @param  Request $request
     */
    public function saveProjectCustomOrder(Request $request) {
        $pids = $request->pids;

        //We are going to delete the old ones, and just rebuild the list entirely for the user
        DB::table("project_custom")->where("uid", "=", \Auth::user()->id)->delete();

        //Rebuild it!
        $rows = array();
        $index = 0;
        foreach($pids as $pid) {
            $row = ["uid"=>\Auth::user()->id,"pid"=>$pid,"sequence"=>$index];
            array_push($rows,$row);
            $index++;
        }

        //Now save the new order
        DB::table('project_custom')->insert($rows);
    }

    /**
     * Updates the users list of custom forms for a project.
     *
     * @param  int $pid - Project ID
     * @param  Request $request
     */
    public function saveFormCustomOrder($pid, Request $request) {
        $fids = $request->fids;

        //We are going to delete the old ones, and just rebuild the list entirely for the user
        DB::table("form_custom")->where("uid", "=", \Auth::user()->id)
            ->where("pid", "=", $pid)->delete();

        //Rebuild it!
        $rows = array();
        $index = 0;
        foreach($fids as $fid) {
            $row = ["uid"=>\Auth::user()->id,"pid"=>$pid,"fid"=>$fid,"sequence"=>$index];
            array_push($rows,$row);
            $index++;
        }

        //Now save the new order
        DB::table('form_custom')->insert($rows);
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
                $projects[$i]['pid'] = $project->pid;
                $projects[$i]['name'] = $project->name;
                $projects[$i]['group'] = $user->getProjectGroup($project);

                if ($user->isProjectAdmin($project)) {
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
                $forms[$i]['fid'] = $form->fid;
                $forms[$i]['pid'] = $form->pid;
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
                    if ($user->canDestroyRecords($form))
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

    public static function buildPermissionsString($permissions) {
        $permissionsArray = explode(" | ", $permissions);
        if (count($permissionsArray) == 1) {
            return $permissionsArray[0];
        } elseif (count($permissionsArray) == 2) {
            return implode(' and ', $permissionsArray);
        } elseif (count($permissionsArray) > 2) {
            $lastIndex = count($permissionsArray) - 1;
            $permissionsArray[$lastIndex] = 'and ' . $permissionsArray[$lastIndex];
            return implode(', ', $permissionsArray);
        }
    }

    public static function savePreferences(Request $request, $uid) {
        if (!\Auth::user()->id != $uid)
            return redirect('user')->with('k3_global_error', 'cannot_edit_preferences');

        $preference = Preference::firstOrNew(array('uid' => $uid));

        dd($preference);
    }
}
