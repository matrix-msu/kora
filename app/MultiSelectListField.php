<?php namespace App;

use App\Http\Controllers\FieldController;
use App\Http\Controllers\RevisionController;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MultiSelectListField extends BaseField {

    const FIELD_OPTIONS_VIEW = "fields.options.mslist";
    const FIELD_ADV_OPTIONS_VIEW = "partials.field_option_forms.mslist";

    protected $fillable = [
        'rid',
        'flid',
        'options'
    ];

    public function getFieldOptionsView(){
        return self::FIELD_OPTIONS_VIEW;
    }

    public function getAdvancedFieldOptionsView(){
        return self::FIELD_ADV_OPTIONS_VIEW;
    }

    public function getDefaultOptions(Request $request){
        return '[!Options!][!Options!]';
    }

    public function updateOptions($field, Request $request, $return=true) {
        $reqDefs = $request->default;
        $default = $reqDefs[0];
        for($i=1;$i<sizeof($reqDefs);$i++){
            $default .= '[!]'.$reqDefs[$i];
        }

        $reqOpts = $request->options;
        $options = $reqOpts[0];
        for($i=1;$i<sizeof($reqOpts);$i++){
            $options .= '[!]'.$reqOpts[$i];
        }

        $field->updateRequired($request->required);
        $field->updateSearchable($request);
        $field->updateDefault($default);
        $field->updateOptions('Options', $options);

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
        $this->options = implode("[!]",$value);
        $this->save();
    }

    public function editRecordField($value, $request) {
        if(!is_null($this) && !is_null($value)){
            $this->options = implode("[!]",$value);
            $this->save();
        }
        elseif(!is_null($this) && is_null($value)){
            $this->delete();
        }
    }

    public function massAssignRecordField($field, $record, $formFieldValue, $request, $overwrite=0) {
        $matching_record_fields = $record->multiselectlistfields()->where("flid", '=', $field->flid)->get();
        $record->updated_at = Carbon::now();
        $record->save();
        if ($matching_record_fields->count() > 0) {
            $multiselectlistfield = $matching_record_fields->first();
            if ($overwrite == true || $multiselectlistfield->options == "" || is_null($multiselectlistfield->options)) {
                $revision = RevisionController::storeRevision($record->rid, 'edit');
                $multiselectlistfield->options = implode("[!]", $formFieldValue);
                $multiselectlistfield->save();
                $revision->oldData = RevisionController::buildDataArray($record);
                $revision->save();
            }
        } else {
            $this->createNewRecordField($field, $record, $formFieldValue, $request);
            $revision = RevisionController::storeRevision($record->rid, 'edit');
            $revision->oldData = RevisionController::buildDataArray($record);
            $revision->save();
        }
    }

    public function createTestRecordField($field, $record){
        $this->flid = $field->flid;
        $this->rid = $record->rid;
        $this->fid = $field->fid;
        $this->options = 'K3TR[!]1337[!]Test[!]Record';
        $this->save();
    }

    public static function getExportSample($field,$type){
        switch ($type){
            case "XML":
                $xml = '<' . Field::xmlTagClear($field->slug) . ' type="' . $field->type . '">';
                $xml .= '<value>' . utf8_encode('LIST VALUE 1') . '</value>';
                $xml .= '<value>' . utf8_encode('LIST VALUE 2') . '</value>';
                $xml .= '<value>' . utf8_encode('so on...') . '</value>';
                $xml .= '</' . Field::xmlTagClear($field->slug) . '>';

                return $xml;
                break;
            case "JSON":
                $fieldArray = array('name' => $field->slug, 'type' => $field->type);
                $options = array('LIST VALUE 1','LIST VALUE 2','so on...');
                $fieldArray['options'] = $options;

                return $fieldArray;
                break;
        }

    }

    public static function setRestfulAdvSearch($data, $field, $request){
        $request->request->add([$field->flid.'_input' => $data->input]);

        return $request;
    }

    public static function setRestfulRecordData($field, $flid, $recRequest){
        $recRequest[$flid] = $field->options;

        return $recRequest;
    }

    public static function getRecordPresetArray($field, $record, $data, $flid_array){
        $mslfield = MultiSelectListField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->first();

        if (!empty($mslfield->options)) {
            $data['options'] = explode('[!]', $mslfield->options);
        }
        else {
            $data['options'] = null;
        }

        $flid_array[] = $field->flid;

        return array($data,$flid_array);
    }

    public static function getList($field, $blankOpt=false)
    {
        $dbOpt = FieldController::getFieldOption($field, 'Options');
        $options = array();

        if ($dbOpt == '') {
            //skip
        } else if (!strstr($dbOpt, '[!]')) {
            $options = [$dbOpt => $dbOpt];
        } else {
            $opts = explode('[!]', $dbOpt);
            foreach ($opts as $opt) {
                $options[$opt] = $opt;
            }
        }

        if ($blankOpt) {
            $options = array('' => '') + $options;
        }

        return $options;
    }

    /**
     * @param null $field
     * @return string
     */
    public function getRevisionData($field = null) {
        return $this->options;
    }

    /**
     * Rollback a multiselect list field based on a revision.
     *
     * @param Revision $revision
     * @param Field $field
     * @return MultiSelectListField
     */
    public static function rollback(Revision $revision, Field $field) {
        if (!is_array($revision->data)) {
            $revision->data = json_decode($revision->data, true);
        }

        if(is_null($revision->data[Field::_MULTI_SELECT_LIST][$field->flid]['data'])) {
            return null;
        }

        $mslfield = self::where("flid", "=", $field->flid)->where("rid", "=", $revision->rid)->first();

        // If the field doesn't exist or was explicitly deleted, we create a new one.
        if ($revision->type == Revision::DELETE || is_null($mslfield)) {
            $mslfield = new self();
            $mslfield->flid = $field->flid;
            $mslfield->rid = $revision->rid;
            $mslfield->fid = $revision->fid;
        }

        $mslfield->options = $revision->data[Field::_MULTI_SELECT_LIST][$field->flid]['data'];
        $mslfield->save();

        return $mslfield;
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

        $query = DB::table("multi_select_list_fields")
            ->select("rid")
            ->where("flid", "=", $flid);

        self::buildAdvancedMultiSelectListQuery($query, $inputs);

        return $query->distinct();
    }

    /**
     * Build the advanced search query for a multi select list. (Works for Generated List too.)
     *
     * @param Builder $db_query
     * @param array $inputs, input values
     */
    public static function buildAdvancedMultiSelectListQuery(Builder &$db_query, $inputs) {
        $db_query->where(function($db_query) use ($inputs) {
            foreach($inputs as $input) {
                $db_query->orWhereRaw("MATCH (`options`) AGAINST (? IN BOOLEAN MODE)",
                    [Search::processArgument($input, Search::ADVANCED_METHOD)]);
            }
        });
    }

    public static function validate($field, $value){
        $req = $field->required;
        $list = MultiSelectListField::getList($field);

        if($req==1 && ($value==null | $value=="")){
            return $field->name.trans('fieldhelpers_val.req');
        }

        if(sizeof(array_diff($value,$list))>0 && $value[0] !== ' '){
            return trans('fieldhelpers_val.mslist',['name'=>$field->name]);
        }

        return '';
    }
}
