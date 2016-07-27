<?php namespace App\Http\Controllers;

use App\ComboListField;
use App\DocumentsField;
use App\Form;
use App\Field;
use App\GalleryField;
use App\GeolocatorField;
use App\ModelField;
use App\PlaylistField;
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
use App\VideoField;
use Illuminate\Database\Eloquent\Model;
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
    public function index($pid, $fid){

        if(!FormController::validProjForm($pid,$fid)){
            return redirect('projects/'.$pid.'/forms');
        }

        if(!\Auth::user()->admin && !\Auth::user()->isFormAdmin(FormController::getForm($fid)))
        {
            $pid = FormController::getForm($fid)->pid;
            flash()->overlay(trans('controller_revision.permission'), trans('controller_revision.whoops'));
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

        $temp = array_values(array_unique(Revision::lists('rid')->all()));

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

        $owner = DB::table('revisions')->where('rid', '=', $rid)->orderBy('created_at','desc')->first()->owner;

        if(!\Auth::user()->admin && !\Auth::user()->isFormAdmin(FormController::getForm($fid)) && \Auth::user()->id != $owner)
        {
            flash()->overlay(trans('controller_revision.permission'), trans('controller_revision.whoops'));
            return redirect('projects/'.$pid.'/forms/'.$fid);
        }

        $form = FormController::getForm($fid);
        $revisions = DB::table('revisions')->where('rid', '=', $rid)->orderBy('created_at','desc')->take(50)->get();

        $pid = $form->pid;
        $records = array();

        $temp = array_values(array_unique(Revision::lists('rid')->all()));

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

            flash()->overlay(trans('controller_revision.record').$form->pid.'-'.$form->fid.'-'.$revision->rid.trans('controller_revision.delete'), trans('controller_revision.success') );
        }
        elseif($revision->type == 'delete'){
            if(RecordController::exists($revision->rid)){
                flash()->overlay(trans('controller_revision.exists'));
            }
            else {
                // We must create a new record
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

                flash()->overlay(trans('controller_revision.record') . $form->pid . '-' . $form->fid . '-' . $record->rid . trans('controller_revision.rollback'), trans('controller_revision.success'));
            }
        }
        else{
            $record = RecordController::getRecord($revision->rid);

            RevisionController::redo($record, $form, $revision, true);

            flash()->overlay(trans('controller_revision.record').$form->pid.'-'.$form->fid.'-'.$record->rid.trans('controller_revision.rollback'), trans('controller_revision.success'));
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
        //
        // If the record exists (revision type is not a deletion) and the less general field (in this case TextField)
        // type exists in the database, the data is simply assigned from the old data array as expected in the rollback.
        //
        // Else a new less general field is created for the record and its values are appropriately assigned.
        // E.g. if a Text field under a certain rid does not exist we create a new one and fill it with necessary data.
        foreach($form->fields()->get() as $field) {

            // TODO: Apply some OOP techniques here.
            //  Maybe?

            switch($field->type) {
                // Text Assignment
                case 'Text':
                    $textfield = TextField::where('flid', '=', $field->flid)->where('rid', '=', $record->rid)->first();
                    if ($revision->type != 'delete' && !is_null($textfield)) {
                        $textfield->text = $data['textfields'][$field->flid]['data'];
                        $textfield->save();
                    } else {
                        $textfield = new TextField();
                        $textfield->flid = $field->flid;
                        $textfield->rid = $record->rid;
                        $textfield->fid = $form->fid;
                        $textfield->text = $data['textfields'][$field->flid]['data'];
                        $textfield->save();
                    }
                    break;

                // Rich Text Assignment
                case 'Rich Text':
                    $rtfield = RichTextField::where('flid', '=', $field->flid)->where('rid', '=', $record->rid)->first();
                    if ($revision->type != 'delete' && !is_null($rtfield)) {
                        $rtfield->rawtext = $data['richtextfields'][$field->flid]['data'];
                        $rtfield->save();
                    } else {
                        $rtfield = new RichTextField();
                        $rtfield->flid = $field->flid;
                        $rtfield->rid = $record->rid;
                        $rtfield->fid = $form->fid;
                        $rtfield->rawtext = $data['richtextfields'][$field->flid]['data'];
                        $rtfield->save();
                    }
                    break;

                // Number Assignment
                case 'Number':
                    $numberfield = NumberField::where('flid', '=', $field->flid)->where('rid', '=', $record->rid)->first();
                    if ($revision->type != 'delete' && !is_null($numberfield)) {
                        $numberfield->number = $data['numberfields'][$field->flid]['data']['number'];
                        $numberfield->save();
                    } else {
                        $numberfield = new NumberField();
                        $numberfield->flid = $field->flid;
                        $numberfield->rid = $record->rid;
                        $numberfield->fid = $form->fid;
                        $numberfield->number = $data['numberfields'][$field->flid]['data']['number'];
                        $numberfield->save();
                    }
                break;

                // List Assignment
                case 'List':
                    $listfield = ListField::where('flid', '=', $field->flid)->where('rid', '=', $record->rid)->first();
                    if ($revision->type != 'delete' && !is_null($listfield)) {
                        $listfield->option = $data['listfields'][$field->flid]['data'];
                        $listfield->save();
                    } else {
                        $listfield = new ListField();
                        $listfield->flid = $field->flid;
                        $listfield->rid = $record->rid;
                        $listfield->fid = $form->fid;
                        $listfield->option = $data['listfields'][$field->flid]['data'];
                        $listfield->save();
                    }
                    break;

                // Multi-Select List Assignment
                case 'Multi-Select List':
                    $mslfield = MultiSelectListField::where('flid', '=', $field->flid)->where('rid', '=', $record->rid)->first();
                    if ($revision->type != 'delete' && !is_null($mslfield)) {
                        $mslfield->options = $data['multiselectlistfields'][$field->flid]['data'];
                        $mslfield->save();
                    } else {
                        $mslfield = new MultiSelectListField();
                        $mslfield->flid = $field->flid;
                        $mslfield->rid = $record->rid;
                        $mslfield->fid = $form->fid;
                        $mslfield->options = $data['multiselectlistfields'][$field->flid]['data'];
                        $mslfield->save();
                    }
                    break;

                // Generated List Assignment
                case 'Generated List':
                    $genlistfield = GeneratedListField::where('flid', '=', $field->flid)->where('rid', '=', $record->rid)->first();
                    if ($revision->type != 'delete' && !is_null($genlistfield)) {
                        $genlistfield->options = $data['generatedlistfields'][$field->flid]['data'];
                        $genlistfield->save();
                    } else {
                        $genlistfield = new GeneratedListField();
                        $genlistfield->flid = $field->flid;
                        $genlistfield->rid = $record->rid;
                        $genlistfield->fid = $form->fid;
                        $genlistfield->options = $data['generatedlistfields'][$field->flid]['data'];
                        $genlistfield->save();
                    }
                    break;

                // Date Assignment
                case 'Date':
                    $datefield = DateField::where('flid', '=', $field->flid)->where('rid', '=', $record->rid)->first();
                    if ($revision->type != 'delete' && !is_null($datefield)) {
                        $datefield->circa = $data['datefields'][$field->flid]['data']['circa'];
                        $datefield->month = $data['datefields'][$field->flid]['data']['month'];
                        $datefield->day = $data['datefields'][$field->flid]['data']['day'];
                        $datefield->year = $data['datefields'][$field->flid]['data']['year'];
                        $datefield->era = $data['datefields'][$field->flid]['data']['era'];
                        $datefield->save();
                    } else {
                        $datefield = new DateField();
                        $datefield->flid = $field->flid;
                        $datefield->rid = $record->rid;
                        $datefield->fid = $form->fid;
                        $datefield->circa = $data['datefields'][$field->flid]['data']['circa'];
                        $datefield->month = $data['datefields'][$field->flid]['data']['month'];
                        $datefield->day = $data['datefields'][$field->flid]['data']['day'];
                        $datefield->year = $data['datefields'][$field->flid]['data']['year'];
                        $datefield->era = $data['datefields'][$field->flid]['data']['era'];
                        $datefield->save();
                    }
                    break;

                // Schedule Assignment
                case 'Schedule':
                    $schedulefield = ScheduleField::where('flid', '=', $field->flid)->where('rid', '=', $record->rid)->first();
                    if ($revision->type != 'delete' && !is_null($schedulefield)) {
                        $schedulefield->events = $data['schedulefields'][$field->flid]['data'];
                        $schedulefield->save();
                    } else {
                        $schedulefield = new ScheduleField();
                        $schedulefield->flid = $field->flid;
                        $schedulefield->rid = $record->rid;
                        $schedulefield->fid = $form->fid;
                        $schedulefield->events = $data['schedulefields'][$field->flid]['data'];
                        $schedulefield->save();
                    }
                    break;

                // Geolocator Assignment
                case 'Geolocator':
                    $geolocatorfield = GeolocatorField::where('flid', '=', $field->flid)->where('rid', '=', $record->rid)->first();
                    if ($revision->type != 'delete' && !is_null($geolocatorfield)) {
                        $geolocatorfield->locations = $data['geolocatorfields'][$field->flid]['data'];
                        $geolocatorfield->save();
                    } else {
                        $geolocatorfield = new GeolocatorField();
                        $geolocatorfield->flid = $field->flid;
                        $geolocatorfield->rid = $record->rid;
                        $geolocatorfield->fid = $form->fid;
                        $geolocatorfield->locations = $data['geolocatorfields'][$field->flid]['data'];
                        $geolocatorfield->save();
                    }
                    break;

                // Documents Assignment
                case 'Documents':
                    $documentsfield = DocumentsField::where('flid', '=', $field->flid)->where('rid', '=', $record->rid)->first();
                    if ($revision->type != 'delete' && !is_null($documentsfield)) {
                        $documentsfield->documents = $data['documentsfields'][$field->flid]['data'];
                        $documentsfield->save();
                    } else {
                        $documentsfield = new DocumentsField();
                        $documentsfield->flid = $field->flid;
                        $documentsfield->rid = $record->rid;
                        $documentsfield->fid = $form->fid;
                        $documentsfield->documents = $data['documentsfields'][$field->flid]['data'];
                        $documentsfield->save();
                    }
                    break;

                // Gallery Assignment
                case 'Gallery':
                    $galleryfield = GalleryField::where('flid', '=', $field->flid)->where('rid', '=', $record->rid)->first();
                    if ($revision->type != 'delete' && !is_null($galleryfield)) {
                        $galleryfield->images = $data['galleryfields'][$field->flid]['data'];
                        $galleryfield->save();
                    } else {
                        $galleryfield = new GalleryField();
                        $galleryfield->flid = $field->flid;
                        $galleryfield->rid = $record->rid;
                        $galleryfield->fid = $form->fid;
                        $galleryfield->images = $data['galleryfields'][$field->flid]['data'];
                        $galleryfield->save();
                    }
                    break;

                // 3-D Model Assignment
                case '3D-Model':
                    $modelfield = ModelField::where('flid', '=', $field->flid)->where('rid', '=', $record->rid)->first();
                    if ($revision->type != 'delete' && !is_null($modelfield)) {
                        $modelfield->model = $data['modelfields'][$field->flid]['data'];
                        $modelfield->save();
                    } else {
                        $modelfield = new ModelField();
                        $modelfield->flid = $field->flid;
                        $modelfield->rid = $record->rid;
                        $modelfield->fid = $form->fid;
                        $modelfield->model = $data['modelfields'][$field->flid]['data'];
                        $modelfield->save();
                    }
                    break;

                // Playlist Assignment
                case 'Playlist':
                    $playfield = PlaylistField::where('flid', '=', $field->flid)->where('rid', '=', $record->rid)->first();
                    if ($revision->type != 'delete' && !is_null($playfield)) {
                        $playfield->audio = $data['playlistfields'][$field->flid]['data'];
                        $playfield->save();
                    } else {
                        $playfield = new PlaylistField();
                        $playfield->flid = $field->flid;
                        $playfield->rid = $record->rid;
                        $playfield->fid = $form->fid;
                        $playfield->audio = $data['playlistfields'][$field->flid]['data'];
                        $playfield->save();
                    }
                    break;

                // Video Assignment
                case 'Video':
                    $vidfield = VideoField::where('flid', '=', $field->flid)->where('rid', '=', $record->rid)->first();
                    if ($revision->type != 'delete') {
                        $vidfield->video = $data['videofields'][$field->flid]['data'];
                        $vidfield->save();
                    } else {
                        $vidfield = new PlaylistField();
                        $vidfield->flid = $field->flid;
                        $vidfield->rid = $record->rid;
                        $vidfield->fid = $form->fid;
                        $vidfield->video = $data['videofields'][$field->flid]['data'];
                        $vidfield->save();
                    }
                    break;

                case 'Combo List':
                    $cmbfield = ComboListField::where('flid', '=', $field->flid)->where('rid', '=', $record->rid)->first();

                    $valuesArray = $data['combofields'][$field->flid]['values'];

                    $values = "";
                    for($i=0; $i < count($valuesArray) - 1; $i++) {
                        $values .= $valuesArray[$i];
                        $values .= '[!val!]';
                    }
                    $values .=  $valuesArray[count($valuesArray) - 1];

                    if($revision->type != 'delete') {
                        $cmbfield->options = $values;
                        $cmbfield->save();
                    } else {
                        $cmbfield = new ComboListField();
                        $cmbfield->flid = $field->flid;
                        $cmbfield->rid = $record->rid;
                        $cmbfield->fid = $form->fid;
                        $cmbfield->options = $values;
                        $cmbfield->save();
                    }
                    break;
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


        /** Check each field and get the data associated with it.
         *
         * Complexities occur when forming the possibly (probably) large associative array describing a record's fields.
         * For each field, the general case is as follows: the general field type is checked and name is acquired,
         * if the field has data at its lower, less general level (e.g. TextFields), it is assigned to the data array,
         * else null is assigned.
         */
        foreach($form->fields()->get() as $field) {

            // TODO: Apply some OOP techniques here.
            //  Maybe?

            switch ($field->type)
            {
                case 'Text':
                    $data['textfields'][$field->flid]['name'] = $field->name;
                    $textfield = TextField::where('flid', '=', $field->flid)->where('rid', '=', $record->rid)->first();
                    if(!is_null($textfield))
                        $data['textfields'][$field->flid]['data'] = $textfield->text;
                    else
                        $data['textfields'][$field->flid]['data'] = null;
                    break;

                case 'Rich Text':
                    $data['richtextfields'][$field->flid]['name'] = $field->name;
                    $rtfield = RichTextField::where('flid', '=', $field->flid)->where('rid', '=', $record->rid)->first();
                    if(!is_null($rtfield))
                        $data['richtextfields'][$field->flid]['data'] = $rtfield->rawtext;
                    else
                        $data['richtextfields'][$field->flid]['data'] = null;
                    break;

                case 'Number':
                    $data['numberfields'][$field->flid]['name'] = $field->name;
                    $numberfield = NumberField::where('flid', '=', $field->flid)->where('rid', '=', $record->rid)->first();
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
                    $mslfield = MultiSelectListField::where('flid', '=', $field->flid)->where('rid', '=', $record->rid)->first();
                    if(!is_null($mslfield))
                        $data['multiselectlistfields'][$field->flid]['data'] = $mslfield->options;
                    else
                        $data['multiselectlistfields'][$field->flid]['data'] = null;
                    break;

                case 'Generated List':
                    $data['generatedlistfields'][$field->flid]['name'] = $field->name;
                    $genfield = GeneratedListField::where('flid', '=', $field->flid)->where('rid', '=', $record->rid)->first();
                    if(!is_null($genfield))
                        $data['generatedlistfields'][$field->flid]['data'] = $genfield->options;
                    else
                        $data['generatedlistfields'][$field->flid]['data'] = null;
                    break;

                case 'Date':
                    $data['datefields'][$field->flid]['name'] = $field->name;
                    $datefield = DateField::where('flid', '=', $field->flid)->where('rid', '=', $record->rid)->first();
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
                    $schedulefield = ScheduleField::where('flid', '=', $field->flid)->where('rid', '=', $record->rid)->first();
                    if(!is_null($schedulefield))
                        $data['schedulefields'][$field->flid]['data'] = $schedulefield->events;
                    else
                        $data['schedulefields'][$field->flid]['data'] = null;
                    break;

                case 'Geolocator':
                    $data['geolocatorfields'][$field->flid]['name'] = $field->name;
                    $geofield = GeolocatorField::where('flid', '=', $field->flid)->where('rid', '=', $record->rid)->first();
                    if(!is_null($geofield))
                        $data['geolocatorfields'][$field->flid]['data'] = $geofield->locations;
                    else
                        $data['geolocatorfields'][$field->flid]['data'] = null;
                    break;

                case 'Documents':
                    $data['documentsfields'][$field->flid]['name'] = $field->name;
                    $docfield = DocumentsField::where('flid', '=', $field->flid)->where('rid', '=', $record->rid)->first();
                    if(!is_null($docfield))
                        $data['documentsfields'][$field->flid]['data'] = $docfield->documents;
                    else
                        $data['documentsfields'][$field->flid]['data'] = null;
                    break;

                case 'Gallery':
                    $data['galleryfields'][$field->flid]['name'] = $field->name;
                    $galfield = GalleryField::where('flid', '=', $field->flid)->where('rid', '=', $record->rid)->first();
                    if(!is_null($galfield))
                        $data['galleryfields'][$field->flid]['data'] = $galfield->images;
                    else
                        $data['galleryfields'][$field->flid]['data'] = null;
                    break;

                case '3D-Model':
                    $data['modelfields'][$field->flid]['name'] = $field->name;
                    $modelfield = ModelField::where('flid', '=', $field->flid)->where('rid', '=', $record->rid)->first();
                    if(!is_null($modelfield))
                        $data['modelfields'][$field->flid]['data'] = $modelfield->model;
                    else
                        $data['modelfields'][$field->flid]['data'] = null;
                    break;

                case 'Playlist':
                    $data['playlistfields'][$field->flid]['name'] = $field->name;
                    $playfield = PlaylistField::where('flid', '=', $field->flid)->where('rid', '=', $record->rid)->first();
                    if(!is_null($playfield))
                        $data['playlistfields'][$field->flid]['data'] = $playfield->audio;
                    else
                        $data['playlistfields'][$field->flid]['data'] = null;
                    break;

                case 'Video':
                    $data['videofields'][$field->flid]['name'] = $field->name;
                    $videofield = VideoField::where('flid', '=', $field->flid)->where('rid', '=', $record->rid)->first();
                    if(!is_null($videofield))
                        $data['videofields'][$field->flid]['data'] = $videofield->video;
                    else
                        $data['videofields'][$field->flid]['data'] = null;
                    break;

                case 'Combo List':
                    $data['combofields'][$field->flid]['name'] = $field->name;

                    $combofield = ComboListField::where('flid', '=', $field->flid)->where('rid', '=', $record->rid)->first();
                    if(!is_null($combofield)) {
                        $first = array(); $second = array(); $combodata = array(); $valArray = array();

                        // Get information from the first field.
                        $first['name'] = ComboListField::getComboFieldName($field, 'one');
                        $first['type'] = ComboListField::getComboFieldType($field, 'one');

                        // Get information from the second field.
                        $second['name'] = ComboListField::getComboFieldName($field, 'two');
                        $second['type'] = ComboListField::getComboFieldType($field, 'two');

                        // Get the values from the actual combo list field.
                        $valArray = explode('[!val!]', $combofield->options);

                        // Get the options of the field
                        $options = $field->options;

                        $combodata['ftype1'] = $combofield->ftype1;
                        $combodata['ftype2'] = $combofield->ftype2;

                        $data['combofields'][$field->flid]['first'] = $first;
                        $data['combofields'][$field->flid]['second'] = $second;
                        $data['combofields'][$field->flid]['values'] = $valArray;
                        $data['combofields'][$field->flid]['options'] = $options;
                        $data['combofields'][$field->flid]['data'] = $combodata;
                    }
                    else {
                        $data['combofields'][$field->flid]['data'] = null;
                    }
                    break;
            }

        }

        /* Have to see which method is better, for now we'll use json_encode (remember to use json_decode($array, true)).
           Alternative method is presented here. The base64_encode method might end up working better. Who knows right?

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
        Revision::where('fid','=',$fid)->update(["rollback" => 0]);
    }
}
