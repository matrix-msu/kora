<?php namespace App;

use App\Http\Controllers\ProjectController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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
	protected $fillable = ['name', 'project_id', 'create', 'edit', 'delete'];

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

        foreach($group_users as $group_user) { //TODO::CASTLE
            //remove this project from that users custom list
            $user = User::where("id","=",$group_user->user_id)->first();
            $user->removeCustomProject($this->pid);
        }

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
        $adminGroup->project_id = $project->id;
        $adminGroup->save();
		
		$users_to_add = array();

        if(!is_null($request) && !is_null($request->admins)) {
            $adminGroup->users()->attach($request->admins);
			
            foreach($request->admins as $uid) {
				array_push($users_to_add, $uid);
                self::emailProjectAdmin($uid, $adminGroup->id);
            }
        }

        $adminGroup->users()->attach(array(\Auth::user()->id));
		array_push($users_to_add, \Auth::user()->id);

        //We want to now give this project to the custom list of all system admins
        $admins = User::where("admin","=",1)->get();
        foreach($admins as $admin) {
			array_push($users_to_add, $admin->id);
        }
		
		$proj = ProjectController::getProject($adminGroup->project_id);
		//$proj->batchAddUsersAsCustom($users_to_add); //TODO::CASTLE

        $adminGroup->create = 1;
        $adminGroup->edit = 1;
        $adminGroup->delete = 1;

        $adminGroup->save();

        return $adminGroup;
    }

    /**
     * Emails a user when they are added as admin to a newly created project.
     *
     * @param  int $uid - User ID
     * @param  int $pgid - Project Group ID
     */
    private static function emailProjectAdmin($uid, $pgid) {
        $user = User::where('id', $uid)->first();
        $userMail = $user->email;
        $name = $user->preferences['first_name'];

        $group = ProjectGroup::where('id', '=', $pgid)->first();
        $project = ProjectController::getProject($group->project_id);
        $email = 'emails.project.added';

        try {
            Mail::send($email, compact('project', 'name', 'group'), function ($message) use ($userMail) {
                $message->from(config('mail.from.address'));
                $message->to($userMail);
                $message->subject('Kora Project Permissions');
            });
        } catch(\Swift_TransportException $e) {
            //Log for now
            Log::info('Project admin email failed');
        }
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
        $defaultGroup->project_id = $project->id;
        $defaultGroup->save();

        $defaultGroup->create = 0;
        $defaultGroup->edit = 0;
        $defaultGroup->delete = 0;

        $defaultGroup->save();
    }
}
