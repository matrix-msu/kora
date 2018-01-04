<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Request;
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
     * @return BelongsTo
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
        $guBuilder = DB::table("project_group_user")->where("project_group_id", "=", $this->id);
        $group_users = $guBuilder->get();

        foreach($group_users as $group_user) {
            //remove this project from that users custom list
            $user = User::where("id","=",$group_user->user_id)->first();
            $user->removeCustomProject($this->pid);
        }

        //then delete the group connections
        $guBuilder->delete();

        parent::delete();
    }

    /**
     * Creates the admin group for a project.
     *
     * @param  Project $project - Project to add group
     * @param  Request $request
     * @return ProjectGroup - The new admin group
     */
    public static function makeAdminGroup(Project $project, $request=null) {
        $groupName = $project->name;
        $groupName .= ' Admin Group';

        $adminGroup = new ProjectGroup();
        $adminGroup->name = $groupName;
        $adminGroup->pid = $project->pid;
        $adminGroup->save();

        if(!is_null($request) && !is_null($request->admins)) {
            $adminGroup->users()->attach($request->admins);
            foreach($request->admins as $uid) {
                $user = User::where("id","=",$uid)->first();
                $user->addCustomProject($adminGroup->pid);
            }
        } else {
            $adminGroup->users()->attach(array(\Auth::user()->id));
            \Auth::user()->addCustomProject($adminGroup->pid);
        }

        //We want to now give this project to the custom list of all system admins
        $admins = User::where("admin","=",1)->get();
        foreach($admins as $admin) {
            $admin->addCustomProject($adminGroup->pid);
        }

        $adminGroup->create = 1;
        $adminGroup->edit = 1;
        $adminGroup->delete = 1;

        $adminGroup->save();

        return $adminGroup;
    }

    /**
     * Creates the default group for a project.
     *
     * @param  Project $project - Project to add group
     */
    public static function makeDefaultGroup(Project $project) {
        $groupName = $project->name;
        $groupName .= ' Default Group';

        $defaultGroup = new ProjectGroup();
        $defaultGroup->name = $groupName;
        $defaultGroup->pid = $project->pid;
        $defaultGroup->save();

        $defaultGroup->create = 0;
        $defaultGroup->edit = 0;
        $defaultGroup->delete = 0;

        $defaultGroup->save();
    }
}
