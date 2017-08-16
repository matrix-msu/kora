<?php namespace App\Http\Controllers;

use App\ComboListField;
use App\Field;
use App\Http\Requests\FieldRequest;
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
     * @param  int $rootPage - Page that will own this field
     * @return View
     */
	public function create($pid, $fid, $rootPage) {
        if(!FormController::validProjForm($pid, $fid)) {
            return redirect('projects/'.$pid);
        }

        if(!self::checkPermissions($fid, 'create')) {
            return redirect('projects/'.$pid.'/forms/'.$fid.'/fields');
        }

		$form = FormController::getForm($fid);
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
        if($field->type=='Combo List' && ($request->cfname1 == '' | $request->cfname2 == '')) {
            flash()->error("You must enter a name for both Combo List fields!");

            return redirect()->back()->withInput();
        }

        $field->options = $field->getTypedField()->getDefaultOptions($request);
        $field->default = '';

        $field->sequence = $seq;

        $field->save();

        //if advanced options was selected we should call the correct one
        $advError = false;
        if($request->advance) {
            $result = $field->getTypedField()->updateOptions($field, $request, false);
            if($result != '') {
                $advError = true;
                flash()->error('There was an error with the advanced options. '.$result.' Please visit the options page of the field.');
            }
        }

        //A field has been changed, so current record rollbacks become invalid.
        $form = FormController::getForm($field->fid);
        RevisionController::wipeRollbacks($form->fid);

        if(!$advError) //if we error on the adv page we should hide the success message so error can display
            flash()->overlay("Your field has been successfully created!", "Good Job!");

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

        //Combo has two presets so we make an exception
        if($field->type=="Combo List"){
            $presetsOne = $presets->get("one");
            $presetsTwo = $presets->get("two");
            return view(ComboListField::FIELD_OPTIONS_VIEW, compact('field', 'form', 'proj','presetsOne','presetsTwo'));
        } else {
            return view($field->getTypedField()->getFieldOptionsView(), compact('field', 'form', 'proj','presets'));
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

        flash()->overlay("Your field has been successfully updated!", "Good Job!");

        return redirect('projects/'.$pid.'/forms/'.$fid);
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
            return redirect()->action('FormController@show', ['pid' => $pid, 'fid' => $fid]);
        }

        if(!self::checkPermissions($fid, 'delete')) {
            return redirect()->action('FormController@show', ['pid' => $pid, 'fid' => $fid]);
        }

        $field = self::getField($flid);
        $form = FormController::getForm($fid);
        $pageID = $field->page_id; //capture before delete
        $field->delete();

        //we need to restructure page sequence on delete
        PageController::restructurePageSequence($pageID);

        RevisionController::wipeRollbacks($form->fid);

        flash()->overlay("Your field has been successfully deleted!", "Good Job!");
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
                    flash()->overlay("You do not have permission to create fields for that form.", "Whoops");
                    return false;
                }
                return true;
            case 'edit':
                if(!(\Auth::user()->canEditFields(FormController::getForm($fid)))) {
                    flash()->overlay("You do not have permission to edit fields for that form.", "Whoops");
                    return false;
                }
                return true;
            case 'delete':
                if(!(\Auth::user()->canDeleteFields(FormController::getForm($fid)))) {
                    flash()->overlay("You do not have permission to delete fields for that form.", "Whoops");
                    return false;
                }
                return true;
            default:
                if(!(\Auth::user()->inAFormGroup(FormController::getForm($fid)))) {
                    flash()->overlay("You do not have permission to view that field.", "Whoops");
                    return false;
                }
                return true;
        }
    }
}
