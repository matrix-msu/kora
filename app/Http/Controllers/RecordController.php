<?php namespace App\Http\Controllers;

use App\Field;
use App\RecordPreset;
use App\Revision;
use App\User;
use App\Form;
use App\Record;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use RecursiveIteratorIterator;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;

class RecordController extends Controller {

    /*
    |--------------------------------------------------------------------------
    | Record Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles record creation and manipulation
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
     * Gets the all records view.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @return View
     */
	public function index($pid, $fid) {
        if(!FormController::validProjForm($pid, $fid))
            return redirect('projects/'.$pid)->with('k3_global_error', 'form_invalid');

        if(!FieldController::checkPermissions($fid))
            return redirect('/projects/'.$pid)->with('k3_global_error', 'cant_view_form');

        $form = FormController::getForm($fid);

        $pagination = app('request')->input('page-count') === null ? 10 : app('request')->input('page-count');
        $order = app('request')->input('order') === null ? 'lmd' : app('request')->input('order');
        $order_type = substr($order, 0, 2) === "lm" ? "updated_at" : "rid";
        $order_direction = substr($order, 2, 3) === "a" ? "asc" : "desc";
        $records = Record::where('fid', '=', $fid)->orderBy($order_type, $order_direction)->paginate($pagination);

        $total = Record::where('fid', '=', $fid)->count();

        return view('records.index', compact('form', 'records', 'total'));
	}

    /**
     * Gets the new record view.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @return View
     */
	public function create($pid, $fid) {
        if(!FormController::validProjForm($pid, $fid))
            return redirect('projects/'.$pid)->with('k3_global_error', 'form_invalid');

        if(!self::checkPermissions($fid, 'ingest'))
            return redirect('projects/'.$pid.'/forms/'.$fid)->with('k3_global_error', 'cant_create_records');

        if(Field::where('fid','=',$fid)->count() == 0)
            return redirect('projects/'.$pid.'/forms/'.$fid)->with('k3_global_error', 'no_fields_record');

        $form = FormController::getForm($fid);
        $presets = array();

        foreach(RecordPreset::where('fid', '=', $fid)->get() as $preset)
            $presets[] = ['id' => $preset->id, 'name' => $preset->name];

        $fields = array(); //array of field ids
        foreach($form->fields()->get() as $field)
            $fields[] = $field->flid;

        return view('records.create', compact('form', 'presets', 'fields'));
	}

    /**
     * Saves a new record in Kora.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  Request $request
     * @return Redirect
     */
	public function store($pid, $fid, Request $request) {
	    //Validates records
        foreach($request->all() as $key => $value) {
            if(!is_numeric($key))
                continue;
            $field = FieldController::getField($key);
            $message = $field->getTypedField()->validateField($field, $request);
            if(!empty($message)) {
                $arrayed_keys = array();

                foreach($request->all() as $akey => $avalue) {
                    if(is_array($avalue))
                        array_push($arrayed_keys,$akey);
                }

                if($request->api)
                    return response()->json(["status"=>false,"message"=>"record_validation_error","record_validation_error"=>$message],500);
                else
                    return redirect()->back()->withInput($request->except($arrayed_keys))
                        ->with('k3_global_error', 'record_validation_error')->with('record_validation_error', $message);
            }
        }

        //Handle Mass Creation
        $numRecs = 1;
        if(isset($request->mass_creation_num)) {
            $numRecs = $request->mass_creation_num;
            //safeguard
            if($numRecs > 1000)
                $numRecs = 1000;
        }

        //Handle record preset
        $makePreset = false;
        $presetName = '';
        if(isset($request->record_preset_name)) {
            $presetName = $request->record_preset_name;
            if(strlen($presetName) < 3)
                return redirect()->back()->withInput($request)->with('k3_global_error', 'record_validation_error')->with('record_validation_error', 'present_name_short');
            $makePreset = true;
        }

        for($i = 0; $i < $numRecs ; $i++) {
            $record = new Record();
            $record->pid = $pid;
            $record->fid = $fid;
            $record->owner = $request->userId;
            $record->save(); //need to save to create rid needed to make kid
            $record->kid = $pid . '-' . $fid . '-' . $record->rid;
            $record->save();

            foreach($request->all() as $key => $value) {
                if(!is_numeric($key))
                    continue;
                $field = FieldController::getField($key);
                $field->getTypedField()->createNewRecordField($field, $record, $value, $request);
            }

            //
            // Only create a revision if the record was not mass created.
            // This prevents clutter from an operation that the user
            // will likely not want to undo using revisions.
            //
            if($numRecs > 1)
                RevisionController::storeRevision($record->rid, 'create');

            //If we are making a preset, let's make sure it's done, and done once
            if($makePreset) {
                $makePreset = false; //prevents a preset being made for every duplicate record

                $rpc = new RecordPresetController();
                $presetRequest = new Request();
                $presetRequest->name = $presetName;
                $presetRequest->rid = $record->rid;

                $rpc->presetRecord($presetRequest);
            }
        }

        if($request->api)
            return response()->json(["status"=>true,"message"=>"record_created","kid"=>$record->kid],200);
        else
            return redirect('projects/' . $pid . '/forms/' . $fid . '/records')->with('k3_global_success', 'record_created');
	}

    /**
     * Gets the individual record view.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  int $rid - Record ID
     * @return View
     */
	public function show($pid, $fid, $rid) {
        if(!self::validProjFormRecord($pid, $fid, $rid))
            return redirect('projects')->with('k3_global_error', 'record_invalid');

        if(!FieldController::checkPermissions($fid))
            return redirect('/projects/'.$pid)->with('k3_global_error', 'cant_view_form');

        $form = FormController::getForm($fid);
        $record = self::getRecord($rid);
        $owner = User::where('id', '=', $record->owner)->first();
        $numRevisions = Revision::where('rid',$rid)->count();
        $alreadyPreset = (RecordPreset::where('rid',$rid)->count() > 0);

        return view('records.show', compact('record', 'form', 'owner', 'numRevisions', 'alreadyPreset'));
	}

    /**
     * Get the edit record view.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  int $rid - Record ID
     * @return View
     */
	public function edit($pid, $fid, $rid) {
        if(!self::validProjFormRecord($pid, $fid, $rid))
            return redirect('projects')->with('k3_global_error', 'record_invalid');

        if(!\Auth::user()->isOwner(self::getRecord($rid)) && !self::checkPermissions($fid, 'modify'))
            return redirect('projects/'.$pid.'/forms/'.$fid)->with('k3_global_error', 'cant_edit_record');

        $form = FormController::getForm($fid);
        $record = self::getRecord($rid);

        return view('records.edit', compact('record', 'form'));
	}

    /**
     * Gets record to be cloned and throws its data into the new record view.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  int $rid - Record ID
     * @return View
     */
    public function cloneRecord($pid, $fid, $rid) {
        if(!self::validProjFormRecord($pid, $fid, $rid))
            return redirect('projects')->with('k3_global_error', 'record_invalid');

        $form = FormController::getForm($fid);
        $record = self::getRecord($rid);

        return view('records.clone', compact('record', 'form'));
    }

    public function validateRecord($pid, $fid, Request $request) {
        $errors = [];
        $form = FormController::getForm($fid);

        foreach($form->fields()->get() as $field) {
            $message = $field->getTypedField()->validateField($field, $request);
            if(!empty($message))
                $errors += $message; //We add these arrays because it maintains the keys, where array_merge re-indexes
        }

        return response()->json(["status"=>true,"errors"=>$errors],200);
    }

    /**
     * Removes record files from the system for records that no longer exist. This will prevent the possiblity of
     *  rolling back these records.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @return array - The records that were removed
     */
    public function cleanUp($pid, $fid) {
        $form = FormController::getForm($fid);

        if(!(\Auth::user()->isFormAdmin($form)))
            return response()->json(["status"=>false,"message"=>"not_form_admin"],500);

        //
        // Using revisions, if a record's most recent change is a deletion,
        // we remove the file directory associated with that record.
        // More specifically, if the record no longer exists we
        // intend to clean up the files associated with it.
        //
        $all_revisions = Revision::where('fid', '=', $fid)->get();
        $rids = array();

        foreach($all_revisions as $revision) {
            $rids[] = $revision->rid;
        }
        $rids = array_unique($rids);

        $revisions = array(); // To be filled with revisions with records that do not exist.
        foreach($rids as $rid) {
            // If a record's most recent revision is a deletion...
            $revision = Revision::where('rid', '=', $rid)->orderBy('created_at', 'desc')->first();
            if($revision->type == Revision::DELETE)
                $revisions[] = $revision; // ... add to the array.
        }

        $base_path = config('app.base_path').'storage/app/files/p'.$pid.'/f'.$fid;

        //
        // For each revision, delete it's associated record's files.
        //
        foreach($revisions as $revision) {
            $path = $base_path . "/r" . $revision->rid;
            if(is_dir($path)) {
                $it = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
                $files = new RecursiveIteratorIterator($it,
                    RecursiveIteratorIterator::CHILD_FIRST);
                foreach($files as $file) {
                    if($file->isDir())
                        rmdir($file->getRealPath());
                    else
                        unlink($file->getRealPath());
                }
                rmdir($path);
            }
        }

        return $revisions;
    }

    /**
     * Update a record with new data.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  int $rid - Record ID
     * @param  Request $request
     * @return Redirect
     */
	public function update($pid, $fid, $rid, Request $request) {
	    //Validate record
        foreach($request->all() as $key => $value) {
            if(!is_numeric($key))
                continue;
            $field = FieldController::getField($key);
            $message = $field->getTypedField()->validateField($field, $request);
            if(!empty($message))
                return redirect()->back()->withInput()->with('k3_global_error', 'record_validation_error')->with('record_validation_error', $message);
        }

        //Handle record preset
        $makePreset = false;
        $presetName = '';
        if(isset($request->record_preset_name)) {
            $presetName = $request->record_preset_name;
            if(strlen($presetName) < 3)
                return redirect()->back()->withInput($request)->with('k3_global_error', 'record_validation_error')->with('record_validation_error', 'present_name_short');
            $makePreset = true;
        }

        $record = Record::where('rid', '=', $rid)->first();
        $record->updated_at = Carbon::now();
        $record->save();

        $revision = RevisionController::storeRevision($record->rid, 'edit');

        $form_fields_expected = Form::find($fid)->fields()->get();

        foreach($form_fields_expected as $expected_field) {
            $key = $expected_field->flid;

            if($request->has($key))
                $value = $request->input($key);
            else
                $value = null;

            $field = FieldController::getField($key);
            $typedField = $field->getTypedFieldFromRID($record->rid);
            if(!is_null($typedField))
                $typedField->editRecordField($value,$request);
            else if(!is_null($value)) //If it didnt exist and value was there, build it
                $field->getTypedField()->createNewRecordField($field,$record,$value,$request);
        }

        $revision->oldData = RevisionController::buildDataArray($record);
        $revision->save();

        //Make new preset
        if($makePreset) {
            $rpc = new RecordPresetController();
            $presetRequest = new Request();
            $presetRequest->name = $presetName;
            $presetRequest->rid = $record->rid;

            $rpc->presetRecord($presetRequest);
        } else {
            //Otherwise, let's update the preset if it exists
            RecordPresetController::updateIfExists($record->rid);
        }

        if(!$request->api)
            return redirect('projects/' . $pid . '/forms/' . $fid . '/records/' . $rid)->with('k3_global_success', 'record_updated');
        else
            return response()->json(["status"=>true,"message"=>"record_updated"],200);
	}

    /**
     * Delete a record from Kora3.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  int $rid - Record ID
     * @param  bool $mass - Is deleting mass records
     * @return Redirect
     */
    public function destroy($pid, $fid, $rid, $mass = false) {
        if(!self::validProjFormRecord($pid, $fid, $rid))
            return redirect('projects')->with('k3_global_error', 'record_invalid');

        if(!\Auth::user()->isOwner(self::getRecord($rid)) && !self::checkPermissions($fid, 'destroy'))
            return redirect('projects/'.$pid.'/forms/'.$fid)->with('k3_global_error', 'cant_delete_record');

        $record = self::getRecord($rid);

        if(!$mass)
            RevisionController::storeRevision($record->rid, 'delete');

        $record->delete();

        return redirect()->action('FormController@show', ['pid' => $pid, 'fid' => $fid])->with('k3_global_success', 'record_deleted');
	}

    /**
     * Delete all records from a form.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @return JsonResponse
     */
    public function deleteAllRecords($pid, $fid) {
        $form = FormController::getForm($fid);

        if(!\Auth::user()->isFormAdmin($form)) {
            return response()->json(["status"=>false,"message"=>"not_form_admin"],500);
        } else {
            Record::where("fid", "=", $fid)->delete();

            return response()->json(["status"=>true,"message"=>"all_record_deleted"],200);
        }
    }

    /**
     * Gets the view for the record import process.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @return View
     */
    public function importRecordsView($pid,$fid) {
        if(!FormController::validProjForm($pid, $fid))
            return redirect('projects/'.$pid)->with('k3_global_error', 'form_invalid');

        if(!self::checkPermissions($fid, 'ingest'))
            return redirect('projects/'.$pid.'/forms/'.$fid)->with('k3_global_error', 'cant_create_records');

        $form = FormController::getForm($fid);

        return view('records.import',compact('form','pid','fid'));
    }

    /**
     * Get a record back by RID.
     *
     * @param  int $rid - Record ID
     * @return Record - Requested record
     */
    public static function getRecord($rid) {
        $record = Record::where('rid', '=', $rid)->first();

        return $record;
    }

    /**
     * Get a record back by KID.
     *
     * @param  int $kid - Kora ID
     * @return Record - Requested record
     */
    public static function getRecordByKID($kid) {
        $record = Record::where('kid', '=', $kid)->first();

        return $record;
    }

    /**
     * Determines if record exists.
     *
     * @param  int $rid - Record ID
     * @return bool - Does exist
     */
    public static function exists($rid) {
        return !is_null(Record::where('rid','=',$rid)->first());
    }

    /**
     * Determines if the project, form, record ID combos are valid.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  int $rid - Record ID
     * @return bool - Valid pairs
     */
    public static function validProjFormRecord($pid, $fid, $rid) {
        $record = self::getRecord($rid);
        $form = FormController::getForm($fid);
        $proj = ProjectController::getProject($pid);

        if(!FormController::validProjForm($pid, $fid))
            return false;
        if(is_null($record) || is_null($form) || is_null($proj))
            return false;
        else if ($record->fid == $form->fid)
            return true;
        else
            return false;
    }

    /**
     * Checks users abilities to create, edit, delete records.
     *
     * @param  int $fid - Form ID
     * @param  string $permission - Permission to search for
     * @return bool - Has permissions
     */
    private static function checkPermissions($fid, $permission='') {
        switch($permission) {
            case 'ingest':
                if(!(\Auth::user()->canIngestRecords(FormController::getForm($fid))))
                    return false;
                break;
            case 'modify':
                if(!(\Auth::user()->canModifyRecords(FormController::getForm($fid))))
                    return false;
                break;
            case 'destroy':
                if(!(\Auth::user()->canDestroyRecords(FormController::getForm($fid))))
                    return false;
                break;
            default: // "Read Only"
                return false;
        }

        return true;
    }

    /**
     * Get collective file size of the record files in a form.
     *
     * @param  int $fid - Form ID
     * @return string - File size
     */
    public function getFormFilesize($fid) {
        $form = FormController::getForm($fid);
        $pid = $form->pid;
        $filesize = 0;

        $basedir = config('app.base_path') . "storage/app/files/p".$pid."/f".$fid;
        $filesize += self::dirCrawl($basedir);

        $filesize = self::fileSizeConvert($filesize);

        return $filesize;
    }

    /**
     * Scans a form's file directory to get the total filesize.
     *
     * @param  string $dir - Directory to scan
     * @return int - Size in bytes
     */
    private function dirCrawl($dir) {
        $filesize = 0;

        if(file_exists($dir)) {
            foreach(new \DirectoryIterator($dir) as $file) {
                if($file->isDir() && $file->getFilename() != '.' && $file->getFilename() != '..') {
                    // If the file is a valid directory, call dirCrawl and access its child directory(s)
                    $filesize += self::dirCrawl($file->getPathname());
                } else if($file->isFile()) {
                    // If the file is indeed a file, add its size
                    $filesize += $file->getSize();
                }
            }
        }

        return $filesize;
    }

    /**
     * Converts the directory size in bytes to the most readable form.
     *
     * @param  int $bytes - Size in bytes
     * @return string - The readable size value
     */
    private function fileSizeConvert($bytes) {
        $result = "0 B";
        $bytes = floatval($bytes);
        $arBytes = array(
            0 => array(
                "UNIT" => "TB",
                "VALUE" => pow(1024, 4)
            ),
            1 => array(
                "UNIT" => "GB",
                "VALUE" => pow(1024, 3)
            ),
            2 => array(
                "UNIT" => "MB",
                "VALUE" => pow(1024, 2)
            ),
            3 => array(
                "UNIT" => "KB",
                "VALUE" => 1024
            ),
            4 => array(
                "UNIT" => "B",
                "VALUE" => 1
            ),
        );

        foreach($arBytes as $arItem) {
            if($bytes >= $arItem["VALUE"]) {
                $result = $bytes / $arItem["VALUE"];
                $result = strval(round($result, 2))." ".$arItem["UNIT"];
                break;
            }
        }
        return $result;
    }

    /**
     * Gets the view for mass assigning records.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @return View
     */
    public function showMassAssignmentView($pid,$fid) {
        if(!FormController::validProjForm($pid, $fid))
            return redirect('projects/'.$pid)->with('k3_global_error', 'form_invalid');

        if(!self::checkPermissions($fid, 'modify'))
            return redirect('projects/'.$pid.'/forms/'.$fid)->with('k3_global_error', 'cant_edit_records');

        $form = FormController::getForm($fid);
        $all_fields = $form->fields()->get();
        $fields = new Collection();
        foreach($all_fields as $field) {
            $type = $field->type;
            if($type == "Documents" || $type == "Gallery" || $type == "Playlist" || $type == "3D-Model" || $type == 'Video')
                continue;
            else
                $fields->push($field);
        }
        return view('records.batchAssignment',compact('form','fields','pid','fid'));
    }

    /**
     * Mass assigns a value to a field in all records.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  Request $request
     * @return Redirect
     */
    public function massAssignRecords($pid, $fid, Request $request) {
        if(!self::checkPermissions($fid, 'modify'))
            return redirect('projects/'.$pid.'/forms/'.$fid)->with('k3_global_error', 'cant_edit_records');

        $flid = $request->input("field_selection");
        if(!is_numeric($flid))
            return redirect()->back()->with('k3_global_error', 'field_invalid');

        if($request->has($flid))
            $formFieldValue = $request->input($flid); //Note this only works when there is one form element being submitted, so if you have more, check Date
        else
            return redirect()->back()->with('k3_global_error', 'no_mass_value');

        if($request->has("overwrite"))
            $overwrite = $request->input("overwrite"); //Overwrite field in all records, even if it has data
        else
            $overwrite = 0;

        $field = FieldController::getField($flid);
        $typedField = $field->getTypedField();
        
        foreach(Form::find($fid)->records()->get() as $record) {
            $typedField->massAssignRecordField($field, $record, $formFieldValue, $request, $overwrite);
        }

        return redirect()->action('RecordController@index',compact('pid','fid'))->with('k3_global_success', 'mass_records_updated');
    }

    /**
     * Mass assigns a value to a field in a set of records.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  Request $request
     * @return Redirect
     */
    public function massAssignRecordSet($pid, $fid, Request $request) {
        if(!$this->checkPermissions($fid,'modify')) {
            return redirect()->back();
        }

        $flid = $request->input("field_selection");
        if(!is_numeric($flid)) {
            flash()->overlay(trans('controller_record.notvalid'));
            return redirect()->back();
        }

        if($request->has($flid)) {
            $formFieldValue = $request->input($flid); //Note this only works when there is one form element being submitted, so if you have more, check Date
        } else {
            flash()->overlay(trans('controller_record.provide'),trans('controller_record.whoops'));
            return redirect()->back();
        }

        if ($request->has("overwrite"))
            $overwrite = $request->input("overwrite"); //Overwrite field in all records, even if it has data
        else
            $overwrite = 0;

        $field = FieldController::getField($flid);
        $typedField = $field->getTypedField();

        foreach(Form::find($fid)->records()->whereIn('rid', $request->rids)->get() as $record) {
            $typedField->massAssignRecordField($field, $record, $formFieldValue, $request, $overwrite);
        }

        flash()->overlay(trans('controller_record.recupdate'),trans('controller_record.goodjob'));
        return redirect()->action('RecordController@index',compact('pid','fid'));
    }

    /**
     * Creates several test records in a form for testing purposes.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  Request $request
     * @return Redirect
     */
    public function createTest($pid, $fid, Request $request) {
        $numRecs = $request->test_records_num;

        $form = FormController::getForm($fid);
        $fields = $form->fields()->get();

        for($i = 0; $i < $numRecs ; $i++) {
            $record = new Record();
            $record->pid = $pid;
            $record->fid = $fid;
            $record->owner = Auth::user()->id;
            $record->isTest = 1;
            $record->save(); //need to save to create rid needed to make kid
            $record->kid = $pid . '-' . $fid . '-' . $record->rid;
            $record->save();

            foreach($fields as $field) {
                $field->getTypedField()->createTestRecordField($field, $record);
            }
        }

        return redirect()->action('RecordController@index',compact('pid','fid'))->with('k3_global_success', 'test_records_created');
    }

    /**
     * Delete all test records from a form.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @return JsonResponse
     */
    public function deleteTestRecords($pid, $fid) {
        $form = FormController::getForm($fid);

        if(!\Auth::user()->isFormAdmin($form)) {
            return response()->json(["status"=>false,"message"=>"not_form_admin"],500);
        } else {
            Record::where("fid", "=", $fid)->where("isTest", "=", 1)->delete();

            return response()->json(["status"=>true,"message"=>"test_records_deleted"],200);
        }
    }
}
