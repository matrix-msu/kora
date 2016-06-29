<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Token extends Model {

	protected $fillable = [
        'token',
        'type'
    ];

    /**
     * Get the projects associated to with a token.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function projects(){
        return $this->belongsToMany('App\Project');
    }


    /**
     * Determines if a token belongs to a certain project.
     *
     * @param Project $project
     * @return mixed
     */
    public function hasProject(Project $project)
    {
        $thisProjects = $this->projects()->get();
        return $thisProjects->contains($project);
    }

    /**
     * Because the MyISAM engine doesn't support foreign keys we have to emulate cascading.
     */
    public function delete() {
        DB::table("project_token")->where("token_id", "=", $this->id)->delete();

        parent::delete();
    }
}
