<?php namespace App;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\ProjectController;
use Carbon\Carbon;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Auth\RegistersUsers;
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
	protected $fillable = ['username', 'first_name', 'last_name', 'email', 'password', 'organization', 'language', 'regtoken'];
    /**
     * @var array - Attributes that ignored in the model representation
     */
	protected $hidden = ['password', 'remember_token'];

    public function getFullNameAttribute() {
        return $this->first_name . " " . $this->last_name;
    }

    /**
     * Returns the global cache results associated with a user.
     *
     * @return Builder
     */
    public function gsCaches() {
        return DB::table("global_cache")->where("user_id", "=", $this->id);
    }

    ////THESE FUNCTIONS WILL HANDLE MODIFICATIONS TO AUTHENTICATION IN LARAVEL//////////////////////////////////////////

    ////NOTE: You may to fix some of these when updating to newer versions of laravel, so test them!!

    /** RECAPTCHA
     * Verifies recaptcha token on register.
     *
     * @param  Request $request - The registration request data
     * @param  RegistersUsers $regUsers - The model that runs registration, need reference for error throwing
     */
    public static function verifyRegisterRecaptcha($request, $regUsers) {
        $recaptcha = new ReCaptcha(config('auth.recap_private'));
        $resp = $recaptcha->verify($request['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);

        if($resp->isSuccess()) {
            return true;
        } else {
            //TODO:: test error and make better
            return false;
        }
    }

    /** REGISTRATION
     * Finishes the registration process by submitting user photo and sending activation email.
     *
     * @param  Request $request - The registration request data
     */
    public static function finishRegistration($request) {
        $token = \Auth::user()->token;

        if( !is_null($request->file('profile')) ) {
            //get the file object
            $file = $request->file('profile');
            $filename = $file->getClientOriginalName();
            //path where file will be stored
            $destinationPath = env('BASE_PATH') . 'storage/app/profiles/'.\Auth::user()->id.'/';
            //store filename in user model
            \Auth::user()->profile = $filename;
            \Auth::user()->save();
            //move the file
            $file->move($destinationPath,$filename);
        }

        //Send email
        try {
            Mail::send('emails.activation', compact('token'), function($message) {
                $message->from(env('MAIL_FROM_ADDRESS'));
                $message->to(\Auth::user()->email);
                $message->subject('Kora Account Activation');
            });
        } catch(\Swift_TransportException $e) {
            //TODO::email error response
            //Log for now
            Log::info('Activation email failed');
        }
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
            //TODO::email error response
            //Log for now
            Log::info('Password reset email failed');
        }
    }

    /** LOGIN
     * Filters login results to allow login with either username or email.
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
        if ($this->admin) return true;

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
        if ($this->admin) return true;

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
        if ($this->admin) return true;

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
        if ($adminGroup->hasUser($this)) {
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
        if ($adminGroup->hasUser($this)) {
            return ['id' => $adminGroup->id, 'name' => 'Admin Group'];
        } else {
            $formGroups = $form->groups()->get();
            foreach ($formGroups as $formGroup) {
                if ($formGroup->hasUser($this)) {
                    return ['id' => $formGroup->id, 'name' => $formGroup->name];
                }
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
        if ($adminGroup->hasUser($this))
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
        if ($this->admin) return true;

        if ($this->isProjectAdmin(ProjectController::getProject($form->pid))) return true;

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
        if ($this->admin) return true;

        if ($this->isProjectAdmin(ProjectController::getProject($form->pid))) return true;

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
        if ($this->admin) return true;

        if ($this->isProjectAdmin(ProjectController::getProject($form->pid))) return true;

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
        if ($this->admin) return true;

        if ($this->isProjectAdmin(ProjectController::getProject($form->pid))) return true;

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
        if ($this->admin) return true;

        if ($this->isProjectAdmin(ProjectController::getProject($form->pid))) return true;

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
        if ($this->admin) return true;

        if ($this->isProjectAdmin(ProjectController::getProject($form->pid))) return true;

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
        if ($this->id == $record->owner)
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

        if ($this->isProjectAdmin(ProjectController::getProject($form->pid))) return true;

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

        if ($this->isProjectAdmin(ProjectController::getProject($form->pid))) return true;

        $adminGroup = $form->adminGroup()->first();
        if ($adminGroup->hasUser($this))
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
        $form_projects = Form::where('pid', '=', $pid)->get();
        $forms = array(); //Array of forms the user is allowed to view in a certain project.

        foreach($form_projects as $form) {
            if($this->inAFormGroup($form)) {
                $forms[] = $form;
            }
        }

        return $forms;
    }

    /**
     * Deletes users connections to other models, then deletes self.
     */
    public function delete() {
        DB::table("project_group_user")->where("user_id", "=", $this->id)->delete();
        DB::table("project_custom")->where("uid", "=", $this->id)->delete();
        DB::table("form_group_user")->where("user_id", "=", $this->id)->delete();
        DB::table("form_custom")->where("uid", "=", $this->id)->delete();
        DB::table("backup_support")->where("user_id", "=", $this->id)->delete();
        DB::table("global_cache")->where("user_id", "=", $this->id)->delete();

        //Delete dashboard stuff
        $sections = DB::table("dashboard_sections")->where("uid", "=", $this->id)->get();
        foreach($sections as $sec) {
            DashboardController::deleteSection($sec->id);
        }

        parent::delete();
    }

    /**
     * Gets a list of active plugins user belongs to.
     *
     * @return array - The plugins
     */
    public function getActivePlugins() {
        $plugins = Plugin::where('active','=',1)->get();
        $myPlugins = array();

        foreach($plugins as $plug) {
            $project = ProjectController::getProject($plug->pid);
            $group = ProjectGroup::where('id','=',$project->adminGID)->get()->first();

            if(\Auth::user()->admin | $group->hasUser(\Auth::user()))
                array_push($myPlugins,$plug);
        }

        return $myPlugins;
    }

    /**
     * Gets a sequence value a project for the user's custom view.
     *
     * @param  int $pid - Project ID
     * @return int - The sequence
     */
    public function getCustomProjectSequence($pid) {
        $check = DB::table("project_custom")->where("uid", "=", $this->id)
            ->where("pid", "=", $pid)->first();

        return is_null($check) ? null : $check->sequence;
    }

    /**
     * Gets a sequence value a form for the user's custom view.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @return int - The sequence
     */
    public function getCustomFormSequence($fid) {
        $form = FormController::getForm($fid);
        $pid = $form->pid;

        $check = DB::table("form_custom")->where("uid", "=", $this->id)
            ->where("pid", "=", $pid)
            ->where("fid", "=", $fid)->first();

        return is_null($check) ? null : $check->sequence;
    }

    /**
     * Adds a project to a user's custom list
     *
     * @param  int $pid - Project ID
     */
    public function addCustomProject($pid) {
        //Make sure it doesn't exist first
        $check = DB::table("project_custom")->where("uid", "=", $this->id)
            ->where("pid", "=", $pid)->first();

        if(is_null($check)) {
            $currSeqMax = DB::table("project_custom")->where("uid", "=", $this->id)->max("sequence");
            if(!is_null($currSeqMax))
                $newSeq = $currSeqMax + 1;
            else
                $newSeq = 0;

            DB::table('project_custom')->insert(
                ['uid' => $this->id, 'pid' => $pid, 'sequence' => $newSeq,
                    "created_at" =>  Carbon::now(),
                    "updated_at" =>  Carbon::now()]
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
        $pid = $form->pid;

        //Make sure it doesn't exist first
        $check = DB::table("form_custom")->where("uid", "=", $this->id)
            ->where("pid", "=", $pid)
            ->where("fid", "=", $fid)->first();

        if(is_null($check)) {
            $currSeqMax = DB::table("form_custom")->where("uid", "=", $this->id)
                ->where("pid", "=", $pid)->max("sequence");
            if(!is_null($currSeqMax))
                $newSeq = $currSeqMax + 1;
            else
                $newSeq = 0;

            DB::table('form_custom')->insert(
                ['uid' => $this->id, 'pid' => $pid, 'fid' => $fid, 'sequence' => $newSeq,
                    "created_at" =>  Carbon::now(),
                    "updated_at" =>  Carbon::now()]
            );
        }
    }

    /**
     * Removes a project from a user's custom list
     *
     * @param  int $pid - Project ID
     */
    public function removeCustomProject($pid) {
        $customs = DB::table("project_custom")->where("uid", "=", $this->id)->orderBy('sequence', 'asc')
            ->get();

        $found = false;
        $delCustom = null;
        foreach($customs as $custom) {
            if($found) {
                //Once we've found the page we are deleting, we need to change the sequence of any
                // pages that follow.
                $newSeq = $custom->sequence - 1;
                DB::table('project_custom')
                    ->where('id', $custom->id)
                    ->update(['sequence' => $newSeq]);
            }

            if($custom->pid == $pid) {
                $found = true;
                $delCustom = $custom;
                DB::table('project_custom')
                    ->where('id', $custom->id)
                    ->update(['sequence' => 1337]);
            }
        }

        if(!is_null($delCustom))
            DB::table('project_custom')->where('id', '=', $delCustom->id)->delete();
    }

    /**
     * Removes a form from a user's custom list
     *
     * @param  int $fid - Form ID
     */
    public function removeCustomForm($fid) {
        $form = FormController::getForm($fid);
        $pid = $form->pid;

        $customs = DB::table("form_custom")->where("uid", "=", $this->id)
            ->where("pid", "=", $pid)
            ->orderBy('sequence', 'asc')
            ->get();

        $found = false;
        $delCustom = null;
        foreach($customs as $custom) {
            if($found) {
                //Once we've found the page we are deleting, we need to change the sequence of any
                // pages that follow.
                $newSeq = $custom->sequence - 1;
                DB::table('form_custom')
                    ->where('id', $custom->id)
                    ->update(['sequence' => $newSeq]);
            }

            if($custom->fid == $fid) {
                $found = true;
                $delCustom = $custom;
                DB::table('form_custom')
                    ->where('id', $custom->id)
                    ->update(['sequence' => 1337]);
            }
        }

        if(!is_null($delCustom))
            DB::table('form_custom')->where('id', '=', $delCustom->id)->delete();
    }

    /**
     * Checks for existence of profile pic and returns its URI.
     *
     * @return string - URI of profile pic
     */
    public function getProfilePicUrl() {
        if(!is_null($this->profile))
            return config('app.storage_url') . 'profiles/'.$this->id.'/'.$this->profile;
        else
            return config('app.url') . 'assets/images/blank_profile.jpg';
    }

    /**
     * Checks for existence of profile pic and returns the filename
     *
     * @return string Filename of profile
     */
    public function getProfilePicFilename() {
        if(!is_null($this->profile))
            return $this->profile;
        else
            return 'blank_profile.jpg';
    }
}
