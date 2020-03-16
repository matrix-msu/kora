<?php namespace App\Http\Controllers;

use App\Form;
use App\KoraFields\FileTypeField;
use App\KoraFields\HistoricalDateField;
use App\Record;
use App\RecordPreset;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExodusHelperController extends Controller {

    /*
    |--------------------------------------------------------------------------
    | Exodus Helper Controller
    |--------------------------------------------------------------------------
    |
    | Helper functions for migrating controls and record data
    |
    */

    /**
     * @var int - The amount of association controls written to one file
     */
    const EXODUS_CONVERSION_SIZE = 10000;

    /**
     * Constructs controller and makes sure user is the root installation user.
     */
    public function __construct() {
        $this->middleware('auth');
        $this->middleware('active');
        $this->middleware('admin');

        //Custom middleware for handling root user checks
        $this->middleware(function ($request, $next) {
            if (Auth::check())
                if (Auth::user()->id != 1)
                    return redirect("/projects")->with('k3_global_error', 'not_admin')->send();

            return $next($request);
        });
    }

    /**
     * Makes an array for the backup_partial_progress table to insert.
     *
     * @param $name - Name of the table to create the array for, e.g. text_fields
     * @param $form - Form that will be built
     * @param $exodus_id - Progress table id
     * @return array - The array to be inserted into the backup_partial_progress table
     */
    public function makeBackupTableArray($recordCnt, $form, $exodus_id) {
        //need to make sure these tables are not running more than one
        $duplicate = DB::table('exodus_partial')->where('name', $form->internal_name)->where('exodus_id', $exodus_id)->count();

        if($duplicate > 0)
            return false;

        return [
            "name" => $form->internal_name,
            "progress" => 0,
            "total_records" => $recordCnt,
            "exodus_id" => $exodus_id,
            "created_at" => Carbon::now(),
            "updated_at" => Carbon::now()
        ];
    }

    /**
     * Constructs and executes data mirgration.
     *
     * @param  $ogSid - Original id of the scheme
     * @param  $fid - Form ID that will be built
     * @param  $formArray - Array of old sids to new fids
     * @param  $pairArray - Array of old to new pids
     * @param  $dbInfo - Info to connect to db
     * @param  $filePath - Local system path for kora 2 files
     * @param  $exodus_id - Progress table id
     * @param  $userNameArray - Array of old user names to new ids
     * @param  $installURL - The files url of the installation
     */
    public function migrateControlsAndRecords($ogSid, $fid, $formArray, $pairArray, $dbInfo, $filePath, $exodus_id, $userNameArray, $installURL) {
        //connect to db and set up variables
        $con = mysqli_connect($dbInfo['host'],$dbInfo['user'],$dbInfo['pass'],$dbInfo['name']);
        $con->set_charset("utf8");
        $form = FormController::getForm($fid);
        $newForm = $form;
        $oldPid = $pairArray[$ogSid];
        $collToPage = array();
        $oldControlInfo = array();
        $assocControlCheck = array();
        $listOptsEnumArray = array();
        $assocFile = array();
        $numRecords = $con->query('select distinct id from p'.$oldPid.'Data where schemeid='.$ogSid)->num_rows;

        $table_array = $this->makeBackupTableArray($numRecords, $form, $exodus_id);
        if($table_array == false) { return;}
        Log::info('Started creating records for '.$form->internal_name.' (sid: '.$ogSid.').');
        echo "Creating records for ".$form->internal_name." (sid: $ogSid)...\n";

        $row_id = DB::table('exodus_partial')->insertGetId(
            $table_array
        );

        //build nodes based off of collections
        $colls = $con->query('select * from collection where schemeid='.$ogSid.' order by sequence');
        $layout = ['pages' => array(), 'fields' => array()];
        $currPageIndex = 0;
        while($c = $colls->fetch_assoc()) {
            $page = array();
            $page['title'] = $c['name'];
            $page['flids'] = array();
            $layout['pages'][] = $page;

            //Store which index the page is at
            $collToPage[$c['collid']] = $currPageIndex;
            $currPageIndex++;
        }

        //build all the fields for the form
        $controls = $con->query('select * from p'.$oldPid.'Control where schemeid='.$ogSid.' order by sequence');
        while($c = $controls->fetch_assoc()) {
            if($c['name'] != 'systimestamp' && $c['name'] != 'recordowner') {
                $type = $c['type'];
                $collid = $c['collid'];
                $desc = utf8_encode($c['description']);
                $req = $c['required'];
                $search = $c['searchable'];
                $advSearch = $c['advSearchable'];
                $showresults = $c['showInResults'];
                $options = $c['options'];
                ///CHECKS TO CLEAN UP XML ISSUES FROM OLD KORA
                $options = str_replace(' & ','&amp;',$options);
                //////////////////////////////////////////////
                if($options==''){$blankOpts=true;}else{$blankOpts=false;}
                $optXML = simplexml_load_string(utf8_encode($options));
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

                        $def = $this->cleanOpts($def);

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

                        if(self::validateDate($defMon,$defDay,$defYear)) {
                            $newDef = [
                                'month' => $defMon,
                                'day' => $defDay,
                                'year' => $defYear,
                                'prefix' => '',
                                'era' => 'CE'
                            ];
                        } else {
                            $newDef = null;
                        }
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
                        if(isset($def[0])) {
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

                        $opts = $this->cleanOpts($opts);

                        if(!$blankOpts)
                            $def = $optXML->defaultValue->__toString();
                        else
                            $def = null;

                        //Store for later
                        $listOptsEnumArray[$c['cid']] = $opts;

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

                        $opts = $this->cleanOpts($opts);

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
                        $opts = array();
                        if(!$blankOpts) {
                            foreach($optXML->scheme as $xmlopt) {
                                array_push($opts, (string)$xmlopt);
                            }
                        }

                        $assocControlCheck[$c['cid']] = $opts;

                        $newOpts = ['SearchForms' => []];
                        $newType = 'Associator';
                        break;
                }

                //Create field array
                $field = array();
                $field['type'] = $newType;
                $field['name'] = $this->renameFields($c['name']);
                $field['alt_name'] = '';
                $newFlid = str_replace(" ","_", $field['name']).'_'.$newForm->project_id.'_'.$newForm->id.'_';

                //Add it to the appropriate page
                $page_id = $collToPage[$collid];
                $layout['pages'][$page_id]['flids'][] = $newFlid;

                //Add the details
                $field['description'] = $desc;
                $field['default'] = $newDef;
                $field['options'] = $newOpts;

                $field['required'] = (int)$req;
                $field["viewable"] = 1;
                $field["searchable"] = (int)$search;
                $field["external_view"] = 1;
                $field["advanced_search"] = (int)$advSearch;
                $field["external_search"] = (int)$search;
                $field["viewable_in_results"] = (int)$showresults;

                //Save field
                $layout['fields'][$newFlid] = $field;
                $fieldMod = $newForm->getFieldModel($field['type']);
                $fieldMod->addDatabaseColumn($newForm->id, $newFlid, $fieldMod::FIELD_DATABASE_METHOD);

                //Makes legacy file field for
                if($fieldMod instanceof FileTypeField) {
                    $fieldMod->addDatabaseColumn($newForm->id, "legacy_$newFlid", $fieldMod::FIELD_DATABASE_METHOD);
                }

                //Builds out the opts for enum field
                if(in_array($field['type'],Form::$enumFields)) {
                    $crt = new \CreateRecordsTable();
                    $crt->updateEnum($newForm->id,$newFlid,$field['options']['Options']);
                }

                //Used to format later things that reference old control ID
                $oldControlInfo[$c['cid']] = $newFlid;
            }
        }

        //Now that fields are added to layout, save it
        $newForm->layout = $layout;
        $newForm->save();

        //Now that we know the control options for all the associators, and which field ID they correlate to,
        // we will save the associators options
        $tmpLayout = $newForm->layout;
        foreach($assocControlCheck as $cid => $sids) {
            $flid = $oldControlInfo[$cid];

            $opts = ['SearchForms' => []];

            foreach($sids as $sid) {
                if(isset($formArray[$sid])) {
                    $optFID = $formArray[$sid];
                    $optVal = ['form_id'=>$optFID, 'flids'=>[]];
                    $opts['SearchForms'][] = $optVal;
                }
            }

            $tmpLayout['fields'][$flid]['options'] = $opts;
        }
        $newForm->layout = $tmpLayout;
        $newForm->save();

        //We need to make sure there are not missing items in list fields, and that they get added to the enum
        Log::info("Gathering ENUM List Control Data...");
        echo "Gathering ENUM List Control Data...\n";

        $tmpLayout = $newForm->layout;
        $crt = new \CreateRecordsTable();
        foreach($listOptsEnumArray as $cid => $opts) {
            $flid = $oldControlInfo[$cid];

            $listRows = $con->query('select * from p'.$oldPid.'Data where cid='.$cid.' AND schemeid='.$ogSid);

            while($lf = $listRows->fetch_assoc()) {
                $val = $lf['value'];
                if(!in_array(addslashes($val),$opts)) {
                    Log::info("Added list option `".(string)$val."` to field, $flid...");
                    echo "Added list option `".(string)$val."` to field, $flid...\n";
                    $opts[] = addslashes((string)$val);
                }
            }

            $tmpLayout['fields'][$flid]['options']['Options'] = $opts;
            $crt->updateEnum($newForm->id,$flid,$opts);
        }
        $newForm->layout = $tmpLayout;
        $newForm->save();

        //time to build the records
        Log::info('Iterating through data');
        echo "Iterating through form record data...\n";

        //Record stuff//////////////////////////////////////////
        error_reporting(E_ALL);
        ini_set('display_errors', '1');
        ini_set('memory_limit','2G'); //We might be pulling a lot of rows so this is a safety precaution
        $records = $con->query('select D.*, C.name from p'.$oldPid.'Data D left join p'.$oldPid.'Control C on D.cid=C.cid where D.schemeid='.$ogSid);
        $oldKidToNewKid = array();
        $recordDataToSave = array();
        $filePartNum = 1;
        $currRecordIndex = 1;
        $histDateModel = new HistoricalDateField();

        while($r = $records->fetch_assoc()) {
            //Start by making the record if it doesn't exist yet
            if(!array_key_exists($r['id'],$oldKidToNewKid)) {
                //Manually assign the ID so we can build the KID without saving the record
                $recordDataToSave[$r['id']]['id'] = $currRecordIndex;
                $kid = $newForm->project_id . '-' . $newForm->id . '-' . $currRecordIndex;
                $now = Carbon::now();

                $recordDataToSave[$r['id']]['project_id'] = $newForm->project_id;
                $recordDataToSave[$r['id']]['form_id'] = $newForm->id;
                $recordDataToSave[$r['id']]['kid'] = $kid;
                $recordDataToSave[$r['id']]['legacy_kid'] = $r['id'];
                $recordDataToSave[$r['id']]['created_at'] = $now;
                $recordDataToSave[$r['id']]['updated_at'] = $now;

                //Store conversion
                $oldKidToNewKid[$r['id']] = $kid;

                $currRecordIndex++;
            }

            if($r['cid']==0) {
                //This is the reverse association list, so we can bounce
                continue;
            } else if($r['name']=='systimestamp') {
                //we don't want to save the timestamp
                continue;
            } else if($r['name']=='recordowner') {
                if(array_key_exists($r['value'],$userNameArray))
                    $recordDataToSave[$r['id']]['owner'] = $userNameArray[$r['username']];
                else
                    $recordDataToSave[$r['id']]['owner'] = 1;
            } else {
                //make sure the control was converted
                if(!isset($oldControlInfo[$r['cid']])) {continue;}

                //Field info
                $flid = $oldControlInfo[$r['cid']];
                $field = $form->layout['fields'][$flid];
                $value = $r['value'];

                switch($field['type']) {
                    case 'Text':
                        $recordDataToSave[$r['id']][$flid] = $value;
                        break;
                    case 'Rich Text':
                        $recordDataToSave[$r['id']][$flid] = $value;
                        break;
                    case 'Generated List':
                        $mtc = array();
                        foreach(simplexml_load_string($value)->text as $xmlopt) {
                            array_push($mtc, (string)$xmlopt);
                        }
                        $recordDataToSave[$r['id']][$flid] = json_encode($mtc);
                        break;
                    case 'Historical Date':
                        $dateXML = simplexml_load_string($value);
                        $prefix = '';
                        $recPrefix = (string)$dateXML->prefix;
                        if($recPrefix == "circa" || $recPrefix == "pre" || $recPrefix == "post")
                            $prefix = $recPrefix;
                        $era = 'CE';
                        if($field['options']['ShowEra'])
                            $era = (string)$dateXML->era;

                        $monthData = (int)$dateXML->month;
                        $dayData = (int)$dateXML->day;
                        $yearData = (int)$dateXML->year;

                        $date = [
                            'month' => $monthData,
                            'day' => $dayData,
                            'year' => $yearData,
                            'prefix' => $prefix,
                            'era' => $era
                        ];
                        $date['sort'] = $histDateModel->getDateSortValue($date['era'], $date['year'], $date['month'], $date['day']);
                        $recordDataToSave[$r['id']][$flid] = json_encode($date);
                        break;
                    case 'Documents':
                    case 'Gallery':
                        $fileXML = simplexml_load_string($value);
                        $realname = (string)$fileXML->originalName;
                        $newname = $this->renameFiles($realname);
                        $localname = (string)$fileXML->localName;
                        //URL for accessing file publically
                        $dataURL = $installURL.'/'.$newForm->project_id . '-' . $newForm->id . '-' . $recordDataToSave[$r['id']]['id'].'/';

                        if($localname!='') {
                            switch(config('filesystems.kora_storage')) {
                                case FileTypeField::_LaravelStorage:
                                    //Make folder
                                    $dataPath = $newForm->project_id . '/' . $newForm->id . '/' . $recordDataToSave[$r['id']]['id'].'/';
                                    $newPath = storage_path('app/files/' . $dataPath);
                                    if(!file_exists($newPath))
                                        mkdir($newPath, 0775, true);

                                    $oldDir = $filePath.'/'.$oldPid.'/'.$ogSid.'/';

                                    if(!file_exists($oldDir.$localname)) {
                                        //OLD FILE DOESNT EXIST SO BALE
                                        Log::info('File not found: '.$oldDir.$localname);
                                        echo 'File not found: '.$oldDir.$localname,"\n";
                                        continue(2);
                                    }

                                    //Hash the file
                                    $checksum = hash_file('sha256', $oldDir.$localname);
                                    $timestamp = time();
                                    //Move files
                                    copy($oldDir.$localname,$newPath.$timestamp.'.'.$newname);

                                    //Get file info
                                    $mimes = FileTypeField::getMimeTypes();
                                    $ext = pathinfo($newPath.$timestamp.'.'.$newname,PATHINFO_EXTENSION);
                                    if(!array_key_exists($ext, $mimes))
                                        $type = 'application/octet-stream';
                                    else
                                        $type = $mimes[$ext];

                                    $info = ['name' => $newname, 'size' => filesize($newPath.$timestamp.'.'.$newname), 'type' => $type,
                                        'url' => $dataURL.urlencode($newname), 'checksum' => $checksum, 'timestamp' => $timestamp, 'caption' => ''];
                                    break;
                                case FileTypeField::_JoyentManta:
                                    //TODO::MANTA
                                    break;
                                default:
                                    break;
                            }

                            $recordDataToSave[$r['id']][$flid] = json_encode([$info]);
                            $recordDataToSave[$r['id']]["legacy_$flid"] = json_encode([$realname]);
                        }
                        break;
                    case 'List':
                        $recordDataToSave[$r['id']][$flid] = $value;
                        break;
                    case 'Multi-Select List':
                        $mlc = array();
                        foreach(simplexml_load_string($value)->value as $xmlopt) {
                            array_push($mlc, (string)$xmlopt);
                        }

                        $recordDataToSave[$r['id']][$flid] = json_encode($mlc);
                        break;
                    case 'Associator':
                        $kids = array();
                        foreach(simplexml_load_string($value)->kid as $xmlopt) {
                            array_push($kids, (string)$xmlopt);
                        }

                        $recordDataToSave[$r['id']][$flid] = json_encode($kids);

                        //We want to save the Typed Field that will have the data eventually, matched to its values in Kora 2 KID form
                        $assocFile[$r['id']][$flid] = $kids;

                        //This prevents the array from getting too big. We will just create the files in parts
                        if(sizeof($assocFile)>self::EXODUS_CONVERSION_SIZE) {
                            $dataToWrite = json_encode($assocFile);
                            $filename = storage_path(ExodusController::EXODUS_DATA_PATH.'assoc_'.$ogSid.'_'.$filePartNum.'.json');
                            file_put_contents($filename,$dataToWrite);

                            //Reset the variables
                            $filePartNum++;
                            $assocFile = array();
                        }
                        break;
                }
            }
        }

        //save type arrays
        $recQuery = new Record(array(),$newForm->id);
        foreach($recordDataToSave as $legacyKID => $data) {
            $recQuery->newQuery()->insert($data);
            //increment table
            DB::table('exodus_partial')->where('id', $row_id)->increment('progress', 1, ['updated_at' => Carbon::now()]);
        }

        //We want to save the Typed Field that will have the data eventually, matched to its values in Kora 2 KID form
        $dataToWrite = json_encode($assocFile);
        $filename = storage_path(ExodusController::EXODUS_DATA_PATH.'assoc_'.$ogSid.'_'.$filePartNum.'.json');
        file_put_contents($filename,$dataToWrite);

        //We want to save the conversion array of kora 2 KIDs to kora v3 RIDs for this scheme
        $ridChunks = array_chunk($oldKidToNewKid, 500, true);
        $partIndex = 0;
        foreach($ridChunks as $ridc) {
            $dataToWrite = json_encode($ridc);
            $filename = storage_path(ExodusController::EXODUS_CONVERSION_PATH.'kid2_to_kid3_'.$ogSid.'_'.$partIndex.'.json');
            file_put_contents($filename,$dataToWrite);
            $partIndex++;
        }

        unset($ridChunks);

        //Last but not least, record presets!!!!!!!!!
        $recordPresets = $records = $con->query('select * from recordPreset where schemeid='.$ogSid);
        $pc = new RecordPresetController();
        while($rp = $recordPresets->fetch_assoc()) {
            if(isset($oldKidToNewKid[$rp['kid']])) {
                $record = RecordController::getRecord($oldKidToNewKid[$rp['kid']]);
                $preset = new RecordPreset();
                $preset->form_id = $record->form_id;
                $preset->record_kid = $record->kid;

                $preset->preset = $pc->getRecordArray($record, $rp['name']);
                $preset->save();
            }
        }

        //End Record stuff//////////////////////////////////////

        //Breath now
        Log::info('Done creating records for '.$form->internal_name.'.');
        echo "Done creating records for ".$form->internal_name."\n";
        DB::table('exodus_overall')->where('id', $exodus_id)->increment('progress',1,['updated_at'=>Carbon::now()]);

        mysqli_close($con);
    }

    private function renameFields($fieldName) {
        //Remove illegal characters
        $newNameSpaced = preg_replace("/[^A-Za-z0-9 ]/", ' ', $fieldName);
        //Remove multi spaces
        $newName = preg_replace('!\s+!', ' ', $newNameSpaced);
        //Trim and return
        return trim($newName);
    }

    private function renameFiles($fileName) {
        return preg_replace("/[^A-Za-z0-9\_\-\.]/", '', $fileName);
    }

    private function cleanOpts($opts) {
        $filtered = [];
        foreach($opts as $opt) {
            if($opt!='')
                $filtered[] = addslashes($opt);
        }

        return $filtered;
    }

    /**
     * Validates the month, day, year combinations so illegal dates can't happen.
     *
     * @param  int $m - Month
     * @param  int $d - Day
     * @param  int $y - Year
     * @return bool - Is valid
     */
    private static function validateDate($m,$d,$y) {
        //Must have a year
        //No day without a month.
        if(
            ($y=='') | ($d!='' && $m=='')
        ) {
            return false;
        }

        //Next we need to make sure the date provided is legal (i.e. no Feb 30th, etc)
        //For the check we need to default any blank values to 1, cause checkdate doesn't like partial dates
        if($m=='') {$m=1;}
        if($d=='') {$d=1;}

        return checkdate($m, $d, $y);
    }
}
