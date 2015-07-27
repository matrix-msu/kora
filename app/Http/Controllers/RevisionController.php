<?php namespace App\Http\Controllers;

use App\Record;
use App\Revision;
use App\Http\Requests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;


class RevisionController extends Controller {

    public function __construct() {
        $this->middleware('auth');
        $this->middleware('active');
    }

    public function index($fid){
        $revisions = DB::table('revisions')->where('fid', '=', $fid)->orderBy('created_at', 'desc')->take(50)->get();

        $records = Record::lists('kid', 'rid');
        $form = FormController::getForm($fid);
        $message = 'Recent';

        return view('revisions.index', compact('revisions', 'records', 'form', 'message'));
    }

    public function show($rid, $fid, $pid)
    {
        return true;
    }


	public static function storeRevision($rid, $type)
    {
//        $revision = new Revision();
//        $record = RecordController::getRecord($rid);
//
//        /* Have to see which method is better, for now we'll use toJson.
//           Alternative method is presented here. The base64_encode method might end up working
//           better for data other than simple text.
//
//        $revision->data = base64_encode(serialize($record));
//        To decode: $decode = unserialize(base64_decode(serialize($revision->data)));
//        */
//
//        $fid = $record->form()->first()->fid;
//        $revision->fid = $fid;
//        $revision->rid = $record->rid;
//        $revision->userId = \Auth::user()->id;
//        $revision->type = $type;
//
//        $revision->data = RevisionController::buildDataArray($record);
//
//        $revision->rollback = 1;
//        $revision->save();
    }

    public static function buildDataArray(Record $record)
    {
        $data = array();

        if (!is_null($record->textfields()->first())){
            $text = array();
            $textfields = $record->textfields()->get();
            foreach($textfields as $textfield)
            {
                $name = Field::where('flid', '=', $textfield->flid)->first()->name;

                $text[$textfield->flid]['name'] = $name;
                $text[$textfield->flid]['data'] = $textfield->text;
            }
            $data['text'] = $text;
        }

    }


    public static function wipeRollbacks($fid)
    {
        //wipe all the rollbacks
    }

}
