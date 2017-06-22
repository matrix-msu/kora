<?php namespace App\Http\Controllers;

use App\Form;
use App\FormGroup;
use App\Http\Controllers\Auth\UserController;
use App\User;
use App\Project;
use App\ProjectGroup;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;


class ProjectGroupController extends Controller {

    /**
     * User must be logged in and an admin to access views in this controller.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('active');
    }

    /**
     * @param $pid
     * @return Response
     */
    public function index($pid)
    {
        $project = ProjectController::getProject($pid);

        if(!(\Auth::user()->isProjectAdmin($project))){
            flash()->overlay(trans('controller_projectgroup.admin'), trans('controller_projectgroup.whoops'));
            return redirect('projects/'.$pid);
        }

        $projectGroups = $project->groups()->get();
        $users = User::lists('username', 'id')->all();
        $all_users = User::all();
        return view('projectGroups.index', compact('project', 'projectGroups', 'users', 'all_users'));
    }

    /**
     * Creates new group for a project.
     *
     * @param $pid
     * @param Request $request
     * @return Response
     */
    public function create($pid, Request $request)
    {
        if($request['name'] == ""){
            flash()->overlay(trans('controller_projectgroup.name'), trans('controller_projectgroup.whoops'));
            return redirect('projects/'.$pid.'/manage/projectgroups');
        }

        $group = self::buildGroup($pid, $request);

        if(!is_null($request['users'])) {
            foreach($request['users'] as $uid){
                //remove them from an old group if they have one
                //get any groups the user belongs to
                $currGroups = DB::table('project_group_user')->where('user_id', $uid)->get();
                $newUser = true;
                $grp = null;
                $idOld = 0;

                //foreach of the user's project groups, see if one belongs to the current project
                foreach($currGroups as $prev){
                    $grp = ProjectGroup::where('id', '=', $prev->project_group_id)->first();
                    if($grp->pid==$group->pid){
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
                        $request['formGroup'] = $defGroup->id;
                        $request['userId'] = $uid;
                        $FGC->addUser($request);
                    }

                    $this->emailUserProject("added", $uid, $group->id);
                }
                else
                    $this->emailUserProject("changed",$uid,$group->id);
            }

            $group->users()->attach($request['users']);
        }

        flash()->overlay(trans('controller_projectgroup.create'), trans('controller_projectgroup.success'));
        return redirect('projects/'.$pid.'/manage/projectgroups');
    }

    /**
     * Remove user from a project group.
     *
     * @param Request $request
     */
    public function removeUser(Request $request)
    {
        $instance = ProjectGroup::where('id', '=', $request['projectGroup'])->first();

        if($request['pid'] == $instance->id)
            self::wipeAdminRights($request, $request['pid']);

        $forms = Form::where('pid', '=', $instance->pid)->get();
        foreach ($forms as $form) {
            $formGroups = FormGroup::where('fid','=',$form->fid)->get();
            foreach($formGroups as $fg){
                DB::table('form_group_user')->where('user_id', $request['userId'])->where('form_group_id', $fg->id)->delete();
            }
        }

        $instance->users()->detach($request['userId']);

        $this->emailUserProject("removed",$request['userId'],$instance->id);
    }

    /**
     * Add a user to a project group.
     *
     * @param Request $request
     */
    public function addUser(Request $request)
    {
        $instance = ProjectGroup::where('id', '=', $request['projectGroup'])->first();

        //get any groups the user belongs to
        $currGroups = DB::table('project_group_user')->where('user_id', $request['userId'])->get();
        $newUser = true;
        $group = null;
        $idOld = 0;

        //foreach of the user's project groups, see if one belongs to the current project
        foreach($currGroups as $prev){
            $group = ProjectGroup::where('id', '=', $prev->project_group_id)->first();
            if($group->pid==$instance->pid){
                $newUser = false;
                $idOld = $group->id;
                break;
            }
        }

        if($newUser) {
            $proj = ProjectController::getProject($instance->pid);

            if($instance->name == $proj->name.' Admin Group') {
                $tag = ' Admin Group';
            }else {
                $tag = ' Default Group';
            }

            //add to all forms
            $forms = Form::where('pid', '=', $instance->pid)->get();
            foreach ($forms as $form) {
                $defGroup = FormGroup::where('name', '=', $form->name . $tag)->get()->first();
                $FGC = new FormGroupController();
                $request['formGroup'] = $defGroup->id;
                $FGC->addUser($request);
            }

            $this->emailUserProject("added", $request['userId'], $instance->id);
        }else{
            //remove from old group
            DB::table('project_group_user')->where('user_id', $request['userId'])->where('project_group_id', $idOld)->delete();

            $this->emailUserProject("changed", $request['userId'], $instance->id);

            echo $idOld;
        }

        $instance->users()->attach($request['userId']);
    }

    /**
     * Deletes a project group.
     *
     * @param Request $request
     */
    public function deleteProjectGroup(Request $request)
    {
        $instance = ProjectGroup::where('id', '=', $request['projectGroup'])->first();

        $users = $instance->users()->get();
        $forms = Form::where('pid', '=', $instance->pid)->get();
        foreach($users as $user){
            foreach ($forms as $form) {
                $formGroups = FormGroup::where('fid','=',$form->fid)->get();
                foreach($formGroups as $fg){
                    DB::table('form_group_user')->where('user_id', $user->id)->where('form_group_id', $fg->id)->delete();
                }
            }

            $this->emailUserProject("removed",$user->id,$instance->id);
        }

        $instance->delete();

        flash()->overlay(trans('controller_projectgroup.delete'), trans('controller_projectgroup.success'));
    }

    /**
     * Change a group's permissions.
     *
     * @param Request $request
     */
    public function updatePermissions(Request $request)
    {
        $instance = ProjectGroup::where('id', '=', $request['projectGroup'])->first();

        $users = $instance->users()->get();
        foreach($users as $user){
            $this->emailUserProject("changed",$user->id,$instance->id);
        }

        if($request['permCreate'])
            $instance->create = 1;
        else
            $instance->create = 0;

        if($request['permEdit'])
            $instance->edit = 1;
        else
            $instance->edit = 0;

        if($request['permDelete'])
            $instance->delete = 1;
        else
            $instance->delete = 0;

        $instance->save();
    }

    public function updateName(Request $request)
    {
        $instance = ProjectGroup::where('id', '=', $request->gid)->first();
        $instance->name = $request->name;

        $instance->save();
    }

    /**
     * Builds a new group for a project.
     *
     * @param $pid
     * @param Request $request
     * @return ProjectGroup
     */
    private function buildGroup($pid, Request $request)
    {
        $group = new ProjectGroup();
        $group->name = $request['name'];
        $group->pid = $pid;
        $group->create = 0;
        $group->edit = 0;
        $group->delete = 0;

        if(!is_null($request['create']))
            $group->create = 1;
        if(!is_null($request['edit']))
            $group->edit = 1;
        if(!is_null($request['delete']))
            $group->delete = 1;

        $group->save();

        return $group;
    }

    private function wipeAdminRights($request, $pid)
    {
        $user = $request['userId'];
        $project = ProjectController::getProject($pid);
        $forms = $project->forms()->get();

        foreach($forms as $form){
            $adminGroup = $form->adminGroup()->first();
            $adminGroup->users()->detach($user);
        }
    }

    private function emailUserProject($type, $uid, $pgid){
        $userMail = DB::table('users')->where('id', $uid)->value('email');
        $name = DB::table('users')->where('id', $uid)->value('name');
        $group = ProjectGroup::where('id', '=', $pgid)->first();
        $project = ProjectController::getProject($group->pid);

        if($type=="added"){
            $email = 'emails.project.added';
        }else if($type=="removed"){
            $email = 'emails.project.removed';
        }else if($type=="changed"){
            $email = 'emails.project.changed';
        }

        Mail::send($email, compact('project', 'name'), function ($message) use($userMail) {
            $message->from(env('MAIL_FROM_ADDRESS'));
            $message->to($userMail);
            $message->subject('Kora Project Permissions');
        });
    }

    public static function updateMainGroupNames($project){
        $admin = ProjectGroup::where('pid', '=', $project->pid)->where('name', 'like', '% Admin Group')->get()->first();
        $default = ProjectGroup::where('pid', '=', $project->pid)->where('name', 'like', '% Default Group')->get()->first();

        $admin->name = $project->name.' Admin Group';
        $admin->save();

        $default->name = $project->name.' Default Group';
        $default->save();
    }
}
