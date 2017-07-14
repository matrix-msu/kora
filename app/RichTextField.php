<?php namespace App;

use App\Http\Controllers\FieldController;
use App\Http\Controllers\RevisionController;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RichTextField extends BaseField {

    const FIELD_OPTIONS_VIEW = "fields.options.richtext";
    const FIELD_ADV_OPTIONS_VIEW = "partials.field_option_forms.richtext";

    protected $fillable = [
        'rid',
        'flid',
        'rawtext',
        'searchable_rawtext'
    ];

    public function getFieldOptionsView(){
        return self::FIELD_OPTIONS_VIEW;
    }

    public function getAdvancedFieldOptionsView(){
        return self::FIELD_ADV_OPTIONS_VIEW;
    }

    public function getDefaultOptions(Request $request){
        return '';
    }

    public function updateOptions($field, Request $request, $return=true) {
        $field->updateRequired($request->required);
        $field->updateSearchable($request);
        $field->updateDefault($request->default);

        if($return) {
            flash()->overlay(trans('controller_field.optupdate'), trans('controller_field.goodjob'));
            return redirect('projects/' . $field->pid . '/forms/' . $field->fid . '/fields/' . $field->flid . '/options');
        } else {
            return '';
        }
    }

    public function createNewRecordField($field, $record, $value, $request){
        if (!empty($value) && !is_null($value)) {
            $this->flid = $field->flid;
            $this->rid = $record->rid;
            $this->fid = $field->fid;
            $this->rawtext = $value;
            $this->save();
        }
    }

    public function editRecordField($value, $request) {
        if(!is_null($this) && !is_null($value)){
            $this->rawtext = $value;
            $this->save();
        }elseif(!is_null($this) && is_null($value)){
            $this->delete();
        }
    }

    public function massAssignRecordField($field, $record, $formFieldValue, $request, $overwrite=0) {
        $matching_record_fields = $record->richtextfields()->where("flid", '=', $field->flid)->get();
        $record->updated_at = Carbon::now();
        $record->save();
        if ($matching_record_fields->count() > 0) {
            $richtextfield = $matching_record_fields->first();
            if ($overwrite == true || $richtextfield->rawtext == "" || is_null($richtextfield->rawtext)) {
                $revision = RevisionController::storeRevision($record->rid, 'edit');
                $richtextfield->rawtext = $formFieldValue;
                $richtextfield->save();
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
        $this->rawtext = '<b>K3TR</b>: This is a <i>test</i> record';
        $this->save();
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

        if (is_null($revision->data[Field::_RICH_TEXT][$field->flid]['data'])) {
            return null;
        }

        // If the field doesn't exist or was explicitly deleted, we create a new one.
        if ($revision->type == Revision::DELETE || !$exists) {
            $this->flid = $field->flid;
            $this->rid = $revision->rid;
            $this->fid = $revision->fid;
        }

        $this->rawtext = $revision->data[Field::_RICH_TEXT][$field->flid]['data'];
        $this->save();
    }

    public function getRecordPresetArray($data, $exists=true) {
        if ($exists) {
            $data['rawtext'] = $this->rawtext;
        }
        else {
            $data['rawtext'] = null;
        }

        return $data;
    }

    public function getExportSample($slug,$type) {
        switch ($type){
            case "XML":
                $xml = '<' . Field::xmlTagClear($slug) . ' type="Rich Text">';
                $xml .= utf8_encode('<b>RICH TEXT VALUE</b>');
                $xml .= '</' . Field::xmlTagClear($slug) . '>';

                return $xml;
                break;
            case "JSON":
                $fieldArray = array('name' => $slug, 'type' => 'Rich Text');
                $fieldArray['richtext'] = '<b>RICH TEXT VALUE</b>';

                return $fieldArray;
                break;
        }

    }

    ///////////////////////////////////////////////END ABSTRACT FUNCTIONS///////////////////////////////////////////////

    public static function setRestfulAdvSearch($data, $field, $request){
        $request->request->add([$field->flid.'_input' => $data->input]);

        return $request;
    }

    public static function setRestfulRecordData($field, $flid, $recRequest){
        $recRequest[$flid] = $field->richtext;

        return $recRequest;
    }

    /**
     * Saves the model.
     *
     * Instead of putting this everywhere the rawtext member is assigned we'll just override the member function.
     *
     * @param array $options
     * @return bool
     */
    public function save(array $options = array()) {
        $this->searchable_rawtext = strip_tags($this->rawtext);

        return parent::save($options);
    }

    /**
     * @param Field | null $field
     * @return string
     */
    public function getRevisionData($field = null) {
        return $this->rawtext;
    }

    /**
     * Builds the advanced search query for a rich text field.
     *
     * @param $flid
     * @param array $query
     * @return Builder
     */
    public static function getAdvancedSearchQuery($flid, array $query) {
        return DB::table("rich_text_fields")
            ->select("rid")
            ->where("flid", "=", $flid)
            ->whereRaw("MATCH (`searchable_rawtext`) AGAINST (? IN BOOLEAN MODE)",
                [Search::processArgument($query[$flid . "_input"], Search::ADVANCED_METHOD)])
            ->distinct();
    }
}