<?php namespace App;

use App\Http\Controllers\ProjectController;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract {

	use Authenticatable, CanResetPassword;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['username', 'name', 'email', 'password', 'organization', 'language', 'regtoken'];

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['password', 'remember_token'];

    /**
     * Returns true if a user is allowed to create forms in a project, false if not.
     *
     * @param Project $project
     * @return bool
     */
    public function canCreateForms(Project $project){
        if ($this->admin) return true;

        $projectGroups = $project->groups()->get();
        foreach($projectGroups as $projectGroup){
            if($projectGroup->hasUser($this) && $projectGroup->create)
                return true;
        }
        return false;
    }

    /**
     * Returns true if a user is allowed to edit forms in a project, false if not.
     *
     * @param Project $project
     * @return bool
     */
    public function canEditForms(Project $project){
        if ($this->admin) return true;

        $projectGroups = $project->groups()->get();
        foreach($projectGroups as $projectGroup){
            if($projectGroup->hasUser($this) && $projectGroup->edit)
                return true;
        }
        return false;
    }

    /**
     * Returns true if a user is allowed to delete forms in a project, false if not.
     *
     * @param Project $project
     * @return bool
     */
    public function canDeleteForms(Project $project){
        if ($this->admin) return true;

        $projectGroups = $project->groups()->get();
        foreach($projectGroups as $projectGroup){
            if($projectGroup->hasUser($this) && $projectGroup->delete)
                return true;
        }
        return false;
    }

    /**
     * Returns true if a user is in any of a project's project groups, false if not.
     *
     * @param Project $project
     * @return bool
     */
    public function inAProjectGroup(Project $project){
        if($this->admin) return true;

        $projectGroups = $project->groups()->get();
        foreach($projectGroups as $projectGroup){
            if($projectGroup->hasUser($this))
                return true;
        }

        if($this->inAnyFormGroup($project)) return true;

        return false;
    }

    /**
     * Returns true is a user is in any of a project's form groups, false if not.
     *
     * @param Project $project
     * @return bool
     */
    public function inAnyFormGroup(Project $project)
    {
        foreach($project->forms()->get() as $form){
            foreach($form->groups()->get() as $group){
                if($group->hasUser($this))
                    return true;
            }
        }
        return false;
    }


    /**
     * Returns true if a user is in a project's admin group, false if not.
     *
     * @param Project $project
     * @return bool
     */
    public function isProjectAdmin(Project $project){
        if($this->admin) return true;

        $adminGroup = $project->adminGroup()->first();
        if ($adminGroup->hasUser($this))
            return true;
        return false;
    }

    /**
     * Returns true if a user is allowed to create fields in a form, false if not.
     *
     * @param Form $form
     * @return bool
     */
    public function canCreateFields(Form $form){
        if ($this->admin) return true;

        $formGroups = $form->groups()->get();
        foreach($formGroups as $formGroup){
            if($formGroup->hasUser($this) && $formGroup->create)
                return true;
        }
        return false;
    }

    /**
     * Returns true if a user is allowed to edit fields in a form, false if not.
     *
     * @param Form $form
     * @return bool
     */
    public function canEditFields(Form $form){
        if ($this->admin) return true;

        $formGroups = $form->groups()->get();
        foreach($formGroups as $formGroup){
            if($formGroup->hasUser($this) && $formGroup->edit)
                return true;
        }
        return false;
    }

    /**
     * Returns true if a user is allowed to delete fields in a form, false if not.
     *
     * @param Form $form
     * @return bool
     */
    public function canDeleteFields(Form $form){
        if ($this->admin) return true;

        $formGroups = $form->groups()->get();
        foreach($formGroups as $formGroup){
            if($formGroup->hasUser($this) && $formGroup->delete)
                return true;
        }
        return false;
    }

    /**
     * Returns true if a user is in any of a form's form groups, false if not.
     *
     * @param Form $form
     * @return bool
     */
    public function inAFormGroup(Form $form){
        if($this->admin) return true;

        $formGroups = $form->groups()->get();
        foreach($formGroups as $formGroup){
            if($formGroup->hasUser($this))
                return true;
        }
        return false;
    }

    /**
     * Returns true if a user is in a form's admin group, false if not.
     *
     * @param Form $form
     * @return bool
     */
    public function isFormAdmin(Form $form){
        if($this->admin) return true;

        if ($this->isProjectAdmin(ProjectController::getProject($form->pid))) return true;

        $adminGroup = $form->adminGroup()->first();
        if ($adminGroup->hasUser($this))
            return true;
        return false;
    }
}
