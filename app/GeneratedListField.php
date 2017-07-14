<?php namespace App;

use App\Http\Controllers\FieldController;
use App\Http\Controllers\RevisionController;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GeneratedListField extends BaseField {

    const FIELD_OPTIONS_VIEW = "fields.options.genlist";
    const FIELD_ADV_OPTIONS_VIEW = "partials.field_option_forms.genlist";

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
        return '[!Regex!][!Regex!][!Options!][!Options!]';
    }

    public function updateOptions($field, Request $request, $return=true) {
        $advString = '';

        $reqDefs = $request->default;
        $default = $reqDefs[0];
        for($i=1;$i<sizeof($reqDefs);$i++){
            $default .= '[!]'.$reqDefs[$i];
        }

        $reqOpts = $request->options;
        $options = $reqOpts[0];
        for($i=1;$i<sizeof($reqOpts);$i++){
            if ($request->regex!='' && !preg_match($request->regex, $reqOpts[$i])) {
                if($return) {
                    flash()->error(trans('controller_option.genregex', ['opt' => $reqOpts[$i]]));
                    return redirect('projects/' . $field->pid . '/forms/' . $field->fid . '/fields/' . $field->flid . '/options')->withInput();
                } else {
                    $request->regex = '';
                    $advString = trans('controller_option.genregex', ['opt' => $reqOpts[$i]]);
                }
            }
            $options .= '[!]'.$reqOpts[$i];
        }

        $field->updateRequired($request->required);
        $field->updateSearchable($request);
        $field->updateDefault($default);
        $field->updateOptions('Regex', $request->regex);
        $field->updateOptions('Options', $options);

        if($return) {
            flash()->overlay(trans('controller_field.optupdate'), trans('controller_field.goodjob'));
            return redirect('projects/' . $field->pid . '/forms/' . $field->fid . '/fields/' . $field->flid . '/options');
        } else {
            return $advString;
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
        }elseif(!is_null($this) && is_null($value)){
            $this->delete();
        }
    }

    public function massAssignRecordField($field, $record, $formFieldValue, $request, $overwrite=0) {
        $matching_record_fields = $record->generatedlistfields()->where("flid", '=', $field->flid)->get();
        $record->updated_at = Carbon::now();
        $record->save();
        if ($matching_record_fields->count() > 0) {
            $generatedlistfield = $matching_record_fields->first();
            if ($overwrite == true || $generatedlistfield->options == "" || is_null($generatedlistfield->options)) {
                $revision = RevisionController::storeRevision($record->rid, 'edit');
                $generatedlistfield->options = implode("[!]", $formFieldValue);
                $generatedlistfield->save();
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

    public function validateField($field, $value, $request) {
        $req = $field->required;
        $regex = FieldController::getFieldOption($field, 'Regex');

        if($req==1 && ($value==null | $value=="")){
            return $field->name.trans('fieldhelpers_val.req');
        }

        foreach($value as $opt){
            if(($regex!=null | $regex!="") && !preg_match($regex,$opt)){
                return trans('fieldhelpers_val.regexopt',['name'=>$field->name,'opt'=>$opt]);
            }
        }

        return '';
    }

    public function rollbackField($field, Revision $revision, $exists=true) {
        if (!is_array($revision->data)) {
            $revision->data = json_decode($revision->data, true);
        }

        if (is_null($revision->data[Field::_GENERATED_LIST][$field->flid]['data'])) {
            return null;
        }

        // If the field doesn't exist or was explicitly deleted, we create a new one.
        if ($revision->type == Revision::DELETE || !$exists) {
            $this->flid = $field->flid;
            $this->rid = $revision->rid;
            $this->fid = $revision->fid;
        }

        $this->options = $revision->data[Field::_GENERATED_LIST][$field->flid]['data'];
        $this->save();
    }

    public function getRecordPresetArray($data, $exists=true) {
        if ($exists) {
            $data['options'] = explode('[!]', $this->options);
        }
        else {
            $data['options'] = null;
        }

        return $data;
    }

    ///////////////////////////////////////////////END ABSTRACT FUNCTIONS///////////////////////////////////////////////

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
     * Builds the advanced search query.
     * Advanced queries for Gen List Fields accept any record that has at least one of the desired parameters.
     *
     * @param $flid
     * @param $query
     * @return Builder
     */
    public static function getAdvancedSearchQuery($flid, $query) {
        $inputs = $query[$flid."_input"];

        $query = DB::table("generated_list_fields")
            ->select("rid")
            ->where("flid", "=", $flid);

        MultiSelectListField::buildAdvancedMultiSelectListQuery($query, $inputs);

        return $query->distinct();
    }
}
