<?php namespace App;

use App\Http\Controllers\FieldController;
use App\Http\Controllers\RevisionController;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TextField extends BaseField {

    const FIELD_OPTIONS_VIEW = "fields.options.text";
    const FIELD_ADV_OPTIONS_VIEW = "partials.field_option_forms.text";

    protected $fillable = [
        'rid',
        'flid',
        'text'
    ];

    public static function getOptions(){
        return '[!Regex!][!Regex!][!MultiLine!]0[!MultiLine!]';
    }

    public static function getExportSample($field,$type){
        switch ($type){
            case "XML":
                $xml = '<' . Field::xmlTagClear($field->slug) . ' type="' . $field->type . '">';
                $xml .= utf8_encode('TEXT VALUE');
                $xml .= '</' . Field::xmlTagClear($field->slug) . '>';

                return $xml;
                break;
            case "JSON":
                $fieldArray = array('name' => $field->slug, 'type' => $field->type);
                $fieldArray['text'] = 'TEXT VALUE';

                return $fieldArray;
                break;
        }

    }

    public static function updateOptions($pid, $fid, $flid, $request, $return=true){
        $advString = '';

        if($request->regex!=''){
            $regArray = str_split($request->regex);
            if($regArray[0]!=end($regArray)){
                $request->regex = '/'.$request->regex.'/';
            }
            if ($request->default!='' && !preg_match($request->regex, $request->default))
            {
                if($return){
                    flash()->error('The default value does not match the given regex pattern.');

                    return redirect('projects/' . $pid . '/forms/' . $fid . '/fields/' . $flid . '/options')->withInput();
                }else{
                    $request->default = '';
                    $advString = 'The default value does not match the given regex pattern.';
                }
            }
        }

        FieldController::updateRequired($pid, $fid, $flid, $request->required);
        FieldController::updateSearchable($pid, $fid, $flid, $request);
        FieldController::updateDefault($pid, $fid, $flid, $request->default);
        FieldController::updateOptions($pid, $fid, $flid, 'Regex', $request->regex);
        FieldController::updateOptions($pid, $fid, $flid, 'MultiLine', $request->multi);

        return $advString;
    }

    public static function createNewRecordField($field, $record, $value){
        if (!empty($value) && !is_null($value)) {
            $tf = new self();
            $tf->flid = $field->flid;
            $tf->rid = $record->rid;
            $tf->fid = $field->fid;
            $tf->text = $value;
            $tf->save();
        }
    }

    public static function editRecordField($field, $record, $value){
        //we need to check if the field exist first
        $tf  = self::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->first();
        if(!is_null($tf) && !is_null($value)){
            $tf->text = $value;
            $tf->save();
        }
        elseif(!is_null($tf) && is_null($value)){
            $tf->delete();
        }
        else {
            self::createNewRecordField($field, $record, $value);
        }
    }

    public static function massAssignRecordField($flid, $record, $form_field_value, $overwrite){
        $matching_record_fields = $record->textfields()->where("flid", '=', $flid)->get();
        $record->updated_at = Carbon::now();
        $record->save();
        if ($matching_record_fields->count() > 0) {
            $textfield = $matching_record_fields->first();
            if ($overwrite == true || $textfield->text == "" || is_null($textfield->text)) {
                $revision = RevisionController::storeRevision($record->rid, 'edit');
                $textfield->text = $form_field_value;
                $textfield->save();
                $revision->oldData = RevisionController::buildDataArray($record);
                $revision->save();
            }
        } else {
            $tf = new self();
            $revision = RevisionController::storeRevision($record->rid, 'edit');
            $tf->flid = $flid;
            $tf->rid = $record->rid;
            $tf->text = $form_field_value;
            $tf->save();
            $revision->oldData = RevisionController::buildDataArray($record);
            $revision->save();
        }
    }

    public static function createTestRecordField($field, $record){
        $tf = new TextField();
        $tf->flid = $field->flid;
        $tf->rid = $record->rid;
        $tf->fid = $field->fid;
        $tf->text = 'K3TR: This is a test record';
        $tf->save();
    }

    public static function setRestfulAdvSearch($data, $field, $request){
        $request->request->add([$field->flid.'_input' => $data->input]);

        return $request;
    }

    public static function setRestfulRecordData($field, $flid, $recRequest){
        $recRequest[$flid] = $field->text;

        return $recRequest;
    }

    public static function getRecordPresetArray($field, $record, $data, $flid_array){
        $textfield = self::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->first();

        if (!empty($textfield->text)) {
            $data['text'] = $textfield->text;
        }
        else {
            $data['text'] = null;
        }

        $flid_array[] = $field->flid;

        return array($data,$flid_array);
    }

    /**
     * @param Field | null $field
     * @return string
     */
    public function getRevisionData($field = null) {
        return $this->text;
    }

    /**
     * Rollback a text field based on a revision.
     *
     * @param Revision $revision
     * @param Field $field
     * @return TextField
     */
    public static function rollback(Revision $revision, Field $field) {
        if (!is_array($revision->data)) {
            $revision->data = json_decode($revision->data, true);
        }

        if (is_null($revision->data[Field::_TEXT][$field->flid]['data'])) {
            return null;
        }

        $textfield = self::where("flid", "=", $field->flid)->where("rid", "=", $revision->rid)->first();

        // If the field doesn't exist or was explicitly deleted, we create a new one.
        if ($revision->type == Revision::DELETE || is_null($textfield)) {
            $textfield = new self();
            $textfield->flid = $field->flid;
            $textfield->rid = $revision->rid;
            $textfield->fid = $revision->fid;
        }

        $textfield->text = $revision->data[Field::_TEXT][$field->flid]['data'];
        $textfield->save();

        return $textfield;
    }

    /**
     * Build the advanced query for a text field.
     *
     * @param $flid, field id
     * @param $query, contents of query.
     * @return Builder
     */
    public static function getAdvancedSearchQuery($flid, $query) {
        return DB::table("text_fields")
            ->select("rid")
            ->where("flid", "=", $flid)
            ->whereRaw("MATCH (`text`) AGAINST (? IN BOOLEAN MODE)",
                [Search::processArgument($query[$flid . "_input"], Search::ADVANCED_METHOD)])
            ->distinct();
    }

    public static function validate($field, $value){
        $req = $field->required;
        $regex = FieldController::getFieldOption($field, 'Regex');

        if($req==1 && ($value==null | $value=="")){
            return $field->name.trans('fieldhelpers_val.req');
        }

        if(($regex!=null | $regex!="") && !preg_match($regex,$value)){
            return trans('fieldhelpers_val.regex',['name'=>$field->name]);
        }

        return '';
    }
}