<?php namespace App\KoraFields;

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
}