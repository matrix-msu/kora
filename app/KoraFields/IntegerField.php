<?php namespace App\KoraFields;

use App\Form;
use App\Record;
use Illuminate\Http\Request;
use Illuminate\Database\Query\Builder;

class IntegerField extends BaseField {

    /*
    |--------------------------------------------------------------------------
    | Integer Field
    |--------------------------------------------------------------------------
    |
    | This model represents the integer field in Kora3
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
     * @param  int $fid - Form ID
     * @param  string $slug - Name of database column based on field internal name
     * @param  array $options - Extra information we may need to set up about the field
     * @return array - The default options
     */
    public function addDatabaseColumn($fid, $slug, $options = null) {
        $table = new \CreateRecordsTable();
        $table->addIntegerColumn($fid, $slug);
    }

    /**
     * Gets the default options string for a new field.
     *
     * @return string - The default options
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
            $request->max = $request->min = null;
        }

        if(
            ($request->default != '' && $request->max != '') &&
            ($request->default > $request->max)
        ) {
            $request->default = $request->max = null;
        }

        if(
            ($request->default != '' && $request->min != '') &&
            ($request->default < $request->min)
        ) {
            $request->default = $request->min = null;
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
        return '';
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
     * @param  string $flid - Field ID
     * @param  string $arg - The keywords
     * @param  Record $recordMod - Model to search through
     * @param  boolean $negative - Get opposite results of the search
     * @return array - The RIDs that match search
     */
    public function keywordSearchTyped($flid, $arg, $recordMod, $negative = false) {
        if($negative)
            $param = '!=';
        else
            $param = '=';

        $tmpArg = str_replace("%","",$arg);
        if(is_numeric($tmpArg)) { // Only search if we're working with a number.
            $tmpArg = intval($tmpArg);

            return $recordMod->newQuery()
                ->select('id')
                ->where($flid, $param,"$tmpArg")
                ->pluck('id')
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
    public function advancedSearchTyped($flid, $query, $recordMod, $negative = false) {
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
                $query->where($flid, ">", $right);
            else // (-inf, right]
                $query->where($flid, "<=", $right);
        } else if($right == "") {
            if($invert) // (-inf, left]
                $query->where($flid, "<", $left);
            else // [left, inf)
                $query->where($flid, ">=", $left);
        } else {
            if($invert) { // (-inf, left] union [right, inf)
                $query->whereNotBetween($flid, [$left, $right]);
            } else { // [left, right]
                $query->whereBetween($flid, [$left, $right]);
            }
        }
    }
}
