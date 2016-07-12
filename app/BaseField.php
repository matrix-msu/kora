<?php namespace App;
/**
 * Created by PhpStorm.
 * User: ian.whalen
 * Date: 3/22/2016
 * Time: 11:12 AM
 */

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
     * Pure virtual keyword search method for a general field.
     *
     * @param array $args, Array of arguments for the search to use.
     * @param bool $partial, True if partial values should be considered in the search.
     * @return bool, True if the field has satisfied the search parameters.
     */
    abstract public function keywordSearch(array $args, $partial);

    /**
     * The routine that drives the keyword search for most fields.
     *
     * @param array $args, Array of arguments for the search routine to use.
     * @param bool $partial, True if partial values should be considered in the search.
     * @param string $haystack, The string to be searched through.
     * @return bool, True if the search parameters are satisfied.
     */
    static public function keywordRoutine(array $args, $partial, $haystack) {
        $text = Search::convertCloseChars($haystack);

        if ($partial) {
            foreach ($args as $arg) {
                if (strlen($arg) && stripos($text, $arg) !== false) {
                    return true; // Text contains a partial match.
                }

            }
        }
        else {
            foreach ($args as $arg) {
                $arg = preg_quote($arg, "\\"); // Escape regular expression characters.

                $pattern = "/(\\W|^)" . $arg . "(\\W|$)/i";

                $result = preg_match($pattern, $text);
                if (strlen($arg) && $result !== false) { // Continue if preg_match did not error.
                    if ($result) {
                        return true; // Text contains a complete match.
                    }
                }
            }
        }

        return false; // Text contains no matches.
    }

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
    }

    /****************************************************************
     *            Moved convertCloseChars to App/Search             *
     ****************************************************************/
}