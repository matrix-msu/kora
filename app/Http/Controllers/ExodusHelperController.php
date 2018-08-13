<?php namespace App\Http\Controllers;

use App\Field;
use App\FileTypeField;
use App\Form;
use App\Metadata;
use App\Page;
use App\Record;
use App\RecordPreset;
use App\User;
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
        $duplicate = DB::table('exodus_partial_progress')->where('name', $form->slug)->where('exodus_id', $exodus_id)->count();

        if($duplicate>0)
            return false;

        return [
            "name" => $form->slug,
            "progress" => 0,
            "overall" => $recordCnt,
            "exodus_id" => $exodus_id,
            "start" => Carbon::now(),
            "created_at" => Carbon::now(),
            "updated_at" => Carbon::now()
        ];
    }

    /**
     * Constructs and executes data mirgration.
     *
     * @param $ogSid - Original id of the scheme
     * @param $fid - Form ID that will be built
     * @param $formArray - Array of old sids to new fids
     * @param $pairArray - Array of old to new pids
     * @param $dbInfo - Info to connect to db
     * @param $filePath - Local system path for kora 2 files
     * @param $exodus_id - Progress table id
     */
    public function migrateControlsAndRecords($ogSid, $fid, $formArray, $pairArray, $dbInfo, $filePath, $exodus_id) {
        //connect to db and set up variables
        $con = mysqli_connect($dbInfo['host'],$dbInfo['user'],$dbInfo['pass'],$dbInfo['name']);
        $form = FormController::getForm($fid);
        $newForm = $form;
        $oldPid = $pairArray[$ogSid];
        $collToPage = array();
        $oldControlInfo = array();
        $assocControlCheck = array();
        $assocFile = array();
        $numRecords = $con->query('select distinct id from p'.$oldPid.'Data where schemeid='.$ogSid)->num_rows;

        $table_array = $this->makeBackupTableArray($numRecords, $form, $exodus_id);
        if($table_array == false) { return;}
        Log::info('Started creating records for '.$form->slug.' (sid: '.$ogSid.').');

        $row_id = DB::table('exodus_partial_progress')->insertGetId(
            $table_array
        );

        //build nodes based off of collections
        $colls = $con->query('select * from collection where schemeid='.$ogSid.' order by sequence');
        $pIndex = 0;
        while($c = $colls->fetch_assoc()) {
            $page = new Page();
            $page->fid = $newForm->fid;
            $page->title = $c['name'];
            $page->sequence = $pIndex;
            ++$pIndex;

            $page->save();

            $collToPage[$c['collid']] = $page->id;
            //Each page needs to keep track of its own sequence for fields
            $collToPage[$c['collid'].'_seq'] = 0;
        }

        //build all the fields for the form
        $controls = $con->query('select * from p'.$oldPid.'Control where schemeid='.$ogSid.' order by sequence');
        while($c = $controls->fetch_assoc()) {
            if($c['name'] != 'systimestamp' && $c['name'] != 'recordowner') {
                $type = $c['type'];
                $collid = $c['collid'];
                $desc = $c['description'];
                $req = $c['required'];
                $search = $c['searchable'];
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
                            $def = '';
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

                            $newOpts = '[!Regex!]' . $regex . '[!Regex!][!MultiLine!]' . $multiline . '[!MultiLine!]';
                            $newDef = $def;
                            $newType = 'Text';
                        }
                        break;
                    case 'MultiTextControl':
                        if(!$blankOpts)
                            $def = (array)$optXML->defaultValue->value;
                        else
                            $def = array();
                        $defOpts = '';
                        if(isset($def[0])) {
                            $defOpts = $def[0];
                            $size = sizeof($def);
                            for($i = 1; $i < $size; ++$i) {
                                $defOpts .= '[!]' . $def[$i];
                            }
                        }
                        if(!$blankOpts)
                            $regex = $optXML->regex->__toString();
                        else
                            $regex = '';

                        $newOpts = '[!Regex!]' . $regex . '[!Regex!][!Options!]' . $defOpts . '[!Options!]';
                        $newDef = $defOpts;
                        $newType = 'Generated List';
                        break;
                    case 'DateControl':
                        if(!$blankOpts) {
                            $startY = (int)$optXML->startYear;
                            $endY = (int)$optXML->endYear;
                            $era = $optXML->era->__toString();
                            $format = $optXML->displayFormat->__toString();
                            $defYear = (int)$optXML->defaultValue->year;
                            $defMon = (int)$optXML->defaultValue->month;
                            $defDay = (int)$optXML->defaultValue->day;
                            $prefix = $optXML->prefixes->__toString();
                        } else {
                            $startY = 1900;
                            $endY = 2020;
                            $era = 'No';
                            $format = 'MMDDYYYY';
                            $defYear = '';
                            $defMon = '';
                            $defDay = '';
                            $prefix = 'No';
                        }
                        $circa = 'No';
                        $for = 'MMDDYYYY';
                        if($prefix=='circa') {$circa='Yes';}
                        if($format=='MDY') {$for='MMDDYYYY';}
                        else if($format=='DMY') {$for='DDMMYYYY';}
                        else if($format=='YMD') {$for='YYYYMMDD';}

                        $newOpts = '[!Circa!]'.$circa.'[!Circa!][!Start!]'.$startY.'[!Start!][!End!]'.$endY.'[!End!][!Format!]'.$for.'[!Format!][!Era!]'.$era.'[!Era!]';
                        $newDef = '[M]'.$defMon.'[M][D]'.$defDay.'[D][Y]'.$defYear.'[Y]';
                        $newType = 'Date';
                        break;
                    case 'MultiDateControl':
                        if(!$blankOpts) {
                            $startY = (int)$optXML->startYear;
                            $endY = (int)$optXML->endYear;
                            $def = (array)$optXML->defaultValue;
                        } else {
                            $startY = 1990;
                            $endY = 2020;
                            $def = array();
                        }

                        if(isset($def['date']))
                            $def = $def['date'];
                        else
                            $def=array();

                        $defOpts = '';
                        if(isset($def[0])) {
                            $defOpts = 'Event 1: ' . $def[0]->month . '/' . $def[0]->day . '/' . $def[0]->year . ' - ' . $def[0]->month . '/' . $def[0]->day . '/' . $def[0]->year;
                            $size = sizeof($def);
                            for($i = 1; $i < $size; ++$i) {
                                $defOpts .= '[!]' . 'Event ' . ($i + 1) . ': ' . $def[$i]->month . '/' . $def[$i]->day . '/' . $def[$i]->year . ' - ' . $def[$i]->month . '/' . $def[$i]->day . '/' . $def[$i]->year;
                            }
                        }

                        $newOpts = '[!Start!]'.$startY.'[!Start!][!End!]'.$endY.'[!End!][!Calendar!]No[!Calendar!]';
                        $newDef = $defOpts;
                        $newType = 'Schedule';
                        break;
                    case 'FileControl':
                        if(!$blankOpts)
                            $maxSize = (int)$optXML->maxSize;
                        else
                            $maxSize=0;
                        if(!$blankOpts)
                            $allowed = (array)$optXML->allowedMIME->mime;
                        else
                            $allowed=array();
                        $allOpts = '';
                        if(isset($allowed[0])) {
                            $allOpts = $allowed[0];
                            $size = sizeof($allowed);
                            for($i = 1; $i < $size; ++$i) {
                                $allOpts .= '[!]' . $allowed[$i];
                            }
                        }

                        $newOpts = '[!FieldSize!]'.$maxSize.'[!FieldSize!][!MaxFiles!]0[!MaxFiles!][!FileTypes!]'.$allOpts.'[!FileTypes!]';
                        $newType = 'Documents';
                        break;
                    case 'ImageControl':
                        if(!$blankOpts)
                            $maxSize = (int)$optXML->maxSize;
                        else
                            $maxSize=0;
                        if(!$blankOpts)
                            $allowed = (array)$optXML->allowedMIME->mime;
                        else
                            $allowed=array();
                        $allOpts = '';
                        if(isset($allowed[0])) {
                            $allOpts = $allowed[0];
                            $size = sizeof($allowed);
                            for($i = 1; $i < $size; ++$i) {
                                if($allowed[$i] != 'image/pjpeg' && $allowed[$i] != 'image/x-png')
                                    $allOpts .= '[!]' . $allowed[$i];
                            }
                        }
                        $thumbW = (int)$optXML->thumbWidth;
                        $thumbH = (int)$optXML->thumbHeight;

                        $newOpts = '[!FieldSize!]'.$maxSize.'[!FieldSize!][!ThumbSmall!]'.$thumbW.'x'.$thumbH.'[!ThumbSmall!][!ThumbLarge!]'.($thumbW*2).'x'.($thumbH*2).'[!ThumbLarge!][!MaxFiles!]0[!MaxFiles!][!FileTypes!]'.$allOpts.'[!FileTypes!]';
                        $newType = 'Gallery';
                        break;
                    case 'ListControl':
                        if(!$blankOpts)
                            $opts = (array)$optXML->option;
                        else
                            $opts = array();
                        $allOpts = '';
                        if(isset($opts[0])) {
                            $allOpts = $opts[0];
                            $size = sizeof($opts);
                            for($i = 1; $i < $size; ++$i) {
                                $allOpts .= '[!]' . $opts[$i];
                            }
                        }
                        if(!$blankOpts)
                            $def = $optXML->defaultValue->__toString();
                        else
                            $def = '';

                        $newOpts = '[!Options!]'.$allOpts.'[!Options!]';
                        $newDef = $def;
                        $newType = 'List';
                        break;
                    case 'MultiListControl':
                        if(!$blankOpts)
                            $opts = (array)$optXML->option;
                        else
                            $opts = array();
                        $allOpts = '';
                        if(isset($opts[0])) {
                            $allOpts = $opts[0];
                            $size = sizeof($opts);
                            for($i = 1; $i < $size; ++$i) {
                                $allOpts .= '[!]' . $opts[$i];
                            }
                        }
                        if(!$blankOpts)
                            $def = (array)$optXML->defaultValue->option;
                        else
                            $def = array();
                        $defOpts = '';
                        if(isset($def[0])) {
                            $defOpts = $def[0];
                            $size = sizeof($def);
                            for($i = 1; $i < $size; ++$i) {
                                $defOpts .= '[!]' . $def[$i];
                            }
                        }

                        $newOpts = '[!Options!]'.$allOpts.'[!Options!]';
                        $newDef = $defOpts;
                        $newType = 'Multi-Select List';
                        break;
                    case 'AssociatorControl':
                        if(!$blankOpts)
                            $opts = (array)$optXML->scheme;
                        else
                            $opts = array();

                        $assocControlCheck[$c['cid']] = $opts;

                        $newOpts = '[!SearchForms!][!SearchForms!]';
                        $newType = 'Associator';
                        break;
                }

                //save it
                $field = new Field();
                $field->pid = $newForm->pid;
                $field->fid = $newForm->fid;
                $field->page_id = $collToPage[$collid];
                $field->sequence = $collToPage[$collid.'_seq'];
                $collToPage[$collid.'_seq'] += 1;
                $field->type = $newType;
                $field->name = $c['name'];
                $slug = str_replace(' ','_',$c['name']).'_'.$newForm->pid.'_'.$newForm->fid.'_';
                $field->slug = $slug;
                $field->desc = $desc;
                $field->required = $req;
                $field->searchable = $search;
                $field->extsearch = $search;
                $field->viewable = 1;
                $field->viewresults = $showresults;
                $field->extview = $showresults;
                $field->default = $newDef;
                $field->options = $newOpts;
                $field->save();

                $oldControlInfo[$c['cid']] = $field->flid;
            }
        }

        //Now that we know the control options for all the associators, and which field ID they correlate to,
        // we will save the associators options
        foreach($assocControlCheck as $cid => $sids) {
            $flid = $oldControlInfo[$cid];

            $optString = '[!SearchForms!]';
            $subOpt = array();

            $af = FieldController::getField($flid);

            foreach($sids as $sid) {
                if(isset($formArray[$sid])) {
                    $optFID = $formArray[$sid];
                    $optVal = '[fid]' . $optFID . '[fid][search]1[search][flids][flids]';
                    array_push($subOpt, $optVal);
                }
            }

            $optString .= implode('[!]',$subOpt);
            $optString .= '[!SearchForms!]';

            $af->options = $optString;
            $af->save();
        }

        //Dublin Core stuff//////////////////////////////////////////
        $dublins = $con->query('select dublinCoreFields from scheme where schemeid='.$ogSid);
        $dubs = $dublins->fetch_assoc(); //only one possible row
        if(!is_null($dubs['dublinCoreFields'])) {
            //load the xml
            $xml = simplexml_load_string($dubs['dublinCoreFields']);

            //for each element
            foreach($xml->children() as $node) {
                //get element name
                $name = $node->getName();
                //get cid
                $cid = (int)$node->id->__toString();
                //convert to new flid
                if(!isset($oldControlInfo[$cid])) {
	                Log::info('Dublin mapping missing or out of date for: '.$name);
	                continue;   
                }
                	
                $dcflid = $oldControlInfo[$cid];

                //create metadata tag
                $field = FieldController::getField($dcflid);
                $meta = new Metadata();
                $meta->name = $name;
                $meta->flid = $dcflid;
                $meta->fid = $field->fid;
                $meta->pid = $field->pid;

                $meta->save();
            }
        }

        //time to build the records
        Log::info('Iterating through data');

        //Record stuff//////////////////////////////////////////
        error_reporting(E_ALL);
        ini_set('display_errors', '1');
        ini_set('memory_limit','2G'); //We might be pulling a lot of rows so this is a safety precaution
        $records = $con->query('select D.*, C.name from p'.$oldPid.'Data D left join p'.$oldPid.'Control C on D.cid=C.cid where D.schemeid='.$ogSid);
        $recrows = $records->fetch_all(MYSQLI_ASSOC);
        $oldKidToNewRid = array();
        $filePartNum = 1;

        $chunks = array_chunk($recrows, 500);
        unset($recrows);

        foreach($chunks as $chunk) {
            foreach($chunk as $r) {
                //Build type arrays
                $textfields = array();
                $richtextfields = array();
                $generatelistfields = array();
                $datefields = array();
                $documentsfields = array();
                $galleryfields = array();
                $listfields = array();
                $multiselectlistfields = array();

                if(!array_key_exists($r['id'],$oldKidToNewRid)) {
                    $recModel = new Record();
                    $recModel->pid = $newForm->pid;
                    $recModel->fid = $newForm->fid;
                    $recModel->save();
                    $recModel->kid = $recModel->pid . '-' . $recModel->fid . '-' . $recModel->rid;
                    $recModel->legacy_kid = $r['id'];
                    $recModel->save();

                    //increment table
                    DB::table('exodus_partial_progress')->where('id', $row_id)->increment('progress', 1, ['updated_at' => Carbon::now()]);

                    $oldKidToNewRid[$r['id']] = $recModel->rid;
                } else {
                    $recModel = RecordController::getRecord($oldKidToNewRid[$r['id']]);
                }
                if($r['cid']==0) {
                    continue; //This is the reverse association list, so we can bounce
                } else if($r['name']=='systimestamp') {
                    continue; //we don't want to save the timestamp
                } else if($r['name']=='recordowner') {
                    //get the original record owner for some consistency, defaults to current user
                    $email = '';
                    $equery = $con->query('select email from user where username=\''.$r['value'].'\'');
                    if(!$equery) {
                        //if we get here, it's most likely an old project/scheme where the record owner is not in control 2
                        $recModel->owner = 1;
                        $recModel->save();
                        continue;
                    }
                    while($e = $equery->fetch_assoc()) {
                        $email = $e['email'];
                    }
                    $newUser = User::where('email','=',$email)->first();
                    if(!is_null($newUser)) {
                        $recModel->owner = $newUser->id;
                    } else {
                        $recModel->owner = 1;
                    }
                    $recModel->save();
                } else {
                    //make sure the control was converted
                    if(!isset($oldControlInfo[$r['cid']])) {continue;}
                    $flid = $oldControlInfo[$r['cid']];
                    $field = FieldController::getField($flid);
                    $value = utf8_encode($r['value']);

                    switch($field->type) {
                        case 'Text':
                            $text = [
                                'rid' => $recModel->rid,
                                'fid' => $recModel->fid,
                                'flid' => $field->flid,
                                'text' => $value
                            ];
                            array_push($textfields,$text);

                            break;
                        case 'Rich Text':
                            $rich = [
                                'rid' => $recModel->rid,
                                'fid' => $recModel->fid,
                                'flid' => $field->flid,
                                'rawtext' => $value
                            ];
                            array_push($richtextfields,$rich);

                            break;
                        case 'Generated List':
                            $mtc = (array)simplexml_load_string($value)->text;
                            $optStr = implode('[!]',$mtc);

                            $gen = [
                                'rid' => $recModel->rid,
                                'fid' => $recModel->fid,
                                'flid' => $field->flid,
                                'options' => $optStr
                            ];
                            array_push($generatelistfields,$gen);

                            break;
                        case 'Date':
                            $dateXML = simplexml_load_string($value);
                            $circa=0;
                            if((string)$dateXML->prefix == 'circa')
                                $circa=1;
                            $era = 'CE';
                            if(FieldController::getFieldOption($field,'Era')=='Yes')
                                $era = (string)$dateXML->era;

                            $monthData = (int)$dateXML->month;
                            $dayData = (int)$dateXML->day;
                            $yearData = (int)$dateXML->year;

                            $dateObj = new \DateTime("$monthData/$dayData/$yearData");
                            $date_object = date_format($dateObj, 'Y-m-d');

                            $date = [
                                'rid' => $recModel->rid,
                                'fid' => $recModel->fid,
                                'flid' => $field->flid,
                                'circa' => $circa,
                                'month' => $monthData,
                                'day' => $dayData,
                                'year' => $yearData,
                                'era' => $era,
                                'date_object' => $date_object
                            ];
                            array_push($datefields,$date);

                            break;
                        case 'Schedule':
                            $mlc = simplexml_load_string($value)->date;
                            $formattedDates = array();
                            $i=1;

                            foreach($mlc as $date) {
                                $m = (int)$date->month;
                                $d = (int)$date->day;
                                $y = (int)$date->year;
                                $dateStr = 'Event '.$i.': '.$m.'/'.$d.'/'.$y.' - '.$m.'/'.$d.'/'.$y;
                                array_push($formattedDates,$dateStr);
                                ++$i;
                            }

                            DB::table('schedule_fields')->insert([
                                [
                                    'rid' => $recModel->rid,
                                    'fid' => $recModel->fid,
                                    'flid' => $field->flid
                                ]
                            ]);

                            $sched->addEvents($formattedDates);

                            break;
                        case 'Documents':
                            $fileXML = simplexml_load_string($value);
                            $realname = (string)$fileXML->originalName;
                            $localname = (string)$fileXML->localName;

                            if($localname!='') {
                                //Make folder
                                $newPath = config('app.base_path') . 'storage/app/files/p' . $newForm->pid . '/f' . $newForm->fid . '/r' . $recModel->rid . '/fl' . $field->flid.'/';
                                mkdir($newPath, 0775, true);

                                $oldDir = $filePath.'/'.$oldPid.'/'.$ogSid.'/';

                                if(!file_exists($oldDir.$localname)) {
                                    //OLD FILE DOESNT EXIST SO BALE
                                    continue;
                                }

                                //Move files
                                copy($oldDir.$localname,$newPath.$realname);

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

                                $docs = [
                                    'rid' => $recModel->rid,
                                    'fid' => $recModel->fid,
                                    'flid' => $field->flid,
                                    'documents' => $info
                                ];
                                array_push($documentsfields,$docs);
                            }
                            break;
                        case 'Gallery':
                            $fileXML = simplexml_load_string($value);
                            $realname = (string)$fileXML->originalName;
                            $localname = (string)$fileXML->localName;

                            if($localname!='') {
                                //Make folder
                                $newPath = config('app.base_path') . 'storage/app/files/p' . $newForm->pid . '/f' . $newForm->fid . '/r' . $recModel->rid . '/fl' . $field->flid.'/';
                                $newPathM = $newPath.'medium/';
                                $newPathT = $newPath.'thumbnail/';
                                mkdir($newPath, 0775, true);
                                mkdir($newPathM, 0775, true);
                                mkdir($newPathT, 0775, true);

                                $oldDir = $filePath.'/'.$oldPid.'/'.$ogSid.'/';

                                if(!file_exists($oldDir.$localname)) {
                                    //OLD FILE DOESNT EXIST SO BALE
                                    continue;
                                }

                                //Move files
                                copy($oldDir.$localname,$newPath.$realname);

                                //Create thumbs
                                $smallParts = explode('x',FieldController::getFieldOption($field,'ThumbSmall'));
                                $largeParts = explode('x',FieldController::getFieldOption($field,'ThumbLarge'));
                                $thumb = true;
                                $medium = true;
                                try {
                                    $tImage = new \Imagick($newPath . $realname);
                                } catch(\ImagickException $e) {
                                    $thumb = false;
                                    Log::info('Issue creating thumbnail for record '.$recModel->rid.'.');
                                }
                                try {
                                    $mImage = new \Imagick($newPath . $realname);
                                } catch(\ImagickException $e) {
                                    $medium = false;
                                    Log::info('Issue creating medium thumbnail for record '.$recModel->rid.'.');
                                }

                                //Size check
                                if($smallParts[0]==0 | $smallParts[1]==0) {
                                    $smallParts[0] = 150;
                                    $smallParts[1] = 150;
                                }
                                if($largeParts[0]==0 | $largeParts[1]==0) {
                                    $largeParts[0] = 300;
                                    $largeParts[1] = 300;
                                }

                                if($thumb) {
                                    $tImage->thumbnailImage($smallParts[0],$smallParts[1],true);
                                    $tImage->writeImage($newPathT.$realname);
                                }
                                if($medium) {
                                    $mImage->thumbnailImage($largeParts[0],$largeParts[1],true);
                                    $mImage->writeImage($newPathM.$realname);
                                }

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

                                $gal = [
                                    'rid' => $recModel->rid,
                                    'fid' => $recModel->fid,
                                    'flid' => $field->flid,
                                    'images' => $info
                                ];
                                array_push($galleryfields,$gal);
                            }
                            break;
                        case 'List':
                            $list = [
                                'rid' => $recModel->rid,
                                'fid' => $recModel->fid,
                                'flid' => $field->flid,
                                'option' => $value
                            ];
                            array_push($listfields,$list);

                            break;
                        case 'Multi-Select List':
                            $mlc = (array)simplexml_load_string($value)->value;
                            $optStr = implode('[!]',$mlc);

                            $msl = [
                                'rid' => $recModel->rid,
                                'fid' => $recModel->fid,
                                'flid' => $field->flid,
                                'options' => $optStr
                            ];
                            array_push($multiselectlistfields,$msl);

                            break;
                        case 'Associator':
                            $kids = (array)simplexml_load_string($value)->kid;

                            $aid = DB::table('associator_fields')->insertGetId([
                                'rid' => $recModel->rid,
                                'fid' => $recModel->fid,
                                'flid' => $field->flid
                            ]);

                            //We want to save the Typed Field that will have the data eventually, matched to its values in Kora 2 KID form
                            $assocFile[$aid] = $kids;

                            //This prevents the array from getting too big. We will just create the files in parts
                            if(sizeof($assocFile)>self::EXODUS_CONVERSION_SIZE) {
                                $dataToWrite = json_encode($assocFile);
                                $filename = config('app.base_path').ExodusController::EXODUS_DATA_PATH.'assoc_'.$ogSid.'_'.$filePartNum.'.json';
                                file_put_contents($filename,$dataToWrite);

                                //Reset the variables
                                $filePartNum++;
                                $assocFile = array();
                            }
                    }
                }

                //save type arrays
                DB::table('text_fields')->insert($textfields);
                DB::table('rich_text_fields')->insert($richtextfields);
                DB::table('generated_list_fields')->insert($generatelistfields);
                DB::table('date_fields')->insert($datefields);
                DB::table('documents_fields')->insert($documentsfields);
                DB::table('gallery_fields')->insert($galleryfields);
                DB::table('list_fields')->insert($listfields);
                DB::table('multi_select_list_fields')->insert($multiselectlistfields);
            }
        }

        unset($chunks);

        //We want to save the Typed Field that will have the data eventually, matched to its values in Kora 2 KID form
        $dataToWrite = json_encode($assocFile);
        $filename = config('app.base_path').ExodusController::EXODUS_DATA_PATH.'assoc_'.$ogSid.'_'.$filePartNum.'.json';
        file_put_contents($filename,$dataToWrite);

        //We want to save the conversion array of Kora 2 KIDs to Kora 3 RIDs for this scheme
        $ridChunks = array_chunk($oldKidToNewRid, 500, true);
        $partIndex = 0;
        foreach($ridChunks as $ridc) {
            $dataToWrite = json_encode($ridc);
            $filename = env('BASE_PATH').ExodusController::EXODUS_CONVERSION_PATH.'kid_to_rid_'.$ogSid.'_'.$partIndex.'.json';
            file_put_contents($filename,$dataToWrite);
            $partIndex++;
        }

        unset($ridChunks);

        //Last but not least, record presets!!!!!!!!!
        $recordPresets = $records = $con->query('select * from recordPreset where schemeid='.$ogSid);
        while($rp = $recordPresets->fetch_assoc()) {
            $preset = new RecordPreset();
            $preset->rid = $oldKidToNewRid[$rp['kid']];
            $preset->fid = $newForm->fid;
            $preset->name = $rp['name'];

            $preset->save();

            $preset->preset = json_encode($this->getRecordArray($preset->rid, $preset->id));
            $preset->save();
        }

        //End Record stuff//////////////////////////////////////

        //Breath now
        Log::info('Done creating records for '.$form->slug.'.');
        DB::table('exodus_overall_progress')->where('id', $exodus_id)->increment('progress',1,['updated_at'=>Carbon::now()]);

        mysqli_close($con);
    }

    /**
     * Gets an array representation of a record for saving in preset.
     *
     * @param  int $pid - Project ID
     * @param  int $id - Preset ID
     * @return array - The record data
     */
    public function getRecordArray($rid, $id) {
        $record = Record::where('rid', '=', $rid)->first();
        $form = Form::where('fid', '=', $record->fid)->first();

        $field_collect = $form->fields()->get();
        $field_array = array();
        $flid_array = array();

        $fileFields = false; // Does the record have any file fields?

        foreach($field_collect as $field) {
            $data = array();
            $data['flid'] = $field->flid;
            $data['type'] = $field->type;

            //Get the typed field so we can store it in proper record preset form
            $typedField = $field->getTypedFieldFromRID($record->rid);
            $exists = true;
            if(is_null($typedField)) {
	            $typedField = $field->getTypedField();
                $exists = false;
            }

            $data = $typedField->getRecordPresetArray($data,$exists);
            $flid_array[] = $field->flid;

            if($typedField instanceof FileTypeField)
                $fileFields = true;

            $field_array[$field->flid] = $data;
        }

        // A file field was in use, so we need to move the record files to a preset directory.
        if($fileFields)
            $this->moveFilesToPreset($record->rid, $id);

        $response['data'] = $field_array;
        $response['flids'] = $flid_array;
        return $response;
    }

    /**
     * Moves the record files to its preset directory.
     *
     * @param  int $pid - Project ID
     * @param  int $id - Preset ID
     */
    public function moveFilesToPreset($rid, $id) {
        $presets_path = config('app.base_path').'storage/app/presetFiles';

        //
        // Create the presets file path if it does not exist.
        //
        if(!is_dir($presets_path))
            mkdir($presets_path, 0775, true);

        $path = $presets_path . '/preset' . $id; // Path for the new preset's directory.

        if(!is_dir($path))
            mkdir($path, 0775, true);

        // Build the record's directory.
        $record = RecordController::getRecord($rid);

        $record_path = config('app.base_path') . 'storage/app/files/p' . $record->pid . '/f' . $record->fid . '/r' . $record->rid;

        //
        // Recursively copy the record's file directory.
        //
        RecordPresetController::recurse_copy($record_path, $path);
    }
}
