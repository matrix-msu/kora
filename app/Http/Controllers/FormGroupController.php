<?php namespace App\Http\Controllers;

use App\Form;
use App\User;
use App\FormGroup;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class FormGroupController extends Controller {

    /**
     * User must be logged in to access views in this controller.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('active');
    }

    /**
     * @pararm $pid
     * @param $fid
     * @return Response
     */
    public function index($pid, $fid)
    {
        if(!FormController::validProjForm($pid,$fid)){
            return redirect('projects');
        }

        $form = FormController::getForm($fid);
        $project = $form->project()->first();

        if(!(\Auth::user()->isFormAdmin($form))) {
            flash()->overlay('You are not an admin for that form.', 'Whoops.');
            return redirect('projects'.$project->pid);
        }

        $formGroups = $form->groups()->get();
        $users = User::lists('username', 'id');
        $all_users = User::all();
        return view('formGroups.index', compact('form', 'formGroups', 'users', 'all_users', 'project'));
    }

    /**
     * Creates a form group.
     *
     * @param Request $request
     * @return Response
     */
    public function create(Request $request)
    {
        $fid = $request['form'];
        $form = FormController::getForm($fid);
        $project = $form->project()->first();
        $pid = $project->pid;

        if($request['name'] == ""){
            flash()->overlay('You must enter a group name.', 'Whoops.');
            return redirect(action('FormGroupController@index', ['fid'=>$form->fid]));
        }

        $group = FormGroupController::buildGroup($pid, $form->fid, $request);

        if(!is_null($request['users']))
            $group->users()->attach($request['users']);

        flash()->overlay('Group created!', 'Success');
        return redirect(action('FormGroupController@index', ['fid'=>$form->fid]));
    }

    /**
     * Remove user from form group.
     *
     * @param Request $request
     */
    public function removeUser(Request $request)
    {
        $instance = FormGroup::where('id', '=', $request['formGroup'])->first();
        $instance->users()->detach($request['userId']);
    }

    /**
     * Add user to form group.
     *
     * @param Request $request
     */
    public function addUser(Request $request)
    {
        $instance = FormGroup::where('id', '=', $request['formGroup'])->first();
        $instance->users()->attach($request['userId']);
    }

    /**
     * Delete user from form group.
     *
     * @param Request $request
     */
    public function deleteFormGroup(Request $request)
    {
        $instance = FormGroup::where('id', '=', $request['formGroup'])->first();
        $instance->delete();
    }

    /**
     * Update form group's permissions.
     *
     * @param Request $request
     */
    public function updatePermissions(Request $request)
    {
        $instance = FormGroup::where('id', '=', $request['formGroup'])->first();

        $permissions = [['permCreate', 'create'],
            ['permEdit', 'edit'],
            ['permDelete', 'delete'],
            ['permIngest', 'ingest'],
            ['permModify', 'modify'],
            ['permDestroy', 'destroy']
        ];

        foreach($permissions as $permission){
            if($request[$permission[0]])
                $instance[$permission[1]] = 1;
            else
                $instance[$permission[1]] = 0;
        }
        $instance->save();
    }


    /**
     * Build a form group.
     *
     * @param $fid
     * @param Request $request
     * @return FormGroup
     */
    private function buildGroup($pid, $fid, Request $request)
    {
        $group = new FormGroup();
        $group->name = $request['name'];
        $group->fid = $fid;

        $permissions = ['create','edit','delete','ingest','modify','destroy'];

        foreach($permissions as $permission) {
            if (!is_null($request[$permission]))
                $group[$permission] = 1;
            else
                $group[$permission] = 0;
        }
        $group->save();
        return $group;
    }

}
