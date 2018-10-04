<?php namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

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
        'slug',
        'description',
        'adminGID'
    ];

    /**
     * @var string - Database column that represents the primary key
     */
    protected $primaryKey = "pid";

    /**
     * Returns the forms associated with a project.
     *
     * @return HasMany
     */
    public function forms() {
        return $this->hasMany('App\Form','pid');
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
        return $this->belongsTo('App\ProjectGroup', 'adminGID');
    }

    /**
     * Returns the groups associated with a project.
     *
     * @return HasMany
     */
    public function groups() {
        return $this->hasMany('App\ProjectGroup','pid');
    }

    /**
     * Returns the option presets associated with a project.
     *
     * @return HasMany
     */
    public function optionPresets() {
        return $this->hasMany('App\OptionPreset','pid');
    }
	
	
	/**
     * Adds a project to multiple users' custom lists
     *
     * @param  array $user_ids - User IDs
	 * @param  
     */
	public function batchAddUsersAsCustom($user_ids) {
		$user_ids = array_unique($user_ids); // remove dupes
		$inserts = array();
		$now = Carbon::now();
		// get all users' custom projects
		$batch_projects = DB::table("project_custom")->whereIn("uid", $user_ids)->get();
		$has_inserts = false;
		
		$sequence_maxes = array();
		$found = array();
		foreach($user_ids as $id) {
			$sequence_maxes[$id] = -1;
			$found[$id] = false;
		}
		 
		foreach($batch_projects as $entry) {
			if($this->pid == $entry->pid) {
				$found[$entry->uid] = true;
			}
			else {
				if($entry->sequence > $sequence_maxes[$entry->uid]) {
					$sequence_maxes[$entry->uid] = $entry->sequence;
				}
			}
		}
		
		foreach($user_ids as $id) {
			$new_sequence = $sequence_maxes[$id] + 1;
			if(!$found[$id]) {
				array_push($inserts, ['uid' => $id, 'pid' => $this->pid, 'sequence' => $new_sequence,
                    "created_at" =>  $now,
                    "updated_at" =>  $now]);
				$has_inserts = true;
			}
		}
		
		if($has_inserts) {
			DB::table('project_custom')->insert($inserts);
		}
	}

    /**
     * Deletes all data belonging to the project, then deletes self.
     */
    public function delete() {
        DB::table("project_token")->where("project_pid", "=", $this->pid)->delete();
        DB::table("project_custom")->where("pid", "=", $this->pid)->delete();
        DB::table("option_presets")->where("pid", "=", $this->pid)->delete();

        $project_groups = ProjectGroup::where("pid", "=", $this->pid)->get();

        foreach($project_groups as $project_group) {
            $project_group->delete();
        }

        // We don't delete the forms as above because we need their delete methods to be called.
        $forms = Form::where("pid", "=", $this->pid)->get();
        foreach($forms as $form) {
            $form->delete();
        }

        parent::delete();
    }

    /**
     * Builds up an array of a project and its forms to be send to Javascript.
     *
     * @return array - The project structure
     */
    public function buildFormSelectorArray() {
        $forms = $this->forms()->get();

        $arr = ["pid" => $this->pid,
            "name" => $this->name,
            "forms" => []];

        foreach($forms as $form) {
            $arr["forms"][] = ["fid" => $form->fid,
                "name" => $form->name];
        }

        return $arr;
    }
}
