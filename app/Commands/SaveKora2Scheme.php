<?php namespace App\Commands;

use App\AssociatorField;
use App\DateField;
use App\DocumentsField;
use App\Field;
use App\FileTypeField;
use App\Form;
use App\GalleryField;
use App\GeneratedListField;
use App\Http\Controllers\ExodusController;
use App\Http\Controllers\FieldController;
use App\Http\Controllers\RecordController;
use App\Http\Controllers\RecordPresetController;
use App\ListField;
use App\Metadata;
use App\MultiSelectListField;
use App\Page;
use App\Record;
use App\RecordPreset;
use App\RichTextField;
use App\ScheduleField;
use App\TextField;
use App\User;
use Carbon\Carbon;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class SaveKora2Scheme extends CommandKora2 implements SelfHandling, ShouldQueue {

    /*
    |--------------------------------------------------------------------------
    | Save Kora 2 Scheme
    |--------------------------------------------------------------------------
    |
    | This command handles the migration of a Kora 2 scheme for the exodus process
    |
    */

    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the command.
     */
    public function handle() {
        //connect to db and set up variables
        $con = mysqli_connect($this->dbInfo['host'],$this->dbInfo['user'],$this->dbInfo['pass'],$this->dbInfo['name']);
        $newForm = $this->form;
        $oldPid = $this->pairArray[$this->sid];
        $collToPage = array();
        $oldControlInfo = array();
        $numRecords = $con->query("select distinct id from p".$oldPid."Data where schemeid=".$this->sid)->num_rows;

        $table_array = $this->makeBackupTableArray($numRecords);
        if($table_array == false) { return;}
        Log::info("Started creating records for ".$this->form->slug.".");

        $row_id = DB::table('exodus_partial_progress')->insertGetId(
            $table_array
        );

        //build nodes based off of collections
        $colls = $con->query("select * from collection where schemeid=".$this->sid." order by sequence");
        $pIndex = 0;
        while($c = $colls->fetch_assoc()) {
            $page = new Page();
            $page->fid = $newForm->fid;
            $page->title = $c['name'];
            $page->sequence = $pIndex;
            $pIndex++;

            $page->save();

            $collToPage[$c['collid']] = $page->id;
            //Each page needs to keep track of its own sequence for fields
            $collToPage[$c['collid']."_seq"] = 0;
        }

        //build all the fields for the form
        $controls = $con->query("select * from p".$oldPid."Control where schemeid=".$this->sid." order by sequence");
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
                        if($textType == 'plain') {
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

                            $newOpts = "[!Regex!]" . $regex . "[!Regex!][!MultiLine!]" . $multiline . "[!MultiLine!]";
                            $newDef = $def;
                            $newType = "Text";
                        } else if($textType == 'rich') {
                            $newOpts = "";
                            $newDef = $def;
                            $newType = "Rich Text";
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
                            for($i = 1; $i < sizeof($def); $i++) {
                                $defOpts .= '[!]' . $def[$i];
                            }
                        }
                        if(!$blankOpts)
                            $regex = $optXML->regex->__toString();
                        else
                            $regex = '';

                        $newOpts = "[!Regex!]" . $regex . "[!Regex!][!Options!]" . $defOpts . "[!Options!]";
                        $newDef = $defOpts;
                        $newType = "Generated List";
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
                        if($prefix=="circa") {$circa="Yes";}
                        if($format=="MDY") {$for="MMDDYYYY";}
                        else if($format=="DMY") {$for="DDMMYYYY";}
                        else if($format=="YMD") {$for="YYYYMMDD";}

                        $newOpts = "[!Circa!]".$circa."[!Circa!][!Start!]".$startY."[!Start!][!End!]".$endY."[!End!][!Format!]".$for."[!Format!][!Era!]".$era."[!Era!]";
                        $newDef = "[M]".$defMon."[M][D]".$defDay."[D][Y]".$defYear."[Y]";
                        $newType = "Date";
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
                            for($i = 1; $i < sizeof($allowed); $i++) {
                                $allOpts .= '[!]' . $allowed[$i];
                            }
                        }

                        $newOpts = "[!FieldSize!]".$maxSize."[!FieldSize!][!MaxFiles!]0[!MaxFiles!][!FileTypes!]".$allOpts."[!FileTypes!]";
                        $newType = "Documents";
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
                            for($i = 1; $i < sizeof($allowed); $i++) {
                                if($allowed[$i] != "image/pjpeg" && $allowed[$i] != "image/x-png")
                                    $allOpts .= '[!]' . $allowed[$i];
                            }
                        }
                        $thumbW = (int)$optXML->thumbWidth;
                        $thumbH = (int)$optXML->thumbHeight;

                        $newOpts = "[!FieldSize!]".$maxSize."[!FieldSize!][!ThumbSmall!]".$thumbW."x".$thumbH."[!ThumbSmall!][!ThumbLarge!]".($thumbW*2)."x".($thumbH*2)."[!ThumbLarge!][!MaxFiles!]0[!MaxFiles!][!FileTypes!]".$allOpts."[!FileTypes!]";
                        $newType = "Gallery";
                        break;
                    case 'ListControl':
                        if(!$blankOpts)
                            $opts = (array)$optXML->option;
                        else
                            $opts = array();
                        $allOpts = '';
                        if(isset($opts[0])) {
                            $allOpts = $opts[0];
                            for($i = 1; $i < sizeof($opts); $i++) {
                                $allOpts .= '[!]' . $opts[$i];
                            }
                        }
                        if(!$blankOpts)
                            $def = $optXML->defaultValue->__toString();
                        else
                            $def = '';

                        $newOpts = "[!Options!]".$allOpts."[!Options!]";
                        $newDef = $def;
                        $newType = "List";
                        break;
                    case 'MultiListControl':
                        if(!$blankOpts)
                            $opts = (array)$optXML->option;
                        else
                            $opts = array();
                        $allOpts = '';
                        if(isset($opts[0])) {
                            $allOpts = $opts[0];
                            for($i = 1; $i < sizeof($opts); $i++) {
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
                            for($i = 1; $i < sizeof($def); $i++) {
                                $defOpts .= '[!]' . $def[$i];
                            }
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
                $field->pid = $newForm->pid;
                $field->fid = $newForm->fid;
                $field->page_id = $collToPage[$collid];
                $field->sequence = $collToPage[$collid."_seq"];
                $field->type = $newType;
                $field->name = $c['name'];
                $slug = str_replace(' ','_',$c['name']).$this->fieldSlugGenerator();
                while(Field::slugExists($slug)) {
                    $slug .= $this->fieldSlugGenerator();
                }
                $field->slug = $slug;
                $field->desc = $desc;
                $field->required = $req;
                $field->searchable = $search;
                $field->extsearch = $search;
                $field->viewable = $showresults;
                $field->viewresults = $showresults;
                $field->extview = $showresults;
                $field->default = $newDef;
                $field->options = $newOpts;
                $field->save();

                $oldControlInfo[$c['cid']] = $field->flid;
            }
        }

        //Dublin Core stuff//////////////////////////////////////////
        $dublins = $con->query("select dublinCoreFields from scheme where schemeid=".$this->sid);
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
        $records = $con->query("select * from p".$oldPid."Data where schemeid=".$this->sid);
        $oldKidToNewRid = array();

        while($r = $records->fetch_assoc()) {
            if(!array_key_exists($r['id'],$oldKidToNewRid)) {
                $recModel = new Record();
                $recModel->pid = $newForm->pid;
                $recModel->fid = $newForm->fid;
                $recModel->save();
                $recModel->kid = $recModel->pid . '-' . $recModel->fid . '-' . $recModel->rid;
                $recModel->save();

                //increment table
                DB::table("exodus_partial_progress")->where("id", $row_id)->increment("progress", 1, ["updated_at" => Carbon::now()]);

                $oldKidToNewRid[$r['id']] = $recModel->rid;
            } else {
                $recModel = RecordController::getRecord($oldKidToNewRid[$r['id']]);
            }

            if($r['cid']==1) {
                continue; //we don't want to save the timestamp
            } else if($r['cid']==2) {
                //get the original record owner for some consistency, defaults to current user
                $email = '';
                $equery = $con->query("select email from user where username='".$r['value']."'");
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
                $value = $r['value'];

                switch($field->type) {
                    case 'Text':
                        $text = new TextField();
                        $text->rid = $recModel->rid;
                        $text->fid = $recModel->fid;
                        $text->flid = $field->flid;
                        $text->text = $value;
                        $text->save();

                        break;
                    case 'Rich Text':
                        $rich = new RichTextField();
                        $rich->rid = $recModel->rid;
                        $rich->fid = $recModel->fid;
                        $rich->flid = $field->flid;
                        $rich->rawtext = $value;
                        $rich->save();

                        break;
                    case 'Generated List':
                        $mtc = (array)simplexml_load_string(utf8_encode($value))->text;
                        $optStr = implode('[!]',$mtc);

                        $gen = new GeneratedListField();
                        $gen->rid = $recModel->rid;
                        $gen->fid = $recModel->fid;
                        $gen->flid = $field->flid;
                        $gen->options = $optStr;
                        $gen->save();

                        break;
                    case 'Date':
                        $dateXML = simplexml_load_string(utf8_encode($value));
                        $circa=0;
                        if((string)$dateXML->prefix == 'circa')
                            $circa=1;
                        $era = 'CE';
                        if(FieldController::getFieldOption($field,'Era')=='Yes')
                            $era = (string)$dateXML->era;

                        $date = new DateField();
                        $date->rid = $recModel->rid;
                        $date->fid = $recModel->fid;
                        $date->flid = $field->flid;
                        $date->circa = $circa;
                        $date->month = (int)$dateXML->month;
                        $date->day = (int)$dateXML->day;
                        $date->year = (int)$dateXML->year;
                        $date->era = $era;
                        $date->save();

                        break;
                    case 'Schedule':
                        $mlc = simplexml_load_string(utf8_encode($value))->date;
                        $formattedDates = array();
                        $i=1;

                        foreach($mlc as $date) {
                            $m = (int)$date->month;
                            $d = (int)$date->day;
                            $y = (int)$date->year;
                            $dateStr = 'Event '.$i.': '.$m.'/'.$d.'/'.$y.' - '.$m.'/'.$d.'/'.$y;
                            array_push($formattedDates,$dateStr);
                            $i++;
                        }

                        $sched = new ScheduleField();
                        $sched->rid = $recModel->rid;
                        $sched->fid = $recModel->fid;
                        $sched->flid = $field->flid;
                        $sched->save();

                        $sched->addEvents($formattedDates);

                        break;
                    case 'Documents':
                        $fileXML = simplexml_load_string(utf8_encode($value));
                        $realname = (string)$fileXML->originalName;
                        $localname = (string)$fileXML->localName;

                        if($localname!='') {
                            $docs = new DocumentsField();
                            $docs->rid = $recModel->rid;
                            $docs->fid = $recModel->fid;
                            $docs->flid = $field->flid;

                            //Make folder
                            $newPath = env('BASE_PATH') . 'storage/app/files/p' . $newForm->pid . '/f' . $newForm->fid . '/r' . $recModel->rid . '/fl' . $field->flid.'/';
                            mkdir($newPath, 0775, true);

                            $oldDir = $this->filePath.'/'.$oldPid.'/'.$this->sid.'/';

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
                            $docs->documents = $info;
                            $docs->save();
                        }
                        break;
                    case 'Gallery':
                        $fileXML = simplexml_load_string(utf8_encode($value));
                        $realname = (string)$fileXML->originalName;
                        $localname = (string)$fileXML->localName;

                        if($localname!='') {
                            $gal = new GalleryField();
                            $gal->rid = $recModel->rid;
                            $gal->fid = $recModel->fid;
                            $gal->flid = $field->flid;

                            //Make folder
                            $newPath = env('BASE_PATH') . 'storage/app/files/p' . $newForm->pid . '/f' . $newForm->fid . '/r' . $recModel->rid . '/fl' . $field->flid.'/';
                            $newPathM = $newPath.'medium/';
                            $newPathT = $newPath.'thumbnail/';
                            mkdir($newPath, 0775, true);
                            mkdir($newPathM, 0775, true);
                            mkdir($newPathT, 0775, true);

                            $oldDir = $this->filePath.'/'.$oldPid.'/'.$this->sid.'/';

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
                                Log::info("Issue creating thumbnail for record ".$recModel->rid.".");
                            }
                            try {
                                $mImage = new \Imagick($newPath . $realname);
                            } catch(\ImagickException $e) {
                                $medium = false;
                                Log::info("Issue creating medium thumbnail for record ".$recModel->rid.".");
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
                            $gal->images = $info;
                            $gal->save();
                        }
                        break;
                    case 'List':
                        $list = new ListField();
                        $list->rid = $recModel->rid;
                        $list->fid = $recModel->fid;
                        $list->flid = $field->flid;
                        $list->option = $value;
                        $list->save();

                        break;
                    case 'Multi-Select List':
                        $mlc = (array)simplexml_load_string(utf8_encode($value))->value;
                        $optStr = implode('[!]',$mlc);

                        $msl = new MultiSelectListField();
                        $msl->rid = $recModel->rid;
                        $msl->fid = $recModel->fid;
                        $msl->flid = $field->flid;
                        $msl->options = $optStr;
                        $msl->save();

                        break;
                    case 'Associator':
                        $kids = (array)simplexml_load_string(utf8_encode($value))->kid;

                        $assoc = new AssociatorField();
                        $assoc->rid = $recModel->rid;
                        $assoc->fid = $recModel->fid;
                        $assoc->flid = $field->flid;
                        $assoc->save();

                        //We want to save the Typed Field that will have the data eventually, matched to its values in Kora 2 KID form
                        $dataToWrite = json_encode([$assoc->id => $kids]);
                        $filename = env('BASE_PATH').ExodusController::EXODUS_DATA_PATH."assoc_".$assoc->id.".json";
                        file_put_contents($filename,$dataToWrite);
                }
            }
        }

        //We want to save the conversion array of Kora 2 KIDs to Kora 3 RIDs for this scheme
        $dataToWrite = json_encode([$oldKidToNewRid]);
        $filename = env('BASE_PATH').ExodusController::EXODUS_CONVERSION_PATH."kid_to_rid_".$this->sid.".json";
        file_put_contents($filename,$dataToWrite);

        //Last but not least, record presets!!!!!!!!!
        $recordPresets = $records = $con->query("select * from recordPreset where schemeid=".$this->sid);
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
        Log::info("Done creating records for ".$this->form->slug.".");
        DB::table("exodus_overall_progress")->where("id", $this->exodus_id)->increment("progress",1,["updated_at"=>Carbon::now()]);

        mysqli_close($con);
    }

    /**
     * Generates a slug for a field to prevent duplicates.
     *
     * @return int - 5 digit numeric tag
     */
    private function fieldSlugGenerator() {
        $valid = '0123456789';

        $password = '';
        for($i = 0; $i < 4; $i++) {
            $password .= $valid[( rand() % 10 )];
        }
        return $password;
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
            if(is_null($typedField))
                $exists = false;

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
        $presets_path = env('BASE_PATH').'storage/app/presetFiles';

        //
        // Create the presets file path if it does not exist.
        //
        if(!is_dir($presets_path))
            mkdir($presets_path, 0755, true);

        $path = $presets_path . '/preset' . $id; // Path for the new preset's directory.

        if (!is_dir($path))
            mkdir($path, 0755, true);

        // Build the record's directory.
        $record = RecordController::getRecord($rid);

        $record_path = env('BASE_PATH') . 'storage/app/files/p' . $record->pid . '/f' . $record->fid . '/r' . $record->rid;

        //
        // Recursively copy the record's file directory.
        //
        RecordPresetController::recurse_copy($record_path, $path);
    }
}
