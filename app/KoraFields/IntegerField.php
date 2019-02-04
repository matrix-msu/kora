<?php namespace App;

use App\Form;
use App\Record;
use App\Search;
use Illuminate\Http\Request;
use Illuminate\Database\Query\Builder;

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
    const FIELD_OPTIONS_VIEW = "partials.fields.options.integer";
    const FIELD_ADV_OPTIONS_VIEW = "partials.fields.advanced.integer";
    const FIELD_ADV_INPUT_VIEW = "partials.records.advanced.integer";
    const FIELD_INPUT_VIEW = "partials.records.input.integer";
    const FIELD_DISPLAY_VIEW = "partials.records.display.integer";

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
        return ['Max' => 0, 'Min' => 0, 'Increment' => 0, 'Unit' => '']
    }

    /**
     * Update the options for a field
     *
     * @param  Field $field - Field to update options
     * @param  Request $request
     * @return Redirect
     */
    public function updateOptions($field, Request $request) {
        if(
            ($request->min != '' && $request->max != '') &&
            ($request->min >= $request->max)
        ) {
            $request->max = $request->min = 0;
        }

        if(
            ($request->default != '' && $request->max != '') &&
            ($request->default > $request->max)
        ) {
            $request->default = $request->max = 0;
        }

        if(
            ($request->default != '' && $request->min != '') &&
            ($request->default < $request->min)
        ) {
            $request->default = $request->min = 0;
        }

        $field['default'] = $request->default;
        $field['options']['Max'] = $request->max;
        $field['options']['Min'] = $request->min;
        $field['options']['Increment'] = $request->inc;
        $field['options']['Unit'] = $request->unit;

        return $field;
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
        $req = $field['required'];
        $value = $request->{$flid};
        $max = $field['options']['Max'];
        $min = $field['options']['Min'];


        if(($req==1 | $forceReq) && ($value==null | $value==""))
            return [$field->flid => $field['name'].' is required'];

        if($min!='' && $value!="" && $value<$min)
            return [$field->flid => $field['name'].' can not be less than '.$min];

        if($max!='' && $value!="" && $value>$max)
            return [$field->flid => $field['name'].' can not be more than '.$max];

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
    public function processImportDataXML($flid, $field, $value, $request, $simple = false) {
        $request[$flid] = (int)$value;

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
        return $value;
    }

    /**
     * Formats data for XML record display.
     *
     * @param  string $field - Field ID
     * @param  string $value - Data to format
     *
     * @return mixed - Processed data
     */
    public function processXMLData($field, $value) {
        return "<$field>".htmlspecialchars($value, ENT_XML1, 'UTF-8')."</$field>";
    }

    /**
     * Formats data for XML record display.
     *
     * @param  string $value - Data to format
     *
     * @return mixed - Processed data
     */
    public function processLegacyData($value) {
        return $value;
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
     * For a test record, add test data to field.
     *
     * @param  string $url - Url for File Type Fields
     * @return mixed - The data
     */
    public function getTestData($url = null) {
        return 0;
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
                $xml = '<' . $slug . '>';
                $xml .= utf8_encode('3');
                $xml .= '</' . $slug . '>';

                return $xml;
                break;
            case "JSON":
                $fieldArray[$slug] = 3;

                return $fieldArray;
                break;
        }
    }

    /**
     * Performs a keyword search on this field and returns any results.
     *
     * @param  int $flid - Field ID
     * @param  string $arg - The keywords
     * @return array - The RIDs that match search
     */
    public function keywordSearchTyped($flid, $arg, $recordMod, $negative = false) {
        if(is_numeric($arg)) { // Only search if we're working with a number.
            $arg = intval($arg);

            return $recordMod->newQuery()
                ->select('id')
                ->where($flid, $param,"%$arg%")
                ->whereBetween("number", [$arg - self::EPSILON, $arg + self::EPSILON])
                ->pluck('id')
                ->toArray();
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

        $query = $recordMod->newQuery()
            ->select("id")
            ->where("flid", "=", $flid);

        self::buildAdvancedNumberQuery($query, $left, $right, $invert);

        return $query->pluck('id')
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
}
