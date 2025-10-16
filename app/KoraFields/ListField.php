<?php namespace App\KoraFields;

use App\Form;
use App\Record;
use App\Http\Controllers\FormController;
use Illuminate\Http\Request;

class ListField extends BaseField {

    /*
    |--------------------------------------------------------------------------
    | List Field
    |--------------------------------------------------------------------------
    |
    | This model represents the list field in kora
    |
    */

    /**
     * @var string - Views for the typed field options
     */
    const FIELD_OPTIONS_VIEW = "partials.fields.options.list";
    const FIELD_ADV_OPTIONS_VIEW = "partials.fields.advanced.list";
    const FIELD_ADV_INPUT_VIEW = "partials.records.advanced.list";
    const FIELD_INPUT_VIEW = "partials.records.input.list";
    const FIELD_DISPLAY_VIEW = "partials.records.display.list";

    /**
     * @var string - Method from CreateRecordsTable() for adding to DB
     */
    const FIELD_DATABASE_METHOD = 'addEnumColumn';

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
     * Create DB column for this field. Overwriting parent to handle ENUM case.
     *
     * @param  int $fid - Form ID
     * @param  string $slug - Name of database column based on field internal name
     * @param  string $method - The add column function from CreateRecordsTable to be used
     * @param  array $options - Extra information we may need to set up about the field
     */
    public function addDatabaseColumn($fid, $slug, $method, $options = null) {
        $table = new \CreateRecordsTable();
        if(is_null($options) || empty($options))
            $table->addEnumColumn($fid, $slug);
        else
            $table->addEnumColumn($fid, $slug, $options);
    }

    /**
     * Gets the default options string for a new field.
     *
     * @return array - The default options
     */
    public function getDefaultOptions($type = null) {
        return ['Options' => ['Please Modify List Values']];
    }

    /**
     * Update the options for a field
     *
     * @param  array $field - Field to update options
     * @param  Request $request
     * @param  int $flid - The field internal name
     * @return array - The updated field array
     */
    public function updateOptions($field, Request $request, $flid = null, $prefix = 'records_') {
        if(is_null($request->options)) {
            $request->options = array();
        }

        if(is_null($flid)) {
            $form = FormController::getForm($request->fid);
            $flid = str_replace(" ","_", $request->name).'_'.$form->project_id.'_'.$form->id.'_';
        }

        $table = new \CreateRecordsTable(['tablePrefix' => $prefix]);

        $table->updateEnum(
            $request->fid,
            $flid,
            $request->options
        );

        $field['default'] = $request->default;
        $field['options']['Options'] = $request->options;

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
        $options = $field['options']['Options'];

        if(($req==1 | $forceReq) && ($value==null | $value==""))
            return [$flid => $field['name'].' is required'];

        if($value!="" && !in_array($value,$options))
            return [$flid => $field['name'].' has an invalid value not in the list.'];

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
     * Formats data for revision entry.
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
     *
     * @return Request - Processed data
     */
    public function processImportDataXML($flid, $field, $value, $request) {
        $request[$flid] = (string)$value;

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
        return $value;
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
        return "<$field>".htmlspecialchars($value, ENT_XML1, 'UTF-8')."</$field>";
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
        return "\"$value\"\n";
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
        $search = $recordMod->newQuery()
            ->select("id");

        foreach($flids as $f) {
            if($negative)
                $search = $search->orWhere($f, 'NOT LIKE',"$arg")->orWhereNull($f);
            else
                $search = $search->orWhere($f, 'LIKE',"$arg");
        }

        return $search->pluck('id')
            ->toArray();
    }

    /**
     * Updates the request for an API search to mimic the advanced search structure.
     *
     * @param  array $data - Data from the search
     * @return array - The update request
     */
    public function setRestfulAdvSearch($data) {
        if(isset($data->input) && is_string($data->input))
            return ['input' => $data->input];
        else
            return [];
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
        $arg = $query['input'];

        if($negative)
            $param = '!=';
        else
            $param = '=';

        return $recordMod->newQuery()
            ->select("id")
            ->where($flid, $param,"$arg")
            ->pluck('id')
            ->toArray();
    }

    ///////////////////////////////////////////////END ABSTRACT FUNCTIONS///////////////////////////////////////////////

    /**
     * Gets the list options for a list field.
     *
     * @param  array $field - Field to pull options from
     * @return array - The list options
     */
    public static function getList($field) {
        $options = ['Options' => array()];
        foreach ($field['options']['Options'] as $option) {
            $options['Options'][$option] = $option;
        }
        return $options;
    }
}
