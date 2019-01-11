<?php namespace App;

use App\Http\Controllers\FormController;
use App\Http\Controllers\ProjectController;
use Carbon\Carbon;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use ReCaptcha\ReCaptcha;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract {

    /*
    |--------------------------------------------------------------------------
    | User
    |--------------------------------------------------------------------------
    |
    | This model represents the data for a User
    |
    */

	use Authenticatable, CanResetPassword, Notifiable;

    /**
     * @var array - Table that model represents
     */
	protected $table = 'users';
    /**
     * @var array - Attributes that can be mass assigned to model
     */
	protected $fillable = ['username', 'email', 'password', 'regtoken', 'preferences'];
    /**
     * @var array - Attributes that ignored in the model representation
     */
	protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'preferences' => 'array'
    ];
	

    public function getFullNameAttribute() {
        return $this->preferences['first_name'].' '.$this->preferences['last_name'];
    }

    /**
     * A User's Permissions
     *
     * @return HasOne
     */
    public function permissions() {
        return $this->preferences;
    }

    /**
     * Returns the global cache results associated with a user.
     *
     * @return Builder
     */
    public function gsCaches() {
        return DB::table("global_cache")->where("user_id", "=", $this->id)->first();
    }

    ////THESE FUNCTIONS WILL HANDLE MODIFICATIONS TO AUTHENTICATION IN LARAVEL//////////////////////////////////////////

    ////NOTE: You may have to fix re-implement these functions when updating to newer versions of laravel, so test them!!

    /** RECAPTCHA
     * Verifies recaptcha token on register. Happens in registration before we verify the other User request data.
     *
     * @param  Request $request - The registration request data
     */
    public static function verifyRegisterRecaptcha($request) {
        $recaptcha = new ReCaptcha(config('auth.recap_private'));
        $resp = $recaptcha->verify($request['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
        
        if($resp->isSuccess())
            return true;
        else
            return false;

        //NOTE::When you re-implement this function in the laravel Register system, use this fail state:
//        if(!\App\User::verifyRegisterRecaptcha($request,$this)) {
//            $notification = array(
//                'message' => 'ReCaptcha validation error',
//                'description' => '',
//                'warning' => true,
//                'static' => true
//            );
//
//            return redirect("/register")->withInput()->with('notification', $notification)->send();
//        }
    }

    /** REGISTRATION
     * Finishes the registration process by submitting user photo and sending activation email. Happens in registration
     * right after logging in the newly created user.
     *
     * @param  Request $request - The registration request data
     * @return bool - Success of activation email
     */
    public static function finishRegistration($request) {
        $user = \Auth::user();
        $token = $user->token;
        $preferences = array();

        //Metadata stuff
        $preferences['first_name'] = $request->first_name;
        $preferences['last_name'] = $request->last_name;
        $preferences['organization'] = $request->organization;
        $preferences['language'] = 'en';

        //Profile picture
        if(!is_null($request->file('profile'))) {
            //get the file object
            $file = $request->file('profile');
            $filename = $file->getClientOriginalName();
            //path where file will be stored
            $destinationPath = storage_path('app/profiles/'.$user->id.'/');
            //store filename in user model
            $preferences['profile_pic'] = $filename;
            //move the file
            $file->move($destinationPath,$filename);
        } else {
            $preferences['profile_pic'] = '';
        }

        //Assign new user preferences
        $preferences['use_dashboard'] = 1;
        $preferences['logo_target'] = 2;
        $preferences['proj_tab_selection'] = 2;
        $preferences['form_tab_selection'] = 2;
        $user->preferences = $preferences;
        $user->save();

        //Send email
        try {
            Mail::send('emails.activation', compact('token'), function($message) use ($user) {
                $message->from(env('MAIL_FROM_ADDRESS'));
                $message->to($user->email);
                $message->subject('Kora Account Activation');
            });

            return true;
        } catch(\Swift_TransportException $e) {
            //Log for now
            Log::info('Activation email failed');
            return false;
        }

        //NOTE::When you re-implement this function in the laravel Register system, use this fail state:
//        if(\App\User::finishRegistration($request))
//            $status = 'activation_email_sent';
//        else
//            $status = 'activation_email_failed';
//
//
//        return $this->registered($request, $user)
//            ?: redirect($this->redirectPath())->with('status', $status)->send();
    }

    /** PASSWORD RESET
     * Overrides the laravel password reset email function so we can customize it. Unless the overridden function
     * changes, we shouldn't need to modify anything when upgrading.
     *
     * @param  string $token - The reset token
     */
    public function sendPasswordResetNotification($token) {
        $email = 'emails.password';
        $userMail = $this->email;

        //Send email
        try {
            Mail::send($email, compact('token'), function ($message) use ($userMail) {
                $message->from(config('mail.from.address'));
                $message->to($userMail);
                $message->subject('Kora Password Reset');
            });
        } catch(\Swift_TransportException $e) {
            //Log for now
            Log::info('Password reset email failed');
        }
    }

    /** LOGIN
     * Filters login results to allow login with either username or email. Happens in the authentication system login
     * attempt function.
     *
     * @param  array $credentials - The login credentials
     * @return array - The filtered credentials
     */
    public static function refineLoginCredentials($credentials) {
        if(strpos($credentials['email'], '@') == false) {
            //logging in with username not email, so change the column-name
            $credentials['username'] = $credentials['email'];
            unset($credentials['email']);
        }

        return $credentials;
    }

    ////END AUTH FUNCTIONS//////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Returns true if a user is allowed to create forms in a project, false if not.
     *
     * @param  Project $project - Project to check permissions
     * @return bool - Can create forms
     */
    public function canCreateForms(Project $project) {
        if($this->admin) return true;

        $projectGroups = $project->groups()->get();
        foreach($projectGroups as $projectGroup) {
            if($projectGroup->hasUser($this) && $projectGroup->create)
                return true;
        }
        return false;
    }

    /**
     * Returns true if a user is allowed to edit forms in a project, false if not.
     *
     * @param  Project $project - Project to check permissions
     * @return bool - Can edit forms
     */
    public function canEditForms(Project $project) {
        if($this->admin) return true;

        $projectGroups = $project->groups()->get();
        foreach($projectGroups as $projectGroup) {
            if($projectGroup->hasUser($this) && $projectGroup->edit)
                return true;
        }
        return false;
    }

    /**
     * Returns true if a user is allowed to delete forms in a project, false if not.
     *
     * @param  Project $project - Project to check permissions
     * @return bool - Can delete forms
     */
    public function canDeleteForms(Project $project) {
        if($this->admin) return true;

        $projectGroups = $project->groups()->get();
        foreach($projectGroups as $projectGroup) {
            if($projectGroup->hasUser($this) && $projectGroup->delete)
                return true;
        }
        return false;
    }

    /**
     * Returns true if a user is in any of a project's project groups, false if not.
     *
     * @param  Project $project - Project to check permissions
     * @return bool - Is project member
     */
    public function inAProjectGroup(Project $project) {
        if($this->admin) return true;

        $projectGroups = $project->groups()->get();
        foreach($projectGroups as $projectGroup) {
            if($projectGroup->hasUser($this))
                return true;
        }

        if($this->inAnyFormGroup($project)) return true;

        return false;
    }

    /**
     * Get the project group a user belongs to
     *
     * @param Project $project - Project to get group
     * @return array - Group info user belongs to
     */
    public function getProjectGroup(Project $project) {
        $adminGroup = $project->adminGroup()->first();
        if($adminGroup->hasUser($this)) {
            return ['id' => $adminGroup->id, 'name' => 'Admin Group'];
        } else {
            $projectGroups = $project->groups()->get();
            foreach ($projectGroups as $projectGroup) {
                if ($projectGroup->hasUser($this)) {
                    return ['id' => $projectGroup->id, 'name' => $projectGroup->name];
                }
            }
        }

        return ['id' => '', 'name' => ''];
    }

    /**
     * Get the form group a user belongs to
     *
     * @param Form $form - Project to get group
     * @return array - Group info user belongs to
     */
    public function getFormGroup(Form $form) {
        $adminGroup = $form->adminGroup()->first();
        if($adminGroup->hasUser($this)) {
            return ['id' => $adminGroup->id, 'name' => 'Admin Group'];
        } else {
            $formGroups = $form->groups()->get();
            foreach($formGroups as $formGroup) {
                if($formGroup->hasUser($this))
                    return ['id' => $formGroup->id, 'name' => $formGroup->name];
            }
        }

        return ['id' => '', 'name' => ''];
    }

    /**
     * Returns true is a user is in any of a project's form groups, false if not.
     *
     * @param  Project $project - Project to check permissions
     * @return bool - Is project form member
     */
    public function inAnyFormGroup(Project $project) {
        foreach($project->forms()->get() as $form) {
            foreach($form->groups()->get() as $group) {
                if($group->hasUser($this))
                    return true;
            }
        }
        return false;
    }


    /**
     * Returns true if a user is in a project's admin group, false if not.
     *
     * @param  Project $project - Project to check permissions
     * @return bool - Is project admin
     */
    public function isProjectAdmin(Project $project) {
        if($this->admin)
            return true;

        $adminGroup = $project->adminGroup()->first();
        if($adminGroup->hasUser($this))
            return true;

        return false;
    }

    /**
     * Returns true if a user is allowed to create fields in a form, false if not.
     *
     * @param Form $form - Form to check permissions
     * @return bool - Can create fields in form
     */
    public function canCreateFields(Form $form) {
        if($this->admin) return true;

        if($this->isProjectAdmin(ProjectController::getProject($form->pid))) return true;

        $formGroups = $form->groups()->get();
        foreach($formGroups as $formGroup) {
            if($formGroup->hasUser($this) && $formGroup->create)
                return true;
        }
        return false;
    }

    /**
     * Returns true if a user is allowed to edit fields in a form, false if not.
     *
     * @param Form $form - Form to check permissions
     * @return bool - Can edit fields in form
     */
    public function canEditFields(Form $form) {
        if($this->admin) return true;

        if($this->isProjectAdmin(ProjectController::getProject($form->pid))) return true;

        $formGroups = $form->groups()->get();
        foreach($formGroups as $formGroup) {
            if($formGroup->hasUser($this) && $formGroup->edit)
                return true;
        }
        return false;
    }

    /**
     * Returns true if a user is allowed to delete fields in a form, false if not.
     *
     * @param Form $form - Form to check permissions
     * @return bool - Can delete fields in form
     */
    public function canDeleteFields(Form $form) {
        if($this->admin) return true;

        if($this->isProjectAdmin(ProjectController::getProject($form->pid))) return true;

        $formGroups = $form->groups()->get();
        foreach($formGroups as $formGroup) {
            if($formGroup->hasUser($this) && $formGroup->delete)
                return true;
        }
        return false;
    }

    /**
     * Returns true if a user is allowed to create records in a form, false if not.
     *
     * @param Form $form - Form to check permissions
     * @return bool - Can create records in form
     */
    public function canIngestRecords(Form $form) {
        if($this->admin) return true;

        if($this->isProjectAdmin(ProjectController::getProject($form->pid))) return true;

        $formGroups = $form->groups()->get();
        foreach($formGroups as $formGroup) {
            if($formGroup->hasUser($this) && $formGroup->ingest)
                return true;
        }
        return false;
    }

    /**
     * Returns true if a user is allowed to edit records in a form, false if not.
     *
     * @param Form $form - Form to check permissions
     * @return bool - Can edit records in form
     */
    public function canModifyRecords(Form $form) {
        if($this->admin) return true;

        if($this->isProjectAdmin(ProjectController::getProject($form->pid))) return true;

        $formGroups = $form->groups()->get();
        foreach($formGroups as $formGroup) {
            if($formGroup->hasUser($this) && $formGroup->modify)
                return true;
        }
        return false;
    }

    /**
     * Returns true if a user is allowed to delete records in a form, false if not.
     *
     * @param Form $form - Form to check permissions
     * @return bool - Can delete records in form
     */
    public function canDestroyRecords(Form $form) {
        if($this->admin) return true;

        if($this->isProjectAdmin(ProjectController::getProject($form->pid))) return true;

        $formGroups = $form->groups()->get();
        foreach($formGroups as $formGroup) {
            if($formGroup->hasUser($this) && $formGroup->destroy)
                return true;
        }
        return false;
    }

    /**
     * Returns true if a user is the owner of a record, false if not.
     *
     * @param Record - Record to check permissions
     * @return bool - Is owner
     */
    public function isOwner(Record $record) {
        if($this->id == $record->owner)
            return true;
        else
            return false;
    }

    /**
     * Returns true if a user is in any of a form's form groups, false if not.
     *
     * @param Form $form - Form to check permissions
     * @return bool - Is form memeber
     */
    public function inAFormGroup(Form $form) {
        if($this->admin) return true;

        if($this->isProjectAdmin(ProjectController::getProject($form->pid))) return true;

        $formGroups = $form->groups()->get();
        foreach($formGroups as $formGroup) {
            if($formGroup->hasUser($this))
                return true;
        }
        return false;
    }

    /**
     * Returns true if a user is in a form's admin group, false if not.
     *
     * @param Form $form - Form to check permissions
     * @return bool - Is form admin
     */
    public function isFormAdmin(Form $form) {
        if($this->admin) return true;

        if($this->isProjectAdmin(ProjectController::getProject($form->pid))) return true;

        $adminGroup = $form->adminGroup()->first();
        if($adminGroup->hasUser($this))
            return true;
        else
            return false;
    }

    /**
     * Returns the projects a particular user is allowed to view.
     *
     * @return array - Projects user can access
     */
    public function allowedProjects() {
        $all_projects = Project::all();
        $projects = array(); //Array of projects the user can view.

        foreach($all_projects as $project) {
            if($this->inAProjectGroup($project) && $project->active==1){
                $projects[] = $project;
            }
        }

        return $projects;
    }


    /**
     * Returns the forms a particular user is allowed to view in a certain project.
     *
     * @param  int $pid - Project ID
     * @return array - Forms user can access
     */
    public function allowedForms($pid) {
        $form_projects = Form::where('project_id', '=', $pid)->get();
        $forms = array(); //Array of forms the user is allowed to view in a certain project.

        foreach($form_projects as $form) {
            if($this->inAFormGroup($form)) {
                $forms[] = $form;
            }
        }

        return $forms;
    }

    /**
     * Gets a sequence values for the user's custom view.
     *
     * @return array - The sequence
     */
    public function getCustomProjectSequence() {
        $check = DB::table("project_custom")->where("user_id", "=", $this->id)->first();

        return is_null($check) ? null : json_decode($check->organization);
    }

    /**
     * Gets a sequence value a form for the user's custom view.
     *
     * @param  int $pid - Project ID
     * @return int - The sequence
     */
    public function getCustomFormSequence($pid) {
        $check = DB::table("form_custom")->where("user_id", "=", $this->id)
            ->where("project_id", "=", $pid)->first();

        return is_null($check) ? null : json_decode($check->organization);
    }

    /**
     * Adds a project to a user's custom list
     *
     * @param  int $pid - Project ID
     */
    public function addCustomProject($pid) {
        //Make sure it doesn't exist first
        $check = DB::table("project_custom")->where("user_id", "=", $this->id)->first();

        //Create or edit custom project list for user
        if(is_null($check)) {
            DB::table('project_custom')->insert(
                ['user_id' => $this->id, 'organization' => json_encode(array($pid)),
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()]
            );
        } else {
            $customArray = json_decode($check->organization);
            array_push($customArray,$pid);

            DB::table('project_custom')->where("id", "=", $check->id)->update(
                ['organization' => json_encode($customArray),
                    "updated_at" => Carbon::now()]
            );
        }
    }

    /**
     * Adds a form to a user's custom list
     *
     * @param  int $fid - Form ID
     */
    public function addCustomForm($fid) {
        $form = FormController::getForm($fid);
        $pid = $form->project_id;

        //Make sure it doesn't exist first
        $check = DB::table("form_custom")->where("user_id", "=", $this->id)
            ->where("project_id", "=", $pid)->first();

        //Create or edit custom form list for user
        if(is_null($check)) {
            DB::table('form_custom')->insert(
                ['user_id' => $this->id, 'project_id' => $pid,'organization' => json_encode(array($fid)),
                    "created_at" => Carbon::now(),
                    "updated_at" => Carbon::now()]
            );
        } else {
            $customArray = json_decode($check->organization);
            array_push($customArray,$fid);

            DB::table('form_custom')->where("id", "=", $check->id)->update(
                ['organization' => json_encode($customArray),
                    "updated_at" => Carbon::now()]
            );
        }
    }

    /**
     * Adds a new admin to all projects for custom view.
     */
    public function addNewAdminToAllCustomProjects() {
        $projects = Project::all();
        $sequence = array();
        $check = DB::table("project_custom")->where("user_id", "=", $this->id)->first();
        $time = Carbon::now();

        foreach($projects as $project) {
            array_push($sequence,$project->id);
        }

        //Create or edit custom project list for user
        if(is_null($check)) {
            DB::table('project_custom')->insert(
                ['user_id' => $this->id, 'organization' => json_encode($sequence),
                    "created_at" => $time,
                    "updated_at" => $time]
            );
        } else {
            DB::table('project_custom')->where("id", "=", $check->id)->update(
                ['organization' => json_encode($sequence),
                    "updated_at" => $time]
            );
        }
    }

    /**
     * Adds a new admin to all forms for custom view.
     */
	public function addNewAdminToAllCustomForms() {
        $forms = Form::all();
        $sequence = [];
        $time = Carbon::now();

        foreach($forms as $form) {
            $sequence[$form->project_id][] = $form->id;
        }

        foreach($sequence as $cpid => $cfids) {
            $check = DB::table("form_custom")->where("user_id", "=", $this->id)
                ->where("project_id", "=", $cpid)->first();

            //Create or edit custom form list for user
            if(is_null($check)) {
                DB::table('form_custom')->insert(
                    ['user_id' => $this->id, 'project_id' => $cpid,'organization' => json_encode($cfids),
                        "created_at" => Carbon::now(),
                        "updated_at" => Carbon::now()]
                );
            } else {
                DB::table('form_custom')->where("id", "=", $check->id)->update(
                        ['organization' => json_encode($cfids),
                            "updated_at" => Carbon::now()]
                    );
            }
        }
	}

    /**
     * Removes a project from a user's custom list
     *
     * @param  int $pid - Project ID
     */
    public function removeCustomProject($pid) {
        $check = DB::table("project_custom")->where("user_id", "=", $this->id)->first();

        //remove project list for user
        if(!is_null($check)) {
            $customArray = json_decode($check->organization);
            $remainingProjects = array();
            for($i=0;$i<sizeof($customArray);$i++) {
                if($customArray[$i] != $pid)
                    array_push($customArray[$i],$remainingProjects);
            }

            DB::table('project_custom')->where("id", "=", $check->id)->update(
                ['organization' => json_encode($remainingProjects),
                    "updated_at" => Carbon::now()]
            );
        }
    }

    /**
     * Removes a form from a user's custom list
     *
     * @param  int $fid - Form ID
     */
    public function removeCustomForm($fid) {
        $form = FormController::getForm($fid);
        $pid = $form->pid;

        $check = DB::table("form_custom")->where("user_id", "=", $this->id)
            ->where("project_id", "=", $pid)->first();

        //remove form list for user
        if(!is_null($check)) {
            $customArray = json_decode($check->organization);
            $remainingForms = array();
            for($i=0;$i<sizeof($customArray);$i++) {
                if($customArray[$i] != $fid)
                    array_push($customArray[$i],$remainingForms);
            }

            DB::table('form_custom')->where("id", "=", $check->id)->update(
                    ['organization' => json_encode($remainingForms),
                        "updated_at" => Carbon::now()]
                );
        }
    }

    /**
     * Removes many projects from a user's custom list
     *
     * @param  int $pids - Project IDs
     */
	public function bulkRemoveCustomProjects($pids) {
        $check = DB::table("project_custom")->where("user_id", "=", $this->id)->first();

        //remove projects list for user
        if(!is_null($check)) {
            $customArray = json_decode($check->organization);
            $remainingProjects = array();
            for($i=0;$i<sizeof($customArray);$i++) {
                if(!in_array($customArray[$i],$pids))
                    array_push($customArray[$i],$remainingProjects);
            }

            DB::table('project_custom')->where("id", "=", $check->id)->update(
                ['organization' => json_encode($remainingProjects),
                    "updated_at" => Carbon::now()]
            );
        }
	}

    /**
     * Removes many forms from a user's custom list
     *
     * @param  int $fids - Form IDs
     */
	public function bulkRemoveCustomForms($fids) {
        $check = DB::table("form_custom")->where("user_id", "=", $this->id)->get();

        //remove forms list for user
        foreach($check as $chk) {
            if(!is_null($chk)) {
                $customArray = json_decode($chk->organization);
                $remainingForms = array();
                for($i = 0; $i < sizeof($customArray); $i++) {
                    if(!in_array($customArray[$i],$fids))
                        array_push($customArray[$i], $remainingForms);
                }

                DB::table('form_custom')->where("id", "=", $chk->id)->update(
                        ['organization' => json_encode($remainingForms),
                            "updated_at" => Carbon::now()]
                    );
            }
        }
	}

    /**
     * Checks for existence of profile pic and returns its URI.
     *
     * @return string - URI of profile pic
     */
    public function getProfilePicUrl() {
        if($this->preferences['profile_pic'] != '')
            return url('app/profiles/'.$this->id.'/'.$this->preferences['profile_pic']);
        else
            return url('assets/images/blank_profile.jpg');
    }

    /**
     * Checks for existence of profile pic and returns the filename
     *
     * @return string Filename of profile
     */
    public function getProfilePicFilename() {
        if($this->preferences['profile_pic'] != '')
            return $this->preferences['profile_pic'];
        else
            return 'blank_profile.jpg';
    }
	
	/**
     * Returns full name if both first name and last name are defined,
	 * otherwise return first or last name (whichever one is defined),
	 * otherwise return username.
     *
     * @return string - identifier for user
     */
	public function getNameOrUsername() {
		$has_first = $this->preferences['first_name'] !== '';
		$has_last = $this->preferences['last_name'] !== '';
		
		if($has_first && $has_last)
			return $this->preferences['first_name'] . " " . $this->preferences['last_name'];
		else if(!$has_first && !$has_last)
			return $this->username;
		else if($has_first)
			return $this->preferences['first_name'];
		else
			return $this->preferences['last_name'];
	}
}
