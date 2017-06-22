<?php namespace App\Http\Controllers;

use App\Form;
use App\ProjectGroup;
use App\User;
use App\FormGroup;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;


/**
 * Form groups control permissions over fields and records within a project.
 *
 * Class FormGroupController
 * @package App\Http\Controllers
 */
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
            return redirect('projects/'.$pid);
        }

        $form = FormController::getForm($fid);
        $project = $form->project()->first();

        if(!(\Auth::user()->isFormAdmin($form))) {
            flash()->overlay(trans('controller_formgroup.admin'), trans('controller_formgroup.whoops'));
            return redirect('projects'.$project->pid);
        }

        $formGroups = $form->groups()->get();
        $users = User::lists('username', 'id')->all();
        $all_users = User::all();
        return view('formGroups.index', compact('form', 'formGroups', 'users', 'all_users', 'project'));
    }

    /**
     * Creates a form group.
     *
     * @param Request $request
     * @return Response
     */
    public function create($pid, $fid, Request $request)
    {
        if(!FormController::validProjForm($pid,$fid)){
            return redirect('projects/'.$pid);
        }

        $form = FormController::getForm($fid);

        if($request['name'] == ""){
            flash()->overlay(trans('controller_formgroup.name'), trans('controller_formgroup.whoops'));
            return redirect(action('FormGroupController@index', ['fid'=>$form->fid]));
        }

        $group = self::buildGroup($pid, $form->fid, $request);

        if(!is_null($request['users'])) {
            foreach ($request['users'] as $uid) {
                //remove them from an old group if they have one
                //get any groups the user belongs to
                $currGroups = DB::table('form_group_user')->where('user_id', $uid)->get();
                $grp = null;
                $idOld = 0;
                $newUser = true;

                //foreach of the user's project groups, see if one belongs to the current project
                foreach ($currGroups as $prev) {
                    $grp = FormGroup::where('id', '=', $prev->form_group_id)->first();
                    if ($grp->fid == $group->fid) {
                        $idOld = $grp->id;
                        $newUser = false;
                        break;
                    }
                }

                if($newUser){
                    //add them to the project if they don't exist
                    $inProj = false;
                    $form = FormController::getForm($group->fid);
                    $proj = ProjectController::getProject($form->pid);
                    //get all project groups for this project
                    $pGroups = ProjectGroup::where('pid','=', $form->pid)->get();

                    foreach($pGroups as $pg){
                        //see if user belongs to project group
                        $uidPG = DB::table('project_group_user')->where('user_id', $uid)->where('project_group_id', $pg->id)->get();

                        if(!empty($uidPG)){
                            $inProj = true;
                        }
                    }

                    //not in project, lets add them
                    if(!$inProj){
                        $default = ProjectGroup::where('name','=',$proj->name.' Default Group')->first();
                        DB::table('project_group_user')->insert([
                            ['project_group_id' => $default->id, 'user_id' => $uid]
                        ]);
                    }
                }

                DB::table('form_group_user')->where('user_id', $uid)->where('form_group_id', $idOld)->delete();
            }

            $group->users()->attach($request['users']);
        }

        flash()->overlay(trans('controller_formgroup.created'), trans('controller_formgroup.success'));
        return redirect(action('FormGroupController@index', ['pid'=>$form->pid, 'fid'=>$form->fid]));
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

        //get any groups the user belongs to
        $currGroups = DB::table('form_group_user')->where('user_id', $request['userId'])->get();
        $newUser = true;
        $group = null;
        $idOld = 0;

        //foreach of the user's form groups, see if one belongs to the current project
        foreach($currGroups as $prev){
            $group = FormGroup::where('id', '=', $prev->form_group_id)->first();
            if($group->fid==$instance->fid){
                $newUser = false;
                $idOld = $group->id;
                break;
            }
        }

        if(!$newUser) {
            //remove from old group
            DB::table('form_group_user')->where('user_id', $request['userId'])->where('form_group_id', $idOld)->delete();

            echo $idOld;
        }else{
            //add them to the project if they don't exist
            $inProj = false;
            $form = FormController::getForm($instance->fid);
            $proj = ProjectController::getProject($form->pid);
            //get all project groups for this project
            $pGroups = ProjectGroup::where('pid','=', $form->pid)->get();

            foreach($pGroups as $pg){
                //see if user belongs to project group
                $uidPG = DB::table('project_group_user')->where('user_id', $request['userId'])->where('project_group_id', $pg->id)->get();

                if(!empty($uidPG)){
                    $inProj = true;
                }
            }

            //not in project, lets add them
            if(!$inProj){
                $default = ProjectGroup::where('name','=',$proj->name.' Default Group')->first();
                DB::table('project_group_user')->insert([
                    ['project_group_id' => $default->id, 'user_id' => $request['userId']]
                ]);
            }
        }

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
     * Note that permissions create, edit, and delete refer to the creation, editing, and deletion of fields, respectfully.
     * And that permissions ingest, modify, and destroy refer to the creation, editing, and deletion of records, respectfully.
     *
     * @param Request $request
     */
    public function updatePermissions(Request $request)
    {
        $formGroup = FormGroup::where('id', '=', $request['formGroup'])->first();

        //Because of some name convention problems in JavaScript we use a simple associative array to
        //relate the permissions passed by the request to the form group
        $permissions = [['permCreate', 'create'],
            ['permEdit', 'edit'],
            ['permDelete', 'delete'],
            ['permIngest', 'ingest'],
            ['permModify', 'modify'],
            ['permDestroy', 'destroy']
        ];

        foreach($permissions as $permission){
            if($request[$permission[0]])
                $formGroup[$permission[1]] = 1;
            else
                $formGroup[$permission[1]] = 0;
        }
        $formGroup->save();
    }

    public function updateName(Request $request)
    {
        $instance = FormGroup::where('id', '=', $request->gid)->first();
        $instance->name = $request->name;

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

    public static function updateMainGroupNames($form){
        $admin = FormGroup::where('fid', '=', $form->fid)->where('name', 'like', '% Admin Group')->get()->first();
        $default = FormGroup::where('fid', '=', $form->fid)->where('name', 'like', '% Default Group')->get()->first();

        $admin->name = $form->name.' Admin Group';
        $admin->save();

        $default->name = $form->name.' Default Group';
        $default->save();
    }

}
