<?php namespace App\Http\Controllers;

use App\FieldValuePreset;
use App\Form;
use App\FormGroup;
use App\KoraFields\FileTypeField;
use App\Project;
use App\ProjectGroup;
use App\Record;
use App\RecordPreset;
use Illuminate\Http\Request;
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

                foreach($json as $kid => $record) {
                    $recordObjs[$kid] = $record;
                    foreach(array_keys($record) as $field) {
                        array_push($tagNames, $field);
                    }
                }

                break;
            case self::CSV:
                $csv = parseCSV($request->file('records'));

                foreach($csv as $kid => $record) {
                    $recordObjs[$kid] = $record;
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

            $originKid = $record->attributes()->kid;
            if(!is_null($originKid))
                $recRequest->query->add(['originRid' => explode('-', $originKid)[2]]);

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
                            "record_validation_error"=>[$request->kid => "$matchup[$key] format is incorrect for applying reverse associations"]],500);
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
                        "record_validation_error"=>[$request->kid => "Invalid provided field, $flid"]],500);
                $fieldMod = $form->layout['fields'][$flid];
                $typedField = $form->getFieldModel($fieldMod['type']);
                $recRequest = $typedField->processImportDataXML($flid,$fieldMod,$field,$recRequest);
            }
        } else if($request->type==self::JSON) {
            $originKid = $request->kid;
            if(Record::isKIDPattern($originKid))
                $recRequest->query->add(['originRid' => explode('-', $originKid)[2]]);

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
            $originKid = $request->kid;
            if(Record::isKIDPattern($originKid))
                $recRequest->query->add(['originRid' => explode('-', $originKid)[2]]);

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
     * Downloads the file with all the failed records.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  Request $request
     */
    public function downloadFailedRecords($pid, $fid, Request $request) {
        $failedRecords = json_decode($request->failures);
        $form = FormController::getForm($fid);

        if($request->type=='JSON')
            $records = [];
        else if($request->type=='XML')
            $records = '<?xml version="1.0" encoding="utf-8"?><Records>';
        else if($request->type=='CSV') {
            $keys = [];
            foreach($failedRecords[0][1] as $key => $value) {
                $keys[] = $key;
            }
            $records = implode(',',$keys)."\n";
        }

        foreach($failedRecords as $element) {
            if($request->type=='JSON')
                $records[$element[0]] = $element[1];
            else if($request->type=='XML')
                $records .= $element[1];
            else if($request->type=='CSV') {
                $values = [];
                foreach($failedRecords[0][1] as $key => $value) {
                    //Escape values before we report them back
                    $value = str_replace('"','""',$value);
                    $values[] = '"'.$value.'"';
                }
                $records .= implode(',',$values)."\n";
            }
        }

        if($request->type=='JSON') {
            header("Content-Disposition: attachment; filename=" . $form->name . '_failedImports.json');
            header("Content-Type: application/octet-stream; ");

            echo json_encode($records);
            exit;
        } else if($request->type=='XML') {
            $records .= '</Records>';

            header("Content-Disposition: attachment; filename=" . $form->name . '_failedImports.xml');
            header("Content-Type: application/octet-stream; ");

            echo $records;
            exit;
        } else if($request->type=='CSV') {
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
        $failedRecords = json_decode($request->failures);
        $form = FormController::getForm($fid);

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
                $messages[$id] = "Unable to determine error. This is usually caused by a structure issue in your CSV/XML/JSON, or an unexpected bug in kora.";
            }
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
            $fieldMod->addDatabaseColumn($form->id, $newFlid);
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
     * Import a kora 2 scheme into kora v3.
     *
     * @param  int $pid  - Project ID
     * @param  Request $request
     * @return Redirect
     */
    public function importFormK2($pid, Request $request) {
        $project = ProjectController::getProject($pid);

        if(!\Auth::user()->isProjectAdmin($project))
            return redirect('projects')->with('k3_global_error', 'not_project_admin');

        $file = $request->file('form');
        $scheme = simplexml_load_file($file);
        $fieldNameArrayForRecordInsert = array();

        $fName = $request->name;
        $fDesc = $request->description;

        //init form
        $form = new Form();

        $form->project_id = $pid;
        $form->preset = 0;
        $form->save();

        $layout = [];

        $admin = FormGroup::makeAdminGroup($form, $request);
        FormGroup::makeDefaultGroup($form);
        $form->adminGroup_id = $admin->id;

        //Make the form's records table
        $rTable = new \CreateRecordsTable();
        $rTable->createFormRecordsTable($form->id);

        //do stuff
        foreach($scheme->children() as $category => $value) {
            if($category=='SchemeDesc') {
                $name = $value->Name->__toString();
                if($fName != "")
                    $name = $fName;
                $desc = $value->Description->__toString();
                if($fDesc != "")
                    $desc = $fDesc;

                $form->name = $name;
                $form->internal_name = str_replace(" ","_", $form->name).'_'.$form->project_id.'_'.$form->id.'_';;
                $form->description = $desc;
                $form->save();
            } else if($category=='Collections') {
                foreach($value->children() as $collection) {
                    $page = array();
                    $page['title'] = $collection->Name->__toString();
                    $page['flids'] = array();
                    $layout['pages'][(int)$collection->id] = $page;
                }
            } else if($category=='Controls') {
                foreach($value->children() as $name => $control) {
                    if($name != 'systimestamp' && $name != 'recordowner') {
                        $type = $control->Type->__toString();
                        $collid = (int)$control->CollId;
                        $desc = utf8_encode($control->Description->__toString());
                        $req = (int)$control->Required;
                        $search = (int)$control->Searchable;
                        $advsearch = (int)$control->advSearchable;
                        $showresults = (int)$control->showInResults;
                        $options = $control->options->__toString();

                        ///CHECKS TO CLEAN UP XML ISSUES FROM OLD KORA
                        $options = str_replace(' & ','&amp;',$options);
                        //////////////////////////////////////////////

                        if($options==''){$blankOpts=true;}else{$blankOpts=false;}
                        $optXML = simplexml_load_string($options);
                        $newOpts = '';
                        $newDef = '';
                        $newType = '';

                        switch($type) {
                            case 'TextControl':
                                if(!$blankOpts)
                                    $def = $optXML->defaultValue->__toString();
                                else
                                    $def = null;

                                if(!$blankOpts)
                                    $textType = $optXML->textEditor->__toString();
                                else
                                    $textType = 'plain';

                                if($textType == 'rich') {
                                    $newOpts = '';
                                    $newDef = $def;
                                    $newType = 'Rich Text';
                                } else {
                                    if(!$blankOpts)
                                        $regex = $optXML->regex->__toString();
                                    else
                                        $regex = '';

                                    if(!$blankOpts)
                                        $rows = (int)$optXML->rows;
                                    else
                                        $rows = 1;
                                    $multiline = 0;
                                    if($rows > 1)
                                        $multiline = 1;

                                    $newOpts = ['Regex' => $regex, 'MultiLine' => $multiline];
                                    $newDef = $def;
                                    $newType = 'Text';
                                }
                                break;
                            case 'MultiTextControl':
                                $def = array();
                                if(!$blankOpts && !is_null($optXML->defaultValue->value)) {
                                    foreach($optXML->defaultValue->value as $xmlopt) {
                                        array_push($def, (string)$xmlopt);
                                    }
                                }

                                if(!$blankOpts)
                                    $regex = $optXML->regex->__toString();
                                else
                                    $regex = '';

                                $newOpts = ['Regex' => $regex, 'Options' => $def];
                                $newDef = $def;
                                $newType = 'Generated List';
                                break;
                            case 'DateControl':
                                if(!$blankOpts) {
                                    $startY = (int)$optXML->startYear;
                                    $endY = (int)$optXML->endYear;
                                    $era = $optXML->era->__toString();
                                    $format = $optXML->displayFormat->__toString() == 'Yes' ? 1 : 0;
                                    $defYear = (int)$optXML->defaultValue->year;
                                    $defMon = (int)$optXML->defaultValue->month;
                                    $defDay = (int)$optXML->defaultValue->day;
                                    $prefix = $optXML->prefixes->__toString();
                                } else {
                                    $startY = 1900;
                                    $endY = 2020;
                                    $era = 0;
                                    $format = 'YYYYMMDD';
                                    $defYear = '';
                                    $defMon = '';
                                    $defDay = '';
                                    $prefix = '';
                                }

                                $for = 'YYYYMMDD';
                                if($prefix!='circa' && $prefix!='pre' && $prefix!='post')
                                    $prefix = '';
                                if($format=='MDY') {$for='MMDDYYYY';}
                                else if($format=='DMY') {$for='DDMMYYYY';}
                                else if($format=='YMD') {$for='YYYYMMDD';}

                                $newOpts = [
                                    'ShowPrefix' => $prefix!='' ? 1 : 0,
                                    'ShowEra' => $era,
                                    'Start' => $startY,
                                    'End' => $endY,
                                    'Format' => $for
                                ];
                                $newDef = [
                                    'month' => $defMon,
                                    'day' => $defDay,
                                    'year' => $defYear,
                                    'prefix' => '',
                                    'era' => 'CE'
                                ];
                                $newType = 'Historical Date';
                                break;
                            case 'MultiDateControl': //We convert multi date to a generated list with a date regex
                                $def = array();
                                if(!$blankOpts) {
                                    foreach($optXML->defaultValue as $xmlopt) {
                                        array_push($def, (string)$xmlopt);
                                    }
                                }

                                if(isset($def['date']))
                                    $def = $def['date'];

                                $defOpts = array();
                                if(isset($def[0]) && $def[0] != '') {
                                    $defOpts[] = $def[0]->month . '/' . $def[0]->day . '/' . $def[0]->year;
                                    $size = sizeof($def);
                                    for($i = 1; $i < $size; ++$i) {
                                        $defOpts[] = $def[$i]->month . '/' . $def[$i]->day . '/' . $def[$i]->year;
                                    }
                                }

                                $newOpts = ['Regex' => '/^(((0)[0-9])|((1)[0-2]))(\/)([0-2][0-9]|(3)[0-1])(\/)\d{4}$/', 'Options' => $defOpts];
                                $newDef = $defOpts;
                                $newType = 'Generated List';
                                break;
                            case 'FileControl':
                                if(!$blankOpts)
                                    $maxSize = (int)$optXML->maxSize;
                                else
                                    $maxSize = '';

                                $allowed = array();
                                if(!$blankOpts) {
                                    foreach($optXML->allowedMIME->mime as $xmlopt) {
                                        array_push($allowed, (string)$xmlopt);
                                    }
                                }

                                $newOpts = ['FieldSize' => $maxSize, 'MaxFiles' => '', 'FileTypes' => $allowed];
                                $newType = 'Documents';
                                break;
                            case 'ImageControl':
                                if(!$blankOpts)
                                    $maxSize = (int)$optXML->maxSize;
                                else
                                    $maxSize=0;

                                $allowed = array();
                                if(!$blankOpts) {
                                    foreach($optXML->allowedMIME->mime as $xmlopt) {
                                        array_push($allowed, (string)$xmlopt);
                                    }
                                }
                                if(empty($allowed))
                                    $cleaned = ['image/jpeg','image/gif','image/png'];
                                else
                                    $cleaned = array_intersect($allowed,['image/jpeg','image/gif','image/png']);

                                $thumbW = (int)$optXML->thumbWidth;
                                $thumbH = (int)$optXML->thumbHeight;

                                $newOpts = ['FieldSize' => $maxSize, 'MaxFiles' => '', 'FileTypes' => $cleaned,
                                    'ThumbSmall' => $thumbW.'x'.$thumbH, 'ThumbLarge' => ($thumbW*2).'x'.($thumbH*2)];
                                $newType = 'Gallery';
                                break;
                            case 'ListControl':
                                $opts = array();
                                if(!$blankOpts) {
                                    foreach($optXML->option as $xmlopt) {
                                        array_push($opts, (string)$xmlopt);
                                    }
                                }

                                if(!$blankOpts)
                                    $def = $optXML->defaultValue->__toString();
                                else
                                    $def = null;

                                $newOpts = ['Options' => $opts];
                                $newDef = $def;
                                $newType = 'List';
                                break;
                            case 'MultiListControl':
                                $opts = array();
                                if(!$blankOpts) {
                                    foreach($optXML->option as $xmlopt) {
                                        array_push($opts, (string)$xmlopt);
                                    }
                                }

                                $def = array();
                                if(!$blankOpts && !is_null($optXML->defaultValue->option)) {
                                    foreach($optXML->defaultValue->option as $xmlopt) {
                                        array_push($def, (string)$xmlopt);
                                    }
                                }

                                $newOpts = ['Options' => $opts];
                                $newDef = $def;
                                $newType = 'Multi-Select List';
                                break;
                            case 'AssociatorControl':
                                $newOpts = ['SearchForms' => []];
                                $newType = 'Associator';
                                break;
                        }

                        //Create field array
                        $field = array();
                        $field['type'] = $newType;
                        $field['name'] = preg_replace("/[^A-Za-z0-9 ]/", ' ', $name);
                        $field['alt_name'] = '';
                        $newFlid = str_replace(" ","_", $field['name']).'_'.$form->project_id.'_'.$form->id.'_';

                        //Add it to the appropriate page
                        $layout['pages'][$collid]['flids'][] = $newFlid;

                        //Add the details
                        $field['description'] = $desc;
                        $field['default'] = $newDef;
                        $field['options'] = $newOpts;

                        $field['required'] = (int)$req;
                        $field["viewable"] = 1;
                        $field["searchable"] = (int)$search;
                        $field["external_view"] = 1;
                        $field["advanced_search"] = (int)$advsearch;
                        $field["external_search"] = (int)$search;
                        $field["viewable_in_results"] = (int)$showresults;

                        //Save field
                        $layout['fields'][$newFlid] = $field;
                        $fieldMod = $form->getFieldModel($field['type']);
                        $fieldMod->addDatabaseColumn($form->id, $newFlid, $newOpts);

                        if(in_array($field['type'],Form::$enumFields)) {
                            $crt = new \CreateRecordsTable();
                            $crt->updateEnum($form->id,$newFlid,$field['options']['Options']);
                        }

                        //Used for getting field ID for record creation
                        $fieldNameArrayForRecordInsert[$name] = $newFlid;
                    }
                }
            }
        }

        $form->layout = $layout;
        $form->save();

        //NOW WE LOOK FOR RECORDS
        if(!is_null($request->file('records'))) {
            $file = $request->file('records');
            $records = simplexml_load_file($file);
            $filesProvided = false;
            $zipDir = storage_path('app/tmpFiles/impU'.\Auth::user()->id);
            if(file_exists($zipDir)) {
                //clear import directory
                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($zipDir),
                    \RecursiveIteratorIterator::LEAVES_ONLY
                );
                foreach($files as $impfile) {
                    // Skip directories (they would be added automatically)
                    if(!$impfile->isDir()) {
                        unlink($impfile);
                    }
                }
            } else {
                mkdir($zipDir);
            }

            //if zip file
            if(!is_null($request->file('files'))) {
                $zip = new \ZipArchive();
                $res = $zip->open($request->file('files'));
                if($res) {
                    $filesProvided = true;

                    $zip->extractTo($zipDir.'/');
                    $zip->close();
                }
            }

            foreach($records->Record as $record) {
                $recModel = new Record(array(),$form->id);
                $recModel->project_id = $form->project_id;
                $recModel->form_id = $form->id;
                $recModel->owner = \Auth::user()->id;
                $recModel->save();

                $recModel->kid = $recModel->project_id."-".$recModel->form_id."-".$recModel->id;
                $recModel->save();

                $usedMultiples = array();

                foreach($record->children() as $name => $value) {
                    //for multi style controls, move on if name already user
                    if(in_array($name,$usedMultiples)) {continue;}
                    //ignore standard control types and process
                    if($name != 'systimestamp' && $name != 'recordowner') {
                        $flid = $fieldNameArrayForRecordInsert[$name];
                        $field = $form->layout['fields'][$flid];

                        //We leave this code here (instead of in the Field model) because they are heavily specific to
                        // the conversion of kora 2 data and will probably never change.
                        switch($field['type']) {
                            case 'Text':
                                $value = (string)$value;

                                if($value!="")
                                    $recModel->{$flid} = $value;
                                break;
                            case 'Rich Text':
                                $value = (string)$value;

                                if($value!="")
                                    $recModel->{$flid} = $value;
                                break;
                            case 'Generated List':
                                array_push($usedMultiples,$name);
                                $opts = (array)$record->$name;
                                if(isset($opts[0])) {
                                    $mtc = array();
                                    foreach($opts as $opt) {
                                        array_push($mtc, (string)$opt);
                                    }
                                    $recModel->{$flid} = json_encode($mtc);
                                }
                                break;
                            case 'Historical Date':
                                $prefix = '';
                                if(isset($value->attributes()["prefix"])) {
                                    $recPrefix = $value->attributes()["prefix"];
                                    if($recPrefix == "circa" || $recPrefix == "pre" || $recPrefix == "post")
                                        $prefix = $recPrefix;
                                }
                                $dateStr = (string)$value;
                                if($dateStr!="") {
                                    $dateArray = explode(' ',$dateStr);
                                    if($field['options']['ShowEra'])
                                        $era = $dateArray[1];
                                    else
                                        $era = 'CE';
                                    $dateParts = explode("/",$dateArray[0]);

                                    $monthData = (int)$dateParts[0];
                                    $dayData = (int)$dateParts[1];
                                    $yearData = (int)$dateParts[2];

                                    $date = [
                                        'month' => $monthData,
                                        'day' => $dayData,
                                        'year' => $yearData,
                                        'prefix' => $prefix,
                                        'era' => $era
                                    ];
                                    $recModel->{$flid} = json_encode($date);
                                }
                                break;
                            case 'Documents':
                                //If the user didn't provide files, bounce
                                if(!$filesProvided)
                                    break;

                                $realname='';
                                if(isset($value->attributes()["originalName"]))
                                    $realname = $value->attributes()["originalName"];
                                $localname = (string)$value;
                                //URL for accessing file publically
                                $dataURL = url('files').'/'.$form->project_id . '-' . $form->id . '-' . $recModel->id.'/';

                                if($localname!='') {
                                    $storageType = 'LaravelStorage'; //TODO:: make this a config once we actually support other storage types
                                    switch($storageType) {
                                        case 'LaravelStorage':
                                            //Make folder
                                            $dataPath = $form->project_id . '/f' . $form->id . '/r' . $recModel->id .'/';
                                            $newPath = storage_path('app/files/' . $dataPath);
                                            if(!file_exists($newPath))
                                                mkdir($newPath, 0775, true);

                                            //Hash the file
                                            $checksum = hash_file('sha256', $zipDir.$localname);
                                            //Move file
                                            rename($zipDir.$localname,$newPath.$realname);

                                            //Get file info
                                            $mimes = FileTypeField::getMimeTypes();
                                            $ext = pathinfo($newPath.$realname,PATHINFO_EXTENSION);
                                            if(!array_key_exists($ext, $mimes))
                                                $type = 'application/octet-stream';
                                            else
                                                $type = $mimes[$ext];

                                            $info = ['name' => $realname, 'size' => filesize($newPath.$realname), 'type' => $type,
                                                'url' => $dataURL.urlencode($realname), 'checksum' => $checksum];
                                            break;
                                        default:
                                            break;
                                    }

                                    $recModel->{$flid} = json_encode([$info]);
                                }
                                break;
                            case 'Gallery':
                                //If the user didn't provide files, bounce
                                if(!$filesProvided)
                                    break;

                                $realname='';
                                if(isset($value->attributes()["originalName"]))
                                    $realname = $value->attributes()["originalName"];
                                $localname = (string)$value;
                                //URL for accessing file publically
                                $dataURL = url('files').'/'.$form->project_id . '-' . $form->id . '-' . $recModel->id.'/';

                                if($localname!='') {
                                    $storageType = 'LaravelStorage'; //TODO:: make this a config once we actually support other storage types
                                    switch($storageType) {
                                        case 'LaravelStorage':
                                            //Make folder
                                            $dataPath = $form->project_id . '/f' . $form->id . '/r' . $recModel->id .'/';
                                            $newPath = storage_path('app/files/' . $dataPath);
                                            if(!file_exists($newPath))
                                                mkdir($newPath, 0775, true);

                                            //Hash the file
                                            $checksum = hash_file('sha256', $zipDir.$localname);
                                            //Move file
                                            rename($zipDir.$localname,$newPath.$realname);

                                            //Get file info
                                            $mimes = FileTypeField::getMimeTypes();
                                            $ext = pathinfo($newPath.$realname,PATHINFO_EXTENSION);
                                            if(!array_key_exists($ext, $mimes))
                                                $type = 'application/octet-stream';
                                            else
                                                $type = $mimes[$ext];

                                            $info = ['name' => $realname, 'size' => filesize($newPath.$realname), 'type' => $type,
                                                'url' => $dataURL.urlencode($realname), 'checksum' => $checksum, 'caption' => ''];
                                            break;
                                        default:
                                            break;
                                    }

                                    $recModel->{$flid} = json_encode([$info]);
                                }
                                break;
                            case 'List':
                                $value = (string)$value;

                                if($value!="")
                                    $recModel->{$flid} = $value;
                                break;
                            case 'Multi-Select List':
                                array_push($usedMultiples,$name);
                                $opts = (array)$record->$name;
                                if(isset($opts[0])) {
                                    $msl = array();
                                    foreach($opts as $opt) {
                                        array_push($msl, (string)$opt);
                                    }
                                    $recModel->{$flid} = json_encode($msl);
                                }
                                break;
                        }
                    }
                }

                $recModel->save();
            }
        }

        return redirect('projects/'.$pid)->with('k3_global_success', 'form_created');
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
            $fieldMod->addDatabaseColumn($form->id, $newFlid);
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
