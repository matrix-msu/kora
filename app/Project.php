<?php namespace App;

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
        'adminGID',
        'active'
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
     * Deletes all data belonging to the project, then deletes self.
     */
    public function delete() {
        DB::table("project_token")->where("project_id", "=", $this->pid)->delete();
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
