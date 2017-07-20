<?php namespace App\Http\Controllers;

use App\Form;
use App\Page;
use App\User;
use App\Field;
use App\FormGroup;
use Illuminate\Http\Request;
use App\Http\Requests\FormRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;


class FormController extends Controller {

    /*
    |--------------------------------------------------------------------------
    | Form Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles creation and manipulation of Form models
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
     * Gets and returns create form view.
     *
     * @param  int $pid - Project ID
     * @return View
     */
	public function create($pid) {
        if(!ProjectController::validProj($pid)) {
            return redirect('projects');
        }

        if(!self::checkPermissions($pid, 'create')) {
            return redirect('projects/'.$pid.'/forms');
        }

        $project = ProjectController::getProject($pid);
        $users = User::lists('username', 'id')->all();

        $presets = array();
        foreach(Form::where('preset', '=', 1, 'and', 'pid', '=', $pid)->get() as $form)
            $presets[] = ['fid' => $form->fid, 'name' => $form->name];

        return view('forms.create', compact('project', 'users', 'presets')); //pass in
	}

    /**
     * Saves a new Form model.
     *
     * @param  FormRequest $request
     * @return Redirect
     */
	public function store(FormRequest $request) {
        $form = Form::create($request->all());

        $form->save();

        if(!isset($request['preset'])) //Since the preset is copying the target form, no need to make a default page
            PageController::makePageOnForm($form->fid,$form->slug." Default Page");

        $adminGroup = self::makeAdminGroup($form, $request);
        self::makeDefaultGroup($form);
        $form->adminGID = $adminGroup->id;
        $form->save();

        if(isset($request['preset']))
            self::addPresets($form, $request['preset']);

        flash()->overlay(trans('controller_form.create'),trans('controller_form.goodjob'));

        return redirect('projects/'.$form->pid);
	}

    /**
     * Gets the display view for a form.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @return View
     */
	public function show($pid, $fid) {
        if(!self::validProjForm($pid,$fid)) {
            return redirect('projects/'.$pid);
        }

        if(!self::checkPermissions($pid)) {
            return redirect('/projects');
        }

        $form = self::getForm($fid);
        $proj = ProjectController::getProject($pid);
        $projName = $proj->name;

        $pageLayout = PageController::getFormLayout($fid);

        return view('forms.show', compact('form','projName','pageLayout'));
	}

    /**
     * Gets the edit view for a form.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @return View
     */
	public function edit($pid, $fid) {
        if(!self::validProjForm($pid,$fid)) {
            return redirect('projects/'.$pid);
        }

        if(!self::checkPermissions($pid, 'edit')) {
            return redirect('/projects/'.$pid.'/forms');
        }

        $form = self::getForm($fid);
        $proj = ProjectController::getProject($pid);
        $projName = $proj->name;

        return view('forms.edit', compact('form','projName'));
	}

    /**
     * Saves any edits made to a form.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  FormRequest $request
     * @return Redirect
     */
	public function update($pid, $fid, FormRequest $request) {
        if(!self::validProjForm($pid,$fid)) {
            return redirect('projects/'.$pid);
        }

        $form = self::getForm($fid);

        if(!self::checkPermissions($pid, 'edit')) {
            return redirect('/projects/'.$form->$pid.'/forms');
        }

        $form->update($request->all());

        FormGroupController::updateMainGroupNames($form);

        flash()->overlay(trans('controller_form.update'),trans('controller_form.goodjob'));

        return redirect('projects/'.$form->pid);
	}

    /**
     * Deletes a form model.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     */
	public function destroy($pid, $fid) {
        if(!self::validProjForm($pid,$fid)) {
            return redirect()->action('ProjectController@show', ['pid' => $pid]);
        }

        if(!self::checkPermissions($pid, 'delete')) {
            return redirect()->action('ProjectController@show', ['pid' => $pid]);
        }

        $form = self::getForm($fid);
        $form->delete();

        flash()->overlay(trans('controller_form.delete'),trans('controller_form.goodjob'));
	}

    /**
     * Set the form to be used as a preset.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  Request $request
     */
    public function preset($pid, $fid, Request $request) {
        if(!self::validProjForm($pid,$fid)) {
            return redirect('projects/'.$pid);
        }

        $form = self::getForm($fid);
        if($request['preset'])
            $form->preset = 1;
        else
            $form->preset = 0;
        $form->save();
    }

    /**
     * Gets the view for the import form page.
     *
     * @param  int $pid - Project ID
     * @return View
     */
    public function importFormView($pid) {
        if(!ProjectController::validProj($pid)) {
            return redirect('projects');
        }

        if(!self::checkPermissions($pid, 'ingest')) {
            return redirect('projects/'.$pid);
        }

        $proj = ProjectController::getProject($pid);

        return view('forms.import',compact('proj','pid'));
    }

    /**
     * Gets the view for the import form page for K2 schemes.
     *
     * @param  int $pid - Project ID
     * @return View
     */
    public function importFormViewK2($pid) {
        if(!ProjectController::validProj($pid)) {
            return redirect('projects');
        }

        if(!self::checkPermissions($pid, 'ingest')) {
            return redirect('projects/'.$pid);
        }

        $proj = ProjectController::getProject($pid);

        return view('forms.importk2',compact('proj','pid'));
    }

    /**
     * Gets a form by fid or slug.
     *
     * @param  mixed $fid - Form ID or slug
     * @return Form - The requested form
     */
    public static function getForm($fid) {
        $form = Form::where('fid','=',$fid)->first();
        if(is_null($form))
            $form = Form::where('slug','=',$fid)->first();

        return $form;
    }

    /**
     * Validates a project/form ID pair.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @return bool - Validity of the pair
     */
    public static function validProjForm($pid, $fid) {
        $form = self::getForm($fid);
        $proj = ProjectController::getProject($pid);

        if(is_null($form) || is_null($proj))
            return false;
        else if($proj->pid==$form->pid)
            return true;
        else
            return false;
    }

    /**
     * Checks user's permission to create and modify forms in a project.
     *
     * @param  int $pid - Project ID
     * @param  string $permission - Permission to check
     * @return bool - Whether user has permission
     */
    public static function checkPermissions($pid, $permission='') {
        switch($permission) {
            case 'create':
                if(!(\Auth::user()->canCreateForms(ProjectController::getProject($pid)))) {
                    flash()->overlay(trans('controller_form.createper'), trans('controller_form.whoops'));
                    return false;
                }
                return true;
            case 'edit':
                if(!(\Auth::user()->canEditForms(ProjectController::getProject($pid)))) {
                    flash()->overlay(trans('controller_form.editper'), trans('controller_form.whoops'));
                    return false;
                }
                return true;
            case 'delete':
                if(!(\Auth::user()->canDeleteForms(ProjectController::getProject($pid)))) {
                    flash()->overlay(trans('controller_form.deleteper'), trans('controller_form.whoops'));
                    return false;
                }
                return true;
            default: //"Read Only"
                if(!(\Auth::user()->inAProjectGroup(ProjectController::getProject($pid)))) {
                    flash()->overlay(trans('controller_form.viewper'), trans('controller_form.whoops'));
                    return false;
                }
                return true;
        }
    }

    /**
     * Creates the form's admin group.
     *
     * @param  Form $form - Form to create group for
     * @param  Request $request
     * @return FormGroup - The newly created group
     */
    //TODO::modular
    private function makeAdminGroup(Form $form, Request $request) {
        $groupName = $form->name;
        $groupName .= ' Admin Group';

        $adminGroup = new FormGroup();
        $adminGroup->name = $groupName;
        $adminGroup->fid = $form->fid;
        $adminGroup->save();

        $formProject = $form->project()->first();
        $projectAdminGroup = $formProject->adminGroup()->first();

        $projectAdmins = $projectAdminGroup->users()->get();
        $idArray = [];

        //Add all current project admins to the form's admin group.
        foreach($projectAdmins as $projectAdmin)
            $idArray[] .= $projectAdmin->id;

        if (!is_null($request['admins']))
            $idArray = array_unique(array_merge($request['admins'], $idArray));

        if (!empty($idArray))
            $adminGroup->users()->attach($idArray);

        $adminGroup->create = 1;
        $adminGroup->edit = 1;
        $adminGroup->delete = 1;
        $adminGroup->ingest = 1;
        $adminGroup->modify = 1;
        $adminGroup->destroy = 1;

        $adminGroup->save();

        return $adminGroup;
    }

    /**
     * Creates the form's default group.
     *
     * @param  Form $form - Form to create group for
     */
    //TODO::modular
    private function makeDefaultGroup(Form $form) {
        $groupName = $form->name;
        $groupName .= ' Default Group';

        $defaultGroup = new FormGroup();
        $defaultGroup->name = $groupName;
        $defaultGroup->fid = $form->fid;
        $defaultGroup->save();

        $defaultGroup->create = 0;
        $defaultGroup->edit = 0;
        $defaultGroup->delete = 0;
        $defaultGroup->ingest = 0;
        $defaultGroup->modify = 0;
        $defaultGroup->destroy = 0;

        $defaultGroup->save();
    }

    /**
     * Copys a form's information from another preset form.
     *
     * @param  Form $form - Form being created
     * @param  int $fid - Form ID of preset form
     */
    private function addPresets(Form $form, $fid) {
        $preset = Form::where('fid', '=', $fid)->first();

        $field_assoc = array();
        $pageConvert = array();

        //Duplicate pages
        foreach($preset->pages()->get() as $page) {
            $newP = new Page();
            $newP->parent_type = $page->parent_type;
            $newP->fid = $form->fid;
            $newP->title = $page->title;
            $newP->sequence = $page->sequence;
            $newP->save();

            $pageConvert[$page->id] = $newP->id;
        }

        //Duplicate fields
        foreach($preset->fields()->get() as $field)  {
            $new = new Field();
            $new->pid = $form->pid;
            $new->fid = $form->fid;
            $new->page_id = $pageConvert[$field->page_id];
            $new->sequence = $field->sequence;
            $new->type = $field->type;
            $new->name = $field->name;
            $new->slug = $field->slug.'_'.$form->slug;
            $new->desc = $field->desc;
            $new->required = $field->required;
            $new->searchable = $field->searchable;
            $new->extsearch = $field->extsearch;
            $new->viewable = $field->viewable;
            $new->viewresults = $field->viewresults;
            $new->extview = $field->extview;
            $new->default = $field->default;
            $new->options = $field->options;
            $new->save();

            $field_assoc[$field->flid] = $new->flid;
        }

        $form->save();
    }
}
