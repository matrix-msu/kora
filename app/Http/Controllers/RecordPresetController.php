<?php namespace App\Http\Controllers;

use App\DateField;
use App\Form;
use App\GeneratedListField;
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

    public function index($pid, $fid)
    {
        if(!FormController::validProjForm($pid,$fid)){
            return redirect('projects');
        }

        $form = FormController::getForm($fid);

        if (!\Auth::user()->isFormAdmin($form)){
            flash()->overlay('You do not have permission to view that page.', 'Whoops.');
            return redirect('projects');
        }

        $presets = RecordPreset::where('fid', '=', $fid)->get();

        return view('recordPresets/index', compact('form', 'presets'));
    }

    public function changePresetName(Request $request)
    {
        $name = $request->name;
        $id = $request->id;

        $preset = RecordPreset::where('id', '=', $id)->first();

        $preset->name = $name;
        $preset->save();
    }

    public function deletePreset(Request $request)
    {
        $id = $request->id;
        $preset = RecordPreset::where('id', '=', $id)->first();
        $preset->delete();

        flash()->overlay('Record has been removed as a preset.', 'Success!');
    }

    public function getRecordArray(Request $request)
    {
        $id = $request->id;
        $rid = RecordPreset::where('id', '=', $id)->first()->rid;
        $record = Record::where('rid', '=', $rid)->first();
        $form = Form::where('fid', '=', $record->fid)->first();

        $field_collect = $form->fields()->get();
        $field_array = array();

        foreach($field_collect as $field)
        {
            $data = array();
            $data['flid'] = $field->flid;
            $data['type'] = $field->type;

            if($field->type == 'Text') {
                $textfield = TextField::where('rid', '=', $record->rid)->first();
                $data['text'] = $textfield->text;
                $flid_array[] = $field->flid;
            }
            elseif($field->type == 'Rich Text') {
                $rtfield = RichTextField::where('rid', '=', $record->rid)->first();
                $data['rawtext'] = $rtfield->rawtext;
                $flid_array[] = $field->flid;
            }
            elseif($field->type == 'Number') {
                $numberfield = NumberField::where('rid', '=', $record->rid)->first();
                $data['number'] = $numberfield->number;
                $flid_array[] = $field->flid;
            }
            elseif($field->type == 'List') {
                $listfield = ListField::where('rid', '=', $record->rid)->first();
                $data['option'] = $listfield->option;
                $flid_array[] = $field->flid;
            }
            elseif($field->type == 'Multi-Select List') {
                $mslfield = MultiSelectListField::where('rid', '=', $record->rid)->first();
                $data['options'] = explode('[!]', $mslfield->options);
                $flid_array[] = $field->flid;
            }
            elseif($field->type == 'Generated List') {
                $gnlfield = GeneratedListField::where('rid', '=', $record->rid)->first();
                $data['options'] = explode('[!]', $gnlfield->options);
                $flid_array[] = $field->flid;
            }
            elseif($field->type == 'Date') {
                $datefield = DateField::where('rid', '=', $record->rid)->first();
                $date_array['circa'] = $datefield->circa;
                $date_array['era'] = $datefield->era;
                $date_array['day'] = $datefield->day;
                $date_array['month'] = $datefield->month;
                $date_array['year'] = $datefield->year;
                $data['data'] = $date_array;
                $flid_array[] = $field->flid;
            }
            elseif($field->type == 'Schedule') {
                $schedfield = ScheduleField::where('rid', '=', $record->rid)->first();
                $data['events'] = explode('[!]', $schedfield->events);
                $flid_array[] = $field->flid;
            }
            $field_array[$field->flid] = $data;
        }

        $response['data'] = $field_array;
        $response['flids'] = $flid_array;
        return $response;
    }
}
