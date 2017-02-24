<?php namespace App\Http\Controllers;

use App\User;
use App\Project;
use App\ProjectGroup;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ProjectController extends Controller {

    /**
     * User must be logged in to access views in this controller.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('active');
        $this->middleware('admin', ['except' => ['index', 'show', 'request']]);
    }

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
        $projectCollections = Project::all();

        $projectArrays = [];
        $projects = array();
        $hasProjects = false;
        $requestProjects = array();
        foreach($projectCollections as $project) {
            if(\Auth::user()->admin || \Auth::user()->inAProjectGroup($project)){
                $projectArrays[] = $project->buildFormSelectorArray();
                array_push($projects,$project);
                $hasProjects = true;
            }else if($project->active){
                $requestProjects[$project->name] = $project->pid;
            }
        }

        $c = new UpdateController();
        if ($c->checkVersion() && !session('notified_of_update')) {
            session(['notified_of_update' => true]);
            flash()->overlay(trans('controller_update.updateneeded'), trans('controller_update.updateheader'));
        }

        return view('projects.index', compact('projects', 'projectArrays', 'hasProjects','requestProjects'));
	}

    public function request(Request $request){
        $projects = array();
        if(!is_null($request->pid)) {
            foreach ($request->pid as $pid) {
                $project = ProjectController::getProject($pid);
                if (!is_null($project))
                    array_push($projects, $project);
            }
        }

        if(sizeof($projects)==0){
            flash()->overlay(trans('controller_project.requestfail'),trans('controller_project.whoops'));

            return redirect('projects');
        }else{
            foreach($projects as $project){
                $admins = $this->getProjectAdminNames($project);

                foreach($admins as $user){
                    Mail::send('emails.request.access', compact('project'), function ($message) use($user) {
                        $message->from(env('MAIL_FROM_ADDRESS'));
                        $message->to($user->email);
                        $message->subject('Kora Project Request');
                    });
                }
            }

            flash()->overlay(trans('controller_project.requestsuccess'),trans('controller_project.whoops'));

            return redirect('projects');
        }
    }

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
        if(\Auth::user()->admin);

        $users = User::lists('username', 'id')->all();
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
        ProjectController::makeDefaultGroup($project, $request);
        $project->adminGID = $adminGroup->id;
        $project->save();

        flash()->overlay(trans('controller_project.create'),trans('controller_project.goodjob'));

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
        if (!ProjectController::validProj(($id))){
            return redirect('/projects');
        }

        if(!FormController::checkPermissions($id)){
            return redirect('/projects');
        }

        $project = ProjectController::getProject($id);
        $projectArrays = [$project->buildFormSelectorArray()];

        return view('projects.show', compact('project', 'projectArrays'));
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
        if (!ProjectController::validProj(($id))){
            return redirect('/projects');
        }

        $user = \Auth::user();
        $project = ProjectController::getProject($id);

        if (!$user->admin && !ProjectController::isProjectAdmin($user, $project)) {
            flash()->overlay(trans('controller_project.editper'), trans('controller_project.whoops'));
            return redirect('/projects');
        }

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

        ProjectGroupController::updateMainGroupNames($project);

        flash()->overlay(trans('controller_project.updated'),trans('controller_project.goodjob'));

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
        if (!ProjectController::validProj(($id))){
            return redirect('/projects');
        }

        $user = \Auth::user();
        $project = ProjectController::getProject($id);

        if (!$user->admin && !ProjectController::isProjectAdmin($user, $project)) {
            flash()->overlay(trans('controller_project.deleteper'), trans('controller_project.whoops'));
            return redirect('/projects');
        }

        $project->delete();

        flash()->overlay(trans('controller_project.deleted'),trans('controller_project.goodjob'));
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
        if ($user->admin) return true;

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

    /**
     * Creates the project's default Group.
     *
     * @param $project
     * @param $request
     * @return Group
     */
    private function makeDefaultGroup($project, $request)
    {
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

        return $defaultGroup;
    }

    /**
     * Gets the project based on id or slug.
     *
     * @param $id
     * @return Project $project (possibly null)
     */
    public static function getProject($id){
        $project = Project::where('pid','=',$id)->first();
        if(is_null($project)){
            $project = Project::where('slug','=',$id)->first();
        }

        return $project;
    }

    /**
     * Determines the validity of a pid.
     *
     * @param $id
     * @return bool
     */
    public static function validProj($id){
        return !is_null(ProjectController::getProject($id));
    }

    public function importProjectView(){
        return view('projects.import');
    }

    private function getProjectAdminNames($project){
        $group = $project->adminGroup()->first();
        $users = $group->users()->get();

        return $users;
    }
}
