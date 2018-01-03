<?php namespace App\Http\Controllers;

use App\Form;
use App\FormGroup;
use App\Project;
use App\User;
use App\ProjectGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
    public function index($pid) {
        $project = ProjectController::getProject($pid);

        if(!\Auth::user()->isProjectAdmin($project))
            return redirect('projects')->with('k3_global_error', 'not_project_admin');

        $projectGroups = $project->groups()->get()->sortBy('id');
        $users = User::lists('username', 'id')->all();
        $all_users = User::all();
        return view('projectGroups.index', compact('project', 'projectGroups', 'users', 'all_users'));
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
                        $request->userId = $uid;
                        $FGC->addUser($request);
                    }

                    $this->emailUserProject("added", $uid, $group->id);
                } else {
                    $this->emailUserProject("changed", $uid, $group->id);
                }

                //After all this, lets make sure they get the custom project added
                $user = User::where("id","=",$uid)->first();
                $user->addCustomProject($pid);
            }

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
          $formGroups = FormGroup::where('fid','=',$form->fid)->get();
          foreach($formGroups as $fg) {
            DB::table('form_group_user')->where('user_id', $request->userId)->where('form_group_id', $fg->id)->delete();
          }
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

      foreach ($request->userIDs as $userID) {
        //get any groups the user belongs to
        $currGroups = DB::table('project_group_user')->where('user_id', $userID)->get();
        $newUser = true;
        $group = null;
        $idOld = 0;

        //foreach of the user's project groups, see if one belongs to the current project
        foreach($currGroups as $prev) {
          $group = ProjectGroup::where('id', '=', $prev->project_group_id)->first();
          if($group->pid == $instance->pid) {
            $newUser = false;
            $idOld = $group->id;
            break;
          }
        }

        if($newUser) {
          $proj = ProjectController::getProject($instance->pid);

          if($instance->name == $proj->name.' Admin Group') {
            $tag = ' Admin Group';
          } else {
            $tag = ' Default Group';
          }

          //add to all forms
          $forms = Form::where('pid', '=', $instance->pid)->get();
          foreach($forms as $form) {
            $defGroup = FormGroup::where('name', '=', $form->name . $tag)->get()->first();
            $FGC = new FormGroupController();
            $request->formGroup = $defGroup->id;
            $request->userId = $userID;
            $request->dontLookBack = true;
            $FGC->addUser($request);
          }

          $this->emailUserProject("added", $userID, $instance->id);
        } else {
          //remove from old group
          DB::table('project_group_user')->where('user_id', $userID)->where('project_group_id', $idOld)->delete();
          $this->emailUserProject("changed", $userID, $instance->id);
          echo $idOld;
        }

        //After all this, lets make sure they get the custom project added
        $user = User::where("id",$userID)->first();
        $user->addCustomProject($instance->pid);

        $instance->users()->attach($userID);
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

            $this->emailUserProject("removed",$user->id,$instance->id);
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
            $this->emailUserProject("changed",$user->id,$instance->id);
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
                $message->from(env('MAIL_FROM_ADDRESS'));
                $message->to($userMail);
                $message->subject('Kora Project Permissions');
            });
        } catch(\Swift_TransportException $e) {
            //TODO::email error response
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
