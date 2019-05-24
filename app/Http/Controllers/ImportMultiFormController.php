<?php namespace App\Http\Controllers;

use App\ComboListField;
use App\Field;
use App\FieldHelpers\UploadHandler;
use App\GeolocatorField;
use App\Record;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ImportMultiFormController extends Controller { //TODO::CASTLE

    /*
    |--------------------------------------------------------------------------
    | Import Multi Form Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the import process for importing records into multiple
    | Forms
    |
    */

    /**
     * @var string - Valid formats for record import
     */
    const JSON = "JSON";
    const XML = "XML";
    const CSV = "CSV";

    /**
     * Constructs controller and makes sure user is authenticated.
     */
    public function __construct() {
        $this->middleware('auth');
        $this->middleware('active');
    }

    /**
     * Gets the view for the multi-form record import process.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @return View
     */
    public function index($pid) {
        if(!ProjectController::validProj($pid))
            return redirect('projects')->with('k3_global_error', 'project_invalid');

        $project = ProjectController::getProject($pid);

        if(!\Auth::user()->isProjectAdmin($project))
            return redirect('projects')->with('k3_global_error', 'not_project_admin');

        //Clear import directory
        $dir = storage_path('app/tmpFiles/MFf0u'.\Auth::user()->id);
        if(file_exists($dir)) {
            //clear import directory
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );
            foreach($files as $file) {
                // Skip directories (they would be added automatically)
                if(!$file->isDir())
                    unlink($file);
            }
        } else {
            mkdir($dir, 0775, true);
        }

        $formObjs = $project->forms()->get();
        $forms = [];
        foreach($formObjs as $obj) {
            $forms[$obj->id] = $obj->name;
        }

        return view('projects.importMF',compact('project','forms'));
    }

    /**
     * Saves a temporary version of an uploaded file.
     *
     * @param  Request $request
     */
    public function saveTmpFile() {
        $uid = \Auth::user()->id;

        $options = array();
        $options['fid'] = 0;
        $options['flid'] = 0;
        $options['folder'] = 'MFf0u'.$uid;

        $upload_handler = new UploadHandler($options);
    }

    /**
     * Removes a temporary file for a multi form record import.
     *
     * @param  string $name - Name of the file to delete
     * @param  Request $request
     */
    public function delTmpFile($filename) {
        $uid = \Auth::user()->id;

        $options = array();
        $options['fid'] = 0;
        $options['flid'] = 0;
        $options['filename'] = $filename;
        $options['folder'] = 'MFf0u'.$uid;
        $options['deleteThat'] = true;

        $upload_handler = new UploadHandler($options);
    }

    /**
     * Begin the import process.
     *
     * @param  int $pid - Project ID
     * @param  Request $request
     */
    public function beginImport($pid, Request $request) {
        $project = ProjectController::getProject($pid);

        if(!\Auth::user()->isProjectAdmin($project))
            return redirect('projects')->with('k3_global_error', 'not_project_admin');

        //if zip file
        if(!is_null($request->file('files'))) {
            $zip = new \ZipArchive();
            $res = $zip->open($request->file('files'));
            if($res) {
                $dir = storage_path('app/tmpFiles/impU'.\Auth::user()->id);
                if(file_exists($dir)) {
                    //clear import directory
                    $files = new \RecursiveIteratorIterator(
                        new \RecursiveDirectoryIterator($dir),
                        \RecursiveIteratorIterator::LEAVES_ONLY
                    );
                    foreach($files as $file) {
                        // Skip directories (they would be added automatically)
                        if(!$file->isDir())
                            unlink($file);
                    }
                }
                $zip->extractTo($dir.'/');
                $zip->close();
            }
        }

        //The forms we will import to
        $fids = json_decode($request->importForms);
        $order = json_decode($request->formOrder);
        //The record file for each form
        $recordSets = json_decode($request->records);
        //The type of file for each form
        $fileTypes = json_decode($request->types);

        $response = [];
        if(sizeof($fids) != sizeof($recordSets))
            return response()->json(["status"=>false,"message"=>"file_form_mismatch"],500);

        for($i=0;$i<sizeof($fids);$i++) {
            $data = [];

            $fid = $fids[$order[$i]];
            $records = storage_path('app/tmpFiles/MFf0u'.\Auth::user()->id.'/'.$recordSets[$i]);
            $type = strtoupper($fileTypes[$i]);

            $recordObjs = array();

            switch($type) {
                case self::XML:
                    $xml = simplexml_load_file($records);

                    foreach($xml->children() as $record) {
                        array_push($recordObjs, $record->asXML());
                    }

                    break;
                case self::JSON:
                    $json = json_decode(file_get_contents($records), true);

                    foreach($json as $kid => $record) {
                        $recordObjs[$kid] = $record;
                    }

                    break;
                case self::CSV:
                    $csv = parseCSV($records);

                    foreach($csv as $kid => $record) {
                        $recordObjs[$kid] = $record;
                    }

                    break;
            }

            $data['records'] = $recordObjs;
            $data['type'] = $type;

            $response[$fid] = $data;
        }

        return $response;
    }

    /**
     * Import Kora 3 records via XML of JSON file. We will leave field specific stuff here because it's too specific.
     * There are some things here that are specific to MF record import, specifically associator related stuff.
     *
     * @param  int $pid - Project ID
     * @param  Request $request
     */
    public function importRecord($pid, Request $request) {
        $fid = $request->fid;
        $form = FormController::getForm($fid);

        if(!(\Auth::user()->isFormAdmin($form)))
            return redirect('projects/'.$pid)->with('k3_global_error', 'not_form_admin');

        $record = $request->record;

        $recRequest = new Request();
        $recRequest['userId'] = \Auth::user()->id;
        $recRequest['api'] = true;

        $assocTag = null;
        $assocArray = [];
        $comboAssocArray = [];

        if($request->type==self::XML) {
            $record = simplexml_load_string($record);

            $originKid = $record->attributes()->kid;
            $originRid = null;
            if(!is_null($originKid)) {
                if(Record::isKIDPattern($originKid))
                    $originRid = explode('-', $originKid)[2];

                $assocTag = (string)$originKid;
            }

            foreach($record->children() as $key => $field) {
                //If value is not set, we assume no value so move on
                if($field->count() == 0 && (string)$field == '')
                    continue;

                //Deal with reverse associations and move on
                if($key == 'reverseAssociations') {
                    if(empty($field->Record))
                        return response()->json(["status"=>false,"message"=>"xml_validation_error",
                            "record_validation_error"=>[$request->kid => "$key format is incorrect for applying reverse associations"]],500);
                    $rAssoc = (array)$field->Record;
                    $rFinal = [];
                    foreach($field->Record as $rAssoc) {
                        $rFinal[(string)$rAssoc['flid']][] = (string)$rAssoc;
                    }
                    $recRequest['newRecRevAssoc'] = $rFinal;
                    continue;
                }

                $flid = Field::where('slug', '=', $key)->get()->first()->flid;

                if(!isset($form->layout['fields'][$flid]))
                    return response()->json(["status"=>false,"message"=>"xml_validation_error",
                        "record_validation_error"=>[$request->kid => "Invalid provided field, $flid"]],500);

                $fieldMod = $form->layout['fields'][$flid];
                $typedField = $form->getFieldModel($fieldMod['type']);
                $recRequest = $typedField->processImportDataXML($flid,$fieldMod,$field,$recRequest);
            }
        } else if($request->type==self::JSON | $request->type==self::CSV) {
            $originKid = $request->kid;
            $originRid = null;
            if(!is_null($originKid)) {
                if(Record::isKIDPattern($originKid))
                    $originRid = explode('-', $originKid)[2];

                $assocTag = $originKid;
            }

            foreach($record as $slug => $field) {
                //If value is not set, move on
                if(!$field | is_null($field))
                    continue;

                //Deal with reverse associations and move on
                if($slug == 'reverseAssociations') {
                    $recRequest['newRecRevAssoc'] = $field;
                    continue;
                }

                $flid = $slug;

                // TODO::Matchup field support

                // if(!isset($form->layout['fields'][$flid]))
                //     continue;
                    // return response()->json(["status"=>false,"message"=>"xml_validation_error",
                    //     "record_validation_error"=>[$request->kid => "Invalid provided field, $flid"]],500);

                $fieldMod = $form->layout['fields'][$flid];
                $typedField = $form->getFieldModel($fieldMod['type']);
                $recRequest = $typedField->processImportData($flid,$fieldMod,$field,$recRequest);
            }
        }

        $recCon = new RecordController();
        $result = $recCon->store($pid,$fid,$recRequest);

        $resData = $result->getData(true);
        $result->setData($resData);

        return $result;
    }

    /**
     * After all the records are built, we connect records together via associated fields using the identifier list
     * we've built.
     *
     * @param  int $pid - Project ID
     * @param  Request $request
     */
    public function crossFormAssociations($pid, Request $request) {
        $project = ProjectController::getProject($pid);

        if(!\Auth::user()->isProjectAdmin($project))
            return redirect('projects')->with('k3_global_error', 'not_project_admin');

        $assocTagConvert = json_decode($request->assocTagConvert); //Conversion of record tag identifiers to KIDs
        $crossFormAssoc = json_decode($request->crossFormAssoc); //Actual associator field data to convert
        $comboCrossAssoc = json_decode($request->comboCrossAssoc); //Combo lists with assoc values we need to check

        foreach($crossFormAssoc as $kid => $data) {
            $record = Record::where('kid','=',$kid)->first();

            foreach($data as $flid => $akids) {
                $field = FieldController::getField($flid);

                //Get values
                $values = array();
                foreach($akids as $tag) {
                    array_push($values,$assocTagConvert->{$tag});
                }

                $typedField = $field->getTypedFieldFromRID($record->rid);
                if(!is_null($typedField)) {
                    //add records to existing assoc
                    $typedField->addRecords($values);
                } else {
                    //create a new one for this record
                    $typedField = $field->getTypedField();
                    $typedField->createNewRecordField($field, $record, $values, $request);
                }
            }
        }

        foreach($comboCrossAssoc as $kid => $data) {
            $record = Record::where('kid','=',$kid)->first();

            $filtered = array_unique($data);

            foreach($filtered as $cca) {
                $parts = explode(' ', $cca);
                $flid = $parts[0];
                $subfield = $parts[1];

                $rows = DB::table(ComboListField::SUPPORT_NAME)
                    ->where('rid','=',$record->rid)
                    ->where('flid','=',$flid)
                    ->where('field_num','=',$subfield)->get()->all();

                foreach($rows as $row) {
                    $newVals = array();
                    $vals = explode('[!]',$row->data);

                    foreach($vals as $val) {
                        if(Record::isKIDPattern($val))
                            array_push($newVals,$val);
                        else
                            array_push($newVals,$assocTagConvert->{$val});
                    }

                    DB::table(ComboListField::SUPPORT_NAME)
                        ->where('id','=',$row->id)
                        ->update(['data' => implode('[!]',$newVals)]);
                }
            }
        }
    }

    /**
     * Downloads the file with all the failed records.
     *
     * @param  int $pid - Project ID
     * @param  Request $request
     */
    public function downloadFailedRecords($pid, Request $request) {
        $failedRecords = json_decode($request->failures);
        $project = ProjectController::getProject($pid);

        if($request->type=='JSON' | $request->type=='CSV')
            $records = [];
        else if($request->type=='XML')
            $records = '<?xml version="1.0" encoding="utf-8"?><Records>';

        foreach($failedRecords as $element) {
            if($request->type=='JSON' | $request->type=='CSV')
                $records[$element[0]] = $element[1];
            else if($request->type=='XML')
                $records .= $element[1];
        }

        if($request->type=='JSON'  | $request->type=='CSV') {
            header("Content-Disposition: attachment; filename=" . $project->name . '_failedImports.json');
            header("Content-Type: application/octet-stream; ");

            echo json_encode($records);
            exit;
        }
        else if($request->type=='XML') {
            $records .= '</Records>';

            header("Content-Disposition: attachment; filename=" . $project->name . '_failedImports.xml');
            header("Content-Type: application/octet-stream; ");

            echo $records;
            exit;
        }
    }

    /**
     * Downloads the file with the reasons why records failed.
     *
     * @param  int $pid - Project ID
     * @param  Request $request
     */
    public function downloadFailedReasons($pid, Request $request) {
        $failedRecords = json_decode($request->failures);
        $project = ProjectController::getProject($pid);

        $messages = [];

        foreach($failedRecords as $element) {
            $id = $element[0];
            if(isset($element[2]->responseJSON->record_validation_error)) {
                $messageArray = $element[2]->responseJSON->record_validation_error;
                foreach($messageArray as $message) {
                    if($message != '' && $message != ' ')
                        $messages[$id] = $message;
                }
            } else {
                $messages[$id] = "Unable to determine error. This is usually caused by a structure issue in your XML/JSON, or an unexpected bug in Kora3.";
            }
        }

        header("Content-Disposition: attachment; filename=" . $project->name . '_importExplain.json');
        header("Content-Type: application/octet-stream; ");

        echo json_encode($messages);
        exit;
    }

}
