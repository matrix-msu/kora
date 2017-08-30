<?php namespace App\Http\Controllers;

use App\User;
use App\Project;
use App\ProjectGroup;
use App\Http\Requests\ProjectRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProjectController extends Controller {

    /*
    |--------------------------------------------------------------------------
    | Project Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles projects within Kora3
    |
    */

    /**
     * Constructs controller and makes sure user is authenticated.
     */
    public function __construct() {
        $this->middleware('auth');
        $this->middleware('active');
        $this->middleware('admin', ['except' => ['index', 'show', 'request']]);
    }

    /**
     * Gets the view for the main projects page.
     * TODO::later sort initial pull by recent
     *
     * @return View
     */
	public function index() {
        $projectCollections = Project::all()->sortBy("name");

        $projects = array();
        $inactive = array();
        $custom = array();
        $pSearch = array();
        $hasProjects = false;
        $requestableProjects = array();
        foreach($projectCollections as $project) {
            if(\Auth::user()->admin || \Auth::user()->inAProjectGroup($project)) {
                if($project->active) {
                    array_push($projects, $project);
                    array_push($pSearch, $project);
                    $seq = \Auth::user()->getCustomProjectSequence($project->pid);
                    $custom[$seq] = $project;
                } else {
                    array_push($inactive, $project);
                    array_push($pSearch, $project);
                }

                $hasProjects = true;
            } else if($project->active) {
                $requestableProjects[$project->name] = $project->pid;
            }
        }

        //We need to sort the custom array
        ksort($custom);

        //TODO::Update stuff
        /*$c = new UpdateController();
        $updateNotification = false;
        if($c->checkVersion() && !session('notified_of_update')) {
            session(['notified_of_update' => true]);
            $updateNotification = true;
        }*/

        return view('projects.index', compact('projects', 'inactive', 'custom', 'pSearch', 'hasProjects', 'requestableProjects'));
	}

    /**
     * Sends an access request to admins of project(s).
     *
     * @param  Request $request
     * @return Redirect
     */
    public function request(Request $request) {
        $projects = array();
        if(!is_null($request->pid)) {
            foreach($request->pid as $pid) {
                $project = self::getProject($pid);
                if(!is_null($project))
                    array_push($projects, $project);
            }
        }

        if(sizeof($projects)==0) {
            return redirect('projects')->with('k3_global_error', 'no_project_requested');
        } else {
            foreach($projects as $project) {
                $admins = $this->getProjectAdminNames($project);

                foreach($admins as $user) {
                    Mail::send('emails.request.access', compact('project'), function ($message) use($user) {
                        $message->from(env('MAIL_FROM_ADDRESS'));
                        $message->to($user->email);
                        $message->subject('Kora Project Request');
                    });
                }
            }

            return redirect('projects')->with('k3_global_success', 'project_access_requested');
        }
    }

    /**
     * Gets the create view for a project.
     *
     * @return View
     */
	public function create() {
        $users = User::lists('username', 'id')->all();

        return view('projects.create', compact('users'));
	}

    /**
     * Saves a new project model to the DB.
     *
     * @param  ProjectRequest $request
     * @return Redirect
     */
	public function store(ProjectRequest $request) {
        $project = Project::create($request->all());

        $adminGroup = ProjectGroup::makeAdminGroup($project, $request);
        ProjectGroup::makeDefaultGroup($project);
        $project->adminGID = $adminGroup->id;
        $project->save();

        return redirect('projects')->with('k3_global_success', 'project_created');
	}

    /**
     * Gets the view for an individual project page.
     *
     * @param  int $id - Project ID
     * @return View
     */
	public function show($id) {
        if(!self::validProj($id))
            return redirect('projects')->with('k3_global_error', 'project_invalid');

        if(!FormController::checkPermissions($id))
            return redirect('/projects')->with('k3_global_error', 'cant_view_project');

        $project = self::getProject($id);
        $formCollections = $project->forms()->get()->sortBy("name");

        $forms = array();
        $custom = array();
        foreach($formCollections as $form){
            array_push($forms,$form);
            $seq = \Auth::user()->getCustomFormSequence($form->fid);
            $custom[$seq] = $form;
        }

        //We need to sort the custom array
        ksort($custom);

        return view('projects.show', compact('project','forms', 'custom'));
	}

    /**
     * Gets the view for editing a project.
     *
     * @param  int $id - Project ID
     * @return View
     */
	public function edit($id) {
        if(!self::validProj($id))
            return redirect('projects')->with('k3_global_error', 'project_invalid');

        $user = \Auth::user();
        $project = self::getProject($id);

        if(!\Auth::user()->isProjectAdmin($project))
            return redirect('projects')->with('k3_global_error', 'not_project_admin');

        return view('projects.edit', compact('project'));
	}

    /**
     * Updates an edited project.
     *
     * @param  int $id - Project ID
     * @param  ProjectRequest $request
     * @return Redirect
     */
	public function update($id, ProjectRequest $request) {
        $project = self::getProject($id);
        $project->update($request->all());

        ProjectGroupController::updateMainGroupNames($project);

        return redirect('projects')->with('k3_global_success', 'project_updated');
	}

    /**
     * Deletes a project.
     *
     * @param  int $id - Project ID
     * @return Redirect
     */
	public function destroy($id) {
        if(!self::validProj($id))
            return redirect()->action('ProjectController@index')->with('k3_global_error', 'project_invalid');

        $project = self::getProject($id);

        if(!\Auth::user()->isProjectAdmin($project))
            return redirect()->action('ProjectController@index')->with('k3_global_error', 'not_project_admin');

        $project->delete();

        return redirect()->action('ProjectController@index')->with('k3_global_success', 'project_deleted');
	}

    /**
     * Determines if user is an admin of the project.
     *
     * @param  User $user - User to authenticate
     * @param  Project $project - Project to check against
     * @return bool - Is project admin
     */
    public function isProjectAdmin(User $user, Project $project) {
        if($user->admin)
            return true;

        $adminGroup = $project->adminGroup()->first();
        if($adminGroup->hasUser($user))
            return true;
        else
            return false;
    }

    /**
     * Gets back a project using its ID or slug.
     *
     * @param  int $id - Project ID
     * @return Project - Project model matching ID/slug
     */
    public static function getProject($id) {
        $project = Project::where('pid','=',$id)->first();
        if(is_null($project))
            $project = Project::where('slug','=',$id)->first();

        return $project;
    }

    /**
     * Determines if project exists.
     *
     * @param  int $id - Project ID
     * @return bool - Is a project
     */
    public static function validProj($id) {
        return !is_null(self::getProject($id));
    }

    /**
     * Gets the view for importing a k3Proj file.
     *
     * @return View
     */
    public function importProjectView() {
        return view('projects.import');
    }

    /**
     * Get a list of project admins for a project.
     *
     * @param  Project $project - Project to retrieve from
     * @return Collection - List of users
     */
    private function getProjectAdminNames($project) {
        $group = $project->adminGroup()->first();
        $users = $group->users()->get();

        return $users;
    }
}
