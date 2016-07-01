<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Project extends Model {

	protected $fillable = [
        'name',
        'slug',
        'description',
        'adminGID',
        'active'
    ];

    protected $primaryKey = "pid";

    /**
     * Returns the forms associated with a project.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function forms(){
        return $this->hasMany('App\Form','pid');
    }

    /**
     * Get the tokens associated with a given project.
     *
     * @return Token(s)
     */
    public function tokens(){
        return $this->belongsToMany('App\Token');
    }

    /**
     * Returns the project's admin group.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function adminGroup(){
        return $this->belongsTo('App\ProjectGroup', 'adminGID');
    }

    /**
     * Returns the groups associated with a project.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function groups(){
        return $this->hasMany('App\ProjectGroup','pid');
    }

    public function optionPresets(){
        return $this->hasMany('App\OptionPreset','pid');
    }

    /**
     * Because the MyISAM engine doesn't support foreign keys we have to emulate cascading.
     */
    public function delete() {
        DB::table("project_token")->where("pid", "=", $this->pid)->delete();
        DB::table("option_presets")->where("pid", "=", $this->pid)->delete();
        DB::table("project_groups")->where("pid", "=", $this->pid)->delete();

        $forms = Form::where("pid", "=", $this->pid)->get();
        foreach($forms as $form) {
            $form->delete();
        }

        parent::delete();
    }

    /**
     * Builds up and array of a project and its forms to be send to Javascript.
     *
     * @return array
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
