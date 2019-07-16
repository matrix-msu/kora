<?php namespace App\KoraFields;

use App\Form;
use App\Record;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

abstract class BaseField extends Model {

    /* //TODO::NEWFIELD
    |--------------------------------------------------------------------------
    | Base Field
    |--------------------------------------------------------------------------
    |
    | This model represents the abstract class for all typed fields in kora
    |
    */

    /**
     * Get the field options view.
     *
     * @return string - The view
     */
    abstract public function getFieldOptionsView();

    /**
     * Get the field options view for advanced field creation.
     *
     * @return string - The view
     */
    abstract public function getAdvancedFieldOptionsView();

    /**
     * Get the field input view for advanced field search.
     *
     * @return string - The view
     */
    abstract public function getAdvancedSearchInputView();

    /**
     * Get the field input view for record creation.
     *
     * @return string - The view
     */
    abstract public function getFieldInputView();

    /**
     * Get the field display view for displaying record.
     *
     * @return string - The view
     */
    abstract public function getFieldDisplayView();

    /**
     * Gets the default options string for a new field.
     *
     * @param  int $fid - Form ID
     * @param  string $slug - Name of database column based on field internal name
     * @param  array $options - Extra information we may need to set up about the field
     * @return array - The default options
     */
    abstract public function addDatabaseColumn($fid, $slug, $options = null);

    /**
     * Gets the default options string for a new field.
     *
     * @param  string $type - Field type for use with combo list (optional)
     * @return array - The default options
     */
    abstract public function getDefaultOptions($type = null);

    /**
     * Update the options for a field
     *
     * @param  array $field - Field to update options
     * @param  Request $request
     * @param  int $flid - The field internal name
     * @param  string $prefix - Table prefix
     * @return array - The updated field array
     */
    abstract public function updateOptions($field, Request $request, $flid = null, $prefix = 'records_');

    /**
     * Validates the record data for a field against the field's options.
     *
     * @param  int $flid - The field (internal name) to validate
     * @param  array $field - The field array options
     * @param  Request $request
     * @param  bool $forceReq - Do we want to force a required value even if the field itself is not required?
     * @return array - Array of errors
     */
    abstract public function validateField($flid, $field, $request, $forceReq = false);

    //TODO::NEWFIELD formerly createNewRecordField
    //Must be in format of JSON export in kora
    /**
     * Formats data for record entry.
     *
     * @param  array $field - The field to represent record data
     * @param  string $value - Data to add
     * @param  Request $request
     *
     * @return mixed - Processed data
     */
    abstract public function processRecordData($field, $value, $request);

    /**
     * Formats data for revision display.
     *
     * @param  mixed $data - The data to store
     * @param  Request $request
     *
     * @return mixed - Processed data
     */
    abstract public function processRevisionData($data);

    /**
     * Formats data for record entry.
     *
     * @param  string $flid - Field ID
     * @param  array $field - The field to represent record data
     * @param  array $value - Data to add
     * @param  Request $request
     *
     * @return mixed - Processed data
     */
    abstract public function processImportData($flid, $field, $value, $request);

    /**
     * Formats data for record entry.
     *
     * @param  string $flid - Field ID
     * @param  array $field - The field to represent record data
     * @param  \SimpleXMLElement $value - Data to add
     * @param  Request $request
     *
     * @return mixed - Processed data
     */
    abstract public function processImportDataXML($flid, $field, $value, $request);

    /**
     * Formats data for record entry.
     *
     * @param  string $flid - Field ID
     * @param  array $field - The field to represent record data
     * @param  array $value - Data to add
     * @param  Request $request
     *
     * @return mixed - Processed data
     */
    abstract public function processImportDataCSV($flid, $field, $value, $request);

    /**
     * Formats data for record display.
     *
     * @param  array $field - The field to represent record data
     * @param  string $value - Data to display
     *
     * @return mixed - Processed data
     */
    abstract public function processDisplayData($field, $value);

    /**
     * Formats data for XML record display.
     *
     * @param  string $field - Field ID
     * @param  string $value - Data to format
     * @param  int $fid - Form ID
     *
     * @return mixed - Processed data
     */
    abstract public function processXMLData($field, $value, $fid = null);

    /**
     * Formats data for XML record display.
     *
     * @param  string $value - Data to format
     *
     * @return mixed - Processed data
     */
    abstract public function processLegacyData($value);

    /**
     * Takes data from a mass assignment operation and applies it to an individual field.
     *
     * @param  Form $form - Form model
     * @param  string $flid - Field ID
     * @param  String $formFieldValue - The value to be assigned
     * @param  Request $request
     * @param  bool $overwrite - Overwrite if data exists
     */
    abstract public function massAssignRecordField($form, $flid, $formFieldValue, $request, $overwrite=0);

    /**
     * Takes data from a mass assignment operation and applies it to an individual field for a set of records.
     *
     * @param  Form $form - Form model
     * @param  string $flid - Field ID
     * @param  String $formFieldValue - The value to be assigned
     * @param  Request $request
     * @param  array $kids - The KIDs to update
     */
    abstract public function massAssignSubsetRecordField($form, $flid, $formFieldValue, $request, $kids);

    /**
     * Performs a keyword search on this field and returns any results.
     *
     * @param  int $flid - Field ID
     * @param  string $arg - The keywords
     * @param  Record $recordMod - Model to search through
     * @param  boolean $negative - Get opposite results of the search
     * @return array - The RIDs that match search
     */
    abstract public function keywordSearchTyped($flid, $arg, $recordMod, $form, $negative = false);

    /**
     * Updates the request for an API search to mimic the advanced search structure.
     *
     * @param  array $data - Data from the search
     * @return array - The update request
     */
    abstract public function setRestfulAdvSearch($data);

    /**
     * Build the advanced query for a text field.
     *
     * @param  $flid, field id
     * @param  $query, contents of query.
     * @param  Record $recordMod - Model to search through
     * @param  boolean $negative - Get opposite results of the search
     * @return array - The RIDs that match search
     */
    abstract public function advancedSearchTyped($flid, $query, $recordMod, $form, $negative = false);

    /**
     * Find every record that does not have data for this field.
     *
     * @param  int $flid - Field ID
     * @param  Record $recordMod - Model to search through
     * @return array - The RIDs that are empty
     */
    public function getEmptyFieldRecords($flid, $recordMod) {
        return $recordMod->newQuery()
            ->select("id")
            ->whereNull($flid)
            ->pluck('id')
            ->toArray();
    }
}
