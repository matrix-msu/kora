<?php namespace App;

use App\Http\Controllers\FieldController;
use App\Http\Controllers\RecordController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AssociatorField extends BaseField {

    const SUPPORT_NAME = "associator_support";

    protected $fillable = [
        'rid',
        'flid',
        'records'
    ];

    public static function getDefault($default, $blankOpt=false)
    {
        $options = array();

        if ($default == '') {
            //skip
        } else if (!strstr($default, '[!]')) {
            $options = [$default => $default];
        } else {
            $opts = explode('[!]', $default);
            foreach ($opts as $opt) {
                $options[$opt] = $opt;
            }
        }

        if ($blankOpt) {
            $options = array('' => '') + $options;
        }

        return $options;
    }

    public function getPreviewValues($rid){
        //individual kid elements
        $recModel = RecordController::getRecord($rid);
        $pid = $recModel->pid;
        $fid = $recModel->fid;
        $rid = $recModel->rid;
        $kid = $recModel->kid;

        //get the preview flid structure of this associator
        $activeForms = array();
        $field = FieldController::getField($this->flid);
        $option = FieldController::getFieldOption($field,'SearchForms');
        if($option!=''){
            $options = explode('[!]',$option);

            foreach($options as $opt){
                $opt_fid = explode('[fid]',$opt)[1];
                $opt_search = explode('[search]',$opt)[1];
                $opt_flids = explode('[flids]',$opt)[1];
                $opt_flids = explode('-',$opt_flids);

                if($opt_search == 1)
                    $flids = array();
                foreach($opt_flids as $flid){
                    $field = FieldController::getField($flid);
                    $flids[$flid] = $field->type;
                }
                $activeForms[$opt_fid] = ['flids' => $flids];
            }
        }

        //grab the preview fields associated with the form of this kid
        $details = $activeForms[$fid];
        $preview = array();
        foreach($details['flids'] as $flid=>$type){
            if($type=='Text'){
                $text = TextField::where("flid", "=", $flid)->where("rid", "=", $rid)->first();
                if($text->text != '')
                    array_push($preview,$text->text);
            }else if($type=='List'){
                $list = ListField::where("flid", "=", $flid)->where("rid", "=", $rid)->first();
                if($list->option != '')
                    array_push($preview,$list->option);
            }
        }

        $html = "<a href='".env('BASE_URL')."public/projects/".$pid."/forms/".$fid."/records/".$rid."'>".$kid."</a>";
        foreach($preview as $val){
            $html .= " | ".$val;
        }

        return $html;
    }

    /**
     * Delete a schedule field, we must also delete its support fields.
     * @throws \Exception
     */
    public function delete() {
        $this->deleteRecords();
        parent::delete();
    }

    /**
     * Adds an record to the associatr_support table.
     * @param array $records an array of records to associate to.
     *  They are in KID format
     */
    public function addRecords(array $records) {
        $now = date("Y-m-d H:i:s");
        foreach($records as $record) {
            $recInfo = explode("-",$record);

            DB::table(self::SUPPORT_NAME)->insert(
                [
                    'rid' => $this->rid,
                    'fid' => $this->fid,
                    'flid' => $this->flid,
                    'record' => $recInfo[2],
                    'created_at' => $now,
                    'updated_at' => $now
                ]
            );
        }
    }

    /**
     * Update events using the same method as add events.
     * The only reliable way to actually update is to delete all previous events and just add the updated versions.
     *
     * @param array $records
     */
    public function updateRecords(array $records) {
        $this->deleteRecords();
        $this->addRecords($records);
    }

    /**
     * Deletes all events associated with the schedule field.
     */
    public function deleteRecords() {
        DB::table(self::SUPPORT_NAME)
            ->where("rid", "=", $this->rid)
            ->where("flid", "=", $this->flid)
            ->delete();
    }

    /**
     * The query for records in a associator field.
     * Use ->get() to obtain all events.
     *
     * @return Builder
     */
    public function records() {
        return DB::table(self::SUPPORT_NAME)->select("*")
            ->where("flid", "=", $this->flid)
            ->where("rid", "=", $this->rid);
    }

    /**
     * True if there are events associated with a particular Schedule field.
     *
     * @return bool
     */
    public function hasRecords() {
        return !! $this->records()->count();
    }

    /**
     * @param null $field
     * @return string
     */
    public function getRevisionData($field = null) {
        //TODO::return $this->records;
    }

    /**
     * Rollback a associator field based on a revision.
     *
     * ** Assumes $revision->data is json decoded. **
     *
     * @param Revision $revision
     * @param Field $field
     */
    public static function rollback(Revision $revision, Field $field) {
        if (!is_array($revision->data)) {
            $revision->data = json_decode($revision->data, true);
        }

        if (is_null($revision->data[Field::_ASSOCIATOR][$field->flid]['data'])) {
            return null;
        }

        $associatorfield = AssociatorField::where("flid", "=", $field->flid)->where("rid", "=", $revision->rid)->first();

        // If the field doesn't exist or was explicitly deleted, we create a new one.
        if ($revision->type == Revision::DELETE || is_null($associatorfield)) {
            $associatorfield = new AssociatorField();
            $associatorfield->flid = $field->flid;
            $associatorfield->fid = $revision->fid;
            $associatorfield->rid = $revision->rid;
        }

        $associatorfield->records = $revision->data[Field::_ASSOCIATOR][$field->flid]['data'];
        $associatorfield->save();
    }
}
