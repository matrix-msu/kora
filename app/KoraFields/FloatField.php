<?php namespace App\KoraFields;

use App\Form;
use App\Record;
use Illuminate\Http\Request;
use Illuminate\Database\Query\Builder;

class FloatField extends BaseField {

    /*
    |--------------------------------------------------------------------------
    | Float Field
    |--------------------------------------------------------------------------
    |
    | This model represents the float field in kora
    |
    */

    /**
     * @var string - Views for the typed field options
     */
    const FIELD_OPTIONS_VIEW = "partials.fields.options.float";
    const FIELD_ADV_OPTIONS_VIEW = "partials.fields.advanced.float";
    const FIELD_ADV_INPUT_VIEW = "partials.records.advanced.float";
    const FIELD_INPUT_VIEW = "partials.records.input.float";
    const FIELD_DISPLAY_VIEW = "partials.records.display.float";

    /**
     * @var string - Method from CreateRecordsTable() for adding to DB
     */
    const FIELD_DATABASE_METHOD = 'addDoubleColumn';

    /**
     * Epsilon value for comparison purposes. Used to match between values in MySQL.
     *
     * @type float
     */
    CONST EPSILON = 0.0001;

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
     * Get the field input view for advanced field search.
     *
     * @return string - The view
     */
    public function getAdvancedSearchInputView() {
        return self::FIELD_ADV_INPUT_VIEW;
    }

    /**
     * Get the field input view for record creation.
     *
     * @return string - The view
     */
    public function getFieldInputView() {
        return self::FIELD_INPUT_VIEW;
    }

    /**
     * Get the field input view for record creation.
     *
     * @return string - The view
     */
    public function getFieldDisplayView() {
        return self::FIELD_DISPLAY_VIEW;
    }

    /**
     * Gets the default options string for a new field.
     *
     * @return array - The default options
     */
    public function getDefaultOptions($type = null) {
        return ['Max' => '', 'Min' => '', 'Unit' => ''];
    }

    /**
     * Update the options for a field
     *
     * @param  Field $field - Field to update options
     * @param  Request $request
     * @param  int $flid - The field internal name
     * @return Redirect
     */
    public function updateOptions($field, Request $request, $flid = null, $prefix = 'records_') {
        if(
            ($request->min != '' && $request->max != '') &&
            ($request->min >= $request->max)
        ) {
            $request->max = $request->min = '';
        }

        if(
            ($request->default != '' && $request->max != '') &&
            ($request->default > $request->max)
        ) {
            $request->default = $request->max = '';
        }

        if(
            ($request->default != '' && $request->min != '') &&
            ($request->default < $request->min)
        ) {
            $request->default = $request->min = '';
        }

        $field['default'] = $request->default;
        $field['options']['Max'] = $request->max;
        $field['options']['Min'] = $request->min;
        $field['options']['Unit'] = $request->unit;

        return $field;
    }

    /**
     * Validates the record data for a field against the field's options.
     *
     * @param  int $flid - The field internal name
     * @param  array $field - The field data array to validate
     * @param  Request $request
     * @param  bool $forceReq - Do we want to force a required value even if the field itself is not required?
     * @return array - Array of errors
     */
    public function validateField($flid, $field, $request, $forceReq = false) {
        $req = $field['required'];
        $value = $request->{$flid};
        $max = $field['options']['Max'];
        $min = $field['options']['Min'];


        if(($req==1 | $forceReq) && ($value==null | $value==""))
            return [$flid => $field['name'].' is required'];

        if($min!='' && $value!="" && $value<$min)
            return [$flid => $field['name'].' can not be less than '.$min];

        if($max!='' && $value!="" && $value>$max)
            return [$flid => $field['name'].' can not be more than '.$max];

        return array();
    }

    /**
     * Formats data for record entry.
     *
     * @param  array $field - The field to represent record data
     * @param  string $value - Data to add
     * @param  Request $request
     *
     * @return mixed - Processed data
     */
    public function processRecordData($field, $value, $request) {
        if($value=='')
            $value = null;
        return $value;
    }

    /**
     * Formats data for revision display.
     *
     * @param  mixed $data - The data to store
     * @param  Request $request
     *
     * @return mixed - Processed data
     */
    public function processRevisionData($data) {
        return $data;
    }

    /**
     * Formats data for record entry.
     *
     * @param  string $flid - Field ID
     * @param  array $field - The field to represent record data
     * @param  array $value - Data to add
     * @param  Request $request
     *
     * @return Request - Processed data
     */
    public function processImportData($flid, $field, $value, $request) {
        $request[$flid] = $value;

        return $request;
    }

    /**
     * Formats data for record entry.
     *
     * @param  string $flid - Field ID
     * @param  array $field - The field to represent record data
     * @param  \SimpleXMLElement $value - Data to add
     * @param  Request $request
     * @param  bool $simple - Is this a simple xml field value
     *
     * @return Request - Processed data
     */
    public function processImportDataXML($flid, $field, $value, $request) {
        $request[$flid] = (float)$value;

        return $request;
    }

    /**
     * Formats data for record entry.
     *
     * @param  string $flid - Field ID
     * @param  array $field - The field to represent record data
     * @param  array $value - Data to add
     * @param  Request $request
     *
     * @return Request - Processed data
     */
    public function processImportDataCSV($flid, $field, $value, $request) {
        $request[$flid] = trim($value);

        return $request;
    }

    /**
     * Formats data for record display.
     *
     * @param  array $field - The field to represent record data
     * @param  string $value - Data to display
     *
     * @return mixed - Processed data
     */
    public function processDisplayData($field, $value) {
        return (float)$value;
    }

    /**
     * Formats data for XML record display.
     *
     * @param  string $field - Field ID
     * @param  string $value - Data to format
     * @param  int $fid - Form ID
     *
     * @return mixed - Processed data
     */
    public function processXMLData($field, $value, $fid = null) {
        return "<$field>".htmlspecialchars((float)$value, ENT_XML1, 'UTF-8')."</$field>";
    }

    /**
     * Formats data for Markdown record display.
     *
     * @param string $field - Field Name
     * @param  string $value - Data to format
     *
     * @return mixed - Processed data
     */
    public function processMarkdownData($field, $value, $fid = null, $tab = "") {
        $float = (float)$value;
        return "$float\n";
    }

    /**
     * Formats data for XML record display.
     *
     * @param  string $value - Data to format
     *
     * @return mixed - Processed data
     */
    public function processLegacyData($value) {
        return (float)$value;
    }


    /**
     * Takes data from a mass assignment operation and applies it to an individual field.
     *
     * @param  Form $form - Form model
     * @param  string $flid - Field ID
     * @param  String $formFieldValue - The value to be assigned
     * @param  Request $request
     * @param  bool $overwrite - Overwrite if data exists
     */
    public function massAssignRecordField($form, $flid, $formFieldValue, $request, $overwrite=0) {
        $recModel = new Record(array(),$form->id);
        if($overwrite)
            $recModel->newQuery()->update([$flid => $formFieldValue]);
        else
            $recModel->newQuery()->whereNull($flid)->update([$flid => $formFieldValue]);
    }

    /**
     * Takes data from a mass assignment operation and applies it to an individual field for a set of records.
     *
     * @param  Form $form - Form model
     * @param  string $flid - Field ID
     * @param  String $formFieldValue - The value to be assigned
     * @param  Request $request
     * @param  array $kids - The KIDs to update
     */
    public function massAssignSubsetRecordField($form, $flid, $formFieldValue, $request, $kids) {
        $recModel = new Record(array(),$form->id);
        $recModel->newQuery()->whereIn('kid',$kids)->update([$flid => $formFieldValue]);
    }

    /**
     * Performs a keyword search on this field and returns any results.
     *
     * @param  array $flids - Field ID
     * @param  string $arg - The keywords
     * @param  Record $recordMod - Model to search through
     * @param  boolean $negative - Get opposite results of the search
     * @return array - The RIDs that match search
     */
    public function keywordSearchTyped($flids, $arg, $recordMod, $form, $negative = false) {
        $tmpArg = str_replace("%","",$arg);
        if(is_numeric($tmpArg)) { // Only search if we're working with a number.
            $tmpArg = floatval($tmpArg);

            $query = $recordMod->newQuery()
                ->select("id");

            foreach($flids as $f) {
                if($negative)
                    $query = $query->orWhereNotBetween($f, [$tmpArg - self::EPSILON, $tmpArg + self::EPSILON]);
                else
                    $query = $query->orWhereBetween($f, [$tmpArg - self::EPSILON, $tmpArg + self::EPSILON]);
            }

            return $query->pluck('id')
                ->toArray();
        } else {
            return [];
        }
    }

    /**
     * Updates the request for an API search to mimic the advanced search structure.
     *
     * @param  array $data - Data from the search
     * @return array - The update request
     */
    public function setRestfulAdvSearch($data) {
        $return = [];

        if(isset($data->left) && is_int($data->left))
            $return['left'] = $data->left;
        else
            $return['left'] = '';

        if(isset($data->right) && is_int($data->right))
            $return['right'] = $data->right;
        else
            $return['right'] = '';

        if(isset($data->invert) && is_bool($data->invert))
            $return['invert'] = $data->invert;

        return $return;
    }

    /**
     * Build the advanced query for a text field.
     *
     * @param  $flid, field id
     * @param  $query, contents of query.
     * @param  Record $recordMod - Model to search through
     * @param  boolean $negative - Get opposite results of the search
     * @return array - The RIDs that match search
     */
    public function advancedSearchTyped($flid, $query, $recordMod, $form, $negative = false) {
        $left = (int)$query['left'];
        $right = (int)$query['right'];
        $invert = isset($query['invert']) ? (bool)$query['invert'] : false;

        $query = $recordMod->newQuery()
            ->select("id");

        self::buildAdvancedNumberQuery($query, $flid, $left, $right, $invert);

        return $query->pluck('id')
            ->toArray();
    }

    /**
     * Build an advanced search number field query.
     *
     * @param  Builder $query - Query to build upon
     * @param  string $flid - Field ID
     * @param  string $left - Input from the form, left index
     * @param  string $right - Input from the form, right index
     * @param  bool $invert - Inverts the search range if true
     */
    private static function buildAdvancedNumberQuery(&$query, $flid, $left, $right, $invert) {
        // Determine the interval we should search over. With epsilons to account for float rounding.
        if($left == "") {
            if($invert) // [right, inf)
                $query->where($flid, ">", floatval($right) - self::EPSILON);
            else // (-inf, right]
                $query->where($flid, "<=", floatval($right) + self::EPSILON);
        } else if($right == "") {
            if($invert) // (-inf, left]
                $query->where($flid, "<", floatval($left) + self::EPSILON);
            else // [left, inf)
                $query->where($flid, ">=", floatval($left) - self::EPSILON);
        } else {
            if($invert) { // (-inf, left] union [right, inf)
                $query->whereNotBetween($flid, [floatval($left) - self::EPSILON,
                    floatval($right) + self::EPSILON]);
            } else { // [left, right]
                $query->whereBetween($flid, [floatval($left) - self::EPSILON,
                    floatval($right) + self::EPSILON]);
            }
        }
    }
}
