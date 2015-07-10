<?php namespace App\Http\Controllers;

use App\Group;
use App\Project;
use App\Http\Requests;
use App\Http\Requests\ProjectRequest;
use App\Http\Controllers\Controller;

use App\User;
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

        ProjectController::makeGroup($project, $request);

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
     * Creates the project's adminGroup.
     *
     * @param $project
     * @param $request
     */
    private function makeGroup($project, $request)
    {
        $groupName = $project->name;
        $groupName .= ' Admin Group';

        $adminGroup = new Group();
        $adminGroup->name = $groupName;
        //if admins not null
        $adminGroup->users()->attach($request['admins']);
        //endif
        $adminGroup->pid = $project->pid;

        //Checkboxes!
    }

    public static function getProject($id){
        $project = Project::where('pid','=',$id)->first();
        if(is_null($project)){
            $project = Project::where('slug','=',$id)->first();
        }

        return $project;
    }

}
