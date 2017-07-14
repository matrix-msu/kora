<?php namespace App;

use App\Http\Controllers\FieldController;
use App\Http\Controllers\RecordController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssociatorField extends BaseField {

    /**
     * @var string - Support table name
     */
    const SUPPORT_NAME = "associator_support";
    /**
     * @var string - View names for option tables
     */
    const FIELD_OPTIONS_VIEW = "fields.options.associator";
    const FIELD_ADV_OPTIONS_VIEW = "partials.field_option_forms.associator";

    /**
     * @var array - Attributes that can be mass assigned to model
     */
    protected $fillable = [
        'rid',
        'flid',
        'records'
    ];

    public function getFieldOptionsView(){
        return self::FIELD_OPTIONS_VIEW;
    }

    public function getAdvancedFieldOptionsView(){
        return self::FIELD_ADV_OPTIONS_VIEW;
    }

    public function getDefaultOptions(Request $request){
        return '[!SearchForms!][!SearchForms!]';
    }

    public function updateOptions($field, Request $request, $return=true) {
        if(is_null($request->default)){
            $default = '';
        }else {
            $reqDefs = array_values(array_unique($request->default));
            $default = $reqDefs[0];
            for ($i = 1; $i < sizeof($reqDefs); $i++) {
                $default .= '[!]' . $reqDefs[$i];
            }
        }

        $field->updateRequired($request->required);
        $field->updateSearchable($request);
        $field->updateDefault($default);
        $field->updateOptions('SearchForms', $request->searchforms);

        if($return) {
            flash()->overlay(trans('controller_field.optupdate'), trans('controller_field.goodjob'));
            return redirect('projects/' . $field->pid . '/forms/' . $field->fid . '/fields/' . $field->flid . '/options');
        } else {
            return '';
        }
    }

    public function createNewRecordField($field, $record, $value, $request){
        $this->flid = $field->flid;
        $this->rid = $record->rid;
        $this->fid = $field->fid;
        $this->save();
        $this->addRecords($value);
    }

    public function editRecordField($value, $request) {
        if(!is_null($this) && !is_null($value)){
            $this->updateRecords($value);
        }
        elseif(!is_null($this) && is_null($value)){
            $this->delete();
            $this->deleteRecords();
        }
    }

    public function massAssignRecordField($field, $record, $formFieldValue, $request, $overwrite=0) {
        //TODO::mass assign
    }

    public function createTestRecordField($field, $record){
        $this->flid = $field->flid;
        $this->rid = $record->rid;
        $this->fid = $field->fid;
        $this->save();

        $this->addRecords(array('1-3-37','1-3-37','1-3-37','1-3-37'));
    }

    public function validateField($field, $value, $request) {
        $req = $field->required;

        if($req==1 && ($value==null | $value=="")){
            return $field->name.trans('fieldhelpers_val.req');
        }
    }

    public function rollbackField($field, Revision $revision, $exists=true) {
        if (!is_array($revision->data)) {
            $revision->data = json_decode($revision->data, true);
        }

        if (is_null($revision->data[Field::_ASSOCIATOR][$field->flid]['data'])) {
            return null;
        }

        // If the field doesn't exist or was explicitly deleted, we create a new one.
        if ($revision->type == Revision::DELETE || !$exists) {
            $this->flid = $field->flid;
            $this->fid = $revision->fid;
            $this->rid = $revision->rid;
        }

        $this->save();
        $updated = explode('[!]',$revision->data[Field::_ASSOCIATOR][$field->flid]['data']);
        $this->updateRecords($updated);
    }

    public function getRecordPresetArray($data, $exists=true) {
        if ($exists) {
            $data['records'] = explode('[!]', $this->records);
        }
        else {
            $data['records'] = null;
        }

        return $data;
    }

    ///////////////////////////////////////////////END ABSTRACT FUNCTIONS///////////////////////////////////////////////

    public static function getExportSample($field,$type){
        switch ($type){
            case "XML":
                //TODO::add sample

                break;
            case "JSON":
                //TODO::add sample

                break;
        }

    }

    public static function setRestfulAdvSearch($data, $field, $request){
        $request->request->add([$field->flid.'_input' => $data->input]);

        return $request;
    }

    public static function setRestfulRecordData($field, $flid, $recRequest){
        $recRequest[$flid] = $field->records;

        return $recRequest;
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
}
