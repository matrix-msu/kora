<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class ProjectGroup extends Model {

    /*
    |--------------------------------------------------------------------------
    | Project Group
    |--------------------------------------------------------------------------
    |
    | This model represents the data for a Project Group
    |
    */

    /**
     * @var array - Attributes that can be mass assigned to model
     */
	protected $fillable = ['name', 'pid', 'create', 'edit', 'delete'];

    /**
     * Returns projects associated with a project group.
     *
     * @return BelongsToMany
     */
    public function users() {
        return $this->belongsToMany('App\User');
    }

    /**
     * Returns a project group's project.
     *
     * @return HasOne
     */
    public function project() {
        return $this->belongsTo('App\Project');
    }

    /**
     * Determines if a user is in a project group.
     *
     * @param User $user - User to verify
     * @return bool - Is member
     */
    public function hasUser(User $user) {
        $thisUsers = $this->users()->get();
        return $thisUsers->contains($user);
    }

    /**
     * Delete's the connections between group and users, and then deletes self.
     */
    public function delete() {
        DB::table("project_group_user")->where("project_group_id", "=", $this->id)->delete();

        parent::delete();
    }
}
