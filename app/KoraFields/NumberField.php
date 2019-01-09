<?php namespace App;

use App\Http\Controllers\FieldController;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

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
    const FIELD_OPTIONS_VIEW = "partials.fields.options.number";
    const FIELD_ADV_OPTIONS_VIEW = "partials.fields.advanced.number";
    const FIELD_ADV_INPUT_VIEW = "partials.records.advanced.number";
    const FIELD_INPUT_VIEW = "partials.records.input.number";
    const FIELD_DISPLAY_VIEW = "partials.records.display.number";

    /**
     * @var string - Data column used for sort
     */
    const SORT_COLUMN = "number";

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
     * Get the field options view for advanced field creation.
     *
     * @return string - Column name
     */
    public function getSortColumn() {
        return self::SORT_COLUMN;
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
     * Gets an array of all the fields options.
     *
     * @param  Field $field
     * @return array - The options array
     */
    public function getOptionsArray(Field $field) {
        $options = array();

        $options['MaxValue'] = FieldController::getFieldOption($field, 'Max');
        $options['MinValue'] = FieldController::getFieldOption($field, 'Min');
        $options['Increment'] = FieldController::getFieldOption($field, 'Increment');
        $options['UnitOfMeasure'] = FieldController::getFieldOption($field, 'Unit');

        return $options;
    }

    /**
     * Update the options for a field
     *
     * @param  Field $field - Field to update options
     * @param  Request $request
     * @return Redirect
     */
    public function updateOptions($field, Request $request) {
        if($request->min!='' && $request->max!='') {
            if($request->min >= $request->max) {
                return redirect('projects/' . $field->pid . '/forms/' . $field->fid . '/fields/' . $field->flid . '/options')
                    ->withInput()->with('k3_global_error', 'max_min_invalid');
            }
        }

        if($request->default!='' && $request->max!='') {
            if($request->default > $request->max) {
                return redirect('projects/' . $field->pid . '/forms/' . $field->fid . '/fields/' . $field->flid . '/options')
                    ->withInput()->with('k3_global_error', 'default_above_max');
            }
        }

        if($request->default!='' && $request->min!='') {
            if($request->default < $request->min) {
                return redirect('projects/' . $field->pid . '/forms/' . $field->fid . '/fields/' . $field->flid . '/options')
                    ->withInput()->with('k3_global_error', 'default_below_min');
            }
        }

        $field->updateRequired($request->required);
        $field->updateSearchable($request);
        $field->updateDefault($request->default);
        $field->updateOptions('Max', $request->max);
        $field->updateOptions('Min', $request->min);
        $field->updateOptions('Increment', $request->inc);
        $field->updateOptions('Unit', $request->unit);

        return redirect('projects/' . $field->pid . '/forms/' . $field->fid . '/fields/' . $field->flid . '/options')
            ->with('k3_global_success', 'field_options_updated');
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
     * @param  String $formFieldValue - The value to be assigned
     * @param  Request $request
     * @param  bool $overwrite - Overwrite if data exists
     */
    public function massAssignRecordField($field, $formFieldValue, $request, $overwrite=0) {
        //Get array of all RIDs in form
        $rids = Record::where('fid','=',$field->fid)->pluck('rid')->toArray();
        //Get list of RIDs that have the value for that field
        $ridsValue = NumberField::where('flid','=',$field->flid)->where('number','!=','')->where('number','!=',NULL)->pluck('rid')->toArray();
        //Subtract to get RIDs with no value
        $ridsNoVal = array_diff($rids, $ridsValue);

        foreach(array_chunk($ridsNoVal,1000) as $chunk) {
            //Create data array and store values for no value RIDs
            $dataArray = [];
            foreach($chunk as $rid) {
                $dataArray[] = [
                    'rid' => $rid,
                    'fid' => $field->fid,
                    'flid' => $field->flid,
                    'number' => $formFieldValue
                ];
            }
            NumberField::insert($dataArray);
        }

        if($overwrite) {
            foreach(array_chunk($ridsValue, 1000) as $chunk) {
                NumberField::where('flid', '=', $field->flid)->whereIn('rid', $chunk)->update(['number' => $formFieldValue]);
            }
        }
    }

    /**
     * Takes data from a mass assignment operation and applies it to an individual field for a record subset.
     *
     * @param  Field $field - The field to represent record data
     * @param  String $formFieldValue - The value to be assigned
     * @param  Request $request
     * @param  array $rids - Overwrite if data exists
     */
    public function massAssignSubsetRecordField($field, $formFieldValue, $request, $rids) {
        //Delete the old data
        NumberField::where('flid','=',$field->flid)->whereIn('rid', $rids)->delete();

        foreach(array_chunk($rids,1000) as $chunk) {
            //Create data array and store values for no value RIDs
            $dataArray = [];
            foreach($chunk as $rid) {
                $dataArray[] = [
                    'rid' => $rid,
                    'fid' => $field->fid,
                    'flid' => $field->flid,
                    'number' => $formFieldValue
                ];
            }
            NumberField::insert($dataArray);
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
        $this->number = 3;
        $this->save();
    }

    /**
     * Validates the record data for a field against the field's options.
     *
     * @param  Field $field - The field to validate
     * @param  Request $request
     * @param  bool $forceReq - Do we want to force a required value even if the field itself is not required?
     * @return array - Array of errors
     */
    public function validateField($field, $request, $forceReq = false) {
        $req = $field->required;
        $value = $request->{$field->flid};
        $min = FieldController::getFieldOption($field, 'Min');
        $max = FieldController::getFieldOption($field, 'Max');

        if(($req==1 | $forceReq) && ($value==null | $value==""))
            return [$field->flid => $field->name.' is required'];

        if($min!='' && $value!="" && $value<$min)
            return [$field->flid => $field->name.' can not be less than '.$min];

        if($max!='' && $value!="" && $value>$max)
            return [$field->flid => $field->name.' can not be more than '.$max];

        return array();
    }

    /**
     * Performs a rollback function on an individual field's record data.
     *
     * @param  Field $field - The field being rolled back
     * @param  Revision $revision - The revision being rolled back
     * @param  bool $exists - Field for record exists
     */
    public function rollbackField($field, Revision $revision, $exists=true) {
        if(!is_array($revision->oldData))
            $revision->oldData = json_decode($revision->oldData, true);

        if(is_null($revision->oldData[Field::_NUMBER][$field->flid]['data']['number']))
            return null;

        // If the field doesn't exist or was explicitly deleted, we create a new one.
        if($revision->type == Revision::DELETE || !$exists) {
            $this->flid = $field->flid;
            $this->rid = $revision->rid;
            $this->fid = $revision->fid;
        }

        $this->number = $revision->oldData[Field::_NUMBER][$field->flid]['data']['number'];
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
                $xml .= utf8_encode('3');
                $xml .= '</' . Field::xmlTagClear($slug) . '>';

                return $xml;
                break;
            case "JSON":
                $fieldArray = [$slug => ['type' => 'Number']];
                $fieldArray[$slug]['value'] = 3;

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
        $recRequest[$flid] = $jsonField->value;

        return $recRequest;
    }

    /**
     * Performs a keyword search on this field and returns any results.
     *
     * @param  int $flid - Field ID
     * @param  string $arg - The keywords
     * @return array - The RIDs that match search
     */
    public function keywordSearchTyped($flid, $arg) {
        if(is_numeric($arg)) { // Only search if we're working with a number.
            $arg = floatval($arg);

            return DB::table("number_fields")
                ->select("rid")
                ->where("flid", "=", $flid)
                ->whereBetween("number", [$arg - self::EPSILON, $arg + self::EPSILON])
                ->distinct()
                ->pluck('rid')
                ->toArray();
        } else {
            return array();
        }
    }

    /**
     * Performs an advanced search on this field and returns any results.
     *
     * @param  int $flid - Field ID
     * @param  array $query - The advance search user query
     * @return array - The RIDs that match search
     */
    public function advancedSearchTyped($flid, $query) {
        $left = $query[$flid . "_left"];
        $right = $query[$flid . "_right"];
        $invert = isset($query[$flid . "_invert"]);

        $query = DB::table("number_fields")
            ->select("rid")
            ->where("flid", "=", $flid);

        self::buildAdvancedNumberQuery($query, $left, $right, $invert);

        return $query->distinct()
            ->pluck('rid')
            ->toArray();
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
            if($invert) // [right, inf)
                $query->where($prefix . "number", ">", floatval($right) - self::EPSILON);
            else // (-inf, right]
                $query->where($prefix . "number", "<=", floatval($right) + self::EPSILON);
        } else if($right == "") {
            if($invert) // (-inf, left]
                $query->where($prefix . "number", "<", floatval($left) + self::EPSILON);
            else // [left, inf)
                $query->where($prefix . "number", ">=", floatval($left) - self::EPSILON);
        } else {
            if($invert) { // (-inf, left] union [right, inf)
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
     * Returns the mysql string required to sort a set of RIDs.
     *
     * @param $ridArray - String of record IDs
     * @param $flid - Field ID
     * @param $dir - Direction of sorting
     * @return string - The MySQL string
     */
    public function getRidValuesForGlobalSort($ridArray,$flids,$dir) {
        $prefix = config('database.connections.mysql.prefix');
        $flidArray = implode(',',$flids);
        return "SELECT `rid`, `number` AS `value` FROM ".$prefix."number_fields WHERE `flid` IN ($flidArray) AND `rid` IN ($ridArray) ORDER BY `number` $dir";
    }
}
