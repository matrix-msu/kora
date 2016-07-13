<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProjectGroup extends Model {

	protected $fillable = ['name', 'pid', 'create', 'edit', 'delete'];

    /**
     * Returns projects associated with a project group.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(){
        return $this->belongsToMany('App\User');
    }

    /**
     * Returns a project group's project.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function project(){
        return $this->belongsTo('App\Project');
    }

    /**
     * Determines if a user is in a project group.
     *
     * @param User $user
     * @return bool
     */
    public function hasUser(User $user){
        $thisUsers = $this->users()->get();
        return $thisUsers->contains($user);
    }

    public function delete() {
        DB::table("project_group_user")->where("project_group_id", "=", $this->id)->delete();

        parent::delete();
    }
}
