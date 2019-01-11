<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model {

    /*
    |--------------------------------------------------------------------------
    | Project
    |--------------------------------------------------------------------------
    |
    | This model represents the data for a Project
    |
    */

    /**
     * @var array - Attributes that can be mass assigned to model
     */
	protected $fillable = [
        'name',
        'description',
        'adminGroup_id'
    ];

    /**
     * Returns the forms associated with a project.
     *
     * @return HasMany
     */
    public function forms() {
        return $this->hasMany('App\Form','project_id');
    }

    /**
     * Get the tokens associated with a given project.
     *
     * @return BelongsToMany
     */
    public function tokens() {
        return $this->belongsToMany('App\Token');
    }

    /**
     * Returns the project's admin group.
     *
     * @return BelongsTo
     */
    public function adminGroup() {
        return $this->belongsTo('App\ProjectGroup', 'adminGroup_id');
    }

    /**
     * Returns the groups associated with a project.
     *
     * @return HasMany
     */
    public function groups() {
        return $this->hasMany('App\ProjectGroup','project_id');
    }

    /**
     * Returns the option presets associated with a project.
     *
     * @return HasMany
     */
    public function optionPresets() {
        return $this->hasMany('App\OptionPreset','project_id');
    }

    /**
     * Deletes all data belonging to the project, then deletes self.
     */
    public function delete() {
        $users = User::all();

        //Manually delete from custom
        foreach($users as $user) {
            $user->removeCustomProject($this->id);
        }

        parent::delete();
    }
	
	/**
     * Adds a project to multiple users' custom lists
     *
     * @param  array $user_ids - User IDs
	 * @param  
     */
	public function batchAddUsersAsCustom($user_ids) {
		$user_ids = array_unique($user_ids); // remove dupes
		// get all users' custom projects
		$users = User::whereIn('id',$user_ids)->get();
		foreach($users as $user) {
		    $user->addCustomProject($this->id);
        }
	}

    /**
     * Builds up an array of a project and its forms to be send to Javascript.
     *
     * @return array - The project structure
     */
    public function buildFormSelectorArray() {
        $forms = $this->forms()->get();

        $arr = ["pid" => $this->id,
            "name" => $this->name,
            "forms" => []];

        foreach($forms as $form) {
            $arr["forms"][] = ["fid" => $form->id,
                "name" => $form->name];
        }

        return $arr;
    }
}
