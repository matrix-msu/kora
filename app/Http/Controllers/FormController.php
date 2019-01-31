<?php namespace App\Http\Controllers;

use App\Form;
use App\User;
use App\FormGroup;
use Illuminate\Http\JsonResponse;
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
        if(!ProjectController::validProj($pid))
            return redirect('projects')->with('k3_global_error', 'project_invalid');

        if(!self::checkPermissions($pid, 'create'))
            return redirect('projects/'.$pid.'/forms')->with('k3_global_error', 'cant_create_form');

        $project = ProjectController::getProject($pid);
		$users = User::all();
        $currProjectAdmins = $project->adminGroup()->first()->users()->get();
        $admins = User::where("admin","=",1)->get();

		$userNames = array();
		foreach ($users as $user) {
			if (!$currProjectAdmins->contains($user) && !$admins->contains($user)) {
				$firstName = $user->first_name;
				$lastName = $user->last_name;
				$userName = $user->username;

				$pushThis = $firstName.' '.$lastName.' ('.$userName.')';
				array_push($userNames, $pushThis);
			}
		}
		natcasesort($userNames);

        $presets = array();
        foreach(Form::where('preset', '=', 1, 'and', 'pid', '=', $pid)->get() as $form)
            $presets[$form->fid] = $form->project->name.' - '.$form->name;

        return view('forms.create', compact('project', 'userNames', 'presets')); //pass in
	}

    /**
     * Saves a new Form model.
     *
     * @param  FormRequest $request
     * @return Redirect
     */
	public function store(FormRequest $request) {
	    $form = Form::create($request->all());

	    //TODO::CASTLE
        //if($request->preset[0]=="") //Since the preset is copying the target form, no need to make a default page
            PageController::makePageOnForm($form->id,$form->name." Default Page");

        $adminGroup = FormGroup::makeAdminGroup($form, $request);
        FormGroup::makeDefaultGroup($form);
        $form->adminGroup_id = $adminGroup->id;
        $form->internal_name = str_replace(" ","_", $form->name).'_'.$form->project_id.'_'.$form->id.'_';
        $form->save();

        //Make the form's records table
        $rTable = new \CreateRecordsTable();
        $rTable->createFormRecordsTable($form->id);

        //TODO::CASTLE
        //if($request->preset[0]!="")
            //self::addPresets($form, $request->preset[0]);

        return redirect('projects/'.$form->project_id.'/forms/'.$form->id)->with('k3_global_success', 'form_created');
	}

    /**
     * Gets the display view for a form.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @return View
     */
	public function show($pid, $fid, Request $request) {
        if(!self::validProjForm($pid, $fid))
            return redirect('projects/'.$pid)->with('k3_global_error', 'form_invalid');

        if(!FieldController::checkPermissions($fid))
            return redirect('/projects/'.$pid)->with('k3_global_error', 'cant_view_form');

        $form = self::getForm($fid);
        $proj = ProjectController::getProject($pid);
        $projName = $proj->name;

        //Build the layout from the DB
        $layout = $form->layout;
        $hasFields = $form->hasFields();

        $notification = array(
          'message' => '',
          'description' => '',
          'warning' => false,
          'static' => false
        );
        $prevUrlArray = $request->session()->get('_previous');
        $prevUrl = reset($prevUrlArray);
        if($prevUrl !== url()->current()) {
          $session = $request->session()->get('k3_global_success');

          if($session == 'form_created')
            $notification['message'] = 'Form Sucessfully Created!';
          else if($session == 'field_created')
            $notification['message'] = 'Field Successfully Created!';
          else if($session == 'field_options_updated')
            $notification['message'] = 'Field Successfully Created!';
          else if($session == 'field_updated')
            $notification['message'] = 'Field Successfully Updated!';
          else if($session == 'record_deleted')
            $notification['message'] = 'Record Successfully Deleted';
          else if($session == 'all_record_deleted')
            $notification['message'] = 'All Form Records Deleted';
          else if($session == 'form_updated')
            $notification['message'] = 'Form Successfully Updated!';
          else if($session == 'old_records_deleted')
            $notification['message'] = 'Old Record Files Deleted';
          else if($session == 'form_imported')
            $notification['message'] = 'Form Successfully Imported!';
        }

        return view('forms.show', compact('form','projName','layout','hasFields','notification'));
	}

    /**
     * Gets the edit view for a form.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @return View
     */
	public function edit($pid, $fid) {
        if(!self::validProjForm($pid, $fid))
            return redirect('projects/'.$pid)->with('k3_global_error', 'form_invalid');

        if(!self::checkPermissions($pid, 'edit'))
            return redirect('projects/'.$pid.'/forms/')->with('k3_global_error', 'cant_edit_form');

        $form = self::getForm($fid);
        $proj = ProjectController::getProject($pid);
        $projName = $proj->name;
        $filesize = RecordController::getFormFilesize($pid, $fid);

        return view('forms.edit', compact('form','projName', 'filesize'));
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
	    if(!self::validProjForm($pid, $fid))
            return redirect('projects/'.$pid)->with('k3_global_error', 'form_invalid');

        if(!self::checkPermissions($pid, 'edit'))
            return redirect('projects/'.$pid.'/forms/')->with('k3_global_error', 'cant_edit_form');

        $form = self::getForm($fid);

        $form->update($request->all());

        if(isset($request->preset)) {
            $form->preset = $request->preset;
        } else {
            $form->preset = 0;
        }

        $form->internal_name = str_replace(" ","_", $form->name).'_'.$form->project_id.'_'.$form->id.'_';
        $form->save();

        FormGroupController::updateMainGroupNames($form);

        flash()->overlay("Your form has been successfully updated!","Good Job!");

        return redirect('projects/'.$form->pid.'/forms/'.$form->fid)->with('k3_global_success', 'form_updated');
	}

    /**
     * Deletes a form model.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     */
	public function destroy($pid, $fid) {
        if(!self::validProjForm($pid,$fid))
            return redirect()->action('ProjectController@show', ['pid' => $pid])->with('k3_global_error', 'form_invalid');

        if(!self::checkPermissions($pid, 'delete'))
            return redirect()->action('ProjectController@show', ['pid' => $pid])->with('k3_global_error', 'cant_delete_form');

        $form = self::getForm($fid);
        $form->delete();

        return redirect('projects/'.$pid)->with('k3_global_success', 'form_deleted');
	}

    /**
     * Validates a new Form object.
     *
     * @param  FormRequest $request
     * @return JsonResponse
     */
    public function validateFormFields(FormRequest $request) {
        return response()->json(["status"=>true, "message"=>"Form Valid", 200]);
    }

    /**
     * Set the form to be used as a preset.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  Request $request
     */
    public function preset($pid, $fid, Request $request) {
        if(!self::validProjForm($pid, $fid))
            return response()->json(["status"=>false,"message"=>"form_invalid"],500);

        $form = self::getForm($fid);
        if($request->preset)
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
        if(!ProjectController::validProj($pid))
            return redirect('projects')->with('k3_global_error', 'project_invalid');

        if(!self::checkPermissions($pid, 'create'))
            return redirect('projects/'.$pid.'/forms')->with('k3_global_error', 'cant_create_form');

        return view('forms.import',compact('pid'));
    }

    /**
     * Gets the view for the import form page for K2 schemes.
     *
     * @param  int $pid - Project ID
     * @return View
     */
    public function importFormViewK2($pid) {
        if(!ProjectController::validProj($pid))
            return redirect('projects')->with('k3_global_error', 'project_invalid');

        if(!self::checkPermissions($pid, 'create'))
            return redirect('projects/'.$pid.'/forms')->with('k3_global_error', 'cant_create_form');

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
        $form = Form::where('id','=',$fid)->first();
        if(is_null($form))
            $form = Form::where('internal_name','=',$fid)->first();

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
        else if($proj->id==$form->project_id)
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
                if(!(\Auth::user()->canCreateForms(ProjectController::getProject($pid))))
                    return false;
                break;
            case 'edit':
                if(!(\Auth::user()->canEditForms(ProjectController::getProject($pid))))
                    return false;
                break;
            case 'delete':
                if(!(\Auth::user()->canDeleteForms(ProjectController::getProject($pid))))
                    return false;
                break;
            default: //"Read Only"
                if(!(\Auth::user()->inAProjectGroup(ProjectController::getProject($pid))))
                    return false;
                break;
        }

        return true;
    }

    /**
     * Copys a form's information from another preset form. //TODO::CASTLE
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
            $new->advsearch = $field->advsearch;
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
