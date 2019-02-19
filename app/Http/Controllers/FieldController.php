<?php namespace App\Http\Controllers;

use App\AssociatorField;
use App\ComboListField;
use App\Field;
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
	    //special error check for combo list field
        if($request->type=='Combo List' && ($request->cfname1 == '' | $request->cfname2 == ''))
            return redirect()->back()->withInput()->with('k3_global_error', 'combo_name_missing');

        $seq = PageController::getNewPageFieldSequence($request->page_id); //we do this before anything so the new field isnt counted in it's logic
        $field = Field::Create($request->all());

        $field->options = $field->getTypedField()->getDefaultOptions($request);
        $field->default = '';

        $field->sequence = $seq;

        $field->save();

        //if advanced options was selected we should call the correct one
        $advError = false;
        if($request->advanced) {
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
    public function update($pid, $fid, $flid, FieldRequest $request) {
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
	public function destroy($pid, $fid, $flid, Request $request) {
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
    public function validateFieldFields(FieldRequest $request) {
        //Note:: This does work. The FieldRequest class validates the field itself, and if we get here, we return all clear!
        return response()->json(["status"=>true, "message"=>"Form Valid", 200]);
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
    public function singleResource($pid, $fid, $rid, $flid, $filename) {
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
    public function singleGeolocator($pid, $fid, $rid, $flid) {
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
    public function singleModel($pid, $fid, $rid, $flid) {
        $field = self::getField($flid);
        $record = RecordController::getRecord($rid);
        $typedField = $field->getTypedFieldFromRID($rid);

        return view('fields.singleModel', compact('field', 'record', 'typedField'));
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
    public function singleRichtext($pid, $fid, $rid, $flid) {
        $field = self::getField($flid);
        $record = RecordController::getRecord($rid);
        $typedField = $field->getTypedFieldFromRID($rid);

        return view('fields.singleRichtext', compact('field', 'record', 'typedField'));
    }
}
