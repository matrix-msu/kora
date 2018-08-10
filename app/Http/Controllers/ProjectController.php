<?php namespace App\Http\Controllers;

use App\User;
use App\Project;
use App\ProjectGroup;
use App\Http\Requests\ProjectRequest;
use Illuminate\Http\JsonResponse;
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
     * TODO::later sort initial pull by recent
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

        //TODO::Update stuff
        /*$c = new UpdateController();
        $updateNotification = false;
        if($c->checkVersion() && !session('notified_of_update')) {
            session(['notified_of_update' => true]);
            $updateNotification = true;
        }*/

        $notification = '';
        $warning = false;
        $prevUrlArray = $request->session()->get('_previous');
        $prevUrl = reset($prevUrlArray);
        // we do not need to see notification every time we reload the page
        if ($prevUrl !== url()->current()) {
          $session = $request->session()->get('k3_global_success');
          if ($session) {
            if ($session == 'project_deleted') $notification = 'Project Successfully Deleted';
            else if ($session == 'project_archived') $notification = 'Project Successfully Archived!';
            else if ($session == 'project_imported') $notification = 'Project Successfully Imported!';
          } else {
            $session = $request->session()->get('k3_global_error');
            $warning = true;
            if (strpos($session, 'cant') !== false || strpos($session, 'admin') !== false) {
              $notification = 'Insufficient Permissions';
            }
          }
        }

        return view('projects.index', compact('projects', 'inactive', 'custom', 'pSearch', 'hasProjects', 'requestableProjects', 'notification', 'warning'));
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
            return redirect('projects')->with('k3_global_error', 'no_project_requested');
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
                        //TODO::email error response
                        //Log for now
                        Log::info('Project request email failed');
                    }
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

        $notification = '';
        $warning = false;
        $prevUrlArray = $request->session()->get('_previous');
        $prevUrl = reset($prevUrlArray);
        // we do not need to see notification every time we reload the page
        if ($prevUrl !== url()->current()) {
          $session = $request->session()->get('k3_global_success');
          if ($session) {
            if ($session == 'project_updated') $notification = 'Project Sucessfully Updated!';
            else if ($session == 'project_created') $notification = 'Project Successfully Created!';
            else if ($session == 'form_deleted') $notification = 'Form Successfully Deleted!';
            else if ($session == 'form_imported') $notification = 'Form Successfully Imported!';
          } else {
            $session = $request->session()->get('k3_global_error');
            $warning = true;
            if (strpos($session, 'cant') !== false || strpos($session, 'admin') !== false) {
              $notification = 'Insufficient Permissions';
            }
          }
        }

        return view('projects.show', compact('project','forms', 'custom', 'notification', 'warning'));
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
        if(!\Auth::user()->admin)
            return redirect('projects')->with('k3_global_error', 'not_admin');

        if(!self::validProj($id))
            return redirect()->action('ProjectController@index')->with('k3_global_error', 'project_invalid');

        $project = self::getProject($id);

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
