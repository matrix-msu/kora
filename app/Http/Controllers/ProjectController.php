<?php namespace App\Http\Controllers;

use App\User;
use App\Project;
use App\ProjectGroup;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller {

    /**
     * User must be logged in to access views in this controller.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('active');
        $this->middleware('admin', ['except' => ['index', 'show']]);
    }

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
        $projects = Project::all();


        return view('projects.index', compact('projects'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
        $users = User::lists('name', 'id');
        return view('projects.create', compact('users'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(ProjectRequest $request)
	{
        $project = Project::create($request->all());

        $adminGroup = ProjectController::makeAdminGroup($project, $request);
        $project->adminGID = $adminGroup->id;
        $project->save();

        flash()->overlay('Your project has been successfully created!','Good Job');

        return redirect('projects');
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
    {
        if(!FormController::checkPermissions($id)){
            return redirect('/projects');
        }

        $project = ProjectController::getProject($id);
        return view('projects.show', compact('project'));
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
        $project = ProjectController::getProject($id);
        return view('projects.edit', compact('project'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id, ProjectRequest $request)
	{
        $project = ProjectController::getProject($id);
        $project->update($request->all());
        flash()->overlay('Your project has been successfully updated!','Good Job');

        return redirect('projects');
	}

    /**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
    {
        $project = ProjectController::getProject($id);
        $project->delete();

        flash()->overlay('Your project has been successfully deleted!','Good Job');
	}

    /**
     * Decides if a certain user is a project admin.
     *
     * @param User $user
     * @param Project $project
     * @return bool
     */
    public function isProjectAdmin(User $user, Project $project)
    {
        $adminGroup = $project->adminGroup()->first();
        if($adminGroup->hasUser($user))
            return true;
        return false;
    }

    /**
     * Creates the project's admin Group.
     *
     * @param $project
     * @param $request
     * @return Group
     */
    private function makeAdminGroup($project, $request)
    {
        $groupName = $project->name;
        $groupName .= ' Admin Group';

        $adminGroup = new ProjectGroup();
        $adminGroup->name = $groupName;
        $adminGroup->pid = $project->pid;
        $adminGroup->save();

        if (!is_null($request['admins']))
            $adminGroup->users()->attach($request['admins']);

        $adminGroup->create = 1;
        $adminGroup->edit = 1;
        $adminGroup->delete = 1;

        $adminGroup->save();

        return $adminGroup;
    }

    public static function getProject($id){
        $project = Project::where('pid','=',$id)->first();
        if(is_null($project)){
            $project = Project::where('slug','=',$id)->first();
        }

        return $project;
    }
}
