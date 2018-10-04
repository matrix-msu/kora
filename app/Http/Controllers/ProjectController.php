<?php namespace App\Http\Controllers;

use App\User;
use App\Project;
use App\ProjectGroup;
use App\Http\Requests\ProjectRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
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
    }

    /**
     * Gets the view for the main projects page.
     *
     * @return View
     */
	public function index(Request $request) {
        $projectCollections = Project::all()->sortBy("name", SORT_NATURAL|SORT_FLAG_CASE);

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
                    if($seq == null) {
                        \Auth::user()->addCustomProject($project->pid);
                        $seq = \Auth::user()->getCustomProjectSequence($project->pid);
                    }

                    $custom[$seq] = $project;
                } else {
                    array_push($inactive, $project);
                    array_push($pSearch, $project);
                }

                $hasProjects = true;
            } else if($project->active) {
                $requestableProjects[$project->pid] = $project->name. " (" . $project->slug.")";
            }
        }
		
        //We need to sort the custom array
        ksort($custom);

        // should probably make a global notificationsController
        $notification = array(
          'message' => '',
          'description' => '',
          'warning' => false,
          'static' => false
        );

        if(\Auth::user()->admin) {
            $current = new UpdateController();
            if($current->checkVersion())
                $notification['message'] = 'Update Available!';
        }

        $prevUrlArray = $request->session()->get('_previous');
        $prevUrl = reset($prevUrlArray);
        // we do not need to see notification every time we reload the page
        if ($prevUrl !== url()->current()) {
          $session = $request->session()->get('k3_global_success');
          if ($session) {
            if ($session == 'project_deleted')
              $notification['message'] = 'Project Successfully Deleted';
            else if ($session == 'project_archived')
              $notification['message'] = 'Project Successfully Archived!';
            else if ($session == 'project_imported')
              $notification['message'] = 'Project Successfully Imported!';
          } else {
            $session = $request->session()->get('k3_global_error');
            $notification['warning'] = true;
            $notification['static'] = true;
            if (strpos($session, 'cant') !== false || strpos($session, 'admin') !== false) {
              $notification['message'] = 'Insufficient Permissions';
            }
          }
        }

        return view('projects.index', compact('projects', 'inactive', 'custom', 'pSearch', 'hasProjects', 'requestableProjects', 'notification'));
	}
	
	/**
     * Gets modal to request project permissions
     *
     * @param  Request $request
     * @return String contents of view
     */
	public function getProjectPermissionsModal(Request $request)
	{	
		$projectCollections = Project::all()->sortBy("name", SORT_NATURAL|SORT_FLAG_CASE);
		$requestableProjects = array();
		foreach($projectCollections as $project) {
			if($project->active and !(\Auth::user()->inAProjectGroup($project)))
			{
				$requestableProjects[$project->pid] = $project->name. " (" . $project->slug.")";
			}
		}
		
		return view('partials.projects.projectRequestModalForm', ['requestableProjects' => $requestableProjects])->render();
	}

    /**
     * Sends an access request to admins of project(s).
     *
     * @param  Request $request
     * @return Redirect
     */
    public function request(Request $request) {
        $projects = array();
        if(!is_null($request->pids)) {
            foreach($request->pids as $pid) {
                $project = self::getProject($pid);
                if(!is_null($project))
                    array_push($projects, $project);
            }
        }

        if(sizeof($projects)==0) {
            return response()->json(["status"=>false, "message"=>"project_access_empty", 500]);
        } else {
            foreach($projects as $project) {
                $admins = $this->getProjectAdminNames($project);

                foreach($admins as $user) {
                    try{
                        Mail::send('emails.request.access', compact('project'), function ($message) use($user) {
                            $message->from(config('mail.from.address'));
                            $message->to($user->email);
                            $message->subject('Kora Project Request');
                        });
                    } catch(\Swift_TransportException $e) {
                        //Log for now
                        return response()->json(["status"=>false, "message"=>"project_access_failed", 500]);
                    }
                }
            }
			
            // only occurs on form submit, not on AJAX call
            return response()->json(["status"=>true, "message"=>"project_access_requested", 200]);
        }
    }

    /**
     * Gets the create view for a project.
     *
     * @return View
     */
	public function create() {
        if(!\Auth::user()->admin)
            return redirect('projects')->with('k3_global_error', 'not_admin');

        $currentUser = auth()->user();
        //$users = User::pluck('username', 'id')->all();
        $users = User::where('id', '!=', $currentUser->id)->pluck('username', 'id')->all();
        $projectMode = "project_create";

        return view('projects.create', compact('users','projectMode'));
	}

    /**
     * Saves a new project model to the DB.
     *
     * @param  ProjectRequest $request
     * @return Redirect
     */
	public function store(ProjectRequest $request) {
        if(!\Auth::user()->admin)
            return redirect('projects')->with('k3_global_error', 'not_admin');

        $project = Project::create($request->all());

        $adminGroup = ProjectGroup::makeAdminGroup($project, $request);
        ProjectGroup::makeDefaultGroup($project);
        $project->adminGID = $adminGroup->id;
        $project->active = 1;
        $project->save();

        return redirect('projects/'.$project->pid)->with('k3_global_success', 'project_created');
	}

    /**
     * Gets the view for an individual project page.
     *
     * @param  int $id - Project ID
     * @return View
     */
	public function show($id, Request $request) {
        if(!self::validProj($id))
            return redirect('projects')->with('k3_global_error', 'project_invalid');

        if(!FormController::checkPermissions($id))
            return redirect('/projects')->with('k3_global_error', 'cant_view_project');

        $project = self::getProject($id);
        $formCollections = $project->forms()->get()->sortBy("name", SORT_NATURAL|SORT_FLAG_CASE);

        $forms = array();
        $custom = array();
        foreach($formCollections as $form){
            array_push($forms,$form);

            $seq = \Auth::user()->getCustomFormSequence($form->fid);
            if($seq == null) {
                \Auth::user()->addCustomForm($form->fid);
                $seq = \Auth::user()->getCustomFormSequence($form->fid);
            }

            $custom[$seq] = $form;
        }

        //We need to sort the custom array
        ksort($custom);

        $notification = array(
          'message' => '',
          'description' => '',
          'warning' => false,
          'static' => false
        );
        $prevUrlArray = $request->session()->get('_previous');
        $prevUrl = reset($prevUrlArray);
        // we do not need to see notification every time we reload the page
        if ($prevUrl !== url()->current()) {
          $session = $request->session()->get('k3_global_success');
          if ($session) {
            if ($session == 'project_updated')
              $notification['message'] = 'Project Sucessfully Updated!';
            else if ($session == 'project_created')
              $notification['message'] = 'Project Successfully Created!';
            else if ($session == 'form_deleted')
              $notification['message'] = 'Form Successfully Deleted!';
            else if ($session == 'form_imported')
              $notification['message'] = 'Form Successfully Imported!';
          } else {
            $session = $request->session()->get('k3_global_error');
            $notification['warning'] = true;
            $notification['static'] = true;
            if (strpos($session, 'cant') !== false || strpos($session, 'admin') !== false) {
              $notification['message'] = 'Insufficient Permissions';
            }
          }
        }

        return view('projects.show', compact('project','forms', 'custom', 'notification'));
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

        $projectMode = "project_edit";

        return view('projects.edit', compact('project','projectMode'));
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

        if(!\Auth::user()->isProjectAdmin($project))
            return redirect('projects')->with('k3_global_error', 'not_project_admin');

        $project->update($request->all());

        ProjectGroupController::updateMainGroupNames($project);

        return redirect('projects/'.$id)->with('k3_global_success', 'project_updated');
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
            return redirect('projects')->with('k3_global_error', 'not_project_admin');

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
        if(!\Auth::user()->admin)
            return redirect('projects')->with('k3_global_error', 'not_admin');

        return view('projects.import');
    }

    /**
     * Archives or restores a project.
     *
     * @param  Request $request
     * @return View
     */
    public function setArchiveProject($pid, Request $request) {
        if(!\Auth::user()->admin)
            return redirect('projects')->with('k3_global_error', 'not_admin');

        $project = ProjectController::getProject($pid);
        $project->active = $request->archive;
        $project->save();

        if($request->archive)
            $message = 'project_archived';
        else
            $message = 'project_restored';

        return redirect()->action('ProjectController@index')->with('k3_global_success', $message);
    }

    public function validateProjectFields(ProjectRequest $request) {
        return response()->json(["status"=>true, "message"=>"Project Valid", 200]);
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

        $admins = User::where('admin','=',1)->get();
        $users = $users->merge($admins)->unique();

        return $users;
    }
}
