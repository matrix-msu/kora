<?php namespace App;

use App\Http\Controllers\FieldController;
use App\Http\Controllers\RevisionController;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NumberField extends BaseField {

    const FIELD_OPTIONS_VIEW = "fields.options.number";
    const FIELD_ADV_OPTIONS_VIEW = "partials.field_option_forms.number";

    /**
     * Epsilon value for comparison purposes. Used to match between values in MySQL.
     *
     * @type float
     */
    CONST EPSILON = 0.0001;

    protected $fillable = [
        'rid',
        'flid',
        'number'
    ];

    public function getFieldOptionsView(){
        return self::FIELD_OPTIONS_VIEW;
    }

    public function getAdvancedFieldOptionsView(){
        return self::FIELD_ADV_OPTIONS_VIEW;
    }

    public function getDefaultOptions(Request $request){
        return '[!Max!][!Max!][!Min!][!Min!][!Increment!]1[!Increment!][!Unit!][!Unit!]';
    }

    public function updateOptions($field, Request $request, $return=true) {
        //these are help prevent interruption of correct parameters when error is found in advanced setup
        $advString = '';

        if($request->min!='' && $request->max!=''){
            if($request->min >= $request->max){
                if($return){
                    flash()->error('The max value is less than or equal to the minimum value. ');

                    return redirect('projects/' . $field->pid . '/forms/' . $field->fid . '/fields/' . $field->flid . '/options')->withInput();
                }else{
                    $request->min = '';
                    $request->max = '';
                    $advString = 'The max value is less than or equal to the minimum value.';
                }
            }
        }

        if($request->default!='' && $request->max!=''){
            if($request->default > $request->max) {
                if($return){
                    flash()->error('The max value is less than the default value. ');

                    return redirect('projects/' . $field->pid . '/forms/' . $field->fid . '/fields/' . $field->flid . '/options')->withInput();
                }else{
                    $request->default = '';
                    $advString = 'The max value is less than the default value.';
                }
            }
        }

        if($request->default!='' && $request->min!=''){
            if($request->default < $request->min) {
                if($return){
                    flash()->error('The minimum value is greater than the default value. ');

                    return redirect('projects/' . $field->pid . '/forms/' . $field->fid . '/fields/' . $field->flid . '/options')->withInput();
                }else{
                    $request->default = '';
                    $advString = 'The minimum value is greater than the default value.';
                }
            }
        }

        $field->updateRequired($request->required);
        $field->updateSearchable($request);
        $field->updateDefault($request->default);
        $field->updateOptions('Max', $request->max);
        $field->updateOptions('Min', $request->min);
        $field->updateOptions('Increment', $request->inc);
        $field->updateOptions('Unit', $request->unit);

        if($return) {
            flash()->overlay(trans('controller_field.optupdate'), trans('controller_field.goodjob'));
            return redirect('projects/' . $field->pid . '/forms/' . $field->fid . '/fields/' . $field->flid . '/options');
        } else {
            return $advString;
        }
    }

    public function createNewRecordField($field, $record, $value, $request){
        if (!empty($value) && !is_null($value)) {
            $this->flid = $field->flid;
            $this->rid = $record->rid;
            $this->fid = $field->fid;
            $this->number = $value;
            $this->save();
        }
    }

    public function editRecordField($value, $request) {
        if(!is_null($this) && !is_null($value)){
            $this->number = $value;
            $this->save();
        }
        else if(!is_null($this) && is_null($value)){
            $this->delete();
        }
    }

    public function massAssignRecordField($field, $record, $formFieldValue, $request, $overwrite=0) {
        $matching_record_fields = $record->numberfields()->where("flid", '=', $field->flid)->get();
        $record->updated_at = Carbon::now();
        $record->save();
        if ($matching_record_fields->count() > 0) {
            $numberfield = $matching_record_fields->first();
            if ($overwrite == true || $numberfield->number == "" || is_null($numberfield->number)) {
                $revision = RevisionController::storeRevision($record->rid, 'edit');
                $numberfield->number = $formFieldValue;
                $numberfield->save();
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
        $this->number = 1337;
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

        if (is_null($revision->data[Field::_NUMBER][$field->flid]['data']['number'])) {
            return null;
        }

        // If the field doesn't exist or was explicitly deleted, we create a new one.
        if ($revision->type == Revision::DELETE || !$exists) {
            $this->flid = $field->flid;
            $this->rid = $revision->rid;
            $this->fid = $revision->fid;
        }

        $this->number = $revision->data[Field::_NUMBER][$field->flid]['data']['number'];
        $this->save();
    }

    public function getRecordPresetArray($data, $exists=true) {
        if ($exists) {
            $data['number'] = $this->number;
        }
        else {
            $data['number'] = null;
        }

        return $data;
    }

    ///////////////////////////////////////////////END ABSTRACT FUNCTIONS///////////////////////////////////////////////

    public static function getExportSample($field,$type){
        switch ($type){
            case "XML":
                $xml = '<' . Field::xmlTagClear($field->slug) . ' type="' . $field->type . '">';
                $xml .= utf8_encode('1337');
                $xml .= '</' . Field::xmlTagClear($field->slug) . '>';

                return $xml;
                break;
            case "JSON":
                $fieldArray = array('name' => $field->slug, 'type' => $field->type);
                $fieldArray['number'] = 1337;

                return $fieldArray;
                break;
        }

    }

    public static function setRestfulAdvSearch($data, $field, $request){
        if(isset($data->left))
            $leftNum = $data->left;
        else
            $leftNum = '';
        $request->request->add([$field->flid.'_left' => $leftNum]);
        if(isset($data->right))
            $rightNum = $data->right;
        else
            $rightNum = '';
        $request->request->add([$field->flid.'_right' => $rightNum]);
        if(isset($data->invert))
            $invert = $data->invert;
        else
            $invert = 0;
        $request->request->add([$field->flid.'_invert' => $invert]);

        return $request;
    }

    public static function setRestfulRecordData($field, $flid, $recRequest){
        $recRequest[$flid] = $field->number;

        return $recRequest;
    }

    /**
     * @param null $field
     * @return array
     */
    public function getRevisionData($field = null) {
        return [
            'number' => $this->number,
            'unit' => FieldController::getFieldOption($field, 'Unit')
        ];
    }

    /**
     * Builds the advanced query for a number field.
     * More explicitly, this will build a search range in MySQL based off the inputs.
     *
     * @param $flid, field id
     * @param $query, query array
     * @return Builder
     */
    public static function getAdvancedSearchQuery($flid, $query) {
        $left = $query[$flid . "_left"];
        $right = $query[$flid . "_right"];
        $invert = isset($query[$flid . "_invert"]);

        $query = DB::table("number_fields")
            ->select("rid")
            ->where("flid", "=", $flid);

        self::buildAdvancedNumberQuery($query, $left, $right, $invert);

        return $query->distinct();
    }

    /**
     * Build an advanced search number field query.
     *
     * @param Builder $query, query to build upon.
     * @param string $left, input from the form, left index.
     * @param string $right, input from the form, right index.
     * @param bool $invert, inverts the search range if true.
     * @param string $prefix, for dealing with joined tables.
     */
    public static function buildAdvancedNumberQuery(Builder &$query, $left, $right, $invert, $prefix = "") {
        // Determine the interval we should search over. With epsilons to account for float rounding.
        if ($left == "") {
            if ($invert) { // (right, inf)
                $query->where($prefix . "number", ">", floatval($right) - self::EPSILON);
            }
            else { // (-inf, right]
                $query->where($prefix . "number", "<=", floatval($right) + self::EPSILON);
            }
        }
        else if ($right == "") {
            if ($invert) { // (-inf, left)
                $query->where($prefix . "number", "<", floatval($left) + self::EPSILON);
            }
            else { // [left, inf)
                $query->where($prefix . "number", ">=", floatval($left) - self::EPSILON);
            }
        }
        else {
            if ($invert) { // (-inf, left) union (right, inf)
                $query->whereNotBetween($prefix . "number", [floatval($left) - self::EPSILON,
                    floatval($right) + self::EPSILON]);
            }
            else { // [left, right]
                $query->whereBetween($prefix . "number", [floatval($left) - self::EPSILON,
                    floatval($right) + self::EPSILON]);
            }
        }
    }
}
