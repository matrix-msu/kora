<?php namespace App;

use App\Http\Controllers\FieldController;
use App\Http\Controllers\RevisionController;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

class NumberField extends BaseField {

    /*
    |--------------------------------------------------------------------------
    | Number Field
    |--------------------------------------------------------------------------
    |
    | This model represents the number field in Kora3
    |
    */

    /**
     * @var string - Views for the typed field options
     */
    const FIELD_OPTIONS_VIEW = "fields.options.number";
    const FIELD_ADV_OPTIONS_VIEW = "partials.field_option_forms.number";

    /**
     * Epsilon value for comparison purposes. Used to match between values in MySQL.
     *
     * @type float
     */
    CONST EPSILON = 0.0001;

    /**
     * @var array - Attributes that can be mass assigned to model
     */
    protected $fillable = [
        'rid',
        'flid',
        'number'
    ];

    /**
     * Get the field options view.
     *
     * @return string - The view
     */
    public function getFieldOptionsView() {
        return self::FIELD_OPTIONS_VIEW;
    }

    /**
     * Get the field options view for advanced field creation.
     *
     * @return string - The view
     */
    public function getAdvancedFieldOptionsView() {
        return self::FIELD_ADV_OPTIONS_VIEW;
    }

    /**
     * Gets the default options string for a new field.
     *
     * @param  Request $request
     * @return string - The default options
     */
    public function getDefaultOptions(Request $request) {
        return '[!Max!][!Max!][!Min!][!Min!][!Increment!]1[!Increment!][!Unit!][!Unit!]';
    }

    /**
     * Update the options for a field
     *
     * @param  Field $field - Field to update options
     * @param  Request $request
     * @param  bool $return - Are we returning an error by string or redirect
     * @return mixed - The result
     */
    public function updateOptions($field, Request $request, $return=true) {
        //these are help prevent interruption of correct parameters when error is found in advanced setup
        $advString = '';

        if($request->min!='' && $request->max!='') {
            if($request->min >= $request->max) {
                if($return) {
                    flash()->error('The max value is less than or equal to the minimum value. ');

                    return redirect('projects/' . $field->pid . '/forms/' . $field->fid . '/fields/' . $field->flid . '/options')->withInput();
                } else {
                    $request->min = '';
                    $request->max = '';
                    $advString = 'The max value is less than or equal to the minimum value.';
                }
            }
        }

        if($request->default!='' && $request->max!='') {
            if($request->default > $request->max) {
                if($return) {
                    flash()->error('The max value is less than the default value. ');

                    return redirect('projects/' . $field->pid . '/forms/' . $field->fid . '/fields/' . $field->flid . '/options')->withInput();
                } else {
                    $request->default = '';
                    $advString = 'The max value is less than the default value.';
                }
            }
        }

        if($request->default!='' && $request->min!='') {
            if($request->default < $request->min) {
                if($return) {
                    flash()->error('The minimum value is greater than the default value. ');

                    return redirect('projects/' . $field->pid . '/forms/' . $field->fid . '/fields/' . $field->flid . '/options')->withInput();
                } else {
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

    /**
     * Creates a typed field to store record data.
     *
     * @param  Field $field - The field to represent record data
     * @param  Record $record - Record being created
     * @param  string $value - Data to add
     * @param  Request $request
     */
    public function createNewRecordField($field, $record, $value, $request) {
        if(!empty($value) && !is_null($value)) {
            $this->flid = $field->flid;
            $this->rid = $record->rid;
            $this->fid = $field->fid;
            $this->number = $value;
            $this->save();
        }
    }

    /**
     * Edits a typed field that has record data.
     *
     * @param  string $value - Data to add
     * @param  Request $request
     */
    public function editRecordField($value, $request) {
        if(!is_null($this) && !is_null($value)) {
            $this->number = $value;
            $this->save();
        } else if(!is_null($this) && is_null($value)) {
            $this->delete();
        }
    }

    /**
     * Takes data from a mass assignment operation and applies it to an individual field.
     *
     * @param  Field $field - The field to represent record data
     * @param  Record $record - Record being written to
     * @param  String $formFieldValue - The value to be assigned
     * @param  Request $request
     * @param  bool $overwrite - Overwrite if data exists
     */
    public function massAssignRecordField($field, $record, $formFieldValue, $request, $overwrite=0) {
        $matching_record_fields = $record->numberfields()->where("flid", '=', $field->flid)->get();
        $record->updated_at = Carbon::now();
        $record->save();
        if($matching_record_fields->count() > 0) {
            $numberfield = $matching_record_fields->first();
            if($overwrite == true || $numberfield->number == "" || is_null($numberfield->number)) {
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

    /**
     * For a test record, add test data to field.
     *
     * @param  Field $field - The field to represent record data
     * @param  Record $record - Test record being created
     */
    public function createTestRecordField($field, $record) {
        $this->flid = $field->flid;
        $this->rid = $record->rid;
        $this->fid = $field->fid;
        $this->number = 1337;
        $this->save();
    }

    /**
     * Validates the record data for a field against the field's options.
     *
     * @param  Field $field - The
     * @param  mixed $value - Record data
     * @param  Request $request
     * @return string - Potential error message
     */
    public function validateField($field, $value, $request) {
        $req = $field->required;

        if($req==1 && ($value==null | $value==""))
            return $field->name.trans('fieldhelpers_val.req');
    }

    /**
     * Performs a rollback function on an individual field's record data.
     *
     * @param  Field $field - The field being rolled back
     * @param  Revision $revision - The revision being rolled back
     * @param  bool $exists - Field for record exists
     */
    public function rollbackField($field, Revision $revision, $exists=true) {
        if(!is_array($revision->data))
            $revision->data = json_decode($revision->data, true);

        if(is_null($revision->data[Field::_NUMBER][$field->flid]['data']['number']))
            return null;

        // If the field doesn't exist or was explicitly deleted, we create a new one.
        if($revision->type == Revision::DELETE || !$exists) {
            $this->flid = $field->flid;
            $this->rid = $revision->rid;
            $this->fid = $revision->fid;
        }

        $this->number = $revision->data[Field::_NUMBER][$field->flid]['data']['number'];
        $this->save();
    }

    /**
     * Get the arrayed version of the field data to store in a record preset.
     *
     * @param  array $data - The data array representing the record preset
     * @param  bool $exists - Typed field exists and has data
     * @return array - The updated $data
     */
    public function getRecordPresetArray($data, $exists=true) {
        if($exists)
            $data['number'] = $this->number;
        else
            $data['number'] = null;

        return $data;
    }

    /**
     * Get the required information for a revision data array.
     *
     * @param  Field $field - Optional field to get storage options for certain typed fields
     * @return mixed - The revision data
     */
    public function getRevisionData($field = null) {
        return [
            'number' => $this->number,
            'unit' => FieldController::getFieldOption($field, 'Unit')
        ];
    }

    /**
     * Provides an example of the field's structure in an export to help with importing records.
     *
     * @param  string $slug - Field nickname
     * @param  string $expType - Type of export
     * @return mixed - The example
     */
    public function getExportSample($slug,$type) {
        switch($type) {
            case "XML":
                $xml = '<' . Field::xmlTagClear($slug) . ' type="Number">';
                $xml .= utf8_encode('1337');
                $xml .= '</' . Field::xmlTagClear($slug) . '>';

                return $xml;
                break;
            case "JSON":
                $fieldArray = array('name' => $slug, 'type' => 'Number');
                $fieldArray['number'] = 1337;

                return $fieldArray;
                break;
        }

    }

    /**
     * Updates the request for an API search to mimic the advanced search structure.
     *
     * @param  array $data - Data from the search
     * @param  int $flid - Field ID
     * @param  Request $request
     * @return Request - The update request
     */
    public function setRestfulAdvSearch($data, $flid, $request) {
        if(isset($data->left))
            $leftNum = $data->left;
        else
            $leftNum = '';
        $request->request->add([$flid.'_left' => $leftNum]);
        if(isset($data->right))
            $rightNum = $data->right;
        else
            $rightNum = '';
        $request->request->add([$flid.'_right' => $rightNum]);
        if(isset($data->invert))
            $invert = $data->invert;
        else
            $invert = 0;
        $request->request->add([$flid.'_invert' => $invert]);

        return $request;
    }

    /**
     * Updates the request for an API to mimic record creation .
     *
     * @param  array $jsonField - JSON representation of field data
     * @param  int $flid - Field ID
     * @param  Request $recRequest
     * @param  int $uToken - Custom generated user token for file fields and tmp folders
     * @return Request - The update request
     */
    public function setRestfulRecordData($jsonField, $flid, $recRequest, $uToken=null) {
        $recRequest[$flid] = $jsonField->number;

        return $recRequest;
    }

    /**
     * Performs a keyword search on this field and returns any results.
     *
     * @param  int $fid - Form ID
     * @param  string $arg - The keywords
     * @param  string $method - Type of keyword search
     * @return Builder - The RIDs that match search
     */
    public function keywordSearchTyped($fid, $arg, $method) {
        $arg = str_replace(["*", "\""], "", $arg);

        if(is_numeric($arg)) { // Only search if we're working with a number.
            $arg = floatval($arg);

            return self::select("rid")
                ->where("fid", "=", $fid)
                ->whereBetween("number", [$arg - self::EPSILON, $arg + self::EPSILON])
                ->distinct();
        } else {
            return self::select("rid")
                ->where("id", "<", -1); // Purposefully impossible.
        }
    }

    /**
     * Performs an advanced search on this field and returns any results.
     *
     * @param  int $flid - Field ID
     * @param  array $query - The advance search user query
     * @return Builder - The RIDs that match search
     */
    public function getAdvancedSearchQuery($flid, $query) {
        $left = $query[$flid . "_left"];
        $right = $query[$flid . "_right"];
        $invert = isset($query[$flid . "_invert"]);

        $query = self::select("rid")
            ->where("flid", "=", $flid);

        self::buildAdvancedNumberQuery($query, $left, $right, $invert);

        return $query->distinct();
    }

    /**
     * Build an advanced search number field query. Public because Combolist borrows it. Otherwise it would be private
     * like the others.
     *
     * @param  Builder $query - Query to build upon
     * @param  string $left - Input from the form, left index
     * @param  string $right - Input from the form, right index
     * @param  bool $invert - Inverts the search range if true
     * @param  string $prefix - For dealing with joined tables
     */
    public static function buildAdvancedNumberQuery(Builder &$query, $left, $right, $invert, $prefix = "") {
        // Determine the interval we should search over. With epsilons to account for float rounding.
        if($left == "") {
            if ($invert) // (right, inf)
                $query->where($prefix . "number", ">", floatval($right) - self::EPSILON);
            else // (-inf, right]
                $query->where($prefix . "number", "<=", floatval($right) + self::EPSILON);
        } else if($right == "") {
            if($invert) // (-inf, left)
                $query->where($prefix . "number", "<", floatval($left) + self::EPSILON);
            else // [left, inf)
                $query->where($prefix . "number", ">=", floatval($left) - self::EPSILON);
        } else {
            if($invert) { // (-inf, left) union (right, inf)
                $query->whereNotBetween($prefix . "number", [floatval($left) - self::EPSILON,
                    floatval($right) + self::EPSILON]);
            } else { // [left, right]
                $query->whereBetween($prefix . "number", [floatval($left) - self::EPSILON,
                    floatval($right) + self::EPSILON]);
            }
        }
    }

    ///////////////////////////////////////////////END ABSTRACT FUNCTIONS///////////////////////////////////////////////

    /**
     * Gets formatted value of record field to compare for sort. Only implement if field is sortable.
     *
     * @return string - The value
     */
    public function getValueForSort() {
        return $this->number;
    }
}
