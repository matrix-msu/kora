<?php namespace App\Http\Controllers;

use App\AssociatorField;
use App\ComboListField;
use App\DateField;
use App\DocumentsField;
use App\Form;
use App\GalleryField;
use App\GeneratedListField;
use App\GeolocatorField;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\ListField;
use App\ModelField;
use App\MultiSelectListField;
use App\NumberField;
use App\PlaylistField;
use App\Record;
use App\RecordPreset;
use App\RichTextField;
use App\ScheduleField;
use App\TextField;
use App\VideoField;
use Illuminate\Http\Request;
use RecursiveIteratorIterator;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;

class RecordPresetController extends Controller {
    /**
     * User must be logged in to access views in this controller.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('active');
    }

    /**
     * The record preset index.
     *
     * @param $pid
     * @param $fid
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    public function index($pid, $fid)
    {
        if(!FormController::validProjForm($pid,$fid)){
            return redirect('projects');
        }

        $form = FormController::getForm($fid);

        if (!\Auth::user()->isFormAdmin($form)){
            flash()->overlay(trans('controller_recordpreset.view'), trans('controller_recordpreset.whoops'));
            return redirect('projects');
        }

        $presets = RecordPreset::where('fid', '=', $fid)->get();

        return view('recordPresets/index', compact('form', 'presets'));
    }

    /**
     * Makes a record a preset for other records to be copied from.
     *
     * @param Request $request
     */
    public function presetRecord(Request $request)
    {
        $name = $request->name;
        $rid = $request->rid;

        if(!is_null(RecordPreset::where('rid', '=', $rid)->first())) {
            flash()->overlay(trans('controller_record.already'));
        }
        else {
            $record = RecordController::getRecord($rid);
            $fid = $record->fid;

            $preset = new RecordPreset();
            $preset->rid = $rid;
            $preset->fid = $fid;
            $preset->name = $name;

            $preset->save();

            $this->presetID = $preset->id;

            $preset->preset = json_encode($this->getRecordArray($rid));
            $preset->save();

            flash()->overlay(trans('controller_record.presetsaved'), trans('controller_record.success'));
        }
    }

    /**
     * Updates a record preset with the record's current values if it the record preset exists.
     *
     * @param $rid, record id.
     */
    public static function updateIfExists($rid) {
        $pre = RecordPreset::where("rid", '=', $rid)->first();

        if(is_null($pre)) {
            return;
        }
        else {
            $rpc = new RecordPresetController();
            $pre->preset = $rpc->getRecordArray($rid);
        }
    }

    /**
     * Changes a preset's name.
     *
     * @param Request $request
     */
    public function changePresetName(Request $request)
    {
        $name = $request->name;
        $id = $request->id;

        $preset = RecordPreset::where('id', '=', $id)->first();

        $preset->name = $name;
        $preset->save();
    }

    /**
     * Removes a record as a preset.
     *
     * @param Request $request
     */
    public function deletePreset(Request $request)
    {
        $id = $request->id;
        $preset = RecordPreset::where('id', '=', $id)->first();
        $preset->delete();

        //
        // Delete the preset's file directory.
        //
        $path = env('BASE_PATH').'storage/app/presetFiles/preset'. $id;

        if(is_dir($path)) {
            $it = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
            $files = new RecursiveIteratorIterator($it,
                RecursiveIteratorIterator::CHILD_FIRST);
            foreach($files as $file) {
                if ($file->isDir()){
                    rmdir($file->getRealPath());
                } else {
                    unlink($file->getRealPath());
                }
            }
            rmdir($path);
        }


        flash()->overlay(trans('controller_recordpreset.preset'), trans('controller_recordpreset.success'));
    }

    /**
     * Get the array associated with a certain record preset.
     *
     * @param Request $request
     * @return mixed
     */
    public function getData(Request $request) {
        $id = $request->id;
        $recordPreset = RecordPreset::where('id', '=', $id)->first();
        return json_decode($recordPreset->preset, true);
    }


    /**
     * Builds an array representing a record, saving its FLIDs for creation page population.
     *
     * @param $rid, the record's id.
     * @return mixed
     */
    public function getRecordArray($rid)
    {
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

            switch ($field->type) {
                case 'Text':
                    $textfield = TextField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->first();

                    if (!empty($textfield->text)) {
                        $data['text'] = $textfield->text;
                    }
                    else {
                        $data['text'] = null;
                    }

                    $flid_array[] = $field->flid;
                    break;

                case 'Rich Text':
                    $rtfield = RichTextField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->first();

                    if (!empty($rtfield->rawtext)) {
                        $data['rawtext'] = $rtfield->rawtext;
                    }
                    else {
                        $data['rawtext'] = null;
                    }

                    $flid_array[] = $field->flid;
                    break;

                case 'Number':
                    $numberfield = NumberField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->first();

                    if (!empty($numberfield->number)) {
                        $data['number'] = $numberfield->number;
                    }
                    else {
                        $data['number'] = null;
                    }

                    $flid_array[] = $field->flid;
                    break;

                case 'List':
                    $listfield = ListField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->first();

                    if (!empty($listfield->option)) {
                        $data['option'] = $listfield->option;
                    }
                    else {
                        $data['option'] = null;
                    }

                    $flid_array[] = $field->flid;
                    break;

                case 'Multi-Select List':
                    $mslfield = MultiSelectListField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->first();

                    if (!empty($mslfield->options)) {
                        $data['options'] = explode('[!]', $mslfield->options);
                    }
                    else {
                        $data['options'] = null;
                    }

                    $flid_array[] = $field->flid;
                    break;

                case 'Generated List':
                    $gnlfield = GeneratedListField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->first();

                    if (!empty($gnlfield->options)) {
                        $data['options'] = explode('[!]', $gnlfield->options);
                    }
                    else {
                        $data['options'] = null;
                    }

                    $flid_array[] = $field->flid;
                    break;

                case 'Date':
                    $datefield = DateField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->first();

                    if(!empty($datefield->circa)) {
                        $date_array['circa'] = $datefield->circa;
                    }
                    else {
                        $date_array['circa'] = null;
                    }

                    if(!empty($datefield->era)) {
                        $date_array['era'] = $datefield->era;
                    }
                    else {
                        $date_array['era'] = null;
                    }

                    if(!empty($datefield->day)) {
                        $date_array['day'] = $datefield->day;
                    }
                    else {
                        $date_array['day'] = null;
                    }

                    if(!empty($datefield->month)) {
                        $date_array['month'] = $datefield->month;
                    }
                    else {
                        $date_array['month'] = null;
                    }

                    if(!empty($datefield->year)) {
                        $date_array['year'] = $datefield->year;
                    }
                    else {
                        $date_array['year'] = null;
                    }

                    $data['data'] = $date_array;
                    $flid_array[] = $field->flid;
                    break;

                case 'Schedule':
                    $schedfield = ScheduleField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->first();

                    if(!empty($schedfield->events)) {
                        $data['events'] = explode('[!]', $schedfield->events);
                    }
                    else {
                        $data['events'] = null;
                    }


                    $flid_array[] = $field->flid;
                    break;

                case 'Geolocator':
                    $geofield = GeolocatorField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->first();

                    if (!empty($geofield->locations)) {
                        $data['locations'] = explode('[!]', $geofield->locations);
                    }
                    else {
                        $data['locations'] = null;
                    }

                    $flid_array[] = $field->flid;
                    break;

                case 'Combo List':
                    $cmbfield = ComboListField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->first();

                    if (!empty($cmbfield->options)) {
                        $data['combolists'] = explode('[!val!]', $cmbfield->options);
                    }
                    else {
                        $data['combolists'] = null;
                    }

                    $flid_array[] = $field->flid;
                    break;

                case 'Documents':
                    $docfield = DocumentsField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->first();

                    if (!empty($docfield->documents)) {
                        $data['documents'] = explode('[!]', $docfield->documents);
                    }
                    else {
                        $data['documents'] = null;
                    }

                    $flid_array[] = $field->flid;
                    $fileFields = true;
                    break;

                case 'Gallery':
                    $galfield = GalleryField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->first();

                    if (!empty($galfield->images)) {
                        $data['images'] = explode('[!]', $galfield->images);
                    }
                    else {
                        $data['images'] = null;
                    }

                    $flid_array[] = $field->flid;
                    $fileFields = true;
                    break;

                case 'Playlist':
                    $playfield = PlaylistField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->first();

                    if (!empty($playfield->audio)) {
                        $data['audio'] = explode('[!]', $playfield->audio);
                    }
                    else {
                        $data['audio'] = null;
                    }

                    $flid_array[] = $field->flid;
                    $fileFields = true;
                    break;

                case 'Video':
                    $vidfield = VideoField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->first();

                    if (!empty($vidfield->video)) {
                        $data['video'] = explode('[!]', $vidfield->video);
                    }
                    else {
                        $data['video'] = null;
                    }

                    $flid_array[] = $field->flid;
                    $fileFields = true;
                    break;

                case '3D-Model':
                    $modelfield = ModelField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->first();

                    if (!empty($modelfield->model)) {
                        $data['model'] = $modelfield->model;
                    }
                    else {
                        $data['model'] = null;
                    }

                    $flid_array[] = $field->flid;
                    $fileFields = true;
                    break;

                case 'Associator':
                    $assocfield = AssociatorField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->first();

                    if (!empty($assocfield->records)) {
                        $data['records'] = explode('[!]', $assocfield->records);
                    }
                    else {
                        $data['records'] = null;
                    }

                    $flid_array[] = $field->flid;
                    break;

                default:
                    // None other supported right now, though this list should be exhaustive.
                    break;
            }

            $field_array[$field->flid] = $data;
        }

        // A file field was in use, so we need to move the record files to a preset directory.
        if ($fileFields) {
            $this->moveFilesToPreset($record->rid);
        }

        $response['data'] = $field_array;
        $response['flids'] = $flid_array;
        return $response;
    }

    /**
     * Moves all of a particular record's files to a preset directory.
     *
     * @param $rid, the rid of the record whose files we are moving.
     */
    public function moveFilesToPreset($rid) {
        $presets_path = env('BASE_PATH').'storage/app/presetFiles';

        //
        // Create the presets file path if it does not exist.
        //
        if(!is_dir($presets_path)) {
            mkdir($presets_path, 0755, true);
        }

        $path = $presets_path . '/preset' . $this->presetID; // Path for the new preset's directory.

        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        // Build the record's directory.
        $record = RecordController::getRecord($rid);

        $record_path = env('BASE_PATH') . 'storage/app/files/p' . $record->pid . '/f' . $record->fid . '/r' . $record->rid;

        //
        // Recursively copy the record's file directory.
        //
        RecordPresetController::recurse_copy($record_path, $path);
    }

    /**
     *
     */
    public function moveFilesToTemp(Request $request) {
        $presetID = $request->presetID;
        $flid = $request->flid;
        $userID = $request->userID;

        $presetPath = env('BASE_PATH') . 'storage/app/presetFiles/preset' . $presetID . '/fl' . $flid;
        $tempPath = env('BASE_PATH') . 'storage/app/tmpFiles/f'. $flid . 'u' . $userID;

        //
        // If the temp directory exists for the user, clear out the existing files.
        // Else create the directory.
        //
        if(is_dir($tempPath)) {
            $it = new RecursiveDirectoryIterator($tempPath, RecursiveDirectoryIterator::SKIP_DOTS);
            $files = new RecursiveIteratorIterator($it,
                RecursiveIteratorIterator::CHILD_FIRST);
            foreach($files as $file) {
                if ($file->isDir()){
                    rmdir($file->getRealPath());
                } else {
                    unlink($file->getRealPath());
                }
            }
        }
        else {
            mkdir($tempPath, 0755, true);
        }

        //
        // Copy the preset directory to the temporary directory.
        //
        RecordPresetController::recurse_copy($presetPath, $tempPath);
    }

    /**
     * Recursively copy an entire directory.
     *
     * @param $src, source directory.
     * @param $dst, destination directory.
     * @author gimmicklessgpt, from php.net.
     */
    static public function recurse_copy($src, $dst) {
        $dir = opendir($src);

        if (!is_dir($dst) && !is_file($dst)) {
            mkdir($dst, 0755, true);
        }

        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                    RecordPresetController::recurse_copy($src . '/' . $file,$dst . '/' . $file);
                }
                else {
                    copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    private $presetID = -1; //The id of the preset we are currently working with. (-1 if we don't care)
}
