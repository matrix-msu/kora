<?php namespace App\Http\Controllers;

use App\KoraFields\FileTypeField;
use App\RecordPreset;
use App\Revision;
use App\User;
use App\Record;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
	public function index($pid, $fid, Request $request) {
        if(!FormController::validProjForm($pid, $fid))
            return redirect('projects/'.$pid)->with('k3_global_error', 'form_invalid');

        if(!FieldController::checkPermissions($fid))
            return redirect('/projects/'.$pid)->with('k3_global_error', 'cant_view_form');

        $form = FormController::getForm($fid);

        $pagination = app('request')->input('page-count') === null ? 10 : app('request')->input('page-count');
        $order = app('request')->input('order') === null ? 'lmd' : app('request')->input('order');
        $order_type = substr($order, 0, 2) === "lm" ? "updated_at" : "kid";
        $order_direction = substr($order, 2, 3) === "a" ? "asc" : "desc";
        $recordMod = new Record(array(),$fid);
        $records = $recordMod->newQuery()->orderBy($order_type, $order_direction)->paginate($pagination);

        $total = $recordMod->newQuery()->count();

        $notification = array(
          'message' => '',
          'description' => '',
          'warning' => false,
          'static' => false
        );
        $prevUrlArray = $request->session()->get('_previous');
        $prevUrl = reset($prevUrlArray);
        if ($prevUrl !== url()->current()) {
          $session = $request->session()->get('k3_global_success');

          if ($session == 'record_created')
            $notification['message'] = 'Record Successfully Created!';
          else if ($session == 'record_duplicated')
            $notification['message'] = 'Record Successfully Duplicated!';
          else if ($session == 'mass_records_updated')
            $notification['message'] = 'Batch Assign Successful!';
          else if ($session == 'test_records_created') {
            $numRecs = $request->session()->get('num_test_recs');
            $notification['message'] = $numRecs.' Test Records Created!';
          }
        }

        return view('records.index', compact('form', 'records', 'total', 'notification'));
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

        $form = FormController::getForm($fid);
        if(!$form->hasFields())
            return redirect('projects/'.$pid.'/forms/'.$fid)->with('k3_global_error', 'no_fields_record');

        $form = FormController::getForm($fid);
        $presets = array();

        foreach(RecordPreset::where('form_id', '=', $fid)->get() as $preset) {
            $presets[] = ['id' => $preset->id, 'name' => $preset->preset['name']];
        }

        //Make sure tmp file field folder exists
        $folder = 'recordU'.\Auth::user()->id;
        $dirTmp = storage_path('app/tmpFiles/'.$folder);
        if(file_exists($dirTmp)) {
            foreach(new \DirectoryIterator($dirTmp) as $file) {
                if($file->isFile())
                    unlink($dirTmp.'/'.$file->getFilename());
            }
        } else {
            mkdir($dirTmp,0775,true); //Make it!
            mkdir($dirTmp.'/medium',0775,true); //Make it!
            mkdir($dirTmp.'/thumbnail',0775,true); //Make it!
        }

        return view('records.create', compact('form', 'presets'));
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
	    //These are the values in $request that we can ignore and assume are not field names
	    $form = FormController::getForm($fid);
	    $fieldsArray = $form->layout['fields'];

	    //Validates records
        foreach($fieldsArray as $flid => $field) {
            $message = $form->getFieldModel($field['type'])->validateField($flid, $field, $request);
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
            $record = new Record(array(),$fid);
            $record->project_id = $pid;
            $record->form_id = $fid;
            if($request->assignRoot)
                $record->owner = 1;
            else
                $record->owner = $request->userId;
            $record->save(); //need to save to create id needed to make kid
            $record->kid = $pid . '-' . $fid . '-' . $record->id;

            foreach($request->all() as $key => $value) {
                //Skip request variables that are not fields
                if(!array_key_exists($key,$fieldsArray))
                    continue;

                $field = $fieldsArray[$key];
                $request->rid = $record->id;
                $field['flid'] = $key;
                $processedData = $form->getFieldModel($field['type'])->processRecordData($field, $value, $request);
                $record->{$key} = $processedData;
            }
            // dd($request->all());

            $record->save();

            //Now let's handle reverseAssociations assuming we are coming from the importer or the API //TODO::CASTLE
//            if(isset($request->newRecRevAssoc)) {
//                foreach($request->newRecRevAssoc as $flid => $akids) {
//                    foreach($akids as $akid) {
//                        //NOTE: We do these next two checks so that if we take exported records to a new installation, we don't
//                        // accidentally connect to a record that has nothing to do with us
//                        //Let's make sure the request record exists
//                        if(Record::isKIDPattern($akid) && Record::where('kid','=',$akid)->count()==1) {
//                            $recParts = explode('-', $akid);
//
//                            //Make sure this associator exists first
//                            if(Field::where('flid','=',$flid)->where('fid','=',$recParts[1])->where('type','=','Associator')->count()==0)
//                                continue;
//
//                            //See if the associator field for the reverse record already exists or if we need a new one
//                            $assocField = AssociatorField::where('flid','=',$flid)->where('rid','=',$recParts[2])->first();
//                            if(is_null($assocField)) {
//                                $assocField = new AssociatorField();
//                                $assocField->fid = $recParts[1];
//                                $assocField->flid = $flid;
//                                $assocField->rid = $recParts[2];
//                                $assocField->save();
//                            }
//
//                            $assocField->addRecords(array($record->kid));
//                        }
//                    }
//                }
//            }

            //
            // Only create a revision if the record was not mass created.
            // This prevents clutter from an operation that the user
            // will likely not want to undo using revisions.
            //
            if($numRecs == 1)
                RevisionController::storeRevision($record, Revision::CREATE);

            //If we are making a preset, let's make sure it's done, and done once
            if($makePreset) {
                $makePreset = false; //prevents a preset being made for every duplicate record

                $rpc = new RecordPresetController();
                $presetRequest = new Request();
                $presetRequest->name = $presetName;
                $presetRequest->kid = $record->kid;

                $rpc->presetRecord($presetRequest);
            }
        }

        if($request->api)
            return response()->json(["status"=>true,"message"=>"record_created","kid"=>$record->kid],200);
        else if($request->mass_creation_num > 0)
            return redirect('projects/' . $pid . '/forms/' . $fid . '/records')->with('k3_global_success', 'record_duplicated');
        else
            return redirect('projects/' . $pid . '/forms/' . $fid . '/records')->with('k3_global_success', 'record_created');
	}

    /**
     * Validates a record for creation.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @return JsonResponse
     */
    public function validateRecord($pid, $fid, Request $request) {
        $errors = [];
        $form = FormController::getForm($fid);

        foreach($form->layout['fields'] as $flid => $field) {
            $message = $form->getFieldModel($field['type'])->validateField($flid, $field, $request);
            if(!empty($message))
                $errors += $message; //We add these arrays because it maintains the keys, where array_merge re-indexes
        }

        return response()->json(["status"=>true,"errors"=>$errors],200);
    }

    /**
     * Gets the individual record view.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  int $rid - Record ID
     * @return View
     */
	public function show($pid, $fid, $rid, Request $request) {
        if(!self::validProjFormRecord($pid, $fid, $rid))
            return redirect('projects')->with('k3_global_error', 'record_invalid');

        if(!FieldController::checkPermissions($fid))
            return redirect('/projects/'.$pid)->with('k3_global_error', 'cant_view_form');

        $form = FormController::getForm($fid);
        $kid = "$pid-$fid-$rid";
        $record = self::getRecord($kid);
        $owner = User::where('id', '=', $record->owner)->first();
        $numRevisions = Revision::where('record_kid',$kid)->count();
        $alreadyPreset = (RecordPreset::where('record_kid',$kid)->count() > 0);

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

          if($session == 'record_updated')
            $notification['message'] = 'Record Successfully Updated!';
        }

        return view('records.show', compact('record', 'form', 'owner', 'numRevisions', 'alreadyPreset', 'notification'));
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

        $kid = "$pid-$fid-$rid";
        $record = self::getRecord($kid);

        if(!\Auth::user()->isOwner($record) && !self::checkPermissions($fid, 'modify'))
            return redirect('projects/'.$pid.'/forms/'.$fid)->with('k3_global_error', 'cant_edit_record');

        $form = FormController::getForm($fid);

        //Make sure tmp file field folder exists
        $folder = 'recordU'.\Auth::user()->id;
        $dirTmp = storage_path('app/tmpFiles/'.$folder);
        if(file_exists($dirTmp)) {
            foreach(new \DirectoryIterator($dirTmp) as $file) {
                if($file->isFile())
                    unlink($dirTmp.'/'.$file->getFilename());
            }
        } else {
            mkdir($dirTmp,0775,true); //Make it!
            mkdir($dirTmp.'/medium',0775,true); //Make it!
            mkdir($dirTmp.'/thumbnail',0775,true); //Make it!
        }

        return view('records.edit', compact('record', 'form'));
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
        //These are the values in $request that we can ignore and assume are not field names
        $form = FormController::getForm($fid);
        $fieldsArray = $form->layout['fields'];

        //Validates records
        foreach($fieldsArray as $flid => $field) {
            $message = $form->getFieldModel($field['type'])->validateField($flid, $field, $request);
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

        $kid = "$pid-$fid-$rid";
        $record = self::getRecord($kid);
        $oldRecordCopy = $record->replicate();

        //Before we move files back over from edit, clear the record folder
        $dir = storage_path('app/files/'.$record->project_id.'/'.$record->form_id.'/'.$record->id);
        if(file_exists($dir)) {
            foreach(new \DirectoryIterator($dir) as $file) {
                if($file->isFile())
                    unlink($dir.'/'.$file->getFilename());
            }
        } else {
            mkdir($dir,0775,true); //Make it!
        }

        foreach($request->all() as $key => $value) {
            //Skip request variables that are not fields
            if(!array_key_exists($key,$fieldsArray))
                continue;

            $field = $fieldsArray[$key];
            $field['flid'] = $key;
            $processedData = $form->getFieldModel($field['type'])->processRecordData($field, $value, $request);
            $record->{$key} = $processedData;
        }

        $record->save();

        //Store the edit
        RevisionController::storeRevision($record,Revision::EDIT,$oldRecordCopy);

        //Make new preset
        if($makePreset) {
            $rpc = new RecordPresetController();
            $presetRequest = new Request();
            $presetRequest->name = $presetName;
            $presetRequest->kid = $record->kid;

            $rpc->presetRecord($presetRequest);
        } else {
            //Otherwise, let's update the preset if it exists
            RecordPresetController::updateIfExists($record);
        }

        if(!$request->api)
            return redirect('projects/' . $pid . '/forms/' . $fid . '/records/' . $rid)->with('k3_global_success', 'record_updated');
        else
            return response()->json(["status"=>true,"message"=>"record_updated"],200);
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
        $kid = "$pid-$fid-$rid";
        $record = self::getRecord($kid);

        return view('records.clone', compact('record', 'form'));
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

        $kid = "$pid-$fid-$rid";
        $record = self::getRecord($kid);

        if(!\Auth::user()->isOwner($record) && !self::checkPermissions($fid, 'destroy'))
            return redirect('projects/'.$pid.'/forms/'.$fid)->with('k3_global_error', 'cant_delete_record');

        if(!$mass)
            RevisionController::storeRevision($record, Revision::DELETE);

        $record->delete();

		return redirect('projects/' . $pid . '/forms/' . $fid . '/records')->with('k3_global_success', 'record_deleted');
    }

    /**
     * Delete multiple records from Kora3.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  Request $request
     * @return Redirect
     */
    public function deleteMultipleRecords($pid, $fid, Request $request) {
      $form = FormController::getForm($fid);
      $rid = $request->rid;
      $rids = explode(',', $rid);

      if(!\Auth::user()->isFormAdmin($form)) {
        return redirect('projects')->with('k3_global_error', 'not_form_admin');
      } else {
          $recordMod = new Record(array(),$fid);
          $records = $recordMod->newQuery()->whereIn("id", $rids);

          foreach($records as $record) {
              $record->delete();
          }

        return redirect('projects/' . $pid . '/forms/' . $fid . '/records')->with('k3_global_success', 'multiple_records_deleted');
      }
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
            return redirect('projects')->with('k3_global_error', 'not_form_admin');
        } else {
            $recordMod = new Record(array(),$fid);
            $records = $recordMod->newQuery()->get();
            foreach($records as $rec) {
                $rec->delete();
            }

            return redirect()->action('FormController@show', ['pid' => $pid, 'fid' => $fid])->with('k3_global_success', 'all_record_deleted');
        }
    }

    /**
     * Removes record files from the system for records that no longer exist. This will prevent the possibility of
     *  rolling back these records.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @return array - The records that were removed
     *
     */
    public function cleanUp($pid, $fid) {
        $form = FormController::getForm($fid);

        if(!(\Auth::user()->isFormAdmin($form)))
            return response()->json(["status"=>false,"message"=>"not_form_admin"],500);

        $recMod = new Record(array(),$fid);
        $existingRIDS = $recMod->newQuery()->where('form_id','=',$fid)->pluck('id')->toArray();

        $basePath = storage_path('app/files/'.$pid.'/'.$fid);

        //for each 'r###' directory in $basePath
        foreach(new \DirectoryIterator($basePath) as $rDir) {
            if($rDir->isDot()) continue;

            $rid = $rDir->getFilename();

            //if record does not exist in $existingRIDS
            if(!in_array($rid,$existingRIDS)) {
                //recursively delete record files
                $path = $basePath . $rid;
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

                //prevent rollback revisions for that record if any exist
                Revision::where('record_kid','=',$pid.'-'.$fid.'-'.$rid)->update(['rollback' => 0]);
            }
        }

        return redirect()->action('FormController@show', ['pid' => $pid, 'fid' => $fid])->with('k3_global_success', 'old_records_deleted');
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
     * Get a record back by KID.
     *
     * @param  int $kid - Record Kora ID
     * @return Record - Requested record
     */
    public static function getRecord($kid) {
        $parts = explode('-',$kid);
        $recordMod = new Record(array(),$parts[1]);
        $record = $recordMod->newQuery()->where('kid', '=', $kid)->first();

        return $record;
    }

    /**
     * Determines if record exists.
     *
     * @param  int $rid - Record ID
     * @return bool - Does exist
     */
    public static function exists($kid) {
        $parts = explode('-',$kid);
        $recordMod = new Record(array(),$parts[1]);
        return !is_null($recordMod->newQuery()->where('kid', '=', $kid)->first());
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
        $record = self::getRecord("$pid-$fid-$rid");
        $form = FormController::getForm($fid);
        $proj = ProjectController::getProject($pid);

        if(!FormController::validProjForm($pid, $fid))
            return false;
        if(is_null($record))
            return false;
        else if($record->form_id != $form->id)
            return false;

        return true;
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
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @return string - File size
     */
    public static function getFormFilesize($pid, $fid) {
        $filesize = 0;

        $basedir = storage_path('app/files/'.$pid.'/'.$fid);
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
    private static function dirCrawl($dir) {
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
    private static function fileSizeConvert($bytes) {
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
        $all_fields = $form->layout['fields'];
        $fields = array();
        foreach($all_fields as $flid => $field) {
            //We don't want File Fields to be mass assignable because of the processing expense with large data sets
            if($form->getFieldModel($field['type']) instanceof FileTypeField)
                continue;
            else
                $fields[$flid] = $field;
        }
        return view('records.batchAssignment',compact('form','fields','pid','fid'));
    }

    /**
     * Gets the view for mass assigning a subset of records.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @return View
     */
    public function showSelectedAssignmentView($pid,$fid) {
        if(!FormController::validProjForm($pid, $fid))
            return redirect('projects/'.$pid)->with('k3_global_error', 'form_invalid');

        if(!self::checkPermissions($fid, 'modify'))
            return redirect('projects/'.$pid.'/forms/'.$fid)->with('k3_global_error', 'cant_edit_record');

        $form = FormController::getForm($fid);
        $all_fields = $form->layout['fields'];
        $fields = array();
        foreach($all_fields as $flid => $field) {
            //We don't want File Fields to be mass assignable because of the processing expense with large data sets
            if($form->getFieldModel($field['type']) instanceof FileTypeField)
                continue;
            else
                $fields[$flid] = $field;
        }
        return view('records.batchAssignSelected',compact('form','fields','pid','fid'));
    }

    /**
     * Mass assigns a value to a field in ALL records.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  Request $request
     * @return Redirect
     */
    public function massAssignRecords($pid, $fid, Request $request) {
        if(!self::checkPermissions($fid, 'modify'))
            return redirect('projects/'.$pid.'/forms/'.$fid)->with('k3_global_error', 'cant_edit_records');

        $form = FormController::getForm($fid);
        $flid = $request->field_selection;
        if(!array_key_exists($flid, $form->layout['fields']))
            return redirect()->back()->with('k3_global_error', 'field_invalid');

        if($request->has("overwrite"))
            $overwrite = $request->overwrite; //Overwrite field in all records, even if it has data
        else
            $overwrite = 0;

        $field = $form->layout['fields'][$flid];
        $typedField = $form->getFieldModel($field['type']);
        $formFieldValue = $request->{$flid};

        //A field may not be required for a record but we want to force validation here so we use forceReq
        $message = $typedField->validateField($flid, $field, $request, true);
        if(empty($message)) {
            $typedField->massAssignRecordField($form, $flid, $formFieldValue, $request, $overwrite);

            return redirect()->action('RecordController@index', compact('pid', 'fid'))->with('k3_global_success', 'mass_records_updated');
        } else {
            return redirect()->back()->with('k3_global_error', 'mass_value_invalid');
        }
    }

    /**
     * Validates a mass assign record value.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  Request $request
     * @return JsonResponse
     */
    public function validateMassRecord($pid, $fid, Request $request) {
        if(!FormController::validProjForm($pid, $fid))
            return response()->json(['k3_global_error' => 'form_invalid']);

        $errors = [];

        $form = FormController::getForm($fid);
        $flid = $request->input("field_selection");
        $field = $form->layout['fields'][$flid];

        $message = $form->getFieldModel($field['type'])->validateField($flid, $field, $request);
        if(!empty($message))
            $errors += $message; //We add these arrays because it maintains the keys, where array_merge re-indexes

        return response()->json(["status"=>true,"errors"=>$errors],200);
    }

    /**
     * Mass assigns a value to a field in a set of records.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  Request $request
     * @return Redirect
     */
    public function massAssignRecordSet($pid, $fid, Request $request) { //TODO::CASTLE
        if(!$this->checkPermissions($fid,'modify'))
            return redirect()->back();

        $flid = $request->input("field_selection");
        if(!is_numeric($flid))
            return redirect()->back();

        if($request->has($flid))
            $formFieldValue = $request->input($flid); //Note this only works when there is one form element being submitted, so if you have more, check Date
        else
            return redirect()->back();

        if($request->rids)
            $rids = explode(',', $request->rids);
        else
            $rids = array();

        $field = FieldController::getField($flid);
        $typedField = $field->getTypedField();

        $typedField->massAssignSubsetRecordField($field, $formFieldValue, $request, $rids);

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
        $user = Auth::user();

        for($i = 0; $i < $numRecs ; $i++) {
            $record = new Record(array(),$fid);
            $record->project_id = $pid;
            $record->form_id = $fid;
            $record->owner = $user->id;
            $record->is_test = 1;
            $record->save(); //need to save to create id needed to make kid
            $record->kid = $pid . '-' . $fid . '-' . $record->id;

            foreach($form->layout['fields'] as $flid => $field) {
                $model = $form->getFieldModel($field['type']);
                if($model instanceof FileTypeField) {
                    $url = $pid . '/' . $fid . '/' . $record->id;
                    $record->{$flid} = $model->getTestData($url);
                } else
                    $record->{$flid} = $model->getTestData();
            }

            $record->save();
        }

        return redirect()->action('RecordController@index',compact('pid','fid'))->with('k3_global_success', 'test_records_created')->with('num_test_recs', $numRecs);
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
            return redirect('projects')->with('k3_global_error', 'not_form_admin');
        } else {
            $recordMod = new Record(array(),$fid);
            $recordMod->newQuery()->where("is_test", "=", 1)->delete();

            return redirect()->action('RecordController@index',compact('pid','fid'))->with('k3_global_success', 'test_records_deleted');
        }
    }
}
