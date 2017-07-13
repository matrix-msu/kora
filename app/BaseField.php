<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
     * @var string - Database column that represents the primary key
     */
    protected $primaryKey = "id";

    /**
     * @var array - Names of the base fields in the database
     */
    public static $TABLE_NAMES = ["text_fields", "rich_text_fields", "number_fields", "list_fields",
        "multi_select_list_fields", "generated_list_fields", "combo_list_fields",
        "date_fields", "schedule_fields", "geolocator_fields", "documents_fields",
        "gallery_fields", "playlist_fields", "video_fields", "model_fields", "associator_fields"];

    /**
     * @var array - Maps field constant names to table names (Used with the DB::table method)
     */
    public static $MAPPED_FIELD_TYPES = [
        Field::_TEXT => "text_fields",
        Field::_RICH_TEXT => "rich_text_fields",
        Field::_NUMBER => "number_fields",
        Field::_LIST => "list_fields",
        Field::_MULTI_SELECT_LIST => "multi_select_list_fields",
        Field::_GENERATED_LIST => "generated_list_fields",
        Field::_COMBO_LIST => "combo_list_fields",
        Field::_DATE => "date_fields",
        Field::_SCHEDULE => "schedule_fields",
        Field::_GEOLOCATOR => "geolocator_fields",
        Field::_DOCUMENTS => "documents_fields",
        Field::_GALLERY => "gallery_fields",
        Field::_PLAYLIST => "playlist_fields",
        Field::_VIDEO => "video_fields",
        Field::_3D_MODEL => "model_fields",
        Field::_ASSOCIATOR => "associator_fields"
    ];

    /**
     * Record that the field belongs to.
     *
     * @return BelongsTo
     */
    public function record() {
        return $this->belongsTo('App\Record');
    }

    /**
     * Maps a typed field name to its table's name in the database.
     *
     * @param  String $string - Enum representing typed field
     * @return string - The table name
     */
    public static function getDBName($string) {
        if(isset(Field::$ENUM_TYPED_FIELDS[$string])) {
            return self::$MAPPED_FIELD_TYPES[$string];
        }

        return false;
    }

    /**
     * Deletes all the BaseFields with a certain rid in a clean way.
     *
     * @param  int $rid - Record id
     */
    static public function deleteBaseFields($rid) {
        foreach(self::$TABLE_NAMES as $table_name) {
            DB::table($table_name)->where("rid", "=", $rid)->delete();
        }

        // Delete support tables.
        $support_tables = [ScheduleField::SUPPORT_NAME, GeolocatorField::SUPPORT_NAME, ComboListField::SUPPORT_NAME, AssociatorField::SUPPORT_NAME];

        foreach($support_tables as $support_table) {
            DB::table($support_table)->where("rid", "=", $rid)->delete();
        }
    }

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
     * Gets the default options string for a new field.
     *
     * @param  Request $request
     * @return string - The default options
     */
    abstract public function getDefaultOptions(Request $request);

    /**
     * Update the options for a field
     *
     * @param  Field $field - Field to update options
     * @param  Request $request
     * @param  bool $return - Are we returning an error by string or redirect
     * @return mixed - The result
     */
    abstract public function updateOptions($field, Request $request, $return=true);

    /**
     * Get the required information for a revision data array.
     *
     * @param  Field $field - Optional field to get storage options for certain typed fields
     * @return mixed - The revision data
     */
    abstract public function getRevisionData($field = null);

    /**
     * Creates a typed field to store record data.
     *
     * @param  Field $field - The field to represent record data
     * @param  Record $record - Record being created
     * @param  string $value - Data to add
     * @param  Request $request
     */
    abstract public function createNewRecordField($field, $record, $value, $request);

    /**
     * Edits a typed field that has record data.
     *
     * @param  string $value - Data to add
     * @param  Request $request
     */
    abstract public function editRecordField($value, $request);

    /**
     * Takes data from a mass assignment operation and applies it to an individual field.
     *
     * @param  Field $field - The field to represent record data
     * @param  Record $record - Record being written to
     * @param  String $formFieldValue - The value to be assigned
     * @param  Request $request
     * @param  bool $overwrite - Overwrite if data exists
     */
    abstract public function massAssignRecordField($field, $record, $formFieldValue, $request, $overwrite=0);

    /**
     * For a test record, add test data to field.
     *
     * @param  Field $field - The field to represent record data
     * @param  Record $record - Test record being created
     */
    abstract public function createTestRecordField($field, $record);

    /**
     * Validates the record data for a field against the field's options.
     *
     * @param  Field $field - The
     * @param  mixed $value - Record data
     * @param  Request $request
     * @return string - Potential error message
     */
    abstract public function validateField($field, $value, $request);

    /**
     * Performs a rollback function on an individual field's record data.
     *
     * @param  Field $field - The field being rolled back
     * @param  Revision $revision - The revision being rolled back
     * @param  bool $exists - Field for record exists
     */
    abstract public function rollbackField($field, Revision $revision, $exists=true);
}