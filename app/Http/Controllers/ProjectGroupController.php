<?php namespace App\Http\Controllers;

use App\Commands\ProjectEmails;
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
    public function index($pid, Request $request) {
        $project = ProjectController::getProject($pid);
        $active = 0;

        if(!\Auth::user()->isProjectAdmin($project))
            return redirect('projects')->with('k3_global_error', 'not_project_admin');

        $projectGroups = $project->groups()->get()->sortBy('id');
        $users = User::pluck('username', 'id')->all();
        $all_users = User::all();

        $notification = array(
          'message' => '',
          'description' => '',
          'warning' => false,
          'static' => false
        );

        $prevUrlArray = $request->session()->get('_previous');
        $session = $request->session()->get('k3_global_success');
        if(!is_null($prevUrlArray) && reset($prevUrlArray) !== url()->current() && $session == 'project_group_created') {
            $notification['message'] = 'Project Permissions Group Successfully Created';
            $notification['description'] = $request->session()->get('batch_user_status');
            $notification['static'] = true;
        } else if(!is_null($prevUrlArray) && reset($prevUrlArray) !== url()->current() && $session == 'project_user_added') {
            $notification['message'] = 'Users Successfully Added to Project Group';
            $notification['description'] = $request->session()->get('batch_user_status');
            $notification['static'] = true;
        }

        return view('projectGroups.index', compact('project', 'projectGroups', 'users', 'all_users', 'active', 'notification'));
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

		// send invite emails & create new users
	    if(is_string($request->emails) && $request->emails !== '') {
		  $request->return_user_ids = true;
          $batchResults = (new AdminController())->batch($request); // this action creates the new users
          $user_ids = $batchResults[0];
          $user_results = $batchResults[1];

		  if(is_array($request->users))
		    $request->users = array_merge($request->users, $user_ids);
		  else
		    $request->users = $user_ids;

		  // $request->users is the ids of newly created invited users, and existing users
	    }

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
                    if($grp->project_id==$group->project_id) {
                        $newUser = false;
                        $idOld = $grp->id;
                        break;
                    }
                }

                DB::table('project_group_user')->where('user_id', $uid)->where('project_group_id', $idOld)->delete();

                if ($newUser) {
                    //add to all forms
                    $forms = Form::where('project_id', '=', $group->project_id)->get();
                    foreach($forms as $form) {
                        $defGroup = FormGroup::where('name', '=', $form->name . ' Default Group')->where('form_id', '=', $form->id)->get()->first();
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
            $project = Project::where('id', '=', $pid)->first();
            $project->batchAddUsersAsCustom($request->users);

            $group->users()->attach($request->users);
        }

        return redirect('projects/'.$pid.'/manage/projectgroups')->with('k3_global_success', 'project_group_created')->with('batch_user_status', implode(" | ", $user_results));;
    }

    /**
     * Removes a user from the group.
     *
     * @param  Request $request
     */
    public function removeUser(Request $request) {
        $instance = ProjectGroup::where('id', '=', $request->projectGroup)->first();

        if($request->pid == $instance->project_id)
            self::wipeAdminRights($request);

        $forms = Form::where('project_id', '=', $instance->project_id)->get();
        foreach($forms as $form) {
			$fg_ids = FormGroup::where('form_id','=',$form->id)->pluck('id');
			DB::table('form_group_user')->where('user_id', $request->userId)->whereIn('form_group_id', $fg_ids)->delete();
        }

        $user = User::where("id",(int)$request->userId)->first();
        $user->removeCustomProject($instance->project_id);


        $instance->users()->detach($request->userId);
        $this->emailUserProject("removed",$request->userId,$instance->id);
    }

    /**
     * Adds user to a group.
     *
     * @param  Request $request
     */
    public function addUsers(Request $request) {
        if (is_string($request->emails) && $request->emails !== '') {
			$request->return_user_ids = true;
			// returns new & existing users' ids
            $batchResults = (new AdminController())->batch($request); // this action creates the new users
            $user_ids = $batchResults[0];
            $user_results = $batchResults[1];

			if (is_array($request->userIDs))
				$request->userIDs = array_merge($request->userIDs, $user_ids);
			else
				$request->userIDs = $user_ids;
	    }

		$instance = ProjectGroup::where('id', '=', $request->projectGroup)->first();

		$new_users = array();
		foreach($request->userIDs as $userID) {
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
				if($group->project_id == $instance->project_id) {
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

	    $proj = ProjectController::getProject($instance->project_id);
		// add the users to the custom project
		$proj->batchAddUsersAsCustom($request->userIDs);

		if($instance->name == $proj->name.' Admin Group') {
			$tag = ' Admin Group';
        } else {
			$tag = ' Default Group';
        }
		$forms = Form::where('project_id', '=', $instance->project_id)->get();

		$names = array(); $fids = array();
		foreach ($forms as $form) {
			array_push($names, $form->name . $tag);
			$fids[$form->id] = true;
		}

		$possible_formgroups = FormGroup::whereIn('name', $names)->get();

		foreach ($possible_formgroups as $form_group) {
			if (isset($fids[$form_group->form_id])) { // this filters out form groups with same name but different fid
				$FGC = new FormGroupController();
				$request->formGroup = $form_group;
				$request->_internal = true;
				$request->dontLookBack = true;
				$request->userIDs = $new_users;
				$FGC->addUser($request);
			}
		}

        session(['k3_global_success' => 'project_user_added', "batch_user_status"=>implode(" | ", $user_results)]);
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
        $forms = Form::where('project_id', '=', $instance->project_id)->get();
        foreach($users as $user) {
            foreach($forms as $form) {
                $formGroups = FormGroup::where('form_id','=',$form->id)->get();
                foreach($formGroups as $fg) {
                    DB::table('form_group_user')->where('user_id', $user->id)->where('form_group_id', $fg->id)->delete();
                }
            }

            //Remove their custom project connection
            $user->removeCustomProject($instance->project_id);

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
     * @return JsonResponse
     */
    public function updateName(Request $request) {
        $instance = ProjectGroup::where('id', '=', $request->gid)->first();
        $instance->name = $request->name;

        $instance->save();

        return response()->json(["status"=>true,"message"=>"project_group_name_updated"],200);
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
        $group->project_id = $pid;
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
     * @param  int $pgid - Project Group ID
     */
    private function emailUserProject($type, $uid, $pgid) {
        $user = User::where('id',$uid)->first();
        $userMail = $user->email;
        $name = $user->preferences['first_name'];
        $group = ProjectGroup::where('id', '=', $pgid)->first();
        $project = ProjectController::getProject($group->project_id);
        $email = "emails.project.$type";

        $job = new ProjectEmails('ProjectPermissionsUpdated', ['email' => $email, 'userMail' => $userMail,
            'name' => $name, 'group' => $group, 'project' => $project]);
        $job->handle();
    }

    /**
     * This function will rename the Default and Admin groups' names when the project's name changes.
     *
     * @param  Project $project - Project to update
     */
    public static function updateMainGroupNames($project) {
        $admin = ProjectGroup::where('project_id', '=', $project->id)->where('name', 'like', '% Admin Group')->get()->first();
        $default = ProjectGroup::where('project_id', '=', $project->id)->where('name', 'like', '% Default Group')->get()->first();

        $admin->name = $project->name.' Admin Group';
        $admin->save();

        $default->name = $project->name.' Default Group';
        $default->save();
    }
}
