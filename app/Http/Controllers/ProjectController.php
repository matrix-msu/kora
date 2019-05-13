<?php namespace App\Http\Controllers;

use App\Commands\ProjectEmails;
use App\User;
use App\Project;
use App\ProjectGroup;
use App\Http\Requests\ProjectRequest;
use Illuminate\Http\JsonResponse;
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
    }

    /**
     * Gets the view for the main projects page.
     *
     * @return View
     */
	public function index(Request $request) {
        $projectCollections = Project::all()->sortBy("name", SORT_NATURAL|SORT_FLAG_CASE);
        $user = \Auth::user();

        //Different arrays for display
        $projects = array();
        $inactive = array();
        $custom = array();
        $pSearch = array();
        $requestableProjects = array();

        $hasProjects = false;

        $customseq = $user->getCustomProjectSequence();

        foreach($projectCollections as $project) {
            if($user->admin || $user->inAProjectGroup($project)) {
                if($project->active) {
                    array_push($projects, $project);

                    //First we see if we even have a custom project order, if not, build array
                    if(is_null($customseq))
                        $customseq = array();

                    //Whether we do or just built it, check to see if project is there
                    if(!in_array($project->id,$customseq)) {
                        //Project missing from custom so add it
                        $user->addCustomProject($project->id);
                        //Then manually build the sequence instead of checking the DB again
                        array_push($customseq,$project->id);
                    }

                    $custom[array_search($project->id,$customseq)] = $project;
                } else {
                    array_push($inactive, $project);
                }

                array_push($pSearch, $project);

                $hasProjects = true;
            } else if($project->active) {
                $requestableProjects[$project->id] = $project->name. " (" . $project->internal_name.")";
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

        if($user->admin && $user->id == 1) {
            $current = new UpdateController();
            if($current->checkVersion())
                $notification['message'] = 'Update Available!';
        }

        $prevUrlArray = $request->session()->get('_previous');
        $prevUrl = reset($prevUrlArray);
        // we do not need to see notification every time we reload the page
        if($prevUrl !== url()->current()) {
          $session = $request->session()->get('k3_global_success');
          if($session) {
            if($session == 'project_deleted')
              $notification['message'] = 'Project Successfully Deleted';
            else if($session == 'project_archived')
              $notification['message'] = 'Project Successfully Archived!';
            else if($session == 'project_imported')
              $notification['message'] = 'Project Successfully Imported!';
		    else if($session == "password_reset")
				$notification['message'] = 'Password Successfully Reset!';
          } else {
            $session = $request->session()->get('k3_global_error');
            $notification['warning'] = true;
            $notification['static'] = true;
            if(strpos($session, 'cant') !== false || strpos($session, 'admin') !== false)
              $notification['message'] = 'Insufficient Permissions';
          }
        }

        return view('projects.index', compact('projects', 'inactive', 'custom', 'pSearch', 'hasProjects', 'requestableProjects', 'notification'));
	}
	
	/**
     * Gets modal to request project permissions
     *
     * @param  Request $request
     * @return View
     */
	public function getProjectPermissionsModal(Request $request) {
		$projectCollections = Project::all()->sortBy("name", SORT_NATURAL|SORT_FLAG_CASE);
		$requestableProjects = array();
		foreach($projectCollections as $project) {
			if($project->active and !(\Auth::user()->inAProjectGroup($project)))
				$requestableProjects[$project->id] = $project->name. " (" . $project->internal_name.")";
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
            //find the installation admin
            $installAdmin = User::where('id','=',1)->first();

            foreach($projects as $project) {
                $admins = $this->getProjectAdminNames($project);

                //remove install admin for bcc
                foreach($admins as $index => $admin_data) {
                    //Log::info($admin_data[0]);
                    if($admin_data->id == $installAdmin->id) {
                        // make sure the email target isn't getting BCC'ed as well
                        $admins->forget($index);
                        break;
                    }
                }

                $bccEmails = $admins->pluck('email')->toArray();

                $job = new ProjectEmails('RequestProjectPermissions', ['installAdmin' => $installAdmin, 'bccEmails' => $bccEmails, 'project' => $project]);
                $job->handle();
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

		$projectMode = "project_create";
        $currentUser = auth()->user();
		$users = User::all();

		$userNames = array();
		foreach ($users as $user) {
			if ($user->id != $currentUser->id) {

				$firstName = $user->preferences['first_name'];
				$lastName = $user->preferences['last_name'];
				$userName = $user->username;

				$pushThis = $firstName.' '.$lastName.' ('.$userName.')';
				array_push($userNames, $pushThis);
			}
		}
		natcasesort($userNames);

        return view('projects.create', compact('userNames','projectMode'));
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
        $project->adminGroup_id = $adminGroup->id;
        $project->active = 1;
        $project->internal_name = str_replace(" ","_", $project->name).'_'.$project->id.'_';
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
        $user = \Auth::user();

        $forms = array();
        $custom = array();
        $customseq = $user->getCustomFormSequence($id);
        foreach($formCollections as $form) {
            array_push($forms,$form);

            //First we see if we even have a custom form order, if not, build array
            if(is_null($customseq))
                $customseq = array();

            //Whether we do or just built it, check to see if form is there
            if(!in_array($form->id,$customseq)) {
                //Form missing from custom so add it
                $user->addCustomForm($form->id);
                //Then manually build the sequence instead of checking the DB again
                array_push($customseq,$form->id);
            }

            $custom[array_search($form->id,$customseq)] = $project;
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
        if($prevUrl !== url()->current()) {
          $session = $request->session()->get('k3_global_success');
          if($session) {
            if($session == 'project_updated')
              $notification['message'] = 'Project Sucessfully Updated!';
            else if($session == 'project_created')
              $notification['message'] = 'Project Successfully Created!';
            else if($session == 'form_deleted')
              $notification['message'] = 'Form Successfully Deleted!';
            else if($session == 'form_imported')
              $notification['message'] = 'Form Successfully Imported!';
          } else {
            $session = $request->session()->get('k3_global_error');
            $notification['warning'] = true;
            $notification['static'] = true;
            if(strpos($session, 'cant') !== false || strpos($session, 'admin') !== false)
              $notification['message'] = 'Insufficient Permissions';
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
        $project->internal_name = str_replace(" ","_", $project->name).'_'.$project->id.'_';
        $project->save();

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
        $project = Project::where('id',$id)->first();
        if(is_null($project))
            $project = Project::where('internal_name','=',$id)->first();

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

    /**
     * Validates a project request.
     *
     * @param  ProjectRequest $request
     * @return JsonResponse
     */
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
