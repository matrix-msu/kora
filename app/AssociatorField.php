<?php namespace App;

use App\Http\Controllers\FieldController;
use App\Http\Controllers\RecordController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class AssociatorField extends BaseField {

    const SUPPORT_NAME = "associator_support";

    protected $fillable = [
        'rid',
        'flid',
        'records'
    ];

    public static function getOptions(){
        return '[!SearchForms!][!SearchForms!]';
    }

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

        $html = "<a href='".env('BASE_URL')."projects/".$pid."/forms/".$fid."/records/".$rid."'>".$kid."</a>";
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
        $pieces = array();
        $records = $this->records()->get();
        foreach($records as $record){
            $rid = $record->record;
            $model = RecordController::getRecord($rid);
            array_push($pieces,$model->kid);
        }

        $formatted = implode("[!]", $pieces);
        return $formatted;
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

        $associatorfield = self::where("flid", "=", $field->flid)->where("rid", "=", $revision->rid)->first();

        // If the field doesn't exist or was explicitly deleted, we create a new one.
        if ($revision->type == Revision::DELETE || is_null($associatorfield)) {
            $associatorfield = new self();
            $associatorfield->flid = $field->flid;
            $associatorfield->fid = $revision->fid;
            $associatorfield->rid = $revision->rid;
        }

        $associatorfield->save();
        $updated = explode('[!]',$revision->data[Field::_ASSOCIATOR][$field->flid]['data']);
        $associatorfield->updateRecords($updated);

        return $associatorfield;
    }

    /**
     * Build the advanced search query.
     * Advanced queries for MSL Fields accept any record that has at least one of the desired parameters.
     *
     * @param $flid
     * @param $query
     * @return Builder
     */
    public static function getAdvancedSearchQuery($flid, $query) {
        $inputs = $query[$flid."_input"];

        $query = DB::table(self::SUPPORT_NAME)
            ->select("rid")
            ->where("flid", "=", $flid);

        self::buildAdvancedAssociatorQuery($query, $inputs);

        return $query->distinct();
    }

    /**
     * Build the advanced search query for a multi select list. (Works for Generated List too.)
     *
     * @param Builder $db_query
     * @param array $inputs, input values
     */
    public static function buildAdvancedAssociatorQuery(Builder &$db_query, $inputs) {
        $db_query->where(function($db_query) use ($inputs) {
            foreach($inputs as $input) {
                $rid = explode('-',$input)[2];
                $db_query->orWhereRaw("MATCH (`record`) AGAINST (? IN BOOLEAN MODE)",
                    [Search::processArgument($rid, Search::ADVANCED_METHOD)]);
            }
        });
    }

    public static function validate($field, $value){
        $req = $field->required;

        if($req==1 && ($value==null | $value=="")){
            return $field->name.trans('fieldhelpers_val.req');
        }
    }
}
