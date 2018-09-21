<?php namespace App\Http\Controllers;

use App\Form;
use App\FormGroup;
use App\Project;
use App\User;
use App\ProjectGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProjectGroupController extends Controller {

    /*
    |--------------------------------------------------------------------------
    | Project Group Controller
    |--------------------------------------------------------------------------
    |
    | This controller handle permission groups for projects
    |
    */

    /**
     * Constructs controller and makes sure user is authenticated.
     */
    public function __construct() {
        $this->middleware('auth');
        $this->middleware('active');
    }

    /**
     * Gets the main view for managing project groups.
     *
     * @param $pid - Project ID
     * @return View
     */
    public function index($pid, $active = 0) {
        $project = ProjectController::getProject($pid);

        if(!\Auth::user()->isProjectAdmin($project))
            return redirect('projects')->with('k3_global_error', 'not_project_admin');

        $projectGroups = $project->groups()->get()->sortBy('id');
        $users = User::pluck('username', 'id')->all();
        $all_users = User::all();
        return view('projectGroups.index', compact('project', 'projectGroups', 'users', 'all_users', 'active'));
    }

    /**
     * Creates a new project group.
     *
     * @param $pid - Project ID
     * @param  Request $request
     * @return Redirect
     */
    public function create($pid, Request $request) {
        if($request->name == "")
            return redirect('projects/'.$pid.'/manage/projectgroups')->with('k3_global_error', 'group_name_missing');

        $group = self::buildGroup($pid, $request);

        if(!is_null($request->users)) {
            foreach($request->users as $uid) {
                //remove them from an old group if they have one
                //get any groups the user belongs to
                $currGroups = DB::table('project_group_user')->where('user_id', $uid)->get();
                $newUser = true;
                $grp = null;
                $idOld = 0;

                //foreach of the user's project groups, see if one belongs to the current project
                foreach($currGroups as $prev) {
                    $grp = ProjectGroup::where('id', '=', $prev->project_group_id)->first();
                    if($grp->pid==$group->pid) {
                        $newUser = false;
                        $idOld = $grp->id;
                        break;
                    }
                }

                DB::table('project_group_user')->where('user_id', $uid)->where('project_group_id', $idOld)->delete();

                if($newUser) {
                    //add to all forms
                    $forms = Form::where('pid', '=', $group->pid)->get();
                    foreach ($forms as $form) {
                        $defGroup = FormGroup::where('name', '=', $form->name . ' Default Group')->get()->first();
                        $FGC = new FormGroupController();
                        $request->formGroup = $defGroup->id;
                        $request->userIDs = array($uid);
                        $FGC->addUser($request);
                    }

                    $this->emailUserProject("added", $uid, $group->id);
                } else {
                    $this->emailUserProject("changed", $uid, $group->id);
                }
            }
			
			// add the users to the custom project
			$project = Project::where('pid', '=', $pid)->first();
			$project->batchAddUsersAsCustom($request->users);

            $group->users()->attach($request->users);
        }
		
        return redirect('projects/'.$pid.'/manage/projectgroups')->with('k3_global_success', 'project_group_created');
    }

    /**
     * Removes a user from the group.
     *
     * @param  Request $request
     */
    public function removeUser(Request $request) {
        $instance = ProjectGroup::where('id', '=', $request->projectGroup)->first();

        if ($request->pid == $instance->id)
            self::wipeAdminRights($request);

        $forms = Form::where('pid', '=', $instance->pid)->get();
        foreach($forms as $form) {
			$fg_ids = FormGroup::where('fid','=',$form->fid)->pluck('id');
			DB::table('form_group_user')->where('user_id', $request->userId)->whereIn('form_group_id', $fg_ids)->delete();
        }

        $user = User::where("id",(int)$request->userId)->first();
        $user->removeCustomProject($instance->pid);
		
		
        $instance->users()->detach($request->userId);
        $this->emailUserProject("removed",$request->userId,$instance->id);
    }

    /**
     * Adds user to a group.
     *
     * @param  Request $request
     */
    public function addUsers(Request $request) {
		$instance = ProjectGroup::where('id', '=', $request->projectGroup)->first();

		$new_users = array();
		foreach ($request->userIDs as $userID) {
			//get any groups the user belongs to
			$currGroups = DB::table('project_group_user')->where('user_id', $userID)->get();
			$currGroups_ids = array();
			foreach($currGroups as $group) {
				array_push($currGroups_ids, $group->project_group_id);
			}
		
			$newUser = true;
			$group = null;
			$idOld = 0;
		
			// for the user's project groups, see if one belongs to the current project
			$groups = ProjectGroup::whereIn('id', $currGroups_ids)->get();
			
			foreach($groups as $group) {
				if($group == null){Log::info("Null group"); continue;}
				if ($group->pid == $instance->pid) {
					$newUser = false;
					$idOld = $group->id;
					break;
				}
			}
			
			if($newUser) {
				array_push($new_users, $userID);
			} else {
				//remove from old group
				DB::table('project_group_user')->where('user_id', $userID)->where('project_group_id', $idOld)->delete();
				$this->emailUserProject("changed", $userID, $instance->id);
				echo $idOld;
			}
	
			$instance->users()->attach($userID);
		}
		
	    $proj = ProjectController::getProject($instance->pid);
		// add the users to the custom project
		$proj->batchAddUsersAsCustom($request->userIDs);
		
		if($instance->name == $proj->name.' Admin Group') {
			$tag = ' Admin Group';
        } else {
			$tag = ' Default Group';
        }
		$forms = Form::where('pid', '=', $instance->pid)->get();
		
		$names = array(); $fids = array();
		foreach ($forms as $form) {
			array_push($names, $form->name . $tag);
			$fids[$form->fid] = true;
		}
		
		$possible_formgroups = FormGroup::whereIn('name', $names)->get();
		$wanted_form_group_ids = array();
		
		foreach ($possible_formgroups as $form_group) {
			if (isset($fids[$form_group->fid])) { // this filters out form groups with same name but different fid
				$FGC = new FormGroupController();
				$request->formGroup = $form_group;
				$request->_internal = true;
				$request->dontLookBack = true;
				$request->userIDs = $new_users;
				$FGC->addUser($request);
			}
		}
    }

    /**
     * Deletes a project group.
     *
     * @param  Request $request
     * @return JsonResponse
     */
    public function deleteProjectGroup(Request $request) {
        $instance = ProjectGroup::where('id', '=', $request->projectGroup)->first();

        $users = $instance->users()->get();
        $forms = Form::where('pid', '=', $instance->pid)->get();
        foreach($users as $user) {
            foreach ($forms as $form) {
                $formGroups = FormGroup::where('fid','=',$form->fid)->get();
                foreach($formGroups as $fg) {
                    DB::table('form_group_user')->where('user_id', $user->id)->where('form_group_id', $fg->id)->delete();
                }
            }

            //Remove their custom project connection
            $user->removeCustomProject($instance->pid);

            $this->emailUserProject("removed", $user->id, $instance->id);
        }

        $instance->delete();

        return response()->json(["status"=>true,"message"=>"project_group_deleted"],200);
    }

    /**
     * Updates the permission set for a particular group.
     *
     * @param  Request $request
     */
    public function updatePermissions(Request $request) {
        $instance = ProjectGroup::where('id', '=', $request->projectGroup)->first();

        if($request->permCreate)
            $instance->create = 1;
        else
            $instance->create = 0;

        if($request->permEdit)
            $instance->edit = 1;
        else
            $instance->edit = 0;

        if($request->permDelete)
            $instance->delete = 1;
        else
            $instance->delete = 0;

        $instance->save();
		
        $users = $instance->users()->get();
        foreach($users as $user) {
            $this->emailUserProject("changed", $user->id, $instance->id);
        }
    }

    /**
     * Update the name of a project group.
     *
     * @param  Request $request
     */
    public function updateName(Request $request) {
        $instance = ProjectGroup::where('id', '=', $request->gid)->first();
        $instance->name = $request->name;

        $instance->save();
    }

    /**
     * Does the actual building of the group model.
     *
     * @param  int $pid - Project ID
     * @param  Request $request
     * @return ProjectGroup - Newly built group
     */
    private function buildGroup($pid, Request $request) {
        $group = new ProjectGroup();
        $group->name = $request->name;
        $group->pid = $pid;
        $group->create = 0;
        $group->edit = 0;
        $group->delete = 0;

        if(!is_null($request->create))
            $group->create = 1;
        if(!is_null($request->edit))
            $group->edit = 1;
        if(!is_null($request->delete))
            $group->delete = 1;

        $group->save();

        return $group;
    }

    /**
     * Removes the user from a project's admin group.
     *
     * @param  Request $request
     */
    private function wipeAdminRights(Request $request) {
        $user = $request->userId;
        $project = ProjectController::getProject($request->pid);
        $forms = $project->forms()->get();

        foreach($forms as $form) {
            $adminGroup = $form->adminGroup()->first();
            $adminGroup->users()->detach($user);
        }
    }

    /**
     * Emails a user when their access to a project has changed.
     *
     * @param  string $type - Method to execute
     * @param  int $uid - User ID
     * @param  ProjectGroup 
	 * @param  Project
     */
    /**
     * Emails a user when their access to a project has changed.
     *
     * @param  string $type - Method to execute
     * @param  int $uid - User ID
     * @param  int $pgid - Project Group ID
     */
    private function emailUserProject($type, $uid, $pgid) {
        $userMail = DB::table('users')->where('id', $uid)->value('email');
        $name = DB::table('users')->where('id', $uid)->value('first_name');
        $group = ProjectGroup::where('id', '=', $pgid)->first();
        $project = ProjectController::getProject($group->pid);
        if($type=="added") {
            $email = 'emails.project.added';
        } else if($type=="removed") {
            $email = 'emails.project.removed';
        } else if($type=="changed") {
            $email = 'emails.project.changed';
        }
        try {
            Mail::send($email, compact('project', 'name', 'group'), function ($message) use ($userMail) {
                $message->from(config('mail.from.address'));
                $message->to($userMail);
                $message->subject('Kora Project Permissions');
            });
        } catch(\Swift_TransportException $e) {
            //TODO::email error response
            //Log for now
            Log::info('Access change email failed');
        }
    }

    /**
     * This function will rename the Default and Admin groups' names when the project's name changes.
     *
     * @param  Project $project - Project to update
     */
    public static function updateMainGroupNames($project) {
        $admin = ProjectGroup::where('pid', '=', $project->pid)->where('name', 'like', '% Admin Group')->get()->first();
        $default = ProjectGroup::where('pid', '=', $project->pid)->where('name', 'like', '% Default Group')->get()->first();

        $admin->name = $project->name.' Admin Group';
        $admin->save();

        $default->name = $project->name.' Default Group';
        $default->save();
    }
}
