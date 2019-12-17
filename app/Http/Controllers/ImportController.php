<?php namespace App\Http\Controllers;

use App\FieldValuePreset;
use App\Form;
use App\FormGroup;
use App\KoraFields\ComboListField;
use App\Project;
use App\ProjectGroup;
use App\Record;
use App\RecordPreset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class ImportController extends Controller {

    /*
    |--------------------------------------------------------------------------
    | Import Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles import of Project/Form structures as well as Record
    | data
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
     * Builds the matchup table for comparing imported tag names to actual field names.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  Request $request
     * @return array - Contains html for table as well as list of record objects
     */
    public function matchupFields($pid, $fid, Request $request) {
        // CASTLE::this step needs to delete contents of recordU
        $form = FormController::getForm($fid);

        if(!(\Auth::user()->isFormAdmin($form)))
            return redirect('projects/'.$pid)->with('k3_global_error', 'not_form_admin');

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
                        if(!$file->isDir()) {
                            unlink($file);
                        }
                    }
                }
                $zip->extractTo($dir.'/');
                $zip->close();
            }
        }

        $type = strtoupper($request->type);

        $tagNames = $recordObjs = array();

        switch($type) {
            case self::XML:
                $xml = simplexml_load_file($request->file('records'));

                foreach($xml->children() as $record) {
                    array_push($recordObjs, $record->asXML());
                    foreach($record->children() as $fields) {
                        array_push($tagNames, $fields->getName());
                    }
                }

                break;
            case self::JSON:
                $json = json_decode(file_get_contents($request->file('records')), true);

                foreach($json as $id => $record) {
                    $recordObjs[$id] = $record;
                    foreach(array_keys($record) as $field) {
                        array_push($tagNames, $field);
                    }
                }

                break;
            case self::CSV:
                $csv = parseCSV($request->file('records'));

                foreach($csv as $id => $record) {
                    $recordObjs[$id] = $record;
                    foreach(array_keys($record) as $field) {
                        array_push($tagNames, $field);
                    }
                }

                break;
        }

        $tagNames = array_unique($tagNames);

        $fields = $form->layout['fields'];
        //Build the Labels first
        $table = '';
        $table .= '<div class="form-group mt-xl half">';
        $table .= '<label>Form Field Names</label>';
        $table .= '</div>';
        $table .= '<div class="form-group mt-xl half">';
        $table .= '<label>Select Uploaded Field to Match</label>';
        $table .= '</div>';
        $table .= '<div class="form-group"></div>';

        //Then build the field matchups
        foreach($fields as $flid => $field) {
            $table .= '<div class="form-group mt-xl half">';
            $table .= '<div class="solid-box get-slug-js" slug="'.$flid.'">';
            $table .= $field['name'].' ('.$flid.')';
            $table .= '</div></div>';
            $table .= '<div class="form-group mt-xl half">';
            $table .= '<select class="single-select get-tag-js" data-placeholder="Select field if applicable">';
            $table .= '<option></option>';
            foreach($tagNames as $name) {
                // Matching three different naming conventions
                if(
                    $name==$flid |
                    $name==str_replace(' ', '_', $field['name']) |
                    $name==$field['name']
                )
                    $table .= '<option val="'.$name.'" selected>' . $name . '</option>';
                else
                    $table .= '<option val="'.$name.'">'.$name.'</option>';
            }
            $table .= '</select>';
            $table .= '</div>';
            $table .= '<div class="form-group"></div>';
        }

        //For reverse associations
        $table .= '<div class="form-group mt-xl half">';
        $table .= '<div class="solid-box get-slug-js" slug="reverseAssociations">reverseAssociations</div></div>';
        $table .= '<div class="form-group mt-xl half">';
        $table .= '<select class="single-select get-tag-js" data-placeholder="Select field if applicable">';
        $table .= '<option></option>';
        foreach($tagNames as $name) {
            if($name == "reverseAssociations")
                $table .= '<option val="'.$name.'" selected>' . $name . '</option>';
            else
                $table .= '<option val="'.$name.'">'.$name.'</option>';
        }
        $table .= '</select>';
        $table .= '</div>';
        $table .= '<div class="form-group"></div>';

        //For assoc connections
        $table .= '<div class="form-group mt-xl half">';
        $table .= '<div class="solid-box get-slug-js" slug="kidConnection">kidConnection</div></div>';
        $table .= '<div class="form-group mt-xl half">';
        $table .= '<select class="single-select get-tag-js" data-placeholder="Select field if applicable">';
        $table .= '<option></option>';
        foreach($tagNames as $name) {
            if($name == "kidConnection")
                $table .= '<option val="'.$name.'" selected>' . $name . '</option>';
            else
                $table .= '<option val="'.$name.'">'.$name.'</option>';
        }
        $table .= '</select>';
        $table .= '</div>';
        $table .= '<div class="form-group"></div>';

        //Finish off the table
        $table .= '<div class="form-group mt-xxxl">';
        $table .= '<input type="button" class="btn final-import-btn-js" value="Upload Records">';
        $table .= '</div>';

        $result = array();
        $result['records'] = $recordObjs;
        $result['matchup'] = $table;
        $result['type'] = $type;

        return $result;
    }

    /**
     * Import kora records via XML of JSON file. We will leave field specific stuff here because it's too specific.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  Request $request
     */
    public function importRecord($pid, $fid, Request $request) {
        $form = FormController::getForm($fid);

        if(!(\Auth::user()->isFormAdmin($form)))
            return redirect('projects/'.$pid)->with('k3_global_error', 'not_form_admin');

        $matchup = json_decode($request->table,true);

        $record = json_decode($request->record,true);

        $recRequest = new Request();
        $recRequest['userId'] = \Auth::user()->id;
        $recRequest['api'] = true;

        if($request->type==self::XML) {
            $record = simplexml_load_string($record);

            foreach($record->children() as $key => $field) {
                //Just in case there are extra/unused tags in the XML
                if(!array_key_exists($key,$matchup))
                    continue;

                //If value is not set, we assume no value so move on
                if($field->count() == 0 && (string)$field == '')
                    continue;

                //Deal with reverse associations and move on
                if($matchup[$key] == 'reverseAssociations') {
                    if(empty($field->Record))
                        return response()->json(["status"=>false,"message"=>"xml_validation_error",
                            "record_validation_error"=>[$request->import_id => "$matchup[$key] format is incorrect for applying reverse associations"]],500);
                    $rFinal = [];
                    foreach($field->Record as $rAssoc) {
                        $rFinal[(string)$rAssoc['field']][] = (string)$rAssoc;
                    }
                    $recRequest['newRecRevAssoc'] = $rFinal;
                    continue;
                }

                if($matchup[$key] == 'kidConnection') {
                    $recRequest['kidConnection'] = (string)$field;
                    continue;
                }

                $flid = $matchup[$key];
                if(!isset($form->layout['fields'][$flid]))
                    return response()->json(["status"=>false,"message"=>"xml_validation_error",
                        "record_validation_error"=>[$request->import_id => "Invalid provided field, $flid"]],500);
                $fieldMod = $form->layout['fields'][$flid];
                $typedField = $form->getFieldModel($fieldMod['type']);
                $recRequest = $typedField->processImportDataXML($flid,$fieldMod,$field,$recRequest);
            }
        } else if($request->type==self::JSON) {
            foreach($record as $key => $field) {
                //Just in case there are extra/unused fields in the JSON
                if(!array_key_exists($key,$matchup))
                    continue;

                //If value is not set, move on
                if(!$field | is_null($field))
                    continue;

                //Deal with reverse associations and move on
                if($matchup[$key] == 'reverseAssociations') {
                    $recRequest['newRecRevAssoc'] = $field;
                    continue;
                }

                //kora id connection for associator
                if($matchup[$key] == 'kidConnection') {
                    $recRequest['kidConnection'] = $field;
                    continue;
                }

                $flid = $matchup[$key];
                $fieldMod = $form->layout['fields'][$flid];
                $typedField = $form->getFieldModel($fieldMod['type']);
                $recRequest = $typedField->processImportData($flid,$fieldMod,$field,$recRequest);
            }
        } else if($request->type==self::CSV) {
            foreach($record as $key => $field) {
                //Just in case there are extra/unused fields in the JSON
                if(!array_key_exists($key,$matchup))
                    continue;

                //If value is not set, move on
                if(!$field | is_null($field) | $field=='')
                    continue;

                //Deal with reverse associations and move on
                if($matchup[$key] == 'reverseAssociations') {
                    $rFinal = [];
                    $rAssocs = explode(' | ', $field);
                    foreach($rAssocs as $rAssoc) {
                        $parts = explode(' [KIDS] ', $rAssoc);
                        $aField = $parts[0];
                        $aKIDs = explode(',', $parts[1]);

                        $rFinal[$aField] = $aKIDs;
                    }
                    $recRequest['newRecRevAssoc'] = $rFinal;
                    continue;
                }

                //kora id connection for associator
                if($matchup[$key] == 'kidConnection') {
                    $recRequest['kidConnection'] = $field;
                    continue;
                }

                $flid = $matchup[$key];
                $fieldMod = $form->layout['fields'][$flid];
                $typedField = $form->getFieldModel($fieldMod['type']);
                $recRequest = $typedField->processImportDataCSV($flid,$fieldMod,$field,$recRequest);
            }
        }

        $recRequest->query->add(['pid' => $pid, 'fid' => $fid]);
        $recCon = new RecordController();
        return $recCon->store($pid,$fid,$recRequest);
    }

    public function connectRecords($pid, $fid, Request $request) {
	    ini_set('max_execution_time',0);
        
        $kids = json_decode($request->kids,true);
		$connections = json_decode($request->connections,true);
		$connErrors = [];
        
        $form = FormController::getForm($fid);
        $recModel = new Record(array(),$fid);
        $records = $recModel->newQuery()->whereIn('kid', $kids)->get();

        $fieldsArray = $form->layout['fields'];
		$assocField = array();
        foreach($fieldsArray as $flid => $field) {
            if($field['type'] == Form::_ASSOCIATOR)
                $assocField[] = $flid;
        }
        
        foreach($records as $record) {
        	foreach($assocField as $flid) {
                $assoc = json_decode($record->{$flid});
                if(!is_null($assoc)) {
                    $newAssoc = [];
                    $update = false;
					for($i=0;$i<count($assoc);$i++) {
						$val = $assoc[$i];
                        if(array_key_exists($val, $connections)) { //Connection found
							$update = true;
                            $newAssoc[] = $connections[$val];
                        } else if(Record::isKIDPattern($val)) { //Normal KID value
                            $newAssoc[] = $val;
                        } else //Connection not found
                            $connErrors[] = ['connection' => $val, 'record' => $record->kid, 'field' => $fieldsArray[$flid]['name']];
                    }
                    if($update)
                    	$record->{$flid} = json_encode($newAssoc);
                }
            }
            $record->save();
        }

        return json_encode($connErrors);
    }

    /**
     * Downloads the file with the reasons why records failed.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  Request $request
     */
    public function saveImportFailure($pid, $fid, Request $request) {
        $failedRecord = json_decode($request->failure);

        //Set up and capture the reason
        $userID = Auth::user()->id;
        $referenceID = $failedRecord[0];
        if(isset($failedRecord[2]->responseJSON->record_validation_error)) {
            $messageArray = $failedRecord[2]->responseJSON->record_validation_error;
            foreach($messageArray as $message) {
                if($message != '' && $message != ' ')
                    $errorText = $message;
                else
                    $errorText = "Unable to determine error. This is usually caused by a structure issue in your CSV/XML/JSON, or an unexpected bug in kora.";
            }
        } else {
            $errorText = "Unable to determine error. This is usually caused by a structure issue in your CSV/XML/JSON, or an unexpected bug in kora.";
        }

        //Get the actual record data
        if($request->type==self::JSON | $request->type==self::XML)
            $record = $failedRecord[1];
        else if($request->type==self::CSV) {
            $values = [];
            $keys = [];
            foreach($failedRecord[1] as $key => $value) {
                //Escape values before we report them back
                $value = str_replace('"','""',$value);
                $values[] = '"'.$value.'"';
                $keys[] = $key;
            }
            $record = ['keys'=>implode(',',$keys)."\n", 'value'=>implode(',',$values)."\n"];
        }

        //Save the failed record to DB
        DB::table('failed_records')->insert([
            'user_id' => $userID,
            'reference_id' => $referenceID,
            'form_id' => $fid,
            'error_text' => $errorText,
            'record' => json_encode($record)
        ]);
    }

    /**
     * Downloads the file with all the failed records.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  Request $request
     */
    public function downloadFailedRecords($pid, $fid, Request $request) {
        $failedRecords = DB::table('failed_records')->where('user_id','=',Auth::user()->id)->where('form_id','=',$fid)
            ->orderBy('reference_id','asc')->get();
        $form = FormController::getForm($fid);

        if($request->type==self::JSON)
            $records = [];
        else if($request->type==self::XML)
            $records = '<?xml version="1.0" encoding="utf-8"?><Records>';
        else if($request->type==self::CSV)
            $records = '';

        foreach($failedRecords as $failedRecord) {
            if($request->type==self::JSON)
                $records[] = json_decode($failedRecord->record,true);
            else if($request->type==self::XML)
                $records .= trim($failedRecord->record,'"');
            else if($request->type==self::CSV) {
                //Add key row to the CSV if it hasn't been already
                if($records == '')
                    $records = json_decode($failedRecord->record,true)['keys'];

                $records .= json_decode($failedRecord->record,true)['value'];
            }
        }

        if($request->type==self::JSON) {
            header("Content-Disposition: attachment; filename=" . $form->name . '_failedImports.json');
            header("Content-Type: application/octet-stream; ");

            echo json_encode($records);
            exit;
        } else if($request->type==self::XML) {
            $records .= '</Records>';

            header("Content-Disposition: attachment; filename=" . $form->name . '_failedImports.xml');
            header("Content-Type: application/octet-stream; ");

            echo $records;
            exit;
        } else if($request->type==self::CSV) {
            //Strip off last newline character
            $records = rtrim($records);

            header("Content-Disposition: attachment; filename=" . $form->name . '_failedImports.csv');
            header("Content-Type: application/octet-stream; ");

            echo $records;
            exit;
        }
    }

    /**
     * Downloads the file with the reasons why records failed.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  Request $request
     */
    public function downloadFailedReasons($pid, $fid, Request $request) {
        $failedRecords = DB::table('failed_records')->where('user_id','=',Auth::user()->id)->where('form_id','=',$fid)
            ->orderBy('reference_id','asc')->get();
        $form = FormController::getForm($fid);

        $messages = [];
        foreach($failedRecords as $element) {
            $messages[$element->reference_id] = $element->error_text;
        }

        header("Content-Disposition: attachment; filename=" . $form->name . '_importExplain.json');
        header("Content-Type: application/octet-stream; ");

        echo json_encode($messages);
        exit;
    }

    /**
     * Downloads the file with the kid connections that failed.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  Request $request
     */
    public function downloadFailedConnections($pid, $fid, Request $request) {
        $failedConnections = json_decode($request->failures,true);
        $form = FormController::getForm($fid);

        $messages = [];

        foreach($failedConnections as $element) {
            $conn = $element['connection'];
            $rec = $element['record'];
            $field = $element['field'];
            $messages[] = "The connection name, '$conn', could not be found for record ($rec) in the $field field.";
        }

        header("Content-Disposition: attachment; filename=" . $form->name . '_connectionExplain.json');
        header("Content-Type: application/octet-stream; ");

        echo json_encode($messages);
        exit;
    }

    /**
     * Import a kForm file into kora.
     *
     * @param  int $pid - Project ID
     * @param  Request $request
     * @return Redirect
     */
	public function importForm($pid, Request $request) {
        $project = ProjectController::getProject($pid);

        if(!\Auth::user()->isProjectAdmin($project))
            return redirect('projects')->with('k3_global_error', 'not_project_admin');

        $file = $request->file('form');
        $fName = $request->name;
        $fDesc = $request->description;

        $fileArray = json_decode(file_get_contents($file),true);

        $form = new Form();

        if($fName == "")
            $form->name = $fileArray['name'];
        else
            $form->name = $fName;

        $form->project_id = $pid;

        if($fDesc == "")
            $form->description = $fileArray['description'];
        else
            $form->description = $fDesc;

        $form->preset = $fileArray['preset'];

        $form->save();

        //make admin group
        $adminGroup = FormGroup::makeAdminGroup($form, $request);
        FormGroup::makeDefaultGroup($form);
        $form->adminGroup_id = $adminGroup->id;

        //Save internal name
        $form->internal_name = str_replace(" ","_", $form->name).'_'.$form->project_id.'_'.$form->id.'_';

        //Make the form's records table
        $rTable = new \CreateRecordsTable();
        $rTable->createFormRecordsTable($form->id);

        //field layout stuff
        $flidMapping = array();
        $newFieldsArray = array();
        foreach($fileArray['layout']['fields'] as $flid => $field) {
            //Define new field internal name, add to mapping?
            $newFlid = str_replace(" ","_", $field['name']).'_'.$form->project_id.'_'.$form->id.'_';
            $flidMapping[$flid] = $newFlid;
            $newFieldsArray[$newFlid] = $field;
            //Create column for field in records table
            $fieldMod = $form->getFieldModel($field['type']);

            if($fieldMod instanceof ComboListField) {
                $fieldMod->addDatabaseColumn($form->id, $newFlid, $fieldMod::FIELD_DATABASE_METHOD, [
                    'one' => ['type' => $field['one']['type'], 'name' => $field['one']['name']],
                    'two' => ['type' => $field['two']['type'], 'name' => $field['two']['name']],
                ]);
            } else
                $fieldMod->addDatabaseColumn($form->id, $newFlid, $fieldMod::FIELD_DATABASE_METHOD);
        }

        //Copy page layout, adding new field
        $newPagesArray = array();
        foreach($fileArray['layout']['pages'] as $page) {
            $newPage = ['flids' => [], 'title' => $page['title']];
            foreach($page['flids'] as $flid) {
                $newPage['flids'][] = $flidMapping[$flid];
            }
            $newPagesArray[] = $newPage;
        }

        $form->layout = ['fields' => $newFieldsArray, 'pages' => $newPagesArray];
        $form->save();

        //record presets
        $recPresets = $fileArray['recPresets'];

        foreach($recPresets as $pre) {
            $rec = new RecordPreset();
            $rec->form_id = $form->id;
            $rec->preset = $pre['preset'];
            $rec->save();
        }

        return redirect('projects/'.$pid)->with('k3_global_success', 'form_imported');
    }

    /**
     * Project import uses this to import its forms without the need for a kForm file.
     *
     * @param  int $pid - Project ID
     * @param  array $fileArray - Form structure info
     */
    public function importFormNoFile($pid, $fileArray) {
        $form = new Form();

        $form->name = $fileArray['name'];
        $form->project_id = $pid;
        $form->description = $fileArray['description'];
        $form->preset = $fileArray['preset'];

        $form->save();

        //make admin group
        $adminGroup = FormGroup::makeAdminGroup($form);
        FormGroup::makeDefaultGroup($form);
        $form->adminGroup_id = $adminGroup->id;

        //Save internal name
        $form->internal_name = str_replace(" ","_", $form->name).'_'.$form->project_id.'_'.$form->id.'_';

        //Make the form's records table
        $rTable = new \CreateRecordsTable();
        $rTable->createFormRecordsTable($form->id);

        //field layout stuff
        $flidMapping = array();
        $newFieldsArray = array();
        foreach($fileArray['layout']['fields'] as $flid => $field) {
            //Define new field internal name, add to mapping?
            $newFlid = str_replace(" ","_", $field['name']).'_'.$form->project_id.'_'.$form->id.'_';
            $flidMapping[$flid] = $newFlid;
            $newFieldsArray[$newFlid] = $field;
            //Create column for field in records table
            $fieldMod = $form->getFieldModel($field['type']);

            if($fieldMod instanceof ComboListField) {
                $fieldMod->addDatabaseColumn($form->id, $newFlid, $fieldMod::FIELD_DATABASE_METHOD, [
                    'one' => ['type' => $field['one']['type'], 'name' => $field['one']['name']],
                    'two' => ['type' => $field['two']['type'], 'name' => $field['two']['name']],
                ]);
            } else
                $fieldMod->addDatabaseColumn($form->id, $newFlid, $fieldMod::FIELD_DATABASE_METHOD);
        }

        //Copy page layout, adding new field
        $newPagesArray = array();
        foreach($fileArray['layout']['pages'] as $page) {
            $newPage = ['flids' => [], 'title' => $page['title']];
            foreach($page['flids'] as $flid) {
                $newPage['flids'][] = $flidMapping[$flid];
            }
            $newPagesArray[] = $newPage;
        }

        $form->layout = ['fields' => $newFieldsArray, 'pages' => $newPagesArray];
        $form->save();

        //record presets
        $recPresets = $fileArray['recPresets'];

        foreach($recPresets as $pre) {
            $rec = new RecordPreset();
            $rec->form_id = $form->id;
            $rec->preset = $pre['preset'];
            $rec->save();
        }
    }



    /**
     * Import a kProj file into kora.
     *
     * @param  Request $request
     * @return Redirect
     */
    public function importProject(Request $request) {
        if(!\Auth::user()->admin)
            return redirect('projects/')->with('k3_global_error', 'not_admin');

        $file = $request->file('project');
        $pName = $request->name;
        $pDesc = $request->description;

        $fileArray = json_decode(file_get_contents($file),true);

        $project = new Project();

        if($pName == "")
            $project->name = $fileArray['name'];
        else
            $project->name = $pName;

        if($pDesc == "")
            $project->description = $fileArray['description'];
        else
            $project->description = $pDesc;

        $project->active = 1;

        $project->save();

        //make admin group
        $adminGroup = ProjectGroup::makeAdminGroup($project, $request);
        ProjectGroup::makeDefaultGroup($project);
        $project->adminGroup_id = $adminGroup->id;

        $project->internal_name = str_replace(" ","_", $project->name).'_'.$project->id.'_';

        $project->save();

        $fieldValuePresets = $fileArray['fieldValuePresets'];
        foreach($fieldValuePresets as $opt) {
            $preset = ["name" => $opt['name'],"type"=>$opt['type'],"preset"=>$opt['preset']];
            FieldValuePreset::create(['project_id' => $project->id, 'preset' => $preset, 'shared' => $opt['shared']]);
        }

        $forms = $fileArray['forms'];
        foreach($forms as $form) {
            $this->importFormNoFile($project->id,$form);
        }

        return redirect('projects')->with('k3_global_success', 'project_imported');
    }
}
