<?php namespace App;

use App\Http\Controllers\FieldController;
use App\Http\Controllers\RevisionController;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class GeneratedListField extends BaseField {

    const FIELD_OPTIONS_VIEW = "fields.options.genlist";
    const FIELD_ADV_OPTIONS_VIEW = "partials.field_option_forms.genlist";

    protected $fillable = [
        'rid',
        'flid',
        'options'
    ];

    public static function getOptions(){
        return '[!Regex!][!Regex!][!Options!][!Options!]';
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

    public static function updateOptions($pid, $fid, $flid, $request){
        $reqDefs = $request->default;
        $default = $reqDefs[0];
        for($i=1;$i<sizeof($reqDefs);$i++){
            $default .= '[!]'.$reqDefs[$i];
        }

        $reqOpts = $request->options;
        $options = $reqOpts[0];
        for($i=1;$i<sizeof($reqOpts);$i++){
            if ($request->regex!='' && !preg_match($request->regex, $reqOpts[$i]))
            {
                flash()->error(trans('controller_option.genregex',['opt' => $reqOpts[$i]]));

                return redirect('projects/'.$pid.'/forms/'.$fid.'/fields/'.$flid.'/options')->withInput();
            }
            $options .= '[!]'.$reqOpts[$i];
        }

        FieldController::updateRequired($pid, $fid, $flid, $request->required);
        FieldController::updateSearchable($pid, $fid, $flid, $request);
        FieldController::updateDefault($pid, $fid, $flid, $default);
        FieldController::updateOptions($pid, $fid, $flid, 'Regex', $request->regex);
        FieldController::updateOptions($pid, $fid, $flid, 'Options', $options);
    }

    public static function createNewRecordField($field, $record, $value){
        $glf = new self();
        $glf->flid = $field->flid;
        $glf->rid = $record->rid;
        $glf->fid = $field->fid;
        $glf->options = implode("[!]",$value);
        $glf->save();
    }

    public static function editRecordField($field, $record, $value){
        //we need to check if the field exist first
        $glf = self::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->first();
        if(!is_null($glf) && !is_null($value)){
            $glf->options = implode("[!]",$value);
            $glf->save();
        }elseif(!is_null($glf) && is_null($value)){
            $glf->delete();
        }
        else {
            self::createNewRecordField($field, $record, $value);
        }
    }

    public static function massAssignRecordField($flid, $record, $form_field_value, $overwrite){
        $matching_record_fields = $record->generatedlistfields()->where("flid", '=', $flid)->get();
        $record->updated_at = Carbon::now();
        $record->save();
        if ($matching_record_fields->count() > 0) {
            $generatedlistfield = $matching_record_fields->first();
            if ($overwrite == true || $generatedlistfield->options == "" || is_null($generatedlistfield->options)) {
                $revision = RevisionController::storeRevision($record->rid, 'edit');
                $generatedlistfield->options = implode("[!]", $form_field_value);
                $generatedlistfield->save();
                $revision->oldData = RevisionController::buildDataArray($record);
                $revision->save();
            }
        } else {
            $glf = new self();
            $revision = RevisionController::storeRevision($record->rid, 'edit');
            $glf->flid = $flid;
            $glf->rid = $record->rid;
            $glf->options = implode("[!]", $form_field_value);
            $glf->save();
            $revision->oldData = RevisionController::buildDataArray($record);
            $revision->save();
        }
    }

    public static function createTestRecordField($field, $record){
        $glf = new self();
        $glf->flid = $field->flid;
        $glf->rid = $record->rid;
        $glf->fid = $field->fid;
        $glf->options = 'K3TR[!]1337[!]Test[!]Record';
        $glf->save();
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
        $gnlfield = GeneratedListField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->first();

        if (!empty($gnlfield->options)) {
            $data['options'] = explode('[!]', $gnlfield->options);
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
     * Rollback a generated list field based on a revision.
     *
     * ** Assumes $revision->data is json decoded. **
     *
     * @param Revision $revision
     * @param Field $field
     * @return GeneratedListField
     */
    public static function rollback(Revision $revision, Field $field) {
        if (!is_array($revision->data)) {
            $revision->data = json_decode($revision->data, true);
        }

        if (is_null($revision->data[Field::_GENERATED_LIST][$field->flid]['data'])) {
            return null;
        }

        $genfield = self::where("flid", "=", $field->flid)->where("rid", "=", $revision->rid)->first();

        // If the field doesn't exist or was explicitly deleted, we create a new one.
        if ($revision->type == Revision::DELETE || is_null($genfield)) {
            $genfield = new self();
            $genfield->flid = $field->flid;
            $genfield->rid = $revision->rid;
            $genfield->fid = $revision->fid;
        }

        $genfield->options = $revision->data[Field::_GENERATED_LIST][$field->flid]['data'];
        $genfield->save();

        return $genfield;
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

    public static function validate($field, $value){
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
}
