<?php namespace App;
/**
 * Created by PhpStorm.
 * User: ian.whalen
 * Date: 3/22/2016
 * Time: 11:12 AM
 */

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\DocBlock\Type\Collection;

/**
 * Class BaseField
 * @package App
 */
abstract class BaseField extends Model
{
    protected $primaryKey = "id";

    /**
     * Record that the field belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function record(){
        return $this->belongsTo('App\Record');
    }

    /**
     * Determines if to metadata is allowed to be called on the field.
     *
     * @return bool, true if to metadata can be called on the field.
     */
    abstract public function isMetafiable();

    /**
     * Returns the metadata representation of a field.
     * Simple fields like TextField will return a string, more complex like DocumentsField will return arrays.
     *
     * @param Field $field, a field to get certain options that will be needed.
     * @return string | array | Collection, string or array depending on the field.
     */
    abstract public function toMetadata(Field $field);

    /**
     * Get the required information for a revision data array.
     *
     * @param Field | null $field, optional field to get storage options for certain typed fields.
     * @return array | string
     */
    abstract public function getRevisionData($field = null);

    /**
     * Names of the base fields in the database.
     *
     * @var array
     */
    public static $TABLE_NAMES = ["text_fields", "rich_text_fields", "number_fields", "list_fields",
        "multi_select_list_fields", "generated_list_fields", "combo_list_fields",
        "date_fields", "schedule_fields", "geolocator_fields", "documents_fields",
        "gallery_fields", "playlist_fields", "video_fields", "model_fields", "associator_fields"];

    /**
     * Maps field constant names to table names.
     * Used with the DB::table method.
     *
     * @var array
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
     * Maps a typed field name to its table's name in the database.
     *
     * @param $string
     * @return bool | string.
     */
    public static function getDBName($string) {
        if (isset(Field::$ENUM_TYPED_FIELDS[$string])) {
            return self::$MAPPED_FIELD_TYPES[$string];
        }

        return false;
    }

    /**
     * Deletes all the BaseFields with a certain rid in a clean way.
     *
     * @param $rid int, record id.
     */
    static public function deleteBaseFields($rid) {
        foreach (self::$TABLE_NAMES as $table_name) {
            DB::table($table_name)->where("rid", "=", $rid)->delete();
        }

        // Delete support tables.
        $support_tables = [ScheduleField::SUPPORT_NAME, GeolocatorField::SUPPORT_NAME, ComboListField::SUPPORT_NAME];

        foreach($support_tables as $support_table) {
            DB::table($support_table)->where("rid", "=", $rid)->delete();
        }
    }

    /****************************************************************
     *            Moved convertCloseChars to App/Search             *
     ****************************************************************/
}