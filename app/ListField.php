<?php namespace App;

use App\Http\Controllers\FieldController;
use App\Http\Controllers\RevisionController;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Builder;

class ListField extends BaseField {

    const FIELD_OPTIONS_VIEW = "fields.options.list";
    const FIELD_ADV_OPTIONS_VIEW = "partials.field_option_forms.list";

    protected $fillable = [
        'rid',
        'flid',
        'option'
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
        $reqOpts = $request->options;
        $options = $reqOpts[0];
        for($i=1;$i<sizeof($reqOpts);$i++){
            $options .= '[!]'.$reqOpts[$i];
        }

        $field->updateRequired($request->required);
        $field->updateSearchable($request);
        $field->updateDefault($request->default);
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
        $this->option = $value;
        $this->save();
    }

    public function editRecordField($value, $request) {
        if(!is_null($this) && !is_null($value)){
            $this->option = $value;
            $this->save();
        }
        else if(!is_null($this) && is_null($value)){
            $this->delete();
        }
    }

    public function massAssignRecordField($field, $record, $formFieldValue, $request, $overwrite=0) {
        $matching_record_fields = $record->listfields()->where("flid", '=', $field->flid)->get();
        $record->updated_at = Carbon::now();
        $record->save();
        if ($matching_record_fields->count() > 0) {
            $listfield = $matching_record_fields->first();
            if ($overwrite == true || $listfield->option == "" || is_null($listfield->option)) {
                $revision = RevisionController::storeRevision($record->rid, 'edit');
                $listfield->option = $formFieldValue;
                $listfield->save();
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
        $this->option = 'K3TR';
        $this->save();
    }

    public function validateField($field, $value, $request) {
        $req = $field->required;
        $list = ListField::getList($field);

        if($req==1 && ($value==null | $value=="")){
            return $field->name.trans('fieldhelpers_val.req');
        }

        if($value!='' && !in_array($value,$list)){
            return trans('fieldhelpers_val.list',['name'=>$field->name]);
        }

        return '';
    }

    public function rollbackField($field, Revision $revision, $exists=true) {
        if (!is_array($revision->data)) {
            $revision->data = json_decode($revision->data, true);
        }

        if (is_null($revision->data[Field::_LIST][$field->flid]['data'])) {
            return null;
        }

        // If the field doesn't exist or was explicitly deleted, we create a new one.
        if ($revision->type == Revision::DELETE || !$exists) {
            $this->flid = $field->flid;
            $this->rid = $revision->rid;
            $this->fid = $revision->fid;
        }

        $this->option = $revision->data[Field::_LIST][$field->flid]['data'];
        $this->save();
    }

    public function getRecordPresetArray($data, $exists=true) {
        if ($exists) {
            $data['option'] = $this->option;
        }
        else {
            $data['option'] = null;
        }

        return $data;
    }

    public function getExportSample($slug,$type) {
        switch ($type){
            case "XML":
                $xml = '<' . Field::xmlTagClear($slug) . ' type="List">';
                $xml .= utf8_encode('LIST VALUE');
                $xml .= '</' . Field::xmlTagClear($slug) . '>';

                return $xml;
                break;
            case "JSON":
                $fieldArray = array('name' => $slug, 'type' => 'List');
                $fieldArray['option'] = 'VALUE';

                return $fieldArray;
                break;
        }

    }

    public function setRestfulAdvSearch($data, $flid, $request) {
        $request->request->add([$flid.'_input' => $data->input]);

        return $request;
    }

    public function setRestfulRecordData($jsonField, $flid, $recRequest, $uToken=null) {
        $recRequest[$flid] = $jsonField->option;

        return $recRequest;
    }

    public function keywordSearchTyped($fid, $arg, $method) {
        return self::select("rid")
            ->where("fid", "=", $fid)
            ->whereRaw("MATCH (`option`) AGAINST (? IN BOOLEAN MODE)", [$arg])
            ->distinct();
    }

    public function getAdvancedSearchQuery($flid, $query) {
        $db_query = self::select("rid")
            ->where("flid", "=", $flid);
        $input = $query[$flid . "_input"];

        self::buildAdvancedListQuery($db_query, $input);

        return $db_query->distinct();
    }

    /**
     * Gets formatted value of record field to compare for sort. Only implement if field is sortable.
     *
     * @return string - The value
     */
    public function getValueForSort() {
        return $this->option;
    }

    ///////////////////////////////////////////////END ABSTRACT FUNCTIONS///////////////////////////////////////////////

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
        return $this->option;
    }

    /**
     * Build and advanced query for list field.
     *
     * @param Builder $db_query, reference to query to build.
     * @param string $input, input value from form.
     */
    public static function buildAdvancedListQuery(Builder &$db_query, $input) {
        $db_query->whereRaw("MATCH (`option`) AGAINST (? IN BOOLEAN MODE)",
            [Search::processArgument($input, Search::ADVANCED_METHOD)]);
    }
}
