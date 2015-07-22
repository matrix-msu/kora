<?php namespace App\Http\Controllers;

use App\User;
use App\Project;
use App\ProjectGroup;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


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
            flash()->overlay('You are not an admin for that project.', 'Whoops.');
            return redirect('projects/'.$pid);
        }

        $projectGroups = $project->groups()->get();
        $users = User::lists('username', 'id');
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
            flash()->overlay('You must enter a group name.', 'Whoops.');
            return redirect('projects/'.$pid.'/manage/projectgroups');
        }

        $group = ProjectGroupController::buildGroup($pid, $request);

        if(!is_null($request['users']))
            $group->users()->attach($request['users']);

        flash()->overlay('Group created!', 'Success');
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
            ProjectGroupController::wipeAdminRights($request, $request['pid']);

        $instance->users()->detach($request['userId']);
    }

    /**
     * Add a user to a project group.
     *
     * @param Request $request
     */
    public function addUser(Request $request)
    {
        $instance = ProjectGroup::where('id', '=', $request['projectGroup'])->first();
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
        $instance->delete();

        flash()->overlay('Project group has been deleted.', 'Success!');
    }

    /**
     * Change a group's permissions.
     *
     * @param Request $request
     */
    public function updatePermissions(Request $request)
    {
        $instance = ProjectGroup::where('id', '=', $request['projectGroup'])->first();

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
}
