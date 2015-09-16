<?php namespace App\Http\Controllers;

use App\DocumentsField;
use App\Form;
use App\Field;
use App\GeolocatorField;
use App\Record;
use App\Revision;
use App\DateField;
use App\TextField;
use App\ListField;
use App\NumberField;
use App\ScheduleField;
use App\Http\Requests;
use App\RichTextField;
use App\GeneratedListField;
use Illuminate\Http\Request;
use App\MultiSelectListField;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use PhpParser\Comment\Doc;


class RevisionController extends Controller {

    /**
     * User must be logged in to access views in this controller.
     */
    public function __construct() {
        $this->middleware('auth');
        $this->middleware('active');
    }

    /**
     * Displays the fifty most recent record revisions index for the particular form.
     *
     * @param string $pid
     * @param $fid
     * @param string $rid
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    public function index($pid='', $fid, $rid=''){

        if(!\Auth::user()->admin && !\Auth::user()->isFormAdmin(FormController::getForm($fid)))
        {
            $pid = FormController::getForm($fid)->pid;
            flash()->overlay('You do not have permission to view that page.', 'Whoops.');
            return redirect('projects/'.$pid.'/forms/'.$fid);
        }

        $revisions = DB::table('revisions')->where('fid', '=', $fid)->orderBy('created_at', 'desc')->take(50)->get();

        $rid_array = array();
        foreach($revisions as $revision){
            $rid_array[] = $revision->rid;
        }
        $rid_array = array_values(array_unique($rid_array));

        $form = FormController::getForm($fid);
        $pid = $form->pid;
        $records = array();

        $temp = array_values(array_unique(Revision::lists('rid')));

        for($i=0; $i < count($temp); $i++)
        {
            if(in_array($temp[$i], $rid_array)) {
                $records[$temp[$i]] = $pid . '-' . $form->fid . '-' . $temp[$i];
            }
        }
        $message = 'Recent';

        return view('revisions.index', compact('revisions', 'records', 'form', 'message'));
    }

    /**
     * Shows the revision history for a particular record, still functional if the record is deleted.
     *
     * @param $pid
     * @param $fid
     * @param $rid
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|string
     */
    public function show($pid, $fid, $rid)
    {
        if(!FormController::validProjForm($pid, $fid)){
            return redirect('projects/'.$pid.'/forms');
        }

        if(!\Auth::user()->admin && !\Auth::user()->isFormAdmin(FormController::getForm($fid)) && \Auth::user()->id != $owner)
        {
            flash()->overlay('You do not have permission to view that page.', 'Whoops.');
            return redirect('projects/'.$pid.'/forms/'.$fid);
        }

        $form = FormController::getForm($fid);
        $revisions = DB::table('revisions')->where('rid', '=', $rid)->orderBy('created_at','desc')->take(50)->get();

        $pid = $form->pid;
        $records = array();

        $temp = array_values(array_unique(Revision::lists('rid')));

        for($i=0; $i < count($temp); $i++)
        {
            $records[$temp[$i]] = $pid.'-'.$form->fid.'-'.$temp[$i];
        }
        $message = $pid.'-'.$fid.'-'.$rid;

        return view('revisions.index', compact('revisions', 'records', 'form', 'message', 'rid'))->render();
    }

    /**
     * Rolls back a record.
     *
     * @param Request $request
     */
    public function rollback(Request $request)
    {
        $revision = Revision::where('id', '=', $request['revision'])->first();
        $form = FormController::getForm($revision->fid);

        if ($revision->type == 'create'){
            $record = Record::where('rid', '=', $revision->rid)->first();
            $revision = RevisionController::storeRevision($record->rid, 'delete');
            $record->delete();

            flash()->overlay('Record '.$form->pid.'-'.$form->fid.'-'.$revision->rid.' has been deleted.', 'Success!' );
        }
        elseif($revision->type == 'delete'){
            if(RecordController::exists($revision->rid)){
                flash()->overlay('Cannot recreate a record that already exists.');
            }
            else {
                $record = new Record();
                $record->rid = $revision->rid;
                $record->fid = $revision->fid;
                $record->pid = $form->pid;
                $record->owner = $revision->owner;
                $record->save();
                $record->kid = $record->pid . '-' . $record->fid . '-' . $record->rid;
                $record->save();

                RevisionController::redo($record, $form, $revision, false);
                RevisionController::storeRevision($record->rid, 'create');

                flash()->overlay('Record ' . $form->pid . '-' . $form->fid . '-' . $record->rid . ' has been rolled back.', 'Success!');
            }
        }
        else{
            $record = RecordController::getRecord($revision->rid);

            RevisionController::redo($record, $form, $revision, true);

            flash()->overlay('Record '.$form->pid.'-'.$form->fid.'-'.$record->rid.' has been rolled back.', 'Success!');
        }
    }

    /**
     * Does the actual rolling back using the data array from a particular revision.
     * This is essentially doing the opposite of RevisionController::buildDataArray(Record $record)
     *
     * @param Record $record
     * @param Form $form
     * @param Revision $revision
     * @param $flag
     */
    public static function redo(Record $record, Form $form, Revision $revision, $flag)
    {
        $data = json_decode($revision->data, true);

        if($flag) {
            $new_revision = RevisionController::storeRevision($record->rid, 'rollback');
            $new_revision->oldData = $revision->data;
            $new_revision->save();
        }

        foreach($form->fields()->get() as $field) {
            if ($field->type == 'Text') {
                if($revision->type != 'delete') {
                    foreach ($record->textfields()->get() as $textfield) {
                        if ($textfield->flid == $field->flid) {
                            $textfield->text = $data['textfields'][$field->flid]['data'];
                            $textfield->save();
                        }
                    }
                }
                else {
                    if(!is_null($data['textfields'][$field->flid]['data'])) {
                        $textfield = new TextField();
                        $textfield->flid = $field->flid;
                        $textfield->rid = $record->rid;
                        $textfield->text = $data['textfields'][$field->flid]['data'];
                        $textfield->save();
                    }
                }
            } elseif ($field->type == 'Rich Text') {
                if($revision->type != 'delete') {
                    foreach ($record->richtextfields()->get() as $rtfield) {
                        if ($rtfield->flid == $field->flid) {
                            $rtfield->rawtext = $data['richtextfields'][$field->flid]['data'];
                            $rtfield->save();
                        }
                    }
                }
                else {
                    if(!is_null($data['richtextfields'][$field->flid]['data'])) {
                        $rtfield = new RichTextField();
                        $rtfield->flid = $field->flid;
                        $rtfield->rid = $record->rid;
                        $rtfield->rawtext = $data['richtextfields'][$field->flid]['data'];
                        $rtfield->save();
                    }
                }
            } elseif ($field->type == 'Number') {
                if($revision->type != 'delete') {
                    foreach ($record->numberfields()->get() as $numberfield) {
                        if ($numberfield->flid == $field->flid) {
                            $numberfield->number = $data['numberfields'][$field->flid]['data'];
                            $numberfield->save();
                        }
                    }
                }
                else {
                    if(!is_null($data['numberfields'][$field->flid]['data'])) {
                        $numberfield = new NumberField();
                        $numberfield->flid = $field->flid;
                        $numberfield->rid = $record->rid;
                        $numberfield->number = $data['numberfields'][$field->flid]['data'];
                        $numberfield->save();
                    }
                }
            } elseif ($field->type == 'List') {
                if($revision->type != 'delete') {
                    foreach ($record->listfields()->get() as $listfield) {
                        if ($listfield->flid == $field->flid) {
                            $listfield->option = $data['listfields'][$field->flid]['data'];
                            $listfield->save();
                        }
                    }
                }
                else {
                    if(!is_null($data['listfields'][$field->flid]['data'])) {
                        $listfield = new ListField();
                        $listfield->flid = $field->flid;
                        $listfield->rid = $record->rid;
                        $listfield->option = $data['listfields'][$field->flid]['data'];
                        $listfield->save();
                    }
                }
            } elseif ($field->type == 'Multi-Select List') {
                if($revision->type != 'delete') {
                    foreach ($record->multiselectlistfields()->get() as $mslfield) {
                        if ($mslfield->flid == $field->flid) {
                            $mslfield->options = $data['multiselectlistfields'][$field->flid]['data'];
                            $mslfield->save();
                        }
                    }
                }
                else {
                    if(!is_null($data['multiselectlistfields'][$field->flid]['data'])) {
                        $mslfield = new MultiSelectListField();
                        $mslfield->flid = $field->flid;
                        $mslfield->rid = $record->rid;
                        $mslfield->options = $data['multiselectlistfields'][$field->flid]['data'];
                        $mslfield->save();
                    }
                }
            } elseif ($field->type == 'Generated List') {
                if($revision->type != 'delete') {
                    foreach ($record->generatedlistfields()->get() as $genlistfield) {
                        if ($genlistfield->flid == $field->flid) {
                            $genlistfield->options = $data['generatedlistfields'][$field->flid]['data'];
                            $genlistfield->save();
                        }
                    }
                }
                else {
                    if(!is_null($data['generatedlistfields'][$field->flid]['data'])) {
                        $genlistfield = new GeneratedListField();
                        $genlistfield->flid = $field->flid;
                        $genlistfield->rid = $record->rid;
                        $genlistfield->options = $data['generatedlistfields'][$field->flid]['data'];
                        $genlistfield->save();
                    }
                }
            } elseif ($field->type == 'Date') {
                if($revision->type != 'delete') {
                    foreach ($record->datefields()->get() as $datefield) {
                        if ($datefield->flid == $field->flid) {
                            $datefield->circa = $data['datefields'][$field->flid]['data']['circa'];
                            $datefield->month = $data['datefields'][$field->flid]['data']['month'];
                            $datefield->day = $data['datefields'][$field->flid]['data']['day'];
                            $datefield->year = $data['datefields'][$field->flid]['data']['year'];
                            $datefield->era = $data['datefields'][$field->flid]['data']['era'];
                            $datefield->save();
                        }
                    }
                }
                else {
                    if(!is_null($data['datefields'][$field->flid]['data'])) {
                        $datefield = new DateField();
                        $datefield->flid = $field->flid;
                        $datefield->rid = $record->rid;
                        $datefield->circa = $data['datefields'][$field->flid]['data']['circa'];
                        $datefield->month = $data['datefields'][$field->flid]['data']['month'];
                        $datefield->day = $data['datefields'][$field->flid]['data']['day'];
                        $datefield->year = $data['datefields'][$field->flid]['data']['year'];
                        $datefield->era = $data['datefields'][$field->flid]['data']['era'];
                        $datefield->save();
                    }
                }
            } elseif ($field->type == 'Schedule') {
                if($revision->type != 'delete') {
                    foreach ($record->schedulefields()->get() as $schedulefield) {
                        if ($schedulefield->flid == $field->flid) {
                            $schedulefield->events = $data['schedulefields'][$field->flid]['data'];
                            $schedulefield->save();
                        }
                    }
                }
                else {
                    if(!is_null($data['schedulefields'][$field->flid]['data'])) {
                        $schedulefield = new ScheduleField();
                        $schedulefield->flid = $field->flid;
                        $schedulefield->rid = $record->rid;
                        $schedulefield->events = $data['schedulefields'][$field->flid]['data'];
                        $schedulefield->save();
                    }
                }
            } elseif ($field->type == 'Geolocator') {
                if($revision->type != 'delete') {
                    foreach ($record->geolocatorfields()->get() as $geolocatorfield) {
                        if ($geolocatorfield->flid == $field->flid) {
                            $geolocatorfield->locations = $data['geolocatorfields'][$field->flid]['data'];
                            $geolocatorfield->save();
                        }
                    }
                }
                else {
                    if(!is_null($data['geolocatorfields'][$field->flid]['data'])) {
                        $geolocatorfield = new GeolocatorField();
                        $geolocatorfield->flid = $field->flid;
                        $geolocatorfield->rid = $record->rid;
                        $geolocatorfield->locations = $data['geolocatorfields'][$field->flid]['data'];
                        $geolocatorfield->save();
                    }
                }
            } elseif ($field->type == 'Documents') {
                if($revision->type != 'delete') {
                    foreach ($record->documentsfields()->get() as $documentsfield) {
                        if ($documentsfield->flid == $field->flid) {
                            $documentsfield->documents = $data['documentsfields'][$field->flid]['data'];
                            $documentsfield->save();
                        }
                    }
                }
                else {
                    if(!is_null($data['documentsfields'][$field->flid]['data'])) {
                        $documentsfield = new DocumentsField();
                        $documentsfield->flid = $field->flid;
                        $documentsfield->rid = $record->rid;
                        $documentsfield->documents = $data['documentsfields'][$field->flid]['data'];
                        $documentsfield->save();
                    }
                }
            }
        }
    }

    /**
     * Stores a new revision. Called on record creation, deletion, or edit.
     *
     * @param $rid
     * @param $type
     * @return Revision
     */
    public static function storeRevision($rid, $type)
    {
        $revision = new Revision();
        $record = RecordController::getRecord($rid);

        $fid = $record->form()->first()->fid;
        $revision->fid = $fid;
        $revision->rid = $record->rid;
        $revision->owner = $record->owner;
        $revision->userId = \Auth::user()->id;
        $revision->type = $type;

        $revision->data = RevisionController::buildDataArray($record);

        $revision->rollback = 1;
        $revision->save();

        return $revision;
    }

    /**
     * Builds up an array that functions similarly to the field object. Json encoded for storage.
     *
     * @param Record $record
     * @return string
     */
    public static function buildDataArray(Record $record)
    {
        $data = array();
        $form = Form::where('fid', '=', $record->fid)->first();


        /* Check each field and get the data associated with it.
         *
         * Complexities occur when forming the possibly large associative array describing a record's fields.
         * For each field, the general case is as follows: the general field type is checked and name is acquired,
         * if the field has data at its lower, less general level (e.g. TextFields), it is assigned to the data array,
         * else null is assigned.
         */
        foreach($form->fields()->get() as $field) {
            switch ($field->type)
            {
                case 'Text':
                    $data['textfields'][$field->flid]['name'] = $field->name;
                    $textfield = TextField::where('flid', '=', $field->flid)->first();
                    if(!is_null($textfield))
                        $data['textfields'][$field->flid]['data'] = $textfield->text;
                    else
                        $data['textfields'][$field->flid]['data'] = null;
                    break;

                case 'Rich Text':
                    $data['richtextfields'][$field->flid]['name'] = $field->name;
                    $rtfield = RichTextField::where('flid', '=', $field->flid)->first();
                    if(!is_null($rtfield))
                        $data['richtextfields'][$field->flid]['data'] = $rtfield->rawtext;
                    else
                        $data['richtextfields'][$field->flid]['data'] = null;
                    break;

                case 'Number':
                    $data['numberfields'][$field->flid]['name'] = $field->name;
                    $numberfield = NumberField::where('flid', '=', $field->flid)->first();
                    if(!is_null($numberfield))
                    {
                        $numberdata = array();
                        $numberdata['number'] = $numberfield->number;

                        if($numberfield->number != '')
                            $numberdata['unit'] = FieldController::getFieldOption($field, 'Unit');
                        else
                            $nubmerdata['unit'] = '';

                        $data['numberfields'][$field->flid]['data'] = $numberdata;
                    }
                    else
                        $data['numberfields'][$field->flid]['data'] = null;
                    break;

                case 'List':
                    $data['listfields'][$field->flid]['name'] = $field->name;
                    $listfield = ListField::where('flid', '=', $field->flid)->first();

                    if(!is_null($listfield))
                        $data['listfields'][$field->flid]['data'] = $listfield->option;
                    else
                        $data['listfields'][$field->flid]['data'] = null;
                    break;

                case 'Multi-Select List':
                    $data['multiselectlistfields'][$field->flid]['name'] = $field->name;
                    $mslfield = MultiSelectListField::where('flid', '=', $field->flid)->first();
                    if(!is_null($mslfield))
                        $data['multiselectlistfields'][$field->flid]['data'] = $mslfield->options;
                    else
                        $data['multiselectlistfields'][$field->flid]['data'] = null;
                    break;

                case 'Generated List':
                    $data['generatedlistfields'][$field->flid]['name'] = $field->name;
                    $genfield = GeneratedListField::where('flid', '=', $field->flid)->first();
                    if(!is_null($genfield))
                        $data['generatedlistfields'][$field->flid]['name'] = $genfield->options;
                    else
                        $data['generatedlistfields'][$field->flid]['name'] = null;
                    break;

                case 'Date':
                    $data['datefields'][$field->flid]['name'] = $field->name;
                    $datefield = DateField::where('flid', '=', $field->flid)->first();
                    if(!is_null($datefield))
                    {
                        $datedata = array();

                        $datedata['format'] = FieldController::getFieldOption($field, 'Format');
                        if (FieldController::getFieldOption($field, 'Circa') == 'Yes')
                            $datedata['circa'] = $datefield->circa;
                        else
                            $datedata['circa'] = '';

                        $datedata['day'] = $datefield->day;
                        $datedata['month'] = $datefield->month;
                        $datedata['year'] = $datefield->year;

                        if (FieldController::getFieldOption($field, 'Era') == 'Yes')
                            $datedata['era'] = $datefield->era;
                        else
                            $datedata['era'] = '';

                        $data['datefields'][$field->flid]['data'] = $datedata;
                    }
                    else
                        $data['datefields'][$field->flid]['data'] = null;
                    break;

                case 'Schedule':
                    $data['schedulefields'][$field->flid]['name'] = $field->name;
                    $schedulefield = ScheduleField::where('flid', '=', $field->flid)->first();
                    if(!is_null($schedulefield))
                        $data['schedulefields'][$field->flid]['data'] = $schedulefield->events;
                    else
                        $data['schedulefields'][$field->flid]['data'] = null;
                    break;

                case 'Geolocator':
                    $data['geolocatorfields'][$field->flid]['name'] = $field->name;
                    $geofield = GeolocatorField::where('flid', '=', $field->flid)->first();
                    if(!is_null($geofield))
                        $data['geolocatorfields'][$field->flid]['data'] = $geofield->locations;
                    else
                        $data['geolocatorfields'][$field->flid]['data'] = null;
                    break;

                case 'Documents':
                    $data['documentsfields'][$field->flid]['name'] = $field->name;
                    $docfield = DocumentsField::where('flid', '=', $field->flid)->first();
                    if(!is_null($docfield))
                        $data['documentsfields'][$field->flid]['data'] = $docfield->documents;
                    else
                        $data['documentsfields'][$field->flid]['data'] = null;
                    break;
            }

        }

        /* Have to see which method is better, for now we'll use json_encode (remember to use json_decode($array, true)).
           Alternative method is presented here. The base64_encode method might end up working
           better for things other than simple text and lists. Who knows though.

        $revision->data = base64_encode(serialize($record));
        To decode: $decode = unserialize(base64_decode(serialize($revision->data)));
        */

        return json_encode($data);
    }

    /**
     * Wipes ability to rollback any revisions. Called on field creation, deletion, or edit.
     *
     * @param $fid
     */
    public static function wipeRollbacks($fid)
    {
        $revisions = Revision::where('fid','=',$fid)->get();

        foreach($revisions as $revision)
        {
            $revision->rollback = 0;
            $revision->save();
        }
    }
}
