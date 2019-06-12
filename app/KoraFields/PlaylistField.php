<?php namespace App\KoraFields;

use Illuminate\Http\Request;

class PlaylistField extends FileTypeField {

    /*
    |--------------------------------------------------------------------------
    | Playlist Field
    |--------------------------------------------------------------------------
    |
    | This model represents the playlist field in Kora3
    |
    */

    /**
     * @var string - Views for the typed field options
     */
    const FIELD_OPTIONS_VIEW = "partials.fields.options.playlist";
    const FIELD_ADV_OPTIONS_VIEW = "partials.fields.advanced.playlist";
    const FIELD_ADV_INPUT_VIEW = null;
    const FIELD_INPUT_VIEW = "partials.records.input.playlist";
    const FIELD_DISPLAY_VIEW = "partials.records.display.playlist";

    /**
     * @var array - Supported file types in this field
     */
    const SUPPORTED_TYPES = ['audio/mp3','audio/wav'];

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
     * @param  Request $request
     * @return array - The default options
     */
    public function getDefaultOptions($type = null) {
        return ['FieldSize' => null, 'MaxFiles' => null, 'FileTypes' => self::SUPPORTED_TYPES];
    }

    ///////////////////////////////////////////////END ABSTRACT FUNCTIONS///////////////////////////////////////////////

    /**
     * Returns default mime list, if file types not saved in field options.
     *
     * @param  int $flid - File field that record file will be loaded to
     * @return array - The list
     */
    public function getDefaultMIMEList() {
        return self::SUPPORTED_TYPES;
    }
}
