<?php namespace App\Http\Controllers\Auth;

use App\Form;
use App\Http\Requests\UserRequest;
use App\Project;
use App\ProjectGroup;
use App\Record;
use App\Revision;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        $section = (($section && in_array($section, ['permissions', 'history'])) ? $section : 'profile');

        $user = User::where('id',$uid)->get()->first();

        $admin = $user->admin;

        if ($section == 'permissions') {
            if($admin) {
                return view('user/profile-permissions',compact('user', 'admin',  'section'));
            } else {
                $projects = self::buildProjectsArray($user);
                $forms = self::buildFormsArray($user);
                return view('user/profile-permissions',compact('user', 'admin', 'projects', 'forms', 'section'));
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
            $mcr_order_type = substr($mcr_order, 0, 2) === "lm" ? "revisions.created_at" : "revisions.id";
            $mcr_order_direction = substr($mcr_order, 2, 3) === "a" ? "asc" : "desc";
            $userRevisions = Revision::leftJoin('records', 'revisions.rid', '=', 'records.rid')
                ->leftJoin('users', 'revisions.owner', '=', 'users.id')
                ->select('revisions.*', 'records.kid', 'records.pid', 'users.username as ownerUsername')
                ->where('revisions.username', '=', $user->username)
                ->whereNotNull('kid')
                ->orderBy($rm_order_type, $rm_order_direction)
                ->paginate($pagination);
            $userOwnedRevisions = Revision::leftJoin('records', 'revisions.rid', '=', 'records.rid')
                ->select('revisions.*', 'records.kid', 'records.pid')
                ->where('revisions.owner', '=', $user->id)
                ->whereNotNull('kid')
                ->orderBy($mcr_order_type, $mcr_order_direction)
                ->paginate($pagination);

            return view('user/profile-record-history',compact('user', 'admin', 'userRevisions', 'userOwnedRevisions', 'section', 'sec'));
        } else {
            return view('user/profile',compact('user', 'admin', 'section'));
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
        // return redirect('user/'.\Auth::user()->id)->with('k3_global_error', 'cannot_update_profile');
      }

      $message = array();
      $user = User::where('id', '=', $request->uid)->first();
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

      if (!empty($newOrganization) && $newOrganization != $user->organization) {
        $user->organization = $newOrganization;
        array_push($message, "organization");
      }

      // TODO: When multiple languages implemented, update language change
      // Need to test comparing language code vs language name (en vs English)
      if (!empty($newLanguage) && $newLanguage != $user->language) {
        //$user->language = $newLanguage;
        //array_push($message, "language");
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
          return redirect('/user'.\Auth::user()->id)->with('k3_global_error', 'cannot_delete_root_admin');
        }

        $user = User::where('id', '=', $request->id)->first();
        $user->delete();

        if (\Auth::user()->admin) {
          redirect('admin/users')->with('k3_global_success', 'user_deleted');
        } else {
          redirect('/')->with('k3_global_success', 'account_deleted');
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
        $pDir = config('app.base_path') . 'storage/app/profiles/'.$user->id.'/';
        $pURL = config('app.storage_url') . 'profiles/'.$user->id.'/';

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
     * Change user profile information.
     *
     * @param  Request $request
     * @return JsonResponse - Status of update
     */
    public function changeprofile(Request $request) {
        //TODO:: I want to restructure this when we get to profiles
        $user = Auth::user();
        $type = $request->input("type");

        switch($type) {
            case "lang":
                $lang = $request->input("field");

                if(empty($lang)) {
                    return response()->json(["status"=>false,"message"=>"language_missing"],500);
                } else {
                    $user->language = $lang;
                    $user->save();
                    return response()->json(["status"=>true,"message"=>"language_updated"],200);
                }
                break;
            case "dash":
                $dash = $request->input("field");

                if($dash != "0" && $dash != "1") {
                    return response()->json(["status"=>false,"message"=>"homepage_missing"],500);
                } else {
                    $user->dash = $dash;
                    $user->save();
                    return response()->json(["status"=>true,"message"=>"homepage_updated"],200);
                }
                break;
            case "name":
                //TODO::Big thing to do during said restructure
                $realname = $request->input("field");

                if(empty($realname)) {
                    return response()->json(["status"=>false,"message"=>"name_missing"],500);
                } else {
                    $user->name = $realname;
                    $user->save();
                    return response()->json(["status"=>true,"message"=>"name_updated"],200);
                }
                break;
            case "org":
                $organization = $request->input("field");

                if(empty($organization)) {
                    return response()->json(["status"=>false,"message"=>"organization_missing"],500);
                } else {
                    $user->organization = $organization;
                    $user->save();
                    return response()->json(["status"=>true,"message"=>"organization_updated"],200);
                }
                break;
            default:
                return response()->json(["status"=>false,"message"=>"profile_field_missing"],500);
                break;
        }
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
        if (is_null(\Auth::user())) {
          return redirect('register');
        } elseif (!\Auth::user()->active) {
          return view('auth.activate');
        } else {
          return redirect('projects');
        }
    }

    /**
     * Returns the view for the user activation page.
     *
     * @return View
     */
    public function resendActivation() {
        $token = \Auth::user()->token;

        Mail::send('emails.activation', compact('token'), function($message)
        {
            $message->from(env('MAIL_FROM_ADDRESS'));
            $message->to(\Auth::user()->email);
            $message->subject('Kora Account Activation');
        });

        return redirect('auth/activate')->with('k3_global_success', 'user_activate_resent');
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

            return redirect('/')->with('k3_global_success', 'user_activated');
        } else {
            return redirect('auth/activate')->with('k3_global_error', 'bad_activation_token');
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
            return redirect('/')->with('k3_global_error', 'bad_activation_token');
        } else {
            $user->active = 1;
            $user->save();

            $this->makeDefaultProject($user);

            return redirect('/')->with('k3_global_success', 'user_activated');
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
            return strtolower($permissionsArray[0]);
        } elseif (count($permissionsArray) == 2) {
            return strtolower(implode(' and ', $permissionsArray));
        } elseif (count($permissionsArray) > 2) {
            $lastIndex = count($permissionsArray) - 1;
            $permissionsArray[$lastIndex] = 'and ' . $permissionsArray[$lastIndex];
            return strtolower(implode(', ', $permissionsArray));
        }
    }
}
