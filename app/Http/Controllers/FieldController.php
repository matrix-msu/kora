<?php namespace App\Http\Controllers;

use App\Field;
use App\Http\Requests\FieldRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class FieldController extends Controller {

    /*
    |--------------------------------------------------------------------------
    | Field Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the creation and management of fields in Kora3
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
     * Gets the field creation view.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  Request $request
     * @return View
     */
	public function create($pid, $fid, Request $request) {
        if(!FormController::validProjForm($pid, $fid)) {
            return redirect('projects/'.$pid);
        }

        if(!self::checkPermissions($fid, 'create')) {
            return redirect('projects/'.$pid.'/forms/'.$fid.'/fields');
        }

		$form = FormController::getForm($fid);
        $rootPage = $request->rootPage;
        return view('fields.create', compact('form','rootPage'));
	}

    /**
     * Saves a new field model and redirects to form page.
     *
     * @param  FieldRequest $request
     * @return Redirect
     */
	public function store(FieldRequest $request) {
        $seq = PageController::getNewPageFieldSequence($request->page_id); //we do this before anything so the new field isnt counted in it's logic
        $field = Field::Create($request->all());

        //special error check for combo list field
        if($field->type=='Combo List' && ($_REQUEST['cfname1']=='' | $_REQUEST['cfname2']=='')) {
            flash()->error(trans('controller_field.comboname'));

            return redirect()->back()->withInput();
        }

        $field->options = Field::getOptions($field->type, $request);
        $field->default = '';

        $field->sequence = $seq;

        $field->save();

        //if advanced options was selected we should call the correct one
        $advError = false;
        if($request->advance) {
            $optC = new OptionController();
            $result = $optC->updateAdvanced($field,$request);
            if($result != '') {
                $advError = true;
                flash()->error('There was an error with the advanced options. '.$result.' Please visit the options page of the field.');
            }
        }

        //A field has been changed, so current record rollbacks become invalid.
        $form = FormController::getForm($field->fid);
        RevisionController::wipeRollbacks($form->fid);

        if(!$advError) //if we error on the adv page we should hide the success message so error can display
            flash()->overlay(trans('controller_field.fieldcreated'), trans('controller_field.goodjob'));

        return redirect('projects/'.$field->pid.'/forms/'.$field->fid);
	}

    /**
     * Gets and displays the field options page for a particular field.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  int $flid - Field ID
     * @return View
     */
	public function show($pid, $fid, $flid) {
        if(!self::validProjFormField($pid, $fid, $flid)) {
            return redirect('projects/'.$pid.'/forms/'.$fid);
        }

        if(!self::checkPermissions($fid, 'edit')) {
            return redirect('projects/'.$pid.'/forms/'.$fid.'/fields');
        }

        $field = self::getField($flid);
        $form = FormController::getForm($fid);
        $proj = ProjectController::getProject($pid);

        $presets = OptionPresetController::getPresetsSupported($pid,$field);

        if($field->type=="Text") {
            return view('fields.options.text', compact('field', 'form', 'proj','presets'));
        } else if($field->type=="Rich Text") {
            return view('fields.options.richtext', compact('field', 'form', 'proj'));
        } else if($field->type=="Number") {
            return view('fields.options.number', compact('field', 'form', 'proj'));
        } else if($field->type=="List") {
            return view('fields.options.list', compact('field', 'form', 'proj','presets'));
        } else if($field->type=="Multi-Select List") {
            return view('fields.options.mslist', compact('field', 'form', 'proj','presets'));
        } else if($field->type=="Generated List") {
            return view('fields.options.genlist', compact('field', 'form', 'proj','presets'));
        } else if($field->type=="Combo List") {
            $presetsOne = $presets->get("one");
            $presetsTwo = $presets->get("two");
            return view('fields.options.combolist', compact('field', 'form', 'proj','presetsOne','presetsTwo'));
        } else if($field->type=="Date") {
            return view('fields.options.date', compact('field', 'form', 'proj'));
        } else if($field->type=="Schedule") {
            return view('fields.options.schedule', compact('field', 'form', 'proj','presets'));
        } else if($field->type=="Geolocator") {
            return view('fields.options.geolocator', compact('field', 'form', 'proj','presets'));
        } else if($field->type=="Documents") {
            return view('fields.options.documents', compact('field', 'form', 'proj'));
        } else if($field->type=="Gallery") {
            return view('fields.options.gallery', compact('field', 'form', 'proj'));
        } else if($field->type=="Playlist") {
            return view('fields.options.playlist', compact('field', 'form', 'proj'));
        } else if($field->type=="Video") {
            return view('fields.options.video', compact('field', 'form', 'proj'));
        } else if($field->type=="3D-Model") {
            return view('fields.options.3dmodel', compact('field', 'form', 'proj'));
        } else if($field->type=="Associator") {
            return view('fields.options.associator', compact('field', 'form', 'proj'));
        }
	}

    /**
     * Get the edit view for a field.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  int $flid - Field ID
     * @return View
     */
	public function edit($pid, $fid, $flid) {
        if(!self::validProjFormField($pid, $fid, $flid)) {
            return redirect('projects/'.$pid.'/forms/'.$fid);
        }

        if(!self::checkPermissions($fid, 'edit')) {
            return redirect('projects/'.$pid.'/forms/'.$fid.'/fields');
        }

        $field = self::getField($flid);

        return view('fields.edit', compact('field', 'fid', 'pid','presets'));
	}

    /**
     * Update a field's information.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  int $flid - Field ID
     * @param  FieldRequest $request
     * @return View
     */
	public function update($pid, $fid, $flid, FieldRequest $request) {
        if(!self::validProjFormField($pid, $fid, $flid)) {
            return redirect('projects/'.$pid.'/forms/'.$fid);
        }

        if(!self::checkPermissions($fid, 'edit')) {
            return redirect('projects/'.$pid.'/forms/'.$fid.'/fields');
        }

		$field = self::getField($flid);

        $field->update($request->all());

        //A field has been changed, so current record rollbacks become invalid.
        RevisionController::wipeRollbacks($fid);

        flash()->overlay(trans('controller_field.fieldupdated'), trans('controller_field.goodjob'));

        return redirect('projects/'.$pid.'/forms/'.$fid);
	}

    /**
     * Update the field for if data is required in the field.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  int $flid - Field ID
     * @param  bool $req - Is the field required?
     */
    public static function updateRequired($pid, $fid, $flid, $req) {
        if(!self::validProjFormField($pid, $fid, $flid)) {
            return redirect('projects/'.$pid.'/forms/'.$fid);
        }

        if(!self::checkPermissions($fid, 'edit')) {
            return redirect('projects/'.$pid.'/forms/'.$fid.'/fields');
        }

        $field = self::getField($flid);

        $field->required = $req;
        $field->save();

        //A field has been changed, so current record rollbacks become invalid.
        RevisionController::wipeRollbacks($fid);
    }

    /**
     * Update the field for what context field's data can be searched and viewed.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  int $flid - Field ID
     * @param  Request $request
     */
    public static function updateSearchable($pid, $fid, $flid, Request $request) {
        if(!self::validProjFormField($pid, $fid, $flid)) {
            return redirect('projects/'.$pid.'/forms/'.$fid);
        }

        if(!self::checkPermissions($fid, 'edit')) {
            return redirect('projects/'.$pid.'/forms/'.$fid.'/fields');
        }

        $field = self::getField($flid);

        $field->searchable = $request->searchable;
        $field->extsearch = $request->extsearch;
        $field->viewable = $request->viewable;
        $field->viewresults = $request->viewresults;
        $field->extview = $request->extview;
        $field->save();

        //A field has been changed, so current record rollbacks become invalid.
        RevisionController::wipeRollbacks($fid);
    }

    /**
     * Update the field's default value.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  int $flid - Field ID
     * @param  string $def - Default value of field
     */
    public static function updateDefault($pid, $fid, $flid, $def) {
        if(!self::validProjFormField($pid, $fid, $flid)) {
            return redirect('projects/'.$pid.'/forms/'.$fid);
        }

        if(!self::checkPermissions($fid, 'edit')) {
            return redirect('projects/'.$pid.'/forms/'.$fid.'/fields');
        }

        $field = self::getField($flid);

        $field->default = $def;

        $field->save();

        //A field has been changed, so current record rollbacks become invalid.
        RevisionController::wipeRollbacks($fid);
    }

    /**
     * Update an option for a field.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  int $flid - Field ID
     * @param  string $opt - Option to update
     * @param  string $value - Value for option
     */
    public static function updateOptions($pid, $fid, $flid, $opt, $value) {
        if(!self::validProjFormField($pid, $fid, $flid)) {
            return redirect('projects/'.$pid.'/forms/'.$fid);
        }

        if(!self::checkPermissions($fid, 'edit')) {
            return redirect('projects/'.$pid.'/forms/'.$fid.'/fields');
        }

        $field = self::getField($flid);

        $options = $field->options;
        $tag = '[!'.$opt.'!]';
        $array = explode($tag,$options);

        $field->options = $array[0].$tag.$value.$tag.$array[2];
        $field->save();

        //A field has been changed, so current record rollbacks become invalid.
        RevisionController::wipeRollbacks($fid);
    }

    /**
     * Delete a field model.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  int $flid - Field ID
     */
	public function destroy($pid, $fid, $flid) {
        if(!self::validProjFormField($pid, $fid, $flid)) {
            return redirect('projects/'.$pid.'/forms/'.$fid);
        }

        if(!self::checkPermissions($fid, 'delete')) {
            return redirect('projects/'.$pid.'/forms/'.$fid.'/fields');
        }

        $field = self::getField($flid);
        $form = FormController::getForm($fid);
        $pageID = $field->page_id; //capture before delete
        $field->delete();

        //we need to restructure page sequence on delete
        PageController::restructurePageSequence($pageID);

        RevisionController::wipeRollbacks($form->fid);

        flash()->overlay(trans('controller_field.deleted'), trans('controller_field.goodjob'));
	}

    /**
     * Get a field from the database with either the flid or the slug.
     *
     * @param  mixed $flid - The flid or slug of the field
     * @return Field - The represented field
     */
    public static function getField($flid) {
        $field = Field::where('flid', '=', $flid)->first();
        if(is_null($field))
            $field = Field::where('slug','=',$flid)->first();

        return $field;
    }

    /**
     * Validates the project/form/field ID pairs.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  int $flid - Field ID
     * @return bool - The validity of the IDs
     */
    public static function validProjFormField($pid, $fid, $flid) {
        $field = self::getField($flid);
        $form = FormController::getForm($fid);
        $proj = ProjectController::getProject($pid);

        if (!FormController::validProjForm($pid, $fid))
            return false;

        if (is_null($field) || is_null($form) || is_null($proj))
            return false;
        else if ($field->fid == $form->fid)
            return true;
        else
            return false;
    }

    /**
     * Gets the value of a particular field option.
     *
     * @param  Field $field - Field to get option from
     * @param  string $key - The option name
     * @return string - The value of the option
     */
    public static function getFieldOption($field, $key) {
        $options = $field->options;
        $tag = '[!'.$key.'!]';
        $value = explode($tag,$options)[1];

        return $value;
    }

    /**
     * Checks a users permissions to be able to create and manipulate fields in a form.
     *
     * @param  int $fid - Form ID
     * @param  string $permission - Permission to check for
     * @return bool - Has the permission
     */
    private static function checkPermissions($fid, $permission='') {
        switch($permission) {
            case 'create':
                if(!(\Auth::user()->canCreateFields(FormController::getForm($fid))))  {
                    flash()->overlay(trans('controller_field.createper'), trans('controller_field.whoops'));
                    return false;
                }
                return true;
            case 'edit':
                if(!(\Auth::user()->canEditFields(FormController::getForm($fid)))) {
                    flash()->overlay(trans('controller_field.editper'), trans('controller_field.whoops'));
                    return false;
                }
                return true;
            case 'delete':
                if(!(\Auth::user()->canDeleteFields(FormController::getForm($fid)))) {
                    flash()->overlay(trans('controller_field.deleteper'), trans('controller_field.whoops'));
                    return false;
                }
                return true;
            default:
                if(!(\Auth::user()->inAFormGroup(FormController::getForm($fid)))) {
                    flash()->overlay(trans('controller_field.viewper'), trans('controller_field.whoops'));
                    return false;
                }
                return true;
        }
    }
}
