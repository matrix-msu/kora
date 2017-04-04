<?php namespace App\Http\Controllers;

use App\AssociatorField;
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
     * @param string | int $pid
     * @param string | int $fid
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    public function index($pid, $fid){

        if(!FormController::validProjForm($pid,$fid)){
            return redirect('projects/'.$pid);
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
        if(!RecordController::validProjFormRecord($pid, $fid, $rid)){
            return redirect('projects/'.$pid.'/forms');
        }

        $firstRevision = DB::table('revisions')->where('rid', '=', $rid)->orderBy('created_at','desc')->first();
        if(is_null($firstRevision)){
            flash()->overlay(trans('controller_revision.none'), trans('controller_revision.whoops'));
            return $this->index($pid,$fid);
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

        if ($revision->type == Revision::CREATE){
            $record = Record::where('rid', '=', $revision->rid)->first();
            $revision = RevisionController::storeRevision($record->rid, Revision::DELETE);
            $record->delete();

            flash()->overlay(trans('controller_revision.record').$form->pid.'-'.$form->fid.'-'.$revision->rid.trans('controller_revision.delete'), trans('controller_revision.success') );
        }
        elseif($revision->type == Revision::DELETE){
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

                RevisionController::rollback_routine($record, $form, $revision, false);
                RevisionController::storeRevision($record->rid, Revision::CREATE);

                flash()->overlay(trans('controller_revision.record') . $form->pid . '-' . $form->fid . '-' . $record->rid . trans('controller_revision.rollback'), trans('controller_revision.success'));
            }
        }
        else{
            $record = RecordController::getRecord($revision->rid);
            RevisionController::rollback_routine($record, $form, $revision, true);
            flash()->overlay(trans('controller_revision.record').$form->pid.'-'.$form->fid.'-'.$record->rid.trans('controller_revision.rollback'), trans('controller_revision.success'));
        }
    }

    /**
     * Does the actual rolling back using the data array from a particular revision.
     *
     * @param Record $record
     * @param Form $form
     * @param Revision $revision
     * @param bool $is_rollback
     */
    public static function rollback_routine(Record $record, Form $form, Revision $revision, $is_rollback)
    {
        // Since we'll be passing around the revision object, we decode its data now.
        // This won't be saved and is done for efficiency.
        $revision->data = json_decode($revision->data, true);

        if($is_rollback) {
            $new_revision = RevisionController::storeRevision($record->rid, Revision::ROLLBACK);
            $new_revision->oldData = $revision->data;
            $new_revision->save();
        }

        foreach($form->fields()->get() as $field) {
            switch($field->type) {
                case Field::_TEXT:
                    TextField::rollback($revision, $field);
                    break;

                case Field::_RICH_TEXT:
                    RichTextField::rollback($revision, $field);
                    break;

                case Field::_NUMBER:
                    NumberField::rollback($revision, $field);
                    break;

                case Field::_LIST:
                    ListField::rollback($revision, $field);
                    break;

                case Field::_MULTI_SELECT_LIST:
                    MultiSelectListField::rollback($revision, $field);
                    break;

                case Field::_GENERATED_LIST:
                    GeneratedListField::rollback($revision, $field);
                    break;

                case Field::_DATE:
                    DateField::rollback($revision, $field);
                    break;

                case Field::_SCHEDULE:
                    ScheduleField::rollback($revision, $field);
                    break;

                case Field::_GEOLOCATOR:
                    GeolocatorField::rollback($revision, $field);
                    break;

                case Field::_DOCUMENTS:
                    DocumentsField::rollback($revision, $field);
                    break;

                case Field::_GALLERY:
                    GalleryField::rollback($revision, $field);
                    break;

                case Field::_3D_MODEL:
                    ModelField::rollback($revision, $field);
                    break;

                case Field::_PLAYLIST:
                    PlaylistField::rollback($revision, $field);
                    break;

                case Field::_VIDEO:
                    VideoField::rollback($revision, $field);
                    break;

                case Field::_ASSOCIATOR:
                    AssociatorField::rollback($revision, $field);
                    break;

                case Field::_COMBO_LIST:
                    ComboListField::rollback($revision, $field);
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

        if(\Auth::guest())
            $revision->userId = 1;
        else
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
        $fields = Field::where("fid", "=", $record->fid)->get();

        foreach($fields as $field) {
            $typed_field = $field->getTypedField($record->rid);

            $data[$field->type][$field->flid]['name'] = $field->name;
            if (is_null($typed_field)) {
                $data[$field->type][$field->flid] = null;
            }
            else {
                $data[$field->type][$field->flid] = $typed_field->getRevisionData($field);
            }
        }

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
