<?php namespace App\Http\Controllers;

use App\AssociatorField;
use App\ComboListField;
use App\Field;
use App\Http\Requests\FieldRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
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
        if(!FormController::validProjForm($pid, $fid))
            return redirect('projects/'.$pid)->with('k3_global_error', 'form_invalid');

        if(!self::checkPermissions($fid, 'create'))
            return redirect('projects/'.$pid.'/forms/'.$fid.'/fields')->with('k3_global_error', 'cant_create_field');

		$form = FormController::getForm($fid);
		$validFieldTypes = Field::$validFieldTypes;
        $validComboListFieldTypes = ComboListField::$validComboListFieldTypes;

        return view('fields.create', compact('form','rootPage', 'validFieldTypes', 'validComboListFieldTypes'));
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
            return redirect()->back()->withInput()->with('k3_global_error', 'combo_name_missing');
        }

        $field->options = $field->getTypedField()->getDefaultOptions($request);
        $field->default = '';

        $field->sequence = $seq;

        $field->save();

        //if advanced options was selected we should call the correct one
        $advError = false;
        if($request->advance) {
            $result = $field->getTypedField()->updateOptions($field, $request, false);
            if($result != '')
                $advError = true;
        }

        //A field has been changed, so current record rollbacks become invalid.
        $form = FormController::getForm($field->fid);
        RevisionController::wipeRollbacks($form->fid);

        if(!$advError) //if we error on the adv page we should hide the success message so error can display
            return redirect('projects/'.$field->pid.'/forms/'.$field->fid)->with('k3_global_success', 'field_created');
        else
            return redirect('projects/'.$field->pid.'/forms/'.$field->fid)->with('k3_global_error', 'field_advanced_error');
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
        if(!self::validProjFormField($pid, $fid, $flid))
            return redirect('projects/'.$pid.'/forms/'.$fid)->with('k3_global_error', 'field_invalid');

        if(!self::checkPermissions($fid, 'edit'))
            return redirect('projects/'.$pid.'/forms/'.$fid.'/fields')->with('k3_global_error', 'cant_edit_field');

        $field = self::getField($flid);
        $form = FormController::getForm($fid);
        $proj = ProjectController::getProject($pid);

        $presets = OptionPresetController::getPresetsSupported($pid,$field);

        //Combo has two presets so we make an exception
        if($field->type == Field::_COMBO_LIST) {
            $presetsOne = $presets->get("one");
            $presetsTwo = $presets->get("two");
            return view(ComboListField::FIELD_OPTIONS_VIEW, compact('field', 'form', 'proj','presetsOne','presetsTwo'));
        } else if($field->type == Field::_ASSOCIATOR) {
            //we are building an array about the association permissions to populate the layout
            $option = \App\Http\Controllers\FieldController::getFieldOption($field,'SearchForms');
            $opt_layout = array();
            if($option!=''){
                $options = explode('[!]',$option);

                foreach($options as $opt){
                    $opt_fid = explode('[fid]',$opt)[1];
                    $opt_search = explode('[search]',$opt)[1];
                    $opt_flids = explode('[flids]',$opt)[1];
                    $opt_flids = explode('-',$opt_flids);

                    $opt_layout[$opt_fid] = ['search' => $opt_search, 'flids' => $opt_flids];
                }
            }
            return view(AssociatorField::FIELD_OPTIONS_VIEW, compact('field', 'form', 'proj','presets','opt_layout'));
        } else {
            return view($field->getTypedField()->getFieldOptionsView(), compact('field', 'form', 'proj','presets'));
        }
	}

    /**
     * DEPRECATED - We are no longer editing the field separate from it's options. Therefore the options page above
     *               will be the main edit view. This view will simply bounce to the options page
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  int $flid - Field ID
     * @return Redirect
     */
	public function edit($pid, $fid, $flid) {
        return redirect('projects/'.$pid.'/forms/'.$fid.'/fields/'.$flid.'/options');
	}

    /**
     * Update the options for a particular field.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  int $flid - Field ID
     * @param  FieldRequest $request
     * @return Redirect
     */
    public function update($pid, $fid, $flid, FieldRequest $request){
        if(!self::validProjFormField($pid, $fid, $flid))
            return redirect('projects/'.$pid.'/forms/'.$fid)->with('k3_global_error', 'field_invalid');

        $field = self::getField($flid);

        $field->name = $request->name;
        $field->slug = $request->slug;
        $field->desc = $request->desc;

        $field->save();

        //A field has been changed, so current record rollbacks become invalid.
        RevisionController::wipeRollbacks($fid);

        return $field->getTypedField()->updateOptions($field, $request);
    }

    /**
     * Update the options for a particular field.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  int $flid - Field ID
     * @param  Request $request
     * @return JsonResponse
     */
    public function updateFlag($pid, $fid, $flid, Request $request){
        if(!FieldController::validProjFormField($pid, $fid, $flid))
            return response()->json(["status"=>false,"message"=>"field_invalid"],500);

        $field = FieldController::getField($flid);
        $flag = $request->flag;
        $value = $request->value;

        switch($flag) {
            case "required":
                $field->required = $value;
                $field->save();
                break;
            case "searchable":
                $field->searchable = $value;
                $field->save();
                break;
            case "advsearch":
                $field->advsearch = $value;
                $field->save();
                break;
            case "extsearch":
                $field->extsearch = $value;
                $field->save();
                break;
            case "viewable":
                $field->viewable = $value;
                $field->save();
                break;
            case "viewresults":
                $field->viewresults = $value;
                $field->save();
                break;
            case "extview":
                $field->extview = $value;
                $field->save();
                break;
            default:
                return response()->json(["status"=>false,"message"=>"invalid_field_flag"],500);
        }

        return response()->json(["status"=>true,"message"=>"field_flag_updated"],200);
    }

    /**
     * Delete a field model.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  int $flid - Field ID
     */
	public function destroy($pid, $fid, $flid) {
        if(!self::validProjFormField($pid, $fid, $flid))
            return redirect()->action('FormController@show', ['pid' => $pid, 'fid' => $fid])->with('k3_global_error', 'field_invalid');

        if(!self::checkPermissions($fid, 'delete'))
            return redirect()->action('FormController@show', ['pid' => $pid, 'fid' => $fid])->with('k3_global_error', 'cant_delete_field');

        $field = self::getField($flid);
        $form = FormController::getForm($fid);
        $pageID = $field->page_id; //capture before delete
        $field->delete();

        //we need to restructure page sequence on delete
        PageController::restructurePageSequence($pageID);

        // RevisionController::wipeRollbacks($form->fid);

        // return redirect('projects/'.$pid.'/forms/'.$fid)->with('k3_global_success', 'field_deleted');
        return response()->json(["status"=>true, "message"=>"deleted"], 200);
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
    public static function checkPermissions($fid, $permission='') {
        switch($permission) {
            case 'create':
                if(!(\Auth::user()->canCreateFields(FormController::getForm($fid))))
                    return false;
                break;
            case 'edit':
                if(!(\Auth::user()->canEditFields(FormController::getForm($fid))))
                    return false;
                break;
            case 'delete':
                if(!(\Auth::user()->canDeleteFields(FormController::getForm($fid))))
                    return false;
                break;
            default:
                if(!(\Auth::user()->inAFormGroup(FormController::getForm($fid))))
                    return false;
                break;
        }

        return true;
    }
}
