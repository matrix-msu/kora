<?php namespace App\Http\Controllers;

use App\DateField;
use App\Form;
use App\GeneratedListField;
use App\GeolocatorField;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\ListField;
use App\MultiSelectListField;
use App\NumberField;
use App\Record;
use App\RecordPreset;
use App\RichTextField;
use App\ScheduleField;
use App\TextField;
use Illuminate\Http\Request;

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

        if(!is_null(RecordPreset::where('rid', '=', $rid)->first()))
            flash()->overlay(trans('controller_record.already'));
        else {
            $record = RecordController::getRecord($rid);
            $fid = $record->fid;

            $preset = new RecordPreset();
            $preset->rid = $rid;
            $preset->fid = $fid;
            $preset->name = $name;
            $preset->preset = json_encode(RecordPresetController::getRecordArray($rid));
            $preset->save();

            flash()->overlay(trans('controller_record.presetsaved'), trans('controller_record.success'));
        }
    }

    public function updateIfExists($rid) {
        $pre = RecordPreset::where("rid", '=', $rid)->first();

        if(is_null($pre)) {
            return;
        }
        else {
            $pre->preset = RecordPresetController::getRecordArray($rid);
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
     * @param Request $request
     * @return mixed
     */
    public function getRecordArray($rid)
    {
        $record = Record::where('rid', '=', $rid)->first();
        $form = Form::where('fid', '=', $record->fid)->first();

        $field_collect = $form->fields()->get();
        $field_array = array();
        $flid_array = array();

        foreach($field_collect as $field) {
            $data = array();
            $data['flid'] = $field->flid;
            $data['type'] = $field->type;

            switch ($field->type) {
                case 'Text':
                    $textfield = TextField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->first();
                    $data['text'] = $textfield->text;
                    $flid_array[] = $field->flid;
                    break;

                case 'Rich Text':
                    $rtfield = RichTextField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->first();
                    $data['rawtext'] = $rtfield->rawtext;
                    $flid_array[] = $field->flid;
                    break;

                case 'Number':
                    $numberfield = NumberField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->first();
                    $data['number'] = $numberfield->number;
                    $flid_array[] = $field->flid;
                    break;

                case 'List':
                    $listfield = ListField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->first();
                    $data['option'] = $listfield->option;
                    $flid_array[] = $field->flid;
                    break;

                case 'Multi-Select List':
                    $mslfield = MultiSelectListField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->first();
                    $data['options'] = explode('[!]', $mslfield->options);
                    $flid_array[] = $field->flid;
                    break;

                case 'Generated List':
                    $gnlfield = GeneratedListField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->first();
                    $data['options'] = explode('[!]', $gnlfield->options);
                    $flid_array[] = $field->flid;
                    break;

                case 'Date':
                    $datefield = DateField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->first();
                    $date_array['circa'] = $datefield->circa;
                    $date_array['era'] = $datefield->era;
                    $date_array['day'] = $datefield->day;
                    $date_array['month'] = $datefield->month;
                    $date_array['year'] = $datefield->year;
                    $data['data'] = $date_array;
                    $flid_array[] = $field->flid;
                    break;

                case 'Schedule':
                    $schedfield = ScheduleField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->first();
                    $data['events'] = explode('[!]', $schedfield->events);
                    $flid_array[] = $field->flid;
                    break;

                case 'Geolocator':
                    $geofield = GeolocatorField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->first();
                    $data['locations'] = explode('[!]', $geofield->locations);
                    $flid_array[] = $field->flid;
                    break;

                default:
                    //Presets not supported for any other field types.
                    break;
            }

            $field_array[$field->flid] = $data;
        }

        $response['data'] = $field_array;
        $response['flids'] = $flid_array;
        return $response;
    }
}
