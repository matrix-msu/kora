<?php namespace App\Http\Controllers\Auth;

use App\Record;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
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
     * @return JsonResponse - URI of pic
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

        return response()->json(["status"=>true,"message"=>"profile_pic_updated","pic_url"=>$pURL.$newFilename],200);
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
        if($user==null)
            return redirect('auth/activate')->with('k3_global_error', 'user_doesnt_exist');

        $token = trim($request->token);

        if($user->regtoken == $token && !empty($user->regtoken) && !($user->active ==1)) {
            $user->active = 1;
            $user->save();

            \Auth::login($user);

            return redirect('/')->with('k3_global_success', 'user_activated');
        } else {
            return redirect('auth/activate')->with('k3_global_error', 'invalid_token');
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
            return redirect('/')->with('k3_global_error', 'bad_activation_token');
        } else {
            $user->active = 1;
            $user->save();

            return redirect('/')->with('k3_global_success', 'user_activated');
        }
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
