<?php namespace App\Http\Controllers;

use App\KoraFields\ComboListField;
use App\KoraFields\FileTypeField;
use App\Record;
use App\RecordPreset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RecordPresetController extends Controller {

    /*
    |--------------------------------------------------------------------------
    | Record Preset Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles creation and management of record presets
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
     * Gets the view for managing existing presets.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @return View
     */
    public function index($pid, $fid) {
        if(!FormController::validProjForm($pid, $fid))
            return redirect('projects/'.$pid)->with('k3_global_error', 'form_invalid');

        $form = FormController::getForm($fid);

        if(!(\Auth::user()->isFormAdmin($form)))
            return redirect('projects/'.$pid)->with('k3_global_error', 'not_form_admin');

        $presets = RecordPreset::where('form_id', '=', $fid)->get();

        return view('recordPresets/index', compact('form', 'presets'));
    }

    /**
     * Copies a record and saves it as a record preset template.
     *
     * @param  Request $request
     * @return JsonResponse
     */
    public function presetRecord(Request $request) {
        $name = $request->name;
        $kid = $request->kid;

        if(!is_null(RecordPreset::where('record_kid', '=', $kid)->first())) {
            return response()->json(["status"=>false,"message"=>"record_already_preset"],500);
        } else {
            $record = RecordController::getRecord($kid);
            $preset = new RecordPreset();
            $preset->form_id = $record->form_id;
            $preset->record_kid = $record->kid;

            $preset->preset = $this->getRecordArray($record, $name);
            $preset->save();

            return response()->json(["status"=>true,"message"=>"record_preset_saved"],200);
        }
    }

    /**
     * Takes a record and turns it into an array that is saved in the record preset.
     *
     * @param  Record $record - Record model
     * @param  string $name - Name of preset
     * @return array - The data array
     */
    public function getRecordArray($record, $name) {
        $form = FormController::getForm($record->form_id);

        $fields = $form->layout["fields"];
        $dataArray = array();

        foreach($fields as $flid => $field) {
            $dataArray[$flid] = $record->{$flid};
            //If file field, move the data
            if(!is_null($record->{$flid}) && $form->getFieldModel($field['type']) instanceof FileTypeField) {
                switch(config('filesystems.kora_storage')) {
                    case FileTypeField::_LaravelStorage:
                        //Clear the current directory
                        $dir = storage_path('app/presetFiles/'.$record->kid);
                        if(file_exists($dir)) {
                            foreach(new \DirectoryIterator($dir) as $file) {
                                if($file->isFile())
                                    unlink($dir.'/'.$file->getFilename());
                            }
                        } else {
                            mkdir($dir,0775,true); //Make it!
                        }

                        $recordFiles = json_decode($record->{$flid},true);
                        foreach($recordFiles as $recordFile) {
                            //Get file location
                            $filename = isset($recordFile['timestamp']) ? $recordFile['timestamp'].'.'.$recordFile['name'] : $recordFile['name'];
                            $filePath = storage_path('app/files/'.$record->project_id.'/'.$record->form_id.'/'.$record->id);
                            //Copy the file
                            copy($filePath.'/'.$filename, $dir.'/'.$recordFile['name']);
                        }
                        break;
                    case FileTypeField::_JoyentManta:
                        //TODO::MANTA
                        break;
                    default:
                        break;
                }
            } else if(!is_null($record->{$flid}) && $form->getFieldModel($field['type']) instanceof ComboListField) {
                $subField1 = $field['one']['flid'];
                $subField2 = $field['two']['flid'];
                $subType1 = $field['one']['type'];
                $subType2 = $field['two']['type'];
                $comboVal = [];
                foreach($form->getFieldModel($field['type'])->retrieve($flid, $form->id, $dataArray[$flid]) as $comboRow) {
                    $valOne = $comboRow->{$subField1};
                    switch($subType1) {
                        case \App\Form::_BOOLEAN:
                            $displayOne = $valOne ? 'true' : 'false';
                            break;
                        case \App\Form::_MULTI_SELECT_LIST:
                        case \App\Form::_GENERATED_LIST:
                        case \App\Form::_ASSOCIATOR:
                            $vals = json_decode($valOne);
                            $displayOne = implode(',',$vals);
                            break;
                        case \App\Form::_HISTORICAL_DATE:
                            $dateParts = json_decode($valOne,true);
                            $dateArray = [$dateParts['year']];

                            if(!is_null($dateParts['month']) && $dateParts['month']!='') {
                                $dateArray[] = $dateParts['month'];
                                if(!is_null($dateParts['day']) && $dateParts['day']!='')
                                    $dateArray[] = $dateParts['day'];
                            }

                            $displayOne = implode('-',$dateArray);

                            if(!is_null($dateParts['prefix']) && $dateParts['prefix']!='')
                                $displayOne = $dateParts['prefix'].' '.$displayOne;

                            if(!is_null($dateParts['era']) && $dateParts['era']!='')
                                $displayOne .= ' '.$dateParts['era'];
                            break;
                        default:
                            $displayOne = $valOne;
                            break;
                    }

                    $valTwo = $comboRow->{$subField2};
                    switch($subType2) {
                        case \App\Form::_BOOLEAN:
                            $displayTwo = $valTwo ? 'true' : 'false';
                            break;
                        case \App\Form::_MULTI_SELECT_LIST:
                        case \App\Form::_GENERATED_LIST:
                        case \App\Form::_ASSOCIATOR:
                            $vals = json_decode($valTwo);
                            $displayTwo = implode(',',$vals);
                            break;
                        case \App\Form::_HISTORICAL_DATE:
                            $dateParts = json_decode($valTwo,true);
                            $dateArray = [$dateParts['year']];

                            if(!is_null($dateParts['month']) && $dateParts['month']!='') {
                                $dateArray[] = $dateParts['month'];
                                if(!is_null($dateParts['day']) && $dateParts['day']!='')
                                    $dateArray[] = $dateParts['day'];
                            }

                            $displayTwo = implode('-',$dateArray);

                            if(!is_null($dateParts['prefix']) && $dateParts['prefix']!='')
                                $displayTwo = $dateParts['prefix'].' '.$displayTwo;

                            if(!is_null($dateParts['era']) && $dateParts['era']!='')
                                $displayTwo .= ' '.$dateParts['era'];
                            break;
                        default:
                            $displayTwo = $valTwo;
                            break;
                    }

                    $comboVal[] = ['cfOne'=>$valOne, 'cfDisOne'=>$displayOne, 'cfTwo'=>$valTwo, 'cfDisTwo'=>$displayTwo];
                }
                $dataArray[$flid] = $comboVal;
            }
        }

        //Move any record files
        $response['data'] = $dataArray;
        $response['name'] = $name;

        return $response;
    }

    /**
     * Gets the data from a record preset for record creation.
     *
     * @param  Request $request
     * @return array - The record data
     */
    public function getData(Request $request) {
        $id = $request->id;
        $recordPreset = RecordPreset::where('id', $id)->first();
        $presetData = $recordPreset->preset;

        $form = FormController::getForm($recordPreset->form_id);
        $layout = $form->layout['fields'];
        $presetData['fields'] = $layout;

        return $presetData;
    }

    /**
     * Updates a record's preset if one was made.
     *
     * @param  Record $record - Record Model
     */
    public static function updateIfExists($record) {
        $pre = RecordPreset::where("record_kid", '=', $record->kid)->first();

        if(!is_null($pre)) {
            $rpc = new self();
            $pre->preset = $rpc->getRecordArray($record, $pre->preset['name']);
            $pre->save();
        }
    }

    /**
     * Changes the saved name of the preset.
     *
     * @param  Request $request
     */
    public function changePresetName(Request $request) {
        $name = $request->name;
        $id = $request->id;

        $preset = RecordPreset::where('id', $id)->first();

        $array = $preset->preset;
        $array['name'] = $name;
        $preset->preset = $array;
        $preset->save();
    }

    /**
     * Deletes a record preset.
     *
     * @param  Request $request
     * @return JsonResponse
     */
    public function deletePreset(Request $request) {
        $id = $request->id;
        $preset = RecordPreset::where('id', $id)->first();
        $preset->delete();

        return response()->json(["status"=>true,"message"=>"record_preset_deleted"],200);
    }

    /**
     * Moves file to tmp directory
     *
     * @param  Request $request
     */
    public function moveFilesToTemp(Request $request) {
        $presetID = $request->presetID;

        $preset = RecordPreset::where('id',$presetID)->first();

        switch(config('filesystems.kora_storage')) {
            case FileTypeField::_LaravelStorage:
                //Clear the current directory
                $dir = storage_path('app/tmpFiles/'.$request->tmpFileDir);
                if(file_exists($dir)) {
                    foreach(new \DirectoryIterator($dir) as $file) {
                        if($file->isFile())
                            unlink($dir.'/'.$file->getFilename());
                    }
                } else {
                    mkdir($dir,0775,true); //Make it!
                }

                //Restore old files
                //Get file location
                $presetDir = storage_path('app/presetFiles/'.$preset->record_kid);
                //Move the file
                if(file_exists($presetDir)) {
                    foreach(new \DirectoryIterator($presetDir) as $file) {
                        if($file->isFile())
                            copy($presetDir.'/'.$file->getFilename(),$dir.'/'.$file->getFilename());
                    }
                }

                break;
            case FileTypeField::_JoyentManta:
                //TODO::MANTA
                break;
            default:
                break;
        }
    }
}
