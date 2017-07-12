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

    public static function getExportSample($field,$type){
        switch ($type){
            case "XML":
                $xml = '<' . Field::xmlTagClear($field->slug) . ' type="' . $field->type . '">';
                $xml .= utf8_encode('<b>RICH TEXT VALUE</b>');
                $xml .= '</' . Field::xmlTagClear($field->slug) . '>';

                return $xml;
                break;
            case "JSON":
                $fieldArray = array('name' => $field->slug, 'type' => $field->type);
                $fieldArray['richtext'] = '<b>RICH TEXT VALUE</b>';

                return $fieldArray;
                break;
        }

    }

    public static function createNewRecordField($field, $record, $value){
        if (!empty($value) && !is_null($value)) {
            $rtf = new self();
            $rtf->flid = $field->flid;
            $rtf->rid = $record->rid;
            $rtf->fid = $field->fid;
            $rtf->rawtext = $value;
            $rtf->save();
        }
    }

    public static function editRecordField($field, $record, $value){
        //we need to check if the field exist first
        $rtf = self::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->first();
        if(!is_null($rtf) && !is_null($value)){
            $rtf->rawtext = $value;
            $rtf->save();
        }elseif(!is_null($rtf) && is_null($value)){
            $rtf->delete();
        }
        else {
            self::createNewRecordField($field, $record, $value);
        }
    }

    public static function massAssignRecordField($flid, $record, $form_field_value, $overwrite){
        $matching_record_fields = $record->richtextfields()->where("flid", '=', $flid)->get();
        $record->updated_at = Carbon::now();
        $record->save();
        if ($matching_record_fields->count() > 0) {
            $richtextfield = $matching_record_fields->first();
            if ($overwrite == true || $richtextfield->rawtext == "" || is_null($richtextfield->rawtext)) {
                $revision = RevisionController::storeRevision($record->rid, 'edit');
                $richtextfield->rawtext = $form_field_value;
                $richtextfield->save();
                $revision->oldData = RevisionController::buildDataArray($record);
                $revision->save();
            }
        } else {
            $rtf = new self();
            $revision = RevisionController::storeRevision($record->rid, 'edit');
            $rtf->flid = $flid;
            $rtf->rid = $record->rid;
            $rtf->rawtext = $form_field_value;
            $rtf->save();
            $revision->oldData = RevisionController::buildDataArray($record);
            $revision->save();
        }
    }

    public static function createTestRecordField($field, $record){
        $rtf = new self();
        $rtf->flid = $field->flid;
        $rtf->rid = $record->rid;
        $rtf->fid = $field->fid;
        $rtf->rawtext = '<b>K3TR</b>: This is a <i>test</i> record';
        $rtf->save();
    }

    public static function setRestfulAdvSearch($data, $field, $request){
        $request->request->add([$field->flid.'_input' => $data->input]);

        return $request;
    }

    public static function setRestfulRecordData($field, $flid, $recRequest){
        $recRequest[$flid] = $field->richtext;

        return $recRequest;
    }

    public static function getRecordPresetArray($field, $record, $data, $flid_array){
        $rtfield = RichTextField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->first();

        if (!empty($rtfield->rawtext)) {
            $data['rawtext'] = $rtfield->rawtext;
        }
        else {
            $data['rawtext'] = null;
        }

        $flid_array[] = $field->flid;

        return array($data,$flid_array);
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
     * Rollback a rich text field based on a revision.
     *
     * @param Revision $revision
     * @param Field $field
     * @return RichTextField
     */
    public static function rollback(Revision $revision, Field $field) {
        if (!is_array($revision->data)) {
            $revision->data = json_decode($revision->data, true);
        }

        if (is_null($revision->data[Field::_RICH_TEXT][$field->flid]['data'])) {
            return null;
        }

        $richtextfield = self::where('flid', '=', $field->flid)->where('rid', '=', $revision->rid)->first();

        // If the field doesn't exist or was explicitly deleted, we create a new one.
        if ($revision->type == Revision::DELETE || is_null($richtextfield)) {
            $richtextfield = new self();
            $richtextfield->flid = $field->flid;
            $richtextfield->rid = $revision->rid;
            $richtextfield->fid = $revision->fid;
        }

        $richtextfield->rawtext = $revision->data[Field::_RICH_TEXT][$field->flid]['data'];
        $richtextfield->save();

        return $richtextfield;
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

    public static function validate($field, $value){
        $req = $field->required;

        if($req==1 && ($value==null | $value=="")){
            return $field->name.trans('fieldhelpers_val.req');
        }
    }
}