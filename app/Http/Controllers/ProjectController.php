<?php namespace App\Http\Controllers;

use App\Commands\ProjectEmails;
use App\Token;
use App\User;
use App\Project;
use App\ProjectGroup;
use App\Http\Requests\ProjectRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProjectController extends Controller {

    /*
    |--------------------------------------------------------------------------
    | Project Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles projects within kora
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
        // we do not need to see notification every time we reload the page
        if(!is_null($prevUrlArray) && reset($prevUrlArray) !== url()->current()) {
          $session = $request->session()->get('k3_global_success');
          if($session) {
            if($session == 'project_deleted')
              $notification['message'] = 'Project Successfully Deleted';
            else if($session == 'project_archived')
              $notification['message'] = 'Project Successfully Archived!';
            else if($session == 'project_restored')
              $notification['message'] = 'Project Successfully Restored!';
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
		foreach($users as $user) {
			if($user->id != $currentUser->id) {
				$firstName = $user->preferences['first_name'];
				$lastName = $user->preferences['last_name'];
				$userName = $user->username;

				$pushThis = $firstName.' '.$lastName.' ('.$userName.')';
                $userNames[$user->id] = $pushThis;
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

        //Make the projects initial search token
        $token = new Token();
        $token->token = uniqid();
        $token->title = $project->name. " Search Token";
        $token->search = true;
        $token->save();

        $token->projects()->attach([$project->id]);

        return redirect('projects/'.$project->id)->with('k3_global_success', 'project_created');
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

        $projectTokens = $project->tokens()->orderBy('created_at','asc')->get();

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

            $custom[array_search($form->id,$customseq)] = $form;
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
        // we do not need to see notification every time we reload the page
        if(!is_null($prevUrlArray) && reset($prevUrlArray) !== url()->current()) {
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

        return view('projects.show', compact('project','forms', 'custom', 'notification', 'projectTokens'));
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
        return Project::where('id',$id)->orWhere('internal_name','=',$id)->first();
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
            $message = 'project_restored';
        else
            $message = 'project_archived';

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
