<?php namespace App\Http\Controllers\Auth;

use App\Form;
use App\Project;
use App\Record;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
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
        $this->middleware('active', ['except' => ['activate', 'activator', 'activateshow']]);
    }

    /**
     * Gets info for profile and returns profile view. Also gathers records and systems permission sets.
     *
     * @return View
     */
    public function index() {
        $languages_available = Config::get('app.locales_supported');

        $user = Auth::user();

        $profile = $user->profile;

        if($user->admin) {
            $admin = 1;
            $records = Record::where('owner', '=', $user->id)->orderBy('updated_at', 'desc')->take(30)->get();
            return view('user/profile',compact('languages_available', 'admin', 'records', 'profile'));
        } else {
            $admin = 0;
            $projects = self::buildProjectsArray($user);
            $forms = self::buildFormsArray($user);
            $records = Record::where('owner', '=', $user->id)->orderBy('updated_at', 'desc')->get();

            return view('user/profile',compact('languages_available', 'admin', 'projects', 'forms', 'records', 'profile'));
        }
    }

    /**
     * Changes the user profile picture and returns the pic URI.
     *
     * @param  Request $request
     * @return string - URI of pic
     */
    public function changepicture(Request $request) {
        $file = $request->file('profile');
        $pDir = env('BASE_PATH') . 'storage/app/profiles/'.\Auth::user()->id.'/';
        $pURL = env('STORAGE_URL') . 'profiles/'.\Auth::user()->id.'/';

        //remove old pic
        $oldFile = $pDir.\Auth::user()->profile;
        if(file_exists($oldFile))
            unlink($oldFile);

        //set new pic to db
        $newFilename = $file->getClientOriginalName();
        \Auth::user()->profile = $newFilename;
        \Auth::user()->save();

        //move photo and return new path
        $file->move($pDir,$newFilename);

        return $pURL.$newFilename;
    }

    /**
     * Change user profile information.
     *
     * @param  Request $request
     */
    public function changeprofile(Request $request) {
        $user = Auth::user();
        $type = $request->input("type");

        switch($type) {
            case "lang":
                $lang = $request->input("field");

                if(empty($lang)) {
                    flash()->overlay(trans('controller_auth_user.selectlan'), trans('controller_auth_user.whoops'));
                } else {
                    $user->language = $lang;
                    $user->save();
                    flash()->overlay(trans('controller_auth_user.lanupdate'), trans('controller_auth_user.success'));
                }
                break;
            case "dash":
                $dash = $request->input("field");

                if($dash != "0" && $dash != "1") {
                    flash()->overlay(trans('controller_auth_user.selectdash'), trans('controller_auth_user.whoops'));
                } else {
                    $user->dash = $dash;
                    $user->save();
                    flash()->overlay(trans('controller_auth_user.dashupdate'), trans('controller_auth_user.success'));
                }
                break;
            case "name":
                $realname = $request->input("field");

                if(empty($realname)) {
                    flash()->overlay(trans('controller_auth_user.entername'), trans('controller_auth_user.whoops'));
                } else {
                    $user->name = $realname;
                    $user->save();
                    flash()->overlay(trans('controller_auth_user.nameupdate'), trans('controller_auth_user.success'));
                }
                break;
            case "org":
                $organization = $request->input("field");

                if(empty($organization)) {
                    flash()->overlay(trans('controller_auth_user.enterorg'), trans('controller_auth_user.whoops'));
                } else {
                    $user->organization = $organization;
                    $user->save();
                    flash()->overlay(trans('controller_auth_user.orgupdate'), trans('controller_auth_user.success'));
                }
                break;
            default:
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
            flash()->overlay(trans('controller_auth_user.bothpass'), trans('controller_auth_user.whoops'));
            return redirect('user/profile');

        } else if(strlen($new_pass) < 6) {
            flash()->overlay(trans('controller_auth_user.lessthan'), trans('controller_auth_user.whoops'));
            return redirect('user/profile');

        } else if($new_pass != $confirm) {
            flash()->overlay(trans('controller_auth_user.nomatch'), trans('controller_auth_user.whoops'));
            return redirect('user/profile');

        } else {
            $user->password = bcrypt($new_pass);
            $user->save();

            flash()->overlay(trans('controller_auth_user.passupdate'), trans('controller_auth_user.success'));
            return redirect('user/profile');
        }
    }

    /**
     * Returns the view for the user activation page.
     *
     * @return View
     */
    public function activateshow() {
        return view('auth.activate');
    }

    /**
     * Validates registration token to activate a user.
     *
     * @param  Request $request
     * @return Redirect
     */
    public function activator(Request $request) {
        $user = User::where('username', '=', $request->user)->first();
        if($user==null) {
            flash()->overlay(trans('controller_auth_user.nouser'), trans('controller_auth_user.whoops'));
            return redirect('auth/activate');
        }

        $token = trim($request->token);

        if($user->regtoken == $token && !empty($user->regtoken) && !($user->active ==1)) {
            $user->active = 1;
            $user->save();
            flash()->overlay(trans('controller_auth_user.activated'), trans('controller_auth_user.success'));

            \Auth::login($user);

            return redirect('/');
        } else {
            flash()->overlay(trans('controller_auth_user.badtokenuser'), trans('controller_auth_user.whoops'));
            return redirect('auth/activate');
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
        if(!is_null(\Auth::user())) {
            \Auth::logout(\Auth::user()->id);
        }

        $user = User::where('regtoken', '=', $token)->first();

        \Auth::login($user);

        if($token != $user->regtoken) {
            flash()->overlay(trans('controller_auth_user.badtoken'), trans('controller_auth_user.whoops'));
            return redirect('/');
        } else {
            $user->active = 1;
            $user->save();

            flash()->overlay(trans('controller_auth_user.acttwo'), trans('controller_auth_user.success'));
            return redirect('/');
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
                $projects[$i]['pid'] = $project->pid;
                $projects[$i]['name'] = $project->name;

                if($user->isProjectAdmin($project))
                    $projects[$i]['permissions'] = 'Admin';
                else {
                    if($user->canCreateForms($project))
                        $permissions .= 'Create Forms | ';
                    if($user->canEditForms($project))
                        $permissions .= 'Edit Forms | ';
                    if($user->canDeleteForms($project))
                        $permissions .= 'Delete Forms | ';

                    if($permissions == '')
                        $permissions .= 'Read Only';
                    $projects[$i]['permissions'] = rtrim($permissions, '| ');
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
                    $forms[$i]['permissions'] = rtrim($permissions, '| ');
                }
            }
            $i++;
        }
        return $forms;
    }
}