<?php namespace App\Http\Controllers;

use App\Form;
use App\KoraFields\FileTypeField;
use App\Record;
use App\RecordPreset;
use App\Revision;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RecursiveIteratorIterator;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;

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
        }

        //Move any record files
        $response['files'] = $record->getHashedRecordFiles();

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
    public function moveFilesToTemp(Request $request) { //TODO::CASTLE
        $presetID = $request->presetID;
        $flid = $request->flid;
        $userID = $request->userID;

        $presetPath = storage_path('app/presetFiles/preset' . $presetID . '/fl' . $flid);
        $tempPath = storage_path('app/tmpFiles/f'. $flid . 'u' . $userID);

        //
        // If the temp directory exists for the user, clear out the existing files.
        // Else create the directory.
        //
        if(is_dir($tempPath)) {
            $it = new RecursiveDirectoryIterator($tempPath, RecursiveDirectoryIterator::SKIP_DOTS);
            $files = new RecursiveIteratorIterator($it,
                RecursiveIteratorIterator::CHILD_FIRST);
            foreach($files as $file) {
                if ($file->isDir())
                    rmdir($file->getRealPath());
                else
                    unlink($file->getRealPath());
            }
        } else {
            mkdir($tempPath, 0775, true);
        }

        //
        // Copy the preset directory to the temporary directory.
        //
        self::recurse_copy($presetPath, $tempPath);
    }

    /**
     * Recursively copies a directory and its files to directory.
     *
     * @param  string $src - Directory to copy
     * @param  string $dst - Directory to copy to
     */
    public static function recurse_copy($src, $dst) { //TODO::CASTLE
        if(file_exists($src)) {
            $dir = opendir($src);

            if(!is_dir($dst) && !is_file($dst))
                mkdir($dst, 0775, true);

            while(false !== ($file = readdir($dir))) {
                if(($file != '.') && ($file != '..')) {
                    if(is_dir($src . '/' . $file))
                        self::recurse_copy($src . '/' . $file, $dst . '/' . $file);
                    else
                        copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
            closedir($dir);
        }
    }
}
