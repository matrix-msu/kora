<?php namespace App\Http\Controllers;

use App\AssociatorField;
use App\ComboListField;
use App\Form;
use App\Http\Requests\FieldRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
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
     * @param  int $pageIndex - Page that will own this field
     * @return View
     */
	public function create($pid, $fid, $pageIndex) {
        if(!FormController::validProjForm($pid, $fid))
            return redirect('projects/'.$pid)->with('k3_global_error', 'form_invalid');

        if(!self::checkPermissions($fid, 'create'))
            return redirect('projects/'.$pid.'/forms/'.$fid.'/fields')->with('k3_global_error', 'cant_create_field');

        $form = FormController::getForm($fid);
        $validFieldTypes = Form::$validFieldTypes;
        //$validComboListFieldTypes = ComboListField::$validComboListFieldTypes; //TODO::CASTLE
        $validComboListFieldTypes = [];

        return view('fields.create', compact('form','pageIndex', 'validFieldTypes', 'validComboListFieldTypes'));
	}

    /**
     * Saves a new field model and redirects to form page.
     *
     * @param  FieldRequest $request
     * @return Redirect
     */
	public function store(FieldRequest $request) {
	    if(!FormController::validProjForm($request->pid, $request->fid))
            return redirect('projects/'.$request->pid)->with('k3_global_error', 'form_invalid');

	    $field = [];
        $form = FormController::getForm($request->fid);
        $slug = str_replace(" ","_", $request->name).'_'.$form->project_id.'_'.$form->id.'_';
        $layout = $form->layout;

        //Make sure slug doesn't already exist
        if(array_key_exists($slug,$layout[$request->page_id]["fields"]))
            return redirect('projects/'.$request->pid.'/forms/'.$request->fid)->with('k3_global_error', 'field_name_error');

        //Fill out its data
        $field['type'] = $request->type;
        $field['name'] = $request->name;
        $field['description'] = $request->desc;
        $field['default'] = null;
        $field['required'] = isset($request->required) && $request->required ? 1 : 0;
        $field['searchable'] = isset($request->searchable) && $request->searchable ? 1 : 0;
        $field['advanced_search'] = isset($request->advsearch) && $request->advsearch ? 1 : 0;
        $field['external_search'] = isset($request->extsearch) && $request->extsearch ? 1 : 0;
        $field['viewable'] = isset($request->viewable) && $request->viewable ? 1 : 0;
        $field['viewable_in_results'] = isset($request->viewresults) && $request->viewresults ? 1 : 0;
        $field['external_view'] = isset($request->extview) && $request->extview ? 1 : 0;

        //Field Specific Stuff
        $fieldMod = $form->getFieldModel($request->type);
        $field['options'] = $fieldMod->getDefaultOptions();
        $fieldMod->addDatabaseColumn($form->id, $slug);

        //Add to form
        $layout[$request->page_id]["fields"][$slug] = $field;
        $form->layout = $layout;
        $form->save();

        //if advanced options was selected we should call the correct one
        $advError = false; //TODO::CASTLE
        if($request->advanced) {
//            $result = $field->getTypedField()->updateOptions($field, $request, false);
//            if($result != '')
//                $advError = true;
        }

        //A field has been changed, so current record rollbacks become invalid.  //TODO::CASTLE
        //RevisionController::wipeRollbacks($form->fid);

        if(!$advError) //if we error on the adv page we should hide the success message so error can display
            return redirect('projects/'.$request->pid.'/forms/'.$request->fid)->with('k3_global_success', 'field_created');
        else
            return redirect('projects/'.$request->pid.'/forms/'.$request->fid)->with('k3_global_error', 'field_advanced_error');
	}

    /**
     * Gets and displays the field options page for a particular field.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  int $flid - Field ID
     * @return View
     */
	public function show($pid, $fid, $flid) { //TODO::CASTLE
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
            //we are building an array about the association permissions to populate the layout
            $opt_layout_one = array();
            if(ComboListField::getComboFieldType($field,'one') == 'Associator') {
                $option1 = ComboListField::getComboFieldOption($field, 'SearchForms', 'one');
                if ($option1 != '') {
                    $options = explode('[!]', $option1);

                    foreach ($options as $opt) {
                        $opt_fid = explode('[fid]', $opt)[1];
                        $opt_search = explode('[search]', $opt)[1];
                        $opt_flids = explode('[flids]', $opt)[1];
                        $opt_flids = explode('-', $opt_flids);

                        $opt_layout_one[$opt_fid] = ['search' => $opt_search, 'flids' => $opt_flids];
                    }
                }
            }
            $opt_layout_two = array();
            if(ComboListField::getComboFieldType($field,'two') == 'Associator') {
                $option2 = ComboListField::getComboFieldOption($field, 'SearchForms', 'two');
                if ($option2 != '') {
                    $options = explode('[!]', $option2);

                    foreach ($options as $opt) {
                        $opt_fid = explode('[fid]', $opt)[1];
                        $opt_search = explode('[search]', $opt)[1];
                        $opt_flids = explode('[flids]', $opt)[1];
                        $opt_flids = explode('-', $opt_flids);

                        $opt_layout_two[$opt_fid] = ['search' => $opt_search, 'flids' => $opt_flids];
                    }
                }
            }

            return view(ComboListField::FIELD_OPTIONS_VIEW, compact('field', 'form', 'proj', 'presets', 'opt_layout_one', 'opt_layout_two'));
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

            return view(AssociatorField::FIELD_OPTIONS_VIEW, compact('field', 'form', 'proj','presets', 'opt_layout'));
        } else {
            return view($field->getTypedField()->getFieldOptionsView(), compact('field', 'form', 'proj', 'presets'));
        }
	}

    /**
     * DEPRECATED - We are no longer editing the field separate from it's options. Therefore the options page above
     *               will be the main edit view. This view will simply bounce to the options page.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  int $flid - Field ID
     * @return Redirect
     */
	public function edit($pid, $fid, $flid) { //TODO::CASTLE
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
    public function update($pid, $fid, $flid, FieldRequest $request) { //TODO::CASTLE
        if(!self::validProjFormField($pid, $fid, $flid))
            return redirect('projects/'.$pid.'/forms/'.$fid)->with('k3_global_error', 'field_invalid');

        $field = self::getField($flid);

        $field->name = $request->name;
        $field->slug = $request->slug;
        $field->desc = $request->desc;

        $field->save();

        //A field has been changed, so current record rollbacks become invalid.
        RevisionController::wipeRollbacks($fid);

        $field->getTypedField()->updateOptions($field, $request);

        return redirect('projects/'.$pid.'/forms/'.$fid)->with('k3_global_success', 'field_updated');
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
    public function updateFlag($pid, $fid, $flid, Request $request) { //TODO::CASTLE
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
	public function destroy($pid, $fid, $flid, Request $request) { //TODO::CASTLE
        if(!self::validProjFormField($pid, $fid, $flid))
            return redirect()->action('FormController@show', ['pid' => $pid, 'fid' => $fid])->with('k3_global_error', 'field_invalid');

        if(!self::checkPermissions($fid, 'delete'))
            return redirect()->action('FormController@show', ['pid' => $pid, 'fid' => $fid])->with('k3_global_error', 'cant_delete_field');

        $field = FieldController::getField($flid);
        $pageID = $field->page_id; //capture before delete
        $field->delete();

        //we need to restructure page sequence on delete
        PageController::restructurePageSequence($pageID);

        if(isset($request->redirect_route))
            return redirect('projects/'.$pid.'/forms/'.$fid)->with('k3_global_success', 'field_deleted');
        else
            return response()->json(["status"=>true, "message"=>"deleted"], 200);
	}

    /**
     * Validates a field and its basic options.
     *
     * @return JsonResponse
     */
    public function validateFieldFields(FieldRequest $request) { //TODO::CASTLE
        //Note:: This does work. The FieldRequest class validates the field itself, and if we get here, we return all clear!
        return response()->json(["status"=>true, "message"=>"Form Valid", 200]);
    }

    /**
     * Get a field from the database with either the flid or the slug.
     *
     * @param  mixed $flid - The flid or slug of the field
     * @return Field - The represented field
     */
    public static function getField($flid) { //TODO::CASTLE
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
    public static function validProjFormField($pid, $fid, $flid) { //TODO::CASTLE
        $field = self::getField($flid);
        $form = FormController::getForm($fid);
        $proj = ProjectController::getProject($pid);

        if(!FormController::validProjForm($pid, $fid))
            return false;

        if(is_null($field) || is_null($form) || is_null($proj))
            return false;
        else if($field->fid == $form->fid)
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
    public static function getFieldOption($field, $key) { //TODO::CASTLE
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
    public static function checkPermissions($fid, $permission='') { //TODO::CASTLE
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

    /**
     * View single image/video/audio/document from a record.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  int $rid - Record ID
     * @param  int $flid - Field ID
     * @param  string $filename - Image filename
     * @return Redirect
     */
    public function singleResource($pid, $fid, $rid, $flid, $filename) { //TODO::CASTLE
        $relative_src = 'files/p'.$pid.'/f'.$fid.'/r'.$rid.'/fl'.$flid.'/'.$filename;
        $src = url('app/'.$relative_src);

        if(!file_exists(storage_path('app/'.$relative_src))) {
            // File does not exist
            dd($filename . ' not found');
        }

        $mime = Storage::mimeType('files/p'.$pid.'/f'.$fid.'/r'.$rid.'/fl'.$flid.'/'.$filename);

        if(strpos($mime, 'image') !== false || strpos($mime, 'jpeg') !== false || strpos($mime, 'png') !== false) {
            // Image
            return view('fields.singleImage', compact('filename', 'src'));
        } else if(strpos($mime, 'video') !== false || strpos($mime, 'mp4') !== false) {
            // Video
            return view('fields.singleVideo', compact('filename', 'src'));
        } else if(strpos($mime, 'audio') !== false || strpos($mime, 'mpeg') !== false || strpos($mime, 'mp3') !== false) {
            // Audio
            return view('fields.singleAudio', compact('filename', 'src'));
        }

        // Attempting to open generic document
        $ext = File::extension($src);

        if($ext=='pdf'){
            $content_types='application/pdf';
        } else if($ext=='doc') {
            $content_types='application/msword';
        } else if($ext=='docx') {
            $content_types='application/vnd.openxmlformats-officedocument.wordprocessingml.document';
        } else if($ext=='xls') {
            $content_types='application/vnd.ms-excel';
        } else if($ext=='xlsx') {
            $content_types='application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        } else if($ext=='txt') {
            $content_types='application/octet-stream';
        }

        return response()->file('app/'.$relative_src, [
            'Content-Type' => $content_types
        ]);
    }

    /**
     * View single image/video/audio/document from a record.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  int $rid - Record ID
     * @param  int $flid - Field ID
     * @param  string $filename - Image filename
     * @return Redirect
     */
    public function singleGeolocator($pid, $fid, $rid, $flid) { //TODO::CASTLE
        $field = self::getField($flid);
        $record = RecordController::getRecord($rid);
        $typedField = $field->getTypedFieldFromRID($rid);

        return view('fields.singleGeolocator', compact('field', 'record', 'typedField'));
    }

    /**
     * View single image/video/audio/document from a record.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  int $rid - Record ID
     * @param  int $flid - Field ID
     * @param  string $filename - Image filename
     * @return Redirect
     */
    public function singleRichtext($pid, $fid, $rid, $flid) { //TODO::CASTLE
        $field = self::getField($flid);
        $record = RecordController::getRecord($rid);
        $typedField = $field->getTypedFieldFromRID($rid);

        return view('fields.singleRichtext', compact('field', 'record', 'typedField'));
    }
}
