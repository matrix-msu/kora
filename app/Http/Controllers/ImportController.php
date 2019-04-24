<?php namespace App\Http\Controllers;

use App\Form;
use App\FormGroup;
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
     * Exports a sample file of the structure for importing data.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  string $type - Format type
     * @return string - html for the file download
     */
    public function exportSample($pid, $fid, $type) {
        if(!FormController::validProjForm($pid, $fid))
            return redirect('projects/'.$pid)->with('k3_global_error', 'form_invalid');

        $form = FormController::getForm($fid);

        if(!(\Auth::user()->isFormAdmin($form)))
            return redirect('projects/'.$pid)->with('k3_global_error', 'not_form_admin');

        switch($type) {
            case self::XML:
                $xml = '<?xml version="1.0" encoding="utf-8"?><Records><Record>';

                foreach($form->layout['fields'] as $flid => $field) {
                    $xml .= $form->getFieldModel($field['type'])->getExportSample($flid, self::XML);
                }

                $xml .= '<reverseAssociations><Record flid="1337">1-3-37</Record><Record flid="1337">1-3-37</Record></reverseAssociations>';
                $xml .= '</Record></Records>';

                header("Content-Disposition: attachment; filename=" . $form->name . '_exampleData.xml');
                header("Content-Type: application/octet-stream; ");

                echo $xml;
                exit;
                break;
            case self::JSON:
                $tmpArray = array();

                foreach($form->layout['fields'] as $flid => $field) {
                    $fieldArray = $form->getFieldModel($field['type'])->getExportSample($flid, self::JSON);
                    $tmpArray = array_merge($fieldArray, $tmpArray);
                }

                $tmpArray["reverseAssociations"] = ["1337" => array("1-3-37","1-3-37")];
                $json = [$tmpArray];

                $json = json_encode($json);

                header("Content-Disposition: attachment; filename=" . $form->name . '_exampleData.json');
                header("Content-Type: application/octet-stream; ");

                echo $json;
                exit;
                break;
            // case self::CSV:
                // TODO::CASTLE getExportSample() for all fields
                // $tmpArray = array();

                // foreach($form->layout['fields'] as $flid => $field) {
                //     $csv .= $form->getFieldModel($field['type'])->getExportSample($flid, self::CSV);
                // }

                // $csv .= 'id,reverseAssociations';
                // $csv .= '1337,1-3-37 | 1-3-37';


                // header("Content-Disposition: attachment; filename=" . $form->name . '_exampleData.csv');
                // header("Content-Type: application/octet-stream; ");

                // echo $csv;
                // exit;
                // break;
        }
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

                $tagNames = array_unique($tagNames);
                break;
            case self::JSON:
                $json = json_decode(file_get_contents($request->file('records')), true);

                foreach($json as $kid => $record) {
                    $recordObjs[$kid] = $record;
                    foreach(array_keys($record) as $field) {
                        array_push($tagNames, $field);
                    }
                }

                $tagNames = array_unique($tagNames);
                break;
            case self::CSV:
                $csv = self::parseCSV($request->file('records'));

                foreach($csv as $kid => $record) {
                    $recordObjs[$kid] = $record;
                    foreach(array_keys($record) as $field) {
                        array_push($tagNames, $field);
                    }
                }

                $tagNames = array_unique($tagNames);
                break;
        }

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
                if($flid==$name)
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
     * Import Kora 3 records via XML of JSON file. We will leave field specific stuff here because it's too specific.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  Request $request
     */
    public function importRecord($pid, $fid, Request $request) {
        $form = FormController::getForm($fid);

        if(!(\Auth::user()->isFormAdmin($form)))
            return redirect('projects/'.$pid)->with('k3_global_error', 'not_form_admin');

        $matchup = $request->table;

        $record = $request->record;

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
                        $rFinal[(string)$rAssoc['flid']][] = (string)$rAssoc;
                    }
                    $recRequest['newRecRevAssoc'] = $rFinal;
                    continue;
                }

                $flid = $matchup[$key];
                if(!isset($form->layout['fields'][$flid]))
                    return response()->json(["status"=>false,"message"=>"xml_validation_error",
                        "record_validation_error"=>[$request->kid => "Invalid provided field, $flid"]],500);
                $fieldMod = $form->layout['fields'][$flid];
                $typedField = $form->getFieldModel($fieldMod['type']);
                $simple = !is_null($field->attributes()->simple);
                $recRequest = $typedField->processImportDataXML($flid,$fieldMod,$field,$recRequest,$simple);
            }
        } else if($request->type==self::JSON | $request->type==self::CSV) {
            $originKid = $request->kid;
            if(Record::isKIDPattern($originKid))
                $recRequest->query->add(['originRid' => explode('-', $originKid)[2]]);

            foreach($record as $flid => $field) {
                //Just in case there are extra/unused fields in the JSON
                if(!array_key_exists($flid,$matchup))
                    continue;

                //If value is not set, move on
                if(is_null($field))
                    continue;

                //Deal with reverse associations and move on
                if($matchup[$flid] == 'reverseAssociations') {
                    $recRequest['newRecRevAssoc'] = $field;
                    continue;
                }

                $flid = $matchup[$flid];
                $fieldMod = $form->layout['fields'][$flid];
                $typedField = $form->getFieldModel($fieldMod['type']);
                $recRequest = $typedField->processImportData($flid,$fieldMod,$field,$recRequest);
            }
        }

        $recRequest->query->add(['pid' => $pid, 'fid' => $fid]);
        $recCon = new RecordController();
        return $recCon->store($pid,$fid,$recRequest);
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

        foreach($failedRecords as $element) {
            if($request->type=='JSON')
                $records[$element[0]] = $element[1];
            else if($request->type=='XML')
                $records .= $element[1];
        }

        if($request->type=='JSON') {
            header("Content-Disposition: attachment; filename=" . $form->name . '_failedImports.json');
            header("Content-Type: application/octet-stream; ");

            echo json_encode($records);
            exit;
        }
        else if($request->type=='XML') {
            $records .= '</Records>';

            header("Content-Disposition: attachment; filename=" . $form->name . '_failedImports.xml');
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
                $messages[$id] = "Unable to determine error. This is usually caused by a structure issue in your XML/JSON, or an unexpected bug in Kora3.";
            }
        }

        header("Content-Disposition: attachment; filename=" . $form->name . '_importExplain.json');
        header("Content-Type: application/octet-stream; ");

        echo json_encode($messages);
        exit;
    }

    /**
     * Import a k3Form file into Kora3.
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
     * Import a Kora 2 scheme into Kora3.
     *
     * @param  int $pid  - Project ID
     * @param  Request $request
     * @return Redirect
     */
    public function importFormK2($pid, Request $request) { //TODO::CASTLE
        $project = ProjectController::getProject($pid);

        if(!\Auth::user()->isProjectAdmin($project))
            return redirect('projects')->with('k3_global_error', 'not_project_admin');

        $file = $request->file('form');
        $scheme = simplexml_load_file($file);
        $collToPage = array();
        $fieldNameArrayForRecordInsert = array();

        $fName = $request->name;
        $fSlug = $request->slug;
        $fDesc = $request->description;

        //init form
        $form = new Form();

        $form->pid = $pid;
        $form->preset = 0;
        $form->public_metadata = 0;
        $form->save();

        $admin = FormGroup::makeAdminGroup($form, $request);
        FormGroup::makeDefaultGroup($form);
        $form->adminGID = $admin->id;
        $form->save();

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
                $slug = str_replace(' ','_',$name);
                if($fSlug != "")
                    $slug = $fSlug;
                $z=1;
                while(Form::slugExists($slug)) {
                    $slug .= $z;
                    $z++;
                }
                $form->slug = $slug;
                $form->description = $desc;
                $form->save();
            } else if($category=='Collections') {
                $pIndex = 0;
                foreach($value->children() as $collection) {
                    $page = new Page();
                    $page->fid = $form->fid;
                    $page->title = $collection->Name->__toString();
                    $page->sequence = $pIndex;
                    $pIndex++;

                    $page->save();

                    $collToPage[(int)$collection->id] = $page->id;
                    //Each page needs to keep track of its own sequence for fields
                    $collToPage[(int)$collection->id."_seq"] = 0;
                }
            } else if($category=='Controls') {
                foreach($value->children() as $name => $control) {
                    if($name != 'systimestamp' && $name != 'recordowner') {
                        $type = $control->Type->__toString();
                        $collid = (int)$control->CollId;
                        $desc = $control->Description->__toString();
                        $req = (int)$control->Required;
                        $search = (int)$control->Searchable;
                        $advsearch = (int)$control->advSearchable;
                        $showresults = (int)$control->showInResults;
                        $options = $control->options->__toString();
                        $optXML = simplexml_load_string($options);
                        $newOpts = '';
                        $newDef = '';
                        $newType = '';

                        switch($type) {
                            case 'TextControl':
                                $def = $optXML->defaultValue->__toString();
                                $textType = $optXML->textEditor->__toString();
                                if($textType=='plain' | $textType=='') {
                                    $regex = $optXML->regex->__toString();
                                    $rows = (int)$optXML->rows;
                                    $multiline = 0;
                                    if($rows>1)
                                        $multiline = 1;

                                    $newOpts = "[!Regex!]".$regex."[!Regex!][!MultiLine!]".$multiline."[!MultiLine!]";
                                    $newDef = $def;
                                    $newType = "Text";
                                } else if($textType=='rich') {
                                    $newOpts = "";
                                    $newDef = $def;
                                    $newType = "Rich Text";
                                }
                                break;
                            case 'MultiTextControl':
                                $def = (array)$optXML->defaultValue->value;
                                $defOpts = '';
                                if(isset($def[0])) {
                                    $defOpts = implode("[!]",$def);
                                }
                                $regex = $optXML->regex->__toString();

                                $newOpts = "[!Regex!]".$regex."[!Regex!][!Options!]".$defOpts."[!Options!]";
                                $newDef = $defOpts;
                                $newType = "Generated List";
                                break;
                            case 'DateControl':
                                $startY = (int)$optXML->startYear;
                                $endY = (int)$optXML->endYear;
                                $era = $optXML->era->__toString();
                                $format = $optXML->displayFormat->__toString();
                                $defYear = (int)$optXML->defaultValue->year;
                                $defMon = (int)$optXML->defaultValue->month;
                                $defDay = (int)$optXML->defaultValue->day;
                                $prefix = $optXML->prefixes->__toString();
                                $circa = 'No';
                                $for = 'MMDDYYYY';
                                if($prefix=="circa") {$circa="Yes";}
                                if($format=="MDY") {$for="MMDDYYYY";}
                                else if($format=="DMY") {$for="DDMMYYYY";}
                                else if($format=="YMD") {$for="YYYYMMDD";}

                                $newOpts = "[!Circa!]".$circa."[!Circa!][!Start!]".$startY."[!Start!][!End!]".$endY."[!End!][!Format!]".$for."[!Format!][!Era!]".$era."[!Era!]";
                                $newDef = "[M]".$defMon."[M][D]".$defDay."[D][Y]".$defYear."[Y]";
                                $newType = "Date";
                                break;
                            case 'MultiDateControl':
                                $startY = (int)$optXML->startYear;
                                $endY = (int)$optXML->endYear;
                                $def = (array)$optXML->defaultValue;
                                if(isset($def["date"]))
                                    $def = $def["date"];
                                else
                                    $def=array();
                                $defOpts = '';
                                if(isset($def[0])) {
                                    $defOpts = "Event 1: " . $def[0]->month . "/" . $def[0]->day . "/" . $def[0]->year . " - " . $def[0]->month . "/" . $def[0]->day . "/" . $def[0]->year;
                                    for($i = 1; $i < sizeof($def); $i++) {
                                        $defOpts .= '[!]' . "Event " . ($i + 1) . ": " . $def[$i]->month . "/" . $def[$i]->day . "/" . $def[$i]->year . " - " . $def[$i]->month . "/" . $def[$i]->day . "/" . $def[$i]->year;
                                    }
                                }

                                $newOpts = "[!Start!]".$startY."[!Start!][!End!]".$endY."[!End!][!Calendar!]No[!Calendar!]";
                                $newDef = $defOpts;
                                $newType = "Schedule";
                                break;
                            case 'FileControl':
                                $maxSize = (int)$optXML->maxSize;
                                $allowed = (array)$optXML->allowedMIME->mime;
                                $allOpts = '';
                                if(isset($allowed[0])) {
                                    $allOpts = implode("[!]",$allowed);
                                }

                                $newOpts = "[!FieldSize!]".$maxSize."[!FieldSize!][!MaxFiles!]0[!MaxFiles!][!FileTypes!]".$allOpts."[!FileTypes!]";
                                $newType = "Documents";
                                break;
                            case 'ImageControl':
                                $maxSize = (int)$optXML->maxSize;
                                $allowed = (array)$optXML->allowedMIME->mime;
                                $allOpts = '';
                                if(isset($allowed[0])) {
                                    $allOpts = $allowed[0];
                                    for($i = 1; $i < sizeof($allowed); $i++) {
                                        if ($allowed[$i] != "image/pjpeg" && $allowed[$i] != "image/x-png")
                                            $allOpts .= '[!]' . $allowed[$i];
                                    }
                                }
                                $thumbW = (int)$optXML->thumbWidth;
                                $thumbH = (int)$optXML->thumbHeight;

                                $newOpts = "[!FieldSize!]".$maxSize."[!FieldSize!][!ThumbSmall!]".$thumbW."x".$thumbH."[!ThumbSmall!][!ThumbLarge!]".($thumbW*2)."x".($thumbH*2)."[!ThumbLarge!][!MaxFiles!]0[!MaxFiles!][!FileTypes!]".$allOpts."[!FileTypes!]";
                                $newType = "Gallery";
                                break;
                            case 'ListControl':
                                $opts = (array)$optXML->option;
                                $allOpts = '';
                                if(isset($opts[0])) {
                                    $allOpts = implode("[!]",$opts);
                                }
                                $def = $optXML->defaultValue->__toString();

                                $newOpts = "[!Options!]".$allOpts."[!Options!]";
                                $newDef = $def;
                                $newType = "List";
                                break;
                            case 'MultiListControl':
                                $opts = (array)$optXML->option;
                                $allOpts = '';
                                if(isset($opts[0])) {
                                    $allOpts = implode("[!]",$opts);
                                }
                                $def = (array)$optXML->defaultValue->option;
                                $defOpts = '';
                                if(isset($def[0])) {
                                    $defOpts = implode("[!]",$def);
                                }

                                $newOpts = "[!Options!]".$allOpts."[!Options!]";
                                $newDef = $defOpts;
                                $newType = "Multi-Select List";
                                break;
                            case 'AssociatorControl':
                                $newOpts = "[!SearchForms!][!SearchForms!]";
                                $newType = "Associator";
                                break;
                        }

                        //save it
                        $field = new Field();
                        $field->pid = $form->pid;
                        $field->fid = $form->fid;
                        $field->page_id = $collToPage[$collid];
                        $field->sequence = $collToPage[$collid."_seq"];
                        $collToPage[$collid."_seq"] += 1;
                        $field->type = $newType;
                        $field->name = $name;
                        $slug = str_replace(' ','_',$name).'_'.$form->pid.'_'.$form->fid.'_';
                        $field->slug = $slug;
                        $fieldNameArrayForRecordInsert[$name] = $slug;
                        $field->desc = $desc;
                        $field->required = $req;
                        $field->searchable = $search;
                        $field->advsearch = $advsearch;
                        $field->extsearch = $search;
                        $field->viewable = $showresults;
                        $field->viewresults = $showresults;
                        $field->extview = $showresults;
                        $field->default = $newDef;
                        $field->options = $newOpts;
                        $field->save();
                    }
                }
            }
        }

        //NOW WE LOOK FOR RECORDS
        if(!is_null($request->file('records'))) {
            $file = $request->file('records');
            $records = simplexml_load_file($file);
            $zipDir = storage_path('app/tmpFiles/f'.$form->fid.'u'.\Auth::user()->id.'/');
            $filesProvided = false;

            if(!is_null($request->file('files'))) {
                $filesProvided = true;
                $fileZIP = $request->file('files');

                $zip = new \ZipArchive();
                if($zip->open($fileZIP) === TRUE) {
                    if(mkdir($zipDir)) {
                        $zip->extractTo($zipDir);
                        $zip->close();
                    }
                }
            }

            foreach($records->Record as $record) {
                $recModel = new Record();
                $recModel->pid = $form->pid;
                $recModel->fid = $form->fid;
                $recModel->owner = \Auth::user()->id;
                $recModel->save();

                $recModel->kid = $recModel->pid."-".$recModel->fid."-".$recModel->rid;
                $recModel->save();

                $usedMultiples = array();

                foreach($record->children() as $name => $value) {
                    //for multi style controls, move on if name already user
                    if(in_array($name,$usedMultiples)) {continue;}
                    //ignore standard control types and process
                    if($name != 'systimestamp' && $name != 'recordowner') {
                        $slug = $fieldNameArrayForRecordInsert[$name];
                        $field = Field::where('slug','=',$slug)->get()->first();

                        //We leave this code here (instead of in the Field model) because they are heavily specific to
                        // the conversion of Kora 2 data and will probably never change.
                        //TODO::modular?
                        switch($field->type) {
                            case 'Text':
                                $value = (string)$value;

                                if($value!="") {
                                    $text = new TextField();
                                    $text->rid = $recModel->rid;
                                    $text->fid = $recModel->fid;
                                    $text->flid = $field->flid;
                                    $text->text = $value;
                                    $text->save();
                                }
                                break;
                            case 'Rich Text':
                                $value = (string)$value;

                                if($value!="") {
                                    $rich = new RichTextField();
                                    $rich->rid = $recModel->rid;
                                    $rich->fid = $recModel->fid;
                                    $rich->flid = $field->flid;
                                    $rich->rawtext = $value;
                                    $rich->save();
                                }
                                break;
                            case 'Generated List':
                                array_push($usedMultiples,$name);
                                $opts = (array)$record->$name;
                                if(isset($opts[0])) {
                                    $optStr = implode("[!]",$opts);

                                    $gen = new GeneratedListField();
                                    $gen->rid = $recModel->rid;
                                    $gen->fid = $recModel->fid;
                                    $gen->flid = $field->flid;
                                    $gen->options = $optStr;
                                    $gen->save();
                                }
                                break;
                            case 'Date':
                                $circa=0;
                                if(isset($value->attributes()["prefix"])) {
                                    if($value->attributes()["prefix"] == "circa") {
                                        $circa=1;
                                    }
                                }
                                $dateStr = (string)$value;
                                if($dateStr!="") {
                                    $dateArray = explode(' ',$dateStr);
                                    if(FieldController::getFieldOption($field,'Era')=='Yes')
                                        $era = $dateArray[1];
                                    else
                                        $era = 'CE';
                                    $dateParts = explode("/",$dateArray[0]);

                                    $date = new DateField();
                                    $date->rid = $recModel->rid;
                                    $date->fid = $recModel->fid;
                                    $date->flid = $field->flid;
                                    $date->circa = $circa;
                                    $date->month = $dateParts[0];
                                    $date->day = $dateParts[1];
                                    $date->year = $dateParts[2];
                                    $date->era = $era;
                                    $date->save();
                                }
                                break;
                            case 'Schedule':
                                array_push($usedMultiples,$name);
                                $opts = (array)$record->$name;
                                if(isset($opts[0])) {
                                    //CREATE THE VALUE
                                    $z=1;
                                    $dateStr = explode(' ',$opts[0])[0];
                                    $eventStr = 'Event '.$z.': '.$dateStr.' - '.$dateStr;
                                    $z++;
                                    for($i = 1; $i < sizeof($opts); $i++) {
                                        $dateStr = explode(' ',$opts[$i])[0];
                                        $eventStr .= '[!]Event '.$z.': '.$dateStr.' - '.$dateStr;
                                        $z++;
                                    }

                                    $sched = new ScheduleField();
                                    $sched->rid = $recModel->rid;
                                    $sched->fid = $recModel->fid;
                                    $sched->flid = $field->flid;
                                    $sched->save();

                                    $sched->addEvents(explode("[!]", $eventStr));
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

                                if($localname!='') {
                                    $docs = new DocumentsField();
                                    $docs->rid = $recModel->rid;
                                    $docs->fid = $recModel->fid;
                                    $docs->flid = $field->flid;

                                    //Make folder
                                    $newPath = storage_path('app/files/p' . $form->pid . '/f' . $form->fid . '/r' . $recModel->rid . '/fl' . $field->flid.'/');
                                    mkdir($newPath, 0775, true);

                                    //Move file
                                    rename($zipDir.$localname,$newPath.$realname);

                                    //Get file info
                                    $mimes = FileTypeField::getMimeTypes();
                                    $ext = pathinfo($newPath.$realname,PATHINFO_EXTENSION);
                                    if(!array_key_exists($ext, $mimes))
                                        $type = 'application/octet-stream';
                                    else
                                        $type = $mimes[$ext];

                                    $name = '[Name]'.$realname.'[Name]';
                                    $size = '[Size]'.filesize($newPath.$realname).'[Size]';
                                    $typeS = '[Type]'.$type.'[Type]';
                                    //Build file string
                                    $info = $name.$size.$typeS;
                                    $docs->documents = $info;
                                    $docs->save();
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

                                if($localname!='') {
                                    $gal = new GalleryField();
                                    $gal->rid = $recModel->rid;
                                    $gal->fid = $recModel->fid;
                                    $gal->flid = $field->flid;

                                    //Make folder
                                    $newPath = storage_path('app/files/p' . $form->pid . '/f' . $form->fid . '/r' . $recModel->rid . '/fl' . $field->flid.'/');
                                    $newPathM = $newPath.'medium/';
                                    $newPathT = $newPath.'thumbnail/';
                                    mkdir($newPath, 0775, true);
                                    mkdir($newPathM, 0775, true);
                                    mkdir($newPathT, 0775, true);

                                    //Move files
                                    rename($zipDir.$localname,$newPath.$realname);

                                    //Create thumbs
                                    $smallParts = explode('x',FieldController::getFieldOption($field,'ThumbSmall'));
                                    $largeParts = explode('x',FieldController::getFieldOption($field,'ThumbLarge'));
                                    $tImage = new \Imagick($newPath.$realname);
                                    $mImage = new \Imagick($newPath.$realname);
                                    $tImage->thumbnailImage($smallParts[0],$smallParts[1],true);
                                    $mImage->thumbnailImage($largeParts[0],$largeParts[1],true);
                                    $tImage->writeImage($newPathT.$realname);
                                    $mImage->writeImage($newPathM.$realname);

                                    //Get file info
                                    $mimes = FileTypeField::getMimeTypes();
                                    $ext = pathinfo($newPath.$realname,PATHINFO_EXTENSION);
                                    if(!array_key_exists($ext, $mimes))
                                        $type = 'application/octet-stream';
                                    else
                                        $type = $mimes[$ext];

                                    $name = '[Name]'.$realname.'[Name]';
                                    $size = '[Size]'.filesize($newPath.$realname).'[Size]';
                                    $typeS = '[Type]'.$type.'[Type]';
                                    //Build file string
                                    $info = $name.$size.$typeS;
                                    $gal->images = $info;
                                    $gal->save();
                                }
                                break;
                            case 'List':
                                $value = (string)$value;

                                if($value!="") {
                                    $list = new ListField();
                                    $list->rid = $recModel->rid;
                                    $list->fid = $recModel->fid;
                                    $list->flid = $field->flid;
                                    $list->option = $value;
                                    $list->save();
                                }
                                break;
                            case 'Multi-Select List':
                                array_push($usedMultiples,$name);
                                $opts = (array)$record->$name;
                                if(isset($opts[0])) {
                                    $optStr = implode("[!]",$opts);

                                    $msl = new MultiSelectListField();
                                    $msl->rid = $recModel->rid;
                                    $msl->fid = $recModel->fid;
                                    $msl->flid = $field->flid;
                                    $msl->options = $optStr;
                                    $msl->save();
                                }
                                break;
                        }
                    }
                }
            }

            //clean tmp folder
            if(file_exists($zipDir))
                rmdir($zipDir);
        }

        return redirect('projects/'.$pid)->with('k3_global_success', 'form_created');
    }

    /**
     * Project import uses this to import its forms without the need for a k3Form file.
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
     * Import a k3Proj file into Kora3.
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

//        $optPresets = $fileArray->optPresets; //TODO::CASTLE
//        foreach($optPresets as $opt) {
//            $pre = new OptionPreset();
//
//            $pre->pid = $project->pid;
//            $pre->type = $opt->type;
//            $pre->name = $opt->name;
//            $pre->preset = $opt->preset;
//            $pre->shared = $opt->shared;
//
//            $pre->save();
//        }

        $forms = $fileArray['forms'];
        foreach($forms as $form) {
            $this->importFormNoFile($project->id,$form);
        }

        return redirect('projects')->with('k3_global_success', 'project_imported');
    }

    /**
     * Takes a custom utf8 format file, and converts it into an XML file for import. TODO::Modularize?
     *
     * @param  string $filePath - Path to utf8 file
     * @return string - The system path to the exported file
     */
    public static function utf8ToXML($filePath) { //TODO::CASTLE
        $records = '<?xml version="1.0" encoding="utf-8"?><Records>';

        $handle = fopen($filePath, "r");

        $state = "start";
        $currField = "";
        $currData = array();

        if($handle) {
            echo "Processing file...\n";
            while(($line = fgets($handle)) !== false) {
                $line = trim($line);
                if(mb_substr($line, 0, 8) == "<Record>") {
                    //get kid if applicable
                    $kid = explode('<Record>', $line)[1];

                    if($state == "start")
                        $records .= "<Record kid='$kid'>";
                    else {
                        $processed = self::processUtf8Data($currField, $currData);
                        $currData = array();
                        if(!$processed) {
                            //Data blank so remove start tag for last field, the finish record
                            $records = preg_replace('/<'.$currField.'>$/', '', $records);
                            $records .= "</Record><Record kid='$kid'>";
                        } else {
                            $records .= $processed;
                            $records .= "</$currField></Record><Record kid='$kid'>"; //End last field and record before starting over
                        }
                    }

                    $state = "record";
                } else if(mb_substr($line, 0, 7) == "<field>") {
                    $slug = explode('<field>', $line)[1];

                    if($state == "record") {
                        $records .= "<$slug>";
                    } else {
                        $processed = self::processUtf8Data($currField, $currData);
                        $currData = array();
                        if(!$processed) {
                            //Data blank so remove start tag for last field, then start next field
                            $records = preg_replace('/<'.$currField.'>$/', '', $records);
                            $records .= "<$slug>";
                        } else {
                            $records .= $processed;
                            $records .= "</$currField><$slug>"; //End last field before moving on to next field
                        }
                    }

                    $currField = $slug;

                    $state = "field";
                } else {
                    //We are gathering data
                    $currData[] = trim($line);

                    $state = "data";
                }
            }

            echo "Cleaning up XML...\n";

            switch($state) {
                case "data":
                    $records .= self::processUtf8Data($currField, $currData);
                    $records .= "</$currField></Record>"; //End last field and record
                    break;
                case "field":
                    $records = preg_replace('/<'.$slug.'>$/', '', $records);
                    $records .= "</Record>"; //End last record
                    break;
                default:
                    break;
            }

            $records .= '</Records>';

            //remove bad records
            $records = str_replace("<Record kid=''></Record>","",$records);
            $records = str_replace("<Record kid=''>","<Record>",$records);

            fclose($handle);

            return $records;
        } else {
            return "invalid_file";
        }
    }

    private static function processUtf8Data($slug, $dataArray) { //TODO::CASTLE
        if(sizeof($dataArray)==1)
            $data = $dataArray[0];
        else
            $data = implode(PHP_EOL,$dataArray);

        if($slug=="reverseAssociations")
            $type = "reverseAssociations";
        else {
            $field = FieldController::getField($slug);
            $type = $field->type;
        }

        if($data == "")
            return false;

        switch($type) {
            case Field::_TEXT:
                return htmlspecialchars($data, ENT_XML1, 'UTF-8');
                break;
            case Field::_RICH_TEXT:
                return htmlspecialchars($data, ENT_XML1, 'UTF-8');
                break;
            case Field::_NUMBER:
                return htmlspecialchars((float)$data, ENT_XML1, 'UTF-8');
                break;
            case Field::_LIST:
                return htmlspecialchars($data, ENT_XML1, 'UTF-8');
                break;
            case Field::_MULTI_SELECT_LIST:
                $opts = explode('[!]', $data);
                $fieldxml = '';
                foreach ($opts as $opt) {
                    $fieldxml .= '<value>' . htmlspecialchars($opt, ENT_XML1, 'UTF-8') . '</value>';
                }
                return $fieldxml;
                break;
            case Field::_GENERATED_LIST:
                $opts = explode('[!]', $data);
                $fieldxml = '';
                foreach ($opts as $opt) {
                    $fieldxml .= '<value>' . htmlspecialchars($opt, ENT_XML1, 'UTF-8') . '</value>';
                }
                return $fieldxml;
                break;
            case Field::_COMBO_LIST:
                $rows = explode('[!val!]', $data);
                $fieldxml = '';

                $nameone = Field::xmlTagClear($field['one']['name']);
                $nametwo = Field::xmlTagClear($field['two']['name']);
                $typeone = $field['one']['type'];
                $typetwo = $field['two']['type'];

                foreach($rows as $row) {
                    $rowParts = explode('[!data!]', $row);

                    switch($typeone) {
                        case Field::_MULTI_SELECT_LIST:
                        case Field::_GENERATED_LIST:
                            $valone = '';
                            $vals = explode('[!]', $rowParts[0]);
                            foreach ($vals as $v) {
                                $valone .= '<value>' . htmlspecialchars($v, ENT_XML1, 'UTF-8') . '</value>';
                            }
                            break;
                        case Field::_NUMBER:
                            $valone = htmlspecialchars((float)$rowParts[0], ENT_XML1, 'UTF-8');
                            break;
                        default:
                            $valone = htmlspecialchars($rowParts[0], ENT_XML1, 'UTF-8');
                            break;
                    }

                    switch ($typetwo) {
                        case Field::_MULTI_SELECT_LIST:
                        case Field::_GENERATED_LIST:
                            $valtwo = '';
                            $vals = explode('[!]', $rowParts[1]);
                            foreach ($vals as $v) {
                                $valtwo .= '<value>' . htmlspecialchars($v, ENT_XML1, 'UTF-8') . '</value>';
                            }
                            break;
                        case Field::_NUMBER:
                            $valtwo = htmlspecialchars((float)$rowParts[1], ENT_XML1, 'UTF-8');
                            break;
                        default:
                            $valtwo = htmlspecialchars($rowParts[1], ENT_XML1, 'UTF-8');
                            break;
                    }


                    $fieldxml .= '<Value><' . $nameone . '>' . $valone . '</' . $nameone . '><' . $nametwo . '>' . $valtwo . '</' . $nametwo . '></Value>';
                }

                return $fieldxml;
                break;
            case Field::_DATE:
                $circa = 0; $era = 'CE';
                $m=''; $d=''; $y='';

                $dateData = explode(' ',$data);
                foreach($dateData as $dd) {
                    switch ($dd) {
                        case "circa":
                            $circa = 1;
                            break;
                        case "BCE" | "CE":
                            $era = $dd;
                            break;
                        default:
                            $parts = explode('/',$dd);
                            $m=$parts[0];$d=$parts[1];$y=$parts[2];
                    }
                }

                return '<Circa>' . $circa . '</Circa><Month>' . $m . '</Month><Day>' . $d . '</Day><Year>' . $y . '</Year><Era>' . $era . '</Era>';
                break;
            case Field::_SCHEDULE:
                $events = explode('[!val!]',$data);
                $fieldxml = '';

                foreach($events as $event) {
                    $parts = explode('[!]',$event);

                    $desc = $parts[0];
                    $begin = $parts[1];
                    $end = $parts[2];
                    $allday = 0;

                    if(isset($parts[3])) {
                        $allday = 1;
                        $formatBegin = date("m/d/Y", strtotime($begin));
                        $formatEnd = date("m/d/Y", strtotime($end));
                    } else {
                        $formatBegin = date("m/d/Y h:i A", strtotime($begin));
                        $formatEnd = date("m/d/Y h:i A", strtotime($end));
                    }
                    $fieldxml .= '<Event>';
                    $fieldxml .= '<Title>' . htmlspecialchars($desc, ENT_XML1, 'UTF-8') . '</Title>';
                    $fieldxml .= '<Begin>' . htmlspecialchars($formatBegin, ENT_XML1, 'UTF-8') . '</Begin>';
                    $fieldxml .= '<End>' . htmlspecialchars($formatEnd, ENT_XML1, 'UTF-8') . '</End>';
                    $fieldxml .= '<All_Day>' . htmlspecialchars($allday, ENT_XML1, 'UTF-8') . '</All_Day>';
                    $fieldxml .= '</Event>';
                }

                return $fieldxml;
                break;
            case Field::_GEOLOCATOR:
                $locations = explode('[!val!]',$data);
                $fieldxml = '';

                foreach($locations as $loc) {
                    $parts = explode('[!]',$loc);

                    $desc = $parts[0];
                    $locType = $parts[1];
                    $value = $parts[2];

                    $fieldxml .= '<Location>';
                    $fieldxml .= '<Desc>' . $desc . '</Desc>';

                    switch($locType) {
                        case "latlon":
                            $ll = explode(',', $value);
                            $fieldxml .= '<Lat>' . $ll[0] . '</Lat>';
                            $fieldxml .= '<Lon>' . $ll[1] . '</Lon>';
                        break;
                        case "utm":
                            $utm = explode(':', $value)[0];
                            $fieldxml .= '<Zone>' . $utm[0] . '</Zone>';
                            $fieldxml .= '<East>' . explode(',', $utm[1])[0] . '</East>';
                            $fieldxml .= '<North>' . explode(',', $utm[1])[1] . '</North>';
                            break;
                        case "address":
                            $fieldxml .= '<Address>' . htmlspecialchars($value, ENT_XML1, 'UTF-8') . '</Address>';
                            break;
                    }

                    $fieldxml .= '</Location>';
                }

                return $fieldxml;
                break;
            case Field::_DOCUMENTS:
                $files = explode('[!]', $data);
                $fieldxml = '';
                foreach ($files as $file) {
                    $fieldxml .= '<File><Name>' . htmlspecialchars($file, ENT_XML1, 'UTF-8') . '</Name></File>';
                }
                return $fieldxml;
                break;
            case Field::_GALLERY:
                $files = explode('[!]', $data);
                $fieldxml = '';
                foreach ($files as $file) {
                    $fieldxml .= '<File><Name>' . htmlspecialchars($file, ENT_XML1, 'UTF-8') . '</Name></File>';
                }
                return $fieldxml;
                break;
            case Field::_PLAYLIST:
                $files = explode('[!]', $data);
                $fieldxml = '';
                foreach ($files as $file) {
                    $fieldxml .= '<File><Name>' . htmlspecialchars($file, ENT_XML1, 'UTF-8') . '</Name></File>';
                }
                return $fieldxml;
                break;
            case Field::_VIDEO:
                $files = explode('[!]', $data);
                $fieldxml = '';
                foreach ($files as $file) {
                    $fieldxml .= '<File><Name>' . htmlspecialchars($file, ENT_XML1, 'UTF-8') . '</Name></File>';
                }
                return $fieldxml;
                break;
            case Field::_3D_MODEL:
                $files = explode('[!]', $data);
                $fieldxml = '';
                foreach ($files as $file) {
                    $fieldxml .= '<File><Name>' . htmlspecialchars($file, ENT_XML1, 'UTF-8') . '</Name></File>';
                }
                return $fieldxml;
                break;
            case Field::_ASSOCIATOR:
                $aRecs = explode('[!]', $data);
                return '<Record>' . implode('</Record><Record>', $aRecs) . '</Record>';
                break;
            case "reverseAssociations":
                $aRecs = explode('[!]', $data);
                $fieldxml = '';
                foreach($aRecs as $aRec) {
                    $parts = explode(':', $data);
                    $fieldxml .= "<Record flid='$parts[0]'>$parts[1]</Record>";
                }
                return $fieldxml;
                break;
            default:
                break;
        }

        return '';
    }

    private function parseCSV($record) {
        if (($handle = fopen($record, "r")) !== FALSE) {
            $row = 0;
            $result = $fields = $ids = $data = $records = array();
            while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
                $num = count($data);
                for ($c=0; $c < $num; $c++) {
                    if ($row == 0) {
                        $result[$c] = [];
                        array_push($fields, $data[$c]);
                    } else {
                        if($data[$c]) {
                            if(!in_array($row, $ids))
                                array_push($ids, $row);
                            array_push($result[$c], array($row => $data[$c]));
                        }
                    }
                }
                $row++;
            }
            fclose($handle);

            for ($i=0; $i < count($fields); $i++) {
                if ($result[$i])
                    $data[$fields[$i]] = $result[$i];
            }
            foreach ($ids as $id) {
                $record = array();
                foreach($data as $field => $pairs) {
                    $value = '';
                    foreach($pairs as $pair)
                        if(array_key_exists($id, $pair))
                            $value = $pair[$id];
                    $record[$field] = $value;
                }
                array_push($records, $record);
            }

            return $records;
        }
    }
}
