<?php namespace App\KoraFields;

class TextField extends BaseField {

    /*
    |--------------------------------------------------------------------------
    | Text Field
    |--------------------------------------------------------------------------
    |
    | This model represents the text field in Kora3
    |
    */

    /**
     * @var string - Views for the typed field options //TODO::NEWFIELD
     */
    const FIELD_OPTIONS_VIEW = "partials.fields.options.text";
    const FIELD_ADV_OPTIONS_VIEW = "partials.fields.advanced.text";
    const FIELD_ADV_INPUT_VIEW = "partials.records.advanced.text";
    const FIELD_INPUT_VIEW = "partials.records.input.text";
    const FIELD_DISPLAY_VIEW = "partials.records.display.text";

    public function getDefaultOptions() {
        return ['Regex' => '', 'MultiLine' => 0];
    }

    public function addDatabaseColumn($fid, $slug, $options = null) {
        $table = new \CreateRecordsTable();
        $table->addTextColumn($fid, $slug);
    }
}