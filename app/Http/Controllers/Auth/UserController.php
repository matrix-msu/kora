<?php namespace App\Http\Controllers\Auth;

use App\Form;
use App\Project;
use App\Record;
use App\User;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class UserController extends Controller {

    /*
    |--------------------------------------------------------------------------
    | User Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles ...
    |
    */

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['activate', 'activator', 'activateshow']]);
        $this->middleware('active', ['except' => ['activate', 'activator', 'activateshow']]);
    }

    /**
     * Show the application welcome screen to the user.
     *
     *
     *
     * @return Response
     */
    public function index()
    {
        $languages_available = Config::get('app.locales_supported');

        $user = Auth::user();

        if($user->admin){
            $admin = 1;
            return view('user/profile',compact('languages_available', 'admin'));
        }
        else{
            $admin = 0;
            $projects = UserController::buildProjectsArray($user);
            $forms = UserController::buildFormsArray($user);
            $records = UserController::buildRecordsArray($user);
            return view('user/profile',compact('languages_available', 'admin', 'projects', 'forms', 'records'));
        }
    }

    /**
     * @param Request $request
     * @return Response
     */

    public function changeprofile(Request $request){
        $user = Auth::user();
        $type = $request->input("type");

        if($type == "lang"){
            $lang = $request->input("field");

            if(empty($lang)){
                flash()->overlay('You must select a language','Whoops.');
                //return redirect('user/profile');
            }
            else{
                $user->language = $lang;
                $user->save();
                flash()->overlay("Your language preference has been updated","Success!");
               // return redirect('user/profile');
            }
        }
        elseif($type == "name"){
            $realname = $request->input("field");

            if(empty($realname)){
                flash()->overlay('You must enter a name','Whoops.');
                //return redirect('user/profile');
            }
            else{
                $user->name = $realname;
                $user->save();
                flash()->overlay("Your real name preference has been updated","Success!");
                //return redirect('user/profile');
            }
        }
        elseif($type == "org"){
            $organization = $request->input("field");

            if(empty($organization)){
                flash()->overlay('You must enter an organization','Whoops.');
                //return redirect('user/profile');
            }
            else{
                $user->organization = $organization;
                $user->save();
                flash()->overlay("Your organization preference has been updated","Success!");
               // return redirect('user/profile');
            }

        }
        else{

        }

    }
    public function changepw(Request $request)
    {
        $user = Auth::user();
        $new_pass = $request->new_password;
        $confirm = $request->confirm;

        if (empty($new_pass) && empty($confirm)){
            flash()->overlay('Please fill both password fields before submitting.', 'Whoops.');
            return redirect('user/profile');
        }

        elseif($new_pass != $confirm){
            flash()->overlay('Passwords do not match, please try again.', 'Whoops.');
            return redirect('user/profile');
        }

        else{
            $user->password = bcrypt($new_pass);
            $user->save();

            flash()->overlay('Your password has been changed!', 'Success!');
            return redirect('user/profile');
        }
    }

    /**
     * @return Response
     */
    public function activateshow()
    {
        return view('auth.activate');
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function activator(Request $request)
    {
        $user = User::where('username', '=', $request->user)->first();
        $token = trim($request->token);

        if ($user->regtoken == $token){
            $user->active = 1;
            $user->save();
            flash()->overlay('You have been activated!', 'Success!');

            \Auth::login($user);

            return redirect('/');
        }
        else{
            flash()->overlay('That token does not match that user.', 'Whoops.');
            return redirect('auth/activate');
        }
    }

    /**
     * Activates the user with a link that is emailed to them.
     *
     * @param token
     * @return Response
     */
    public function activate($token)
    {
        $user = User::where('regtoken', '=', $token)->first();

        \Auth::login($user);

        if ($token != $user->regtoken)
        {
            flash()->overlay('That token was invalid, try again.', 'Whoops.');
            return redirect('/');
        }
        else
        {
            $user->active = 1;
            $user->save();

            flash()->overlay('Your account is now active!', 'Success!');
            return redirect('/');
        }


    }

    public static function buildProjectsArray(User $user)
    {
        $all_projects = Project::all();
        $projects = array();
        $i=0;
        foreach($all_projects as $project)
        {
            if($user->inAProjectGroup($project))
            {
                $permissions = '';
                $projects[$i]['pid'] = $project->pid;
                $projects[$i]['name'] = $project->name;

                if($user->isProjectAdmin($project))
                    $projects[$i]['permissions'] = 'Admin';
                else
                {
                    if($user->canCreateForms($project))
                        $permissions .= 'Create Forms ';
                    if($user->canEditForms($project))
                        $permissions .= 'Edit Forms ';
                    if($user->canDeleteForms($project))
                        $permissions .= 'Delete Forms ';
                    if($permissions == '')
                        $permissions .= 'Read Only';
                    $projects[$i]['permissions'] = $permissions;
                }
            }
            $i++;
        }
        return $projects;
    }

    public static function buildFormsArray(User $user)
    {
        $i=0;
        $all_forms = Form::all();
        $forms = array();
        foreach($all_forms as $form)
        {
            if($user->inAFormGroup($form))
            {
                $permissions = '';
                $forms[$i]['fid'] = $form->fid;
                $forms[$i]['pid'] = $form->pid;
                $forms[$i]['name'] = $form->name;

                if($user->isFormAdmin($form))
                    $forms[$i]['permissions'] = 'Admin';
                else
                {
                    if($user->canCreateFields($form))
                        $permissions .= 'Create Fields ';
                    if($user->canEditFields($form))
                        $permissions .= 'Edit Fields ';
                    if($user->canDeleteFields($form))
                        $permissions .= 'Delete Fields ';
                    if($user->canIngestRecords($form))
                        $permissions .= 'Create Records ';
                    if($user->canModifyRecords($form))
                        $permissions .= 'Edit Records ';
                    if ($user->canDestroyRecords($form))
                        $permissions .= 'Delete Records ';
                    if($permissions == '')
                        $permissions .= 'Read Only';
                    $forms[$i]['permissions'] = $permissions;
                }
            }
            $i++;
        }
        return $forms;
    }

    public static function buildRecordsArray(User $user)
    {
        $i=0;
        $all_records = Record::all()->sortby('created_at');
        $records = array();
        foreach($all_records as $record)
        {
            if($user->isOwner($record))
            {
                $records[$i]['rid'] = $record->rid;
                $records[$i]['fid'] = $record->fid;
                $records[$i]['pid'] = $record->pid;
                $records[$i]['updated_at'] = $record->updated_at;
            }
            $i++;
        }
        return $records;
    }
}
