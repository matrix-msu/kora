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

    public function index($fid)
    {
        $form = FormController::getForm($fid);
        $project = $form->project()->first();
        $formGroups = $form->groups()->get();
        $users = User::lists('username', 'id');
        $all_users = User::all();
        return view('formGroups.index', compact('form', 'formGroups', 'users', 'all_users', 'project'));
    }

    public function create(Request $request)
    {
        $form = FormController::getForm($request['form']);
        $project = $form->project()->first();
        $pid = $project->pid;

        if($request['name'] == ""){
            flash()->overlay('You must enter a group name.', 'Whoops.');
            return redirect('projects/'.$pid.'/manage/formgroups');
        }

        $group = FormGroupController::buildGroup($form->fid, $request);

        if(!is_null($request['users']))
            $group->users()->attach($request['users']);

        flash()->overlay('Group created!', 'Success');
        return redirect('projects/'.$pid.'/manage/formgroups');
    }

    public function removeUser(Request $request)
    {

    }

    public function addUser(Request $request)
    {

    }

    public function updatePermissions(Request $request)
    {

    }

    public function deleteProjectGroup(Request $request)
    {

    }

    private function buildGroup($fid, Request $request)
    {
        $group = new FormGroup();
        $group->name = $request['name'];
        $group->fid = $fid;
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

}
