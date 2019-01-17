<?php namespace App\KoraFields;

use App\Record;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

abstract class BaseField extends Model {

    /* //TODO::NEWFIELD
    |--------------------------------------------------------------------------
    | Base Field
    |--------------------------------------------------------------------------
    |
    | This model represents the abstract class for all typed fields in Kora3
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
     * @param  Request $request
     * @return array - The default options
     */
    abstract public function getDefaultOptions();

    /**
     * Update the options for a field
     *
     * @param  array $field - Field to update options
     * @param  Request $request
     * @return array - The updated field array
     */
    abstract public function updateOptions($field, Request $request);

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
    /**
     * Formats data for record entry.
     *
     * @param  array $field - The field to represent record data
     * @param  string $value - Data to add
     * @param  Request $request
     */
    abstract public function processRecordData($field, $value, $request);

    /**
     * Formats data for record display.
     *
     * @param  array $field - The field to represent record data
     * @param  string $value - Data to display
     */
    abstract public function processDisplayData($field, $value);

    /**
     * For a test record, add test data to field.
     */
    abstract public function getTestData();

    /**
     * Performs a keyword search on this field and returns any results.
     *
     * @param  int $flid - Field ID
     * @param  string $arg - The keywords
     * @param  Record $recordMod - Model to search through
     * @return array - The RIDs that match search
     */
    abstract public function keywordSearchTyped($flid, $arg, $recordMod);
}