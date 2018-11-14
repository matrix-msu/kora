<?php namespace App\Http\Controllers;

use App\ComboListField;
use App\Field;
use App\FieldHelpers\UploadHandler;
use App\GeolocatorField;
use App\Record;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ImportMultiFormController extends Controller {

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
            $forms[$obj->fid] = $obj->name;
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
        $options['flid'] = 'MFf0u'.$uid;

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
        $options['flid'] = 'MFf0u'.$uid;
        $options['filename'] = $filename;
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

                $fieldSlug = $key;
                $fieldMod = Field::where('slug', '=', $fieldSlug)->get()->first();
                if(is_null($fieldMod))
                    return response()->json(["status"=>false,"message"=>"xml_validation_error",
                        "record_validation_error"=>[$request->kid => "Invalid provided field, $fieldSlug"]],500);
                $flid = $fieldMod->flid;
                $type = $fieldMod->type;
                $simple = !is_null($field->attributes()->simple);

                //TODO::modular?

                if($type == 'Text' | $type == 'Rich Text' | $type == 'Number' | $type == 'List')
                    $recRequest[$flid] = (string)$field;
                else if($type == 'Multi-Select List') {
                    if(empty($field->value))
                        return response()->json(["status"=>false,"message"=>"xml_validation_error",
                            "record_validation_error"=>[$request->kid => "$fieldSlug format is incorrect for a Multi-Select List Field"]],500);
                    $recRequest[$flid] = (array)$field->value;
                } else if($type == 'Generated List') {
                    if(empty($field->value))
                        return response()->json(["status"=>false,"message"=>"xml_validation_error",
                            "record_validation_error"=>[$request->kid => "$fieldSlug format is incorrect for a Generated List Field"]],500);
                    $recRequest[$flid] = (array)$field->value;
                } else if($type == 'Combo List') {
                    if(empty($field->Value))
                        return response()->json(["status"=>false,"message"=>"xml_validation_error",
                            "record_validation_error"=>[$request->kid => "$fieldSlug format is incorrect for a Combo List Field"]],500);
                    $oneVals = array();
                    $twoVals = array();
                    $cf = FieldController::getField($flid);
                    $nameone = Field::xmlTagClear(ComboListField::getComboFieldName($cf, 'one'));
                    $nametwo = Field::xmlTagClear(ComboListField::getComboFieldName($cf, 'two'));
                    $typeone = ComboListField::getComboFieldType($cf, 'one');
                    $typetwo = ComboListField::getComboFieldType($cf, 'two');
                    if($typeone == "Associator")
                        $comboAssocArray[] = $flid.' 1';
                    if($typetwo == "Associator")
                        $comboAssocArray[] = $flid.' 2';
                    foreach($field->Value as $val) {
                        if(empty($val->{$nameone}))
                            return response()->json(["status"=>false,"message"=>"xml_validation_error",
                                "record_validation_error"=>[$request->kid => "$fieldSlug field one format is incorrect for a Combo List Field"]],500);
                        if((string)$val->{$nameone} != '')
                            $fone = (string)$val->{$nameone};
                        else if(sizeof($val->{$nameone}->value) == 1)
                            $fone = (string)$val->{$nameone}->value;
                        else
                            $fone = implode("[!]",(array)$val->{$nameone}->value);

                        if(empty($val->{$nametwo}))
                            return response()->json(["status"=>false,"message"=>"xml_validation_error",
                                "record_validation_error"=>[$request->kid => "$fieldSlug field two format is incorrect for a Combo List Field"]],500);
                        if((string)$val->{$nametwo} != '')
                            $ftwo = (string)$val->{$nametwo};
                        else if(sizeof($val->{$nametwo}->value) == 1)
                            $ftwo = (string)$val->{$nametwo}->value;
                        else
                            $ftwo = implode("[!]",(array)$val->{$nametwo}->value);

                        array_push($oneVals, $fone);
                        array_push($twoVals, $ftwo);
                    }
                    $recRequest[$flid] = '';
                    $recRequest[$flid . '_combo_one'] = $oneVals;
                    $recRequest[$flid . '_combo_two'] = $twoVals;
                } else if($type == 'Date') {
                    if($simple) {
                        $dateParts = explode('/',(string)$field);
                        $recRequest['circa_' . $flid] = 0;
                        $recRequest['month_' . $flid] = $dateParts[0];
                        $recRequest['day_' . $flid] = $dateParts[1];
                        $recRequest['year_' . $flid] = $dateParts[2];
                        $recRequest['era_' . $flid] = 'CE';
                        $recRequest[$flid] = '';
                    } else {
                        if(empty($field->Month) && empty($field->Day) && empty($field->Year))
                            return response()->json(["status"=>false,"message"=>"xml_validation_error",
                                "record_validation_error"=>[$request->kid => "$fieldSlug format is incorrect for a Date Field"]],500);
                        $recRequest['circa_' . $flid] = (string)$field->Circa;
                        $recRequest['month_' . $flid] = (string)$field->Month;
                        $recRequest['day_' . $flid] = (string)$field->Day;
                        $recRequest['year_' . $flid] = (string)$field->Year;
                        $recRequest['era_' . $flid] = (string)$field->Era;
                        $recRequest[$flid] = '';
                    }
                } else if($type == 'Schedule') {
                    $events = array();
                    if(empty($field->Event))
                        return response()->json(["status"=>false,"message"=>"xml_validation_error",
                            "record_validation_error"=>[$request->kid => "$fieldSlug format is incorrect for a Schedule Field"]],500);
                    foreach($field->Event as $event) {
                        if(empty($event->Title) | empty($event->Begin) | empty($event->End))
                            return response()->json(["status"=>false,"message"=>"xml_validation_error",
                                "record_validation_error"=>[$request->kid => "$fieldSlug event format is incorrect for a Schedule Field"]],500);
                        $string = $event->Title . ': ' . $event->Begin . ' - ' . $event->End;
                        array_push($events, $string);
                    }
                    $recRequest[$flid] = $events;
                } else if($type == 'Geolocator') {
                    $geo = array();
                    if(empty($field->Location))
                        return response()->json(["status"=>false,"message"=>"xml_validation_error",
                            "record_validation_error"=>[$request->kid => "$fieldSlug format is incorrect for a Geolocator Field"]],500);
                    foreach($field->Location as $loc) {
                        $geoReq = new Request();

                        if(!is_null($loc->Lat)) {
                            $geoReq->type = 'latlon';
                            $geoReq->lat = (float)$loc->Lat;
                            $geoReq->lon = (float)$loc->Lon;
                        } else if(!is_null($loc->Zone)) {
                            $geoReq->type = 'utm';
                            $geoReq->zone = (string)$loc->Zone;
                            $geoReq->east = (float)$loc->East;
                            $geoReq->north = (float)$loc->North;
                        } else if(!is_null($loc->Address)) {
                            $geoReq->type = 'geo';
                            $geoReq->addr = (string)$loc->Address;
                        }

                        if(empty($loc->Desc))
                            return response()->json(["status"=>false,"message"=>"xml_validation_error",
                                "record_validation_error"=>[$request->kid => "$fieldSlug description format is incorrect for a Geolocator Field"]],500);
                        $string = '[Desc]' . $loc->Desc . '[Desc]';
                        $string .= GeolocatorField::geoConvert($geoReq);
                        array_push($geo, $string);
                    }
                    $recRequest[$flid] = $geo;
                } else if($type == 'Documents' | $type == 'Playlist' | $type == 'Video' | $type == '3D-Model') {
                    $files = array();
                    if(is_null($originRid))
                        $currDir = storage_path('app/tmpFiles/impU' . \Auth::user()->id);
                    else
                        $currDir = storage_path('app/tmpFiles/impU' . \Auth::user()->id . '/r' . $originRid . '/fl' . $flid);
                    $newDir = storage_path('app/tmpFiles/f' . $flid . 'u' . \Auth::user()->id . '/r' . $request->kid);
                    if(file_exists($newDir)) {
                        foreach(new \DirectoryIterator($newDir) as $file) {
                            if($file->isFile()) {
                                unlink($newDir . '/' . $file->getFilename());
                            }
                        }
                    } else {
                        mkdir($newDir, 0775, true);
                    }
                    if($simple) {
                        $name = (string)$field;
                        //move file from imp temp to tmp files
                        if(!file_exists($currDir . '/' . $name)) {
                            //Before we fail, let's see first if it's just failing because the originRid was specified
                            // and not because the file doesn't actually exist. We will now force look into the ZIPs root folder
                            $currDir = storage_path('app/tmpFiles/impU' . \Auth::user()->id);
                            if(!file_exists($currDir . '/' . $name))
                                return response()->json(["status" => false, "message" => "xml_validation_error",
                                    "record_validation_error" => [$request->kid => "$fieldSlug: trouble finding file $name"]], 500);
                        }
                        copy($currDir . '/' . $name, $newDir . '/' . $name);
                        //add input for this file
                        array_push($files, $name);
                    } else {
                        if(empty($field->File))
                            return response()->json(["status"=>false,"message"=>"xml_validation_error",
                                "record_validation_error"=>[$request->kid => "$fieldSlug format is incorrect for a File Type Field"]],500);
                        foreach ($field->File as $file) {
                            $name = (string)$file->Name;
                            //move file from imp temp to tmp files
                            if(!file_exists($currDir . '/' . $name)) {
                                //Before we fail, let's see first if it's just failing because the originRid was specified
                                // and not because the file doesn't actually exist. We will now force look into the ZIPs root folder
                                $currDir = storage_path('app/tmpFiles/impU' . \Auth::user()->id);
                                if(!file_exists($currDir . '/' . $name))
                                    return response()->json(["status" => false, "message" => "xml_validation_error",
                                        "record_validation_error" => [$request->kid => "$fieldSlug: trouble finding file $name"]], 500);
                            }
                            copy($currDir . '/' . $name, $newDir . '/' . $name);
                            //add input for this file
                            array_push($files, $name);
                        }
                    }
                    $recRequest['file' . $flid] = $files;
                    $recRequest[$flid] = 'f' . $flid . 'u' . \Auth::user()->id . '/r' . $request->kid;
                } else if($type == 'Gallery') {
                    $files = array();
                    if(is_null($originRid))
                        $currDir = storage_path('app/tmpFiles/impU' . \Auth::user()->id);
                    else
                        $currDir = storage_path('app/tmpFiles/impU' . \Auth::user()->id . '/r' . $originRid . '/fl' . $flid);
                    $newDir = storage_path('app/tmpFiles/f' . $flid . 'u' . \Auth::user()->id . '/r' . $request->kid);
                    if(file_exists($newDir)) {
                        foreach(new \DirectoryIterator($newDir) as $file) {
                            if($file->isFile())
                                unlink($newDir . '/' . $file->getFilename());
                        }
                        if(file_exists($newDir . '/thumbnail')) {
                            foreach(new \DirectoryIterator($newDir . '/thumbnail') as $file) {
                                if($file->isFile())
                                    unlink($newDir . '/thumbnail/' . $file->getFilename());
                            }
                        }
                        if(file_exists($newDir . '/medium')) {
                            foreach(new \DirectoryIterator($newDir . '/medium') as $file) {
                                if($file->isFile())
                                    unlink($newDir . '/medium/' . $file->getFilename());
                            }
                        }
                    } else {
                        mkdir($newDir, 0775, true);
                        mkdir($newDir . '/thumbnail', 0775, true);
                        mkdir($newDir . '/medium', 0775, true);
                    }
                    if($simple) {
                        $name = (string)$field;
                        //move file from imp temp to tmp files
                        if(!file_exists($currDir . '/' . $name)) {
                            //Before we fail, let's see first if it's just failing because the originRid was specified
                            // and not because the file doesn't actually exist. We will now force look into the ZIPs root folder
                            $currDir = storage_path('app/tmpFiles/impU' . \Auth::user()->id);
                            if(!file_exists($currDir . '/' . $name))
                                return response()->json(["status" => false, "message" => "xml_validation_error",
                                    "record_validation_error" => [$request->kid => "$fieldSlug: trouble finding file $name"]], 500);
                        }
                        copy($currDir . '/' . $name, $newDir . '/' . $name);
                        if (file_exists($currDir . '/thumbnail'))
                            copy($currDir . '/thumbnail/' . $name, $newDir . '/thumbnail/' . $name);
                        else {
                            $smallParts = explode('x', FieldController::getFieldOption($field, 'ThumbSmall'));
                            $tImage = new \Imagick($newDir . '/' . $name);
                            $tImage->thumbnailImage($smallParts[0], $smallParts[1], true);
                            $tImage->writeImage($newDir . '/thumbnail/' . $name);
                        }
                        if (file_exists($currDir . '/medium'))
                            copy($currDir . '/medium/' . $name, $newDir . '/medium/' . $name);
                        else {
                            $largeParts = explode('x', FieldController::getFieldOption($field, 'ThumbLarge'));
                            $mImage = new \Imagick($newDir . '/' . $name);
                            $mImage->thumbnailImage($largeParts[0], $largeParts[1], true);
                            $mImage->writeImage($newDir . '/medium/' . $name);
                        }
                        //add input for this file
                        array_push($files, $name);
                    } else {
                        if(empty($field->File))
                            return response()->json(["status"=>false,"message"=>"xml_validation_error",
                                "record_validation_error"=>[$request->kid => "$fieldSlug format is incorrect for a File Type Field"]],500);
                        foreach ($field->File as $file) {
                            $name = (string)$file->Name;
                            //move file from imp temp to tmp files
                            if(!file_exists($currDir . '/' . $name)) {
                                //Before we fail, let's see first if it's just failing because the originRid was specified
                                // and not because the file doesn't actually exist. We will now force look into the ZIPs root folder
                                $currDir = storage_path('app/tmpFiles/impU' . \Auth::user()->id);
                                if(!file_exists($currDir . '/' . $name))
                                    return response()->json(["status" => false, "message" => "xml_validation_error",
                                        "record_validation_error" => [$request->kid => "$fieldSlug: trouble finding file $name"]], 500);
                            }
                            copy($currDir . '/' . $name, $newDir . '/' . $name);
                            if (file_exists($currDir . '/thumbnail'))
                                copy($currDir . '/thumbnail/' . $name, $newDir . '/thumbnail/' . $name);
                            else {
                                $smallParts = explode('x', FieldController::getFieldOption($field, 'ThumbSmall'));
                                $tImage = new \Imagick($newDir . '/' . $name);
                                $tImage->thumbnailImage($smallParts[0], $smallParts[1], true);
                                $tImage->writeImage($newDir . '/thumbnail/' . $name);
                            }
                            if (file_exists($currDir . '/medium'))
                                copy($currDir . '/medium/' . $name, $newDir . '/medium/' . $name);
                            else {
                                $largeParts = explode('x', FieldController::getFieldOption($field, 'ThumbLarge'));
                                $mImage = new \Imagick($newDir . '/' . $name);
                                $mImage->thumbnailImage($largeParts[0], $largeParts[1], true);
                                $mImage->writeImage($newDir . '/medium/' . $name);
                            }
                            //add input for this file
                            array_push($files, $name);
                        }
                    }
                    $recRequest['file' . $flid] = $files;
                    $recRequest[$flid] = 'f' . $flid . 'u' . \Auth::user()->id . '/r' . $request->kid;
                } else if($type == 'Associator') {
                    if(empty($field->Record))
                        return response()->json(["status"=>false,"message"=>"xml_validation_error",
                            "record_validation_error"=>[$request->kid => "$fieldSlug format is incorrect for an Associator Field"]],500);
                    //If KID format, treat value as normal, else we assume it will be built as a cross-Form association
                    $aVals = (array)$field->Record;
                    $aRes = array();
                    $aTag = array();
                    foreach($aVals as $aVal) {
                        if(Record::isKIDPattern($aVal))
                            array_push($aRes,$aVal);
                        else
                            array_push($aTag,$aVal);
                    }

                    if(sizeof($aRes)>0)
                        $recRequest[$flid] = $aRes;
                    if(sizeof($aTag)>0)
                        $assocArray[$flid] = $aTag;
                }
            }
        } else if($request->type==self::JSON) {
            $originKid = $request->kid;
            $originRid = null;
            if(!is_null($originKid)) {
                if(Record::isKIDPattern($originKid))
                    $originRid = explode('-', $originKid)[2];

                $assocTag = $originKid;
            }

            foreach($record as $slug => $field) {
                //If value is not set, we assume no value so move on
                if(!isset($field['value']))
                    continue;

                //Deal with reverse associations and move on
                if($slug == 'reverseAssociations') {
                    $recRequest['newRecRevAssoc'] = $field;
                    continue;
                }

                $fieldSlug = $slug;
                $flid = Field::where('slug', '=', $fieldSlug)->get()->first()->flid;
                $type = $field['type'];

                //Type wasnt provided so we have to hunt for it
                if(is_null($type))
                    $type = Field::where('slug', '=', $fieldSlug)->get()->first()->type;

                if(!isset($field['value']))
                    return response()->json(["status"=>false,"message"=>"json_validation_error",
                        "record_validation_error"=>[$request->kid => "$fieldSlug is missing value index"]],500);

                if($type == 'Text') {
                    $recRequest[$flid] = $field['value'];
                } else if($type == 'Rich Text') {
                    $recRequest[$flid] = $field['value'];
                } else if($type == 'Number') {
                    $recRequest[$flid] = $field['value'];
                } else if($type == 'List') {
                    $recRequest[$flid] = $field['value'];
                } else if($type == 'Multi-Select List') {
                    $recRequest[$flid] = $field['value'];
                } else if($type == 'Generated List') {
                    $recRequest[$flid] = $field['value'];
                } else if($type == 'Combo List') {
                    $oneVals = array();
                    $twoVals = array();
                    $cf = FieldController::getField($flid);
                    $nameone = Field::xmlTagClear(ComboListField::getComboFieldName($cf, 'one'));
                    $nametwo = Field::xmlTagClear(ComboListField::getComboFieldName($cf, 'two'));
                    $typeone = ComboListField::getComboFieldType($cf, 'one');
                    $typetwo = ComboListField::getComboFieldType($cf, 'two');
                    if($typeone == "Associator")
                        $comboAssocArray[] = $flid.' 1';
                    if($typetwo == "Associator")
                        $comboAssocArray[] = $flid.' 2';
                    foreach($field['value'] as $val) {
                        if(!isset($val[$nameone]))
                            return response()->json(["status"=>false,"message"=>"json_validation_error",
                                "record_validation_error"=>[$request->kid => "$fieldSlug is missing $nameone index for a value"]],500);
                        if(!isset($val[$nametwo]))
                            return response()->json(["status"=>false,"message"=>"json_validation_error",
                                "record_validation_error"=>[$request->kid => "$fieldSlug is missing $nametwo index for a value"]],500);

                        if(!is_array($val[$nameone]))
                            $fone = $val[$nameone];
                        else
                            $fone = implode("[!]",$val[$nameone]);


                        if(!is_array($val[$nametwo]))
                            $ftwo = $val[$nametwo];
                        else
                            $ftwo = implode("[!]",$val[$nametwo]);

                        array_push($oneVals, $fone);
                        array_push($twoVals, $ftwo);
                    }
                    $recRequest[$flid] = '';
                    $recRequest[$flid . '_combo_one'] = $oneVals;
                    $recRequest[$flid . '_combo_two'] = $twoVals;
                } else if($type == 'Date') {
                    if(!isset($field['value']['month']) && !isset($field['value']['day']) && !isset($field['value']['year']))
                        return response()->json(["status"=>false,"message"=>"json_validation_error",
                            "record_validation_error"=>[$request->kid => "$fieldSlug is missing month, day, and year indices"]],500);
                    $recRequest['circa_' . $flid] = $field['value']['circa'];
                    $recRequest['month_' . $flid] = $field['value']['month'];
                    $recRequest['day_' . $flid] = $field['value']['day'];
                    $recRequest['year_' . $flid] = $field['value']['year'];
                    $recRequest['era_' . $flid] = $field['value']['era'];
                    $recRequest[$flid] = '';
                } else if($type == 'Schedule') {
                    $events = array();
                    foreach($field['value'] as $event) {
                        if(!isset($event['desc']) | !isset($event['begin']) | !isset($event['end']))
                            return response()->json(["status"=>false,"message"=>"json_validation_error",
                                "record_validation_error"=>[$request->kid => "$fieldSlug is missing desc, begin, or end indices for an event"]],500);

                        $string = $event['desc'] . ': ' . $event['begin'] . ' - ' . $event['end'];
                        array_push($events, $string);
                    }
                    $recRequest[$flid] = $events;
                } else if($type == 'Geolocator') {
                    $geo = array();
                    foreach($field['value'] as $loc) {
                        $geoReq = new Request();

                        if(isset($loc['lat'])) {
                            $geoReq->type = 'latlon';
                            $geoReq->lat = $loc['lat'];
                            $geoReq->lon = $loc['lon'];
                        } else if(isset($loc['zone'])) {
                            $geoReq->type = 'utm';
                            $geoReq->zone = $loc['zone'];
                            $geoReq->east = $loc['east'];
                            $geoReq->north = $loc['north'];
                        } else if(isset($loc['address'])) {
                            $geoReq->type = 'geo';
                            $geoReq->addr = $loc['address'];
                        }

                        if(!isset($loc['desc']))
                            return response()->json(["status"=>false,"message"=>"json_validation_error",
                                "record_validation_error"=>[$request->kid => "$fieldSlug is missing desc for a location"]],500);
                        $string = '[Desc]' . $loc['desc'] . '[Desc]';
                        $string .= GeolocatorField::geoConvert($geoReq);
                        array_push($geo, $string);
                    }
                    $recRequest[$flid] = $geo;
                } else if($type == 'Documents' | $type == 'Playlist' | $type == 'Video' | $type == '3D-Model') {
                    $files = array();
                    if(is_null($originRid))
                        $currDir = storage_path('app/tmpFiles/impU' . \Auth::user()->id);
                    else
                        $currDir = storage_path('app/tmpFiles/impU' . \Auth::user()->id . '/r' . $originRid . '/fl' . $flid);
                    $newDir = storage_path('app/tmpFiles/f' . $flid . 'u' . \Auth::user()->id . '/r' . $request->kid);
                    if(file_exists($newDir)) {
                        foreach(new \DirectoryIterator($newDir) as $file) {
                            if($file->isFile()) {
                                unlink($newDir . '/' . $file->getFilename());
                            }
                        }
                    } else {
                        mkdir($newDir, 0775, true);
                    }
                    foreach($field['value'] as $file) {
                        if(!isset($file['name']))
                            return response()->json(["status"=>false,"message"=>"json_validation_error",
                                "record_validation_error"=>[$request->kid => "$fieldSlug is missing name for a file"]],500);
                        $name = $file['name'];
                        //move file from imp temp to tmp files
                        copy($currDir . '/' . $name, $newDir . '/' . $name);
                        //add input for this file
                        array_push($files, $name);
                    }
                    $recRequest['file' . $flid] = $files;
                    $recRequest[$flid] = 'f' . $flid . 'u' . \Auth::user()->id . '/r' . $request->kid;
                } else if($type == 'Gallery') {
                    $files = array();
                    if(is_null($originRid))
                        $currDir = storage_path('app/tmpFiles/impU' . \Auth::user()->id);
                    else
                        $currDir = storage_path('app/tmpFiles/impU' . \Auth::user()->id . '/r' . $originRid . '/fl' . $flid);
                    $newDir = storage_path('app/tmpFiles/f' . $flid . 'u' . \Auth::user()->id . '/r' . $request->kid);
                    if(file_exists($newDir)) {
                        foreach(new \DirectoryIterator($newDir) as $file) {
                            if($file->isFile())
                                unlink($newDir . '/' . $file->getFilename());
                        }
                        if(file_exists($newDir . '/thumbnail')) {
                            foreach(new \DirectoryIterator($newDir . '/thumbnail') as $file) {
                                if($file->isFile())
                                    unlink($newDir . '/thumbnail/' . $file->getFilename());
                            }
                        }
                        if(file_exists($newDir . '/medium')) {
                            foreach(new \DirectoryIterator($newDir . '/medium') as $file) {
                                if($file->isFile())
                                    unlink($newDir . '/medium/' . $file->getFilename());
                            }
                        }
                    } else {
                        mkdir($newDir, 0775, true);
                        mkdir($newDir . '/thumbnail', 0775, true);
                        mkdir($newDir . '/medium', 0775, true);
                    }
                    foreach($field['value'] as $file) {
                        if(!isset($file['name']))
                            return response()->json(["status"=>false,"message"=>"json_validation_error",
                                "record_validation_error"=>[$request->kid => "$fieldSlug is missing name for a file"]],500);
                        $name = $file['name'];
                        //move file from imp temp to tmp files
                        copy($currDir . '/' . $name, $newDir . '/' . $name);
                        if(file_exists($currDir . '/thumbnail'))
                            copy($currDir . '/thumbnail/' . $name, $newDir . '/thumbnail/' . $name);
                        else {
                            $smallParts = explode('x',FieldController::getFieldOption($field,'ThumbSmall'));
                            $tImage = new \Imagick($newDir . '/' . $name);
                            $tImage->thumbnailImage($smallParts[0],$smallParts[1],true);
                            $tImage->writeImage($newDir . '/thumbnail/' . $name);
                        }
                        if(file_exists($currDir . '/medium'))
                            copy($currDir . '/medium/' . $name, $newDir . '/medium/' . $name);
                        else {
                            $largeParts = explode('x',FieldController::getFieldOption($field,'ThumbLarge'));
                            $mImage = new \Imagick($newDir . '/' . $name);
                            $mImage->thumbnailImage($largeParts[0],$largeParts[1],true);
                            $mImage->writeImage($newDir . '/medium/' . $name);
                        }
                        //add input for this file
                        array_push($files, $name);
                    }
                    $recRequest['file' . $flid] = $files;
                    $recRequest[$flid] = 'f' . $flid . 'u' . \Auth::user()->id . '/r' . $request->kid;
                } else if($type == 'Associator') {
                    //If KID format, treat value as normal, else we assume it will be built as a cross-Form association
                    $aVals = $field['value'];
                    $aRes = array();
                    $aTag = array();
                    foreach($aVals as $aVal) {
                        if(Record::isKIDPattern($aVal))
                            array_push($aRes,$aVal);
                        else
                            array_push($aTag,$aVal);
                    }

                    if(sizeof($aRes)>0)
                        $recRequest[$flid] = $aRes;
                    if(sizeof($aTag)>0)
                        $assocArray[$flid] = $aTag;
                }
            }
        }

        $recCon = new RecordController();
        $result = $recCon->store($pid,$fid,$recRequest);

        $resData = $result->getData(true);
        $resData['assocTag'] = $assocTag;
        $resData['assocArray'] = $assocArray;
        $resData['comboAssocArray'] = $comboAssocArray;
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
}
