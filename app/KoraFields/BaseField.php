<?php namespace App\KoraFields;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

abstract class BaseField extends Model {

    /*
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
    abstract public function getFieldOptionsView(); //TODO::NEWFIELD

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
}