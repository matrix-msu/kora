<?php namespace App;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\ProjectController;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Html\FormBuilder;
use Illuminate\Support\Facades\DB;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract {

    /*
    |--------------------------------------------------------------------------
    | User
    |--------------------------------------------------------------------------
    |
    | This model represents the data for a User
    |
    */

	use Authenticatable, CanResetPassword;

    /**
     * @var array - Table that model represents
     */
	protected $table = 'users';
    /**
     * @var array - Attributes that can be mass assigned to model
     */
	protected $fillable = ['username', 'name', 'email', 'password', 'organization', 'language', 'regtoken'];
    /**
     * @var array - Attributes that ignored in the model representation
     */
	protected $hidden = ['password', 'remember_token'];

    /**
     * Returns the global cache results associated with a user.
     *
     * @return FormBuilder
     */
    public function gsCaches() {
        return DB::table("global_cache")->where("user_id", "=", $this->id);
    }

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

        return DB::table("form_custom")->where("uid", "=", $this->id)
            ->where("pid", "=", $pid)
            ->where("fid", "=", $fid)->first()->sequence;
    }

    /**
     * Adds a project to a user's custom list
     *
     * @param  int $pid - Project ID
     */
    public function addCustomProject($pid) {
        //Make sure it doesn't exist first
        $check = DB::table("project_custom")->where("uid", "=", $this->id)
            ->where("pid", "=", $pid)->get();

        if(is_null($check)) {
            $currSeqMax = DB::table("project_custom")->where("uid", "=", $this->id)->max("sequence");
            if(!is_null($currSeqMax))
                $newSeq = $currSeqMax + 1;
            else
                $newSeq = 0;

            DB::table('project_custom')->insert(
                ['uid' => $this->id, 'pid' => $pid, 'sequence' => $newSeq]
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
            ->where("fid", "=", $fid)->get();

        if(is_null($check)) {
            $currSeqMax = DB::table("form_custom")->where("uid", "=", $this->id)
                ->where("pid", "=", $pid)->max("sequence");
            if(!is_null($currSeqMax))
                $newSeq = $currSeqMax + 1;
            else
                $newSeq = 0;

            DB::table('form_custom')->insert(
                ['uid' => $this->id, 'pid' => $pid, 'fid' => $fid, 'sequence' => $newSeq]
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
            return env('STORAGE_URL') . 'profiles/'.$this->id.'/'.$this->profile;
        else
            return env('BASE_URL') . 'logos/blank_profile.jpg';
    }
}
