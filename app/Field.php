<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class Field extends Model {

    protected $fillable = [
        'pid',
        'fid',
        'type',
        'name',
        'slug',
        'desc',
        'required',
        'searchable',
    ];

    /**
     * These are the possible field types at the moment.
     * @type string
     */
    const _TEXT = "Text";
    const _RICH_TEXT = "Rich Text";
    const _NUMBER = "Number";
    const _LIST = "List";
    const _MULTI_SELECT_LIST = "Multi-Select List";
    const _GENERATED_LIST = "Generated List";
    const _DATE = "Date";
    const _SCHEDULE = "Schedule";
    const _GEOLOCATOR = "Geolocator";
    const _DOCUMENTS = "Documents";
    const _GALLERY = "Gallery";
    const _3D_MODEL = "3D-Model";
    const _PLAYLIST = "Playlist";
    const _VIDEO = "Video";
    const _COMBO_LIST = "Combo List";
    const _ASSOCIATOR = "Associator";

    /**
     * This is an array of all possible typed fields.
     * @var array
     */
    static public $ENUM_TYPED_FIELDS = [
        Field::_TEXT, Field::_RICH_TEXT, Field::_NUMBER, Field::_LIST,
        Field::_MULTI_SELECT_LIST, Field::_GENERATED_LIST, Field::_DATE,
        Field::_SCHEDULE, Field::_GEOLOCATOR, Field::_DOCUMENTS, Field::_GALLERY,
        Field::_3D_MODEL, Field::_PLAYLIST, Field::_VIDEO, Field::_COMBO_LIST,
        Field::_ASSOCIATOR
    ];

    protected $primaryKey = "flid";

    public function form(){
        return $this->belongsTo('App\Form');
    }

    public function metadata(){
        return $this->hasOne('App\Metadata','flid');
    }

    /**
     * Searchable variable getter.
     */
    public function isSearchable() {
        return $this->searchable;
    }

    /**
     * Gets the typed field governed by this field.
     * E.g. if this->type == "Text" it will find the TextField it is associated with in the database.
     *
     * This function is the most necessary evil the way our database is set up, but we should transition to using this
     * function rather than having our huge case like the one in this function statements all over our app.
     *
     * @param $rid int, the id of the record associated with the typed field.
     * @return \App\BaseField | null, some typed field or null if the typed field does not exist.
     */
    public function getTypedField($rid) {
        // Here goes nothing...
        switch($this->type) {
            case Field::_TEXT:
                return TextField::where("flid", "=", $this->flid)->where("rid", "=", $rid)->first();
                break;

            case Field::_RICH_TEXT:
                return RichTextField::where("flid", "=", $this->flid)->where("rid", "=", $rid)->first();
                break;

            case Field::_NUMBER:
                return NumberField::where("flid", "=", $this->flid)->where("rid", "=", $rid)->first();
                break;

            case Field::_LIST:
                return ListField::where("flid", "=", $this->flid)->where("rid", "=", $rid)->first();
                break;

            case Field::_MULTI_SELECT_LIST:
                return MultiSelectListField::where("flid", "=", $this->flid)->where("rid", "=", $rid)->first();
                break;

            case Field::_GENERATED_LIST:
                return GeneratedListField::where("flid", "=", $this->flid)->where("rid", "=", $rid)->first();
                break;

            case Field::_DATE:
                return DateField::where("flid", "=", $this->flid)->where("rid", "=", $rid)->first();
                break;

            case Field::_SCHEDULE:
                return ScheduleField::where("flid", "=", $this->flid)->where("rid", "=", $rid)->first();
                break;

            case Field::_GEOLOCATOR:
                return GeolocatorField::where("flid", "=", $this->flid)->where("rid", "=", $rid)->first();
                break;

            case Field::_DOCUMENTS:
                return DocumentsField::where("flid", "=", $this->flid)->where("rid", "=", $rid)->first();
                break;

            case Field::_GALLERY:
                return GalleryField::where("flid", "=", $this->flid)->where("rid", "=", $rid)->first();
                break;

            case Field::_3D_MODEL:
                return ModelField::where("flid", "=", $this->flid)->where("rid", "=", $rid)->first();
                break;

            case Field::_PLAYLIST:
                return PlaylistField::where("flid", "=", $this->flid)->where("rid", "=", $rid)->first();
                break;

            case Field::_VIDEO:
                return VideoField::where("flid", "=", $this->flid)->where("rid", "=", $rid)->first();
                break;

            case Field::_COMBO_LIST:
                return ComboListField::where("flid", "=", $this->flid)->where("rid", "=", $rid)->first();
                break;

            default: // Error occurred.
                return null;
        }
        // Hopefully this will solve something.
    }


    /**
     * Builds the query up for a typed field keyword search.
     *
     * *** This expects a processed argument. See Search::processArgument.
     *
     * @param $arg string, the argument being searched for.
     * @throws \Exception when field does not have a valid field type.
     * @return Builder | null, query builder type, null if invalid field type.
     */
    public function keywordSearchTyped($arg) {
        switch($this->type) {
            case Field::_TEXT:
                $arg_natural = str_replace(["*", "\""], "", $arg);
                $q = TextField::where("flid", "=", $this->flid)
                    ->whereRaw("MATCH (`text`) AGAINST (? IN BOOLEAN MODE)", [$arg]);
                    //->whereRaw("MATCH (`text`) AGAINST (? IN NATURAL LANGUAGE MODE)", [$arg_natural]);

                return $q;
                break;

            case Field::_RICH_TEXT:
                return RichTextField::where("flid", "=", $this->flid)->whereRaw("MATCH (`searchable_rawtext`) AGAINST (? IN BOOLEAN MODE)", [$arg]);
                break;

            case Field::_NUMBER:
                $arg = str_replace(["*", "\""], "", $arg);

                return NumberField::where("flid", "=", $this->flid)->where("number", "=", $arg);
                break;

            case Field::_LIST:
                return ListField::where("flid", "=", $this->flid)->whereRaw("MATCH (`option`) AGAINST (? IN BOOLEAN MODE)", [$arg]);
                break;

            case Field::_MULTI_SELECT_LIST:
                return MultiSelectListField::where("flid", "=", $this->flid)->whereRaw("MATCH (`options`) AGAINST (? IN BOOLEAN MODE)", [$arg]);
                break;

            case Field::_GENERATED_LIST:
                return GeneratedListField::where("flid", "=", $this->flid)->whereRaw("MATCH (`options`) AGAINST (? IN BOOLEAN MODE)", [$arg]);
                break;

            case Field::_DATE:
                $arg = str_replace(["*", "\""], "", $arg);

                // Boolean to decide if we should consider circa options.
                $circa = explode("[!Circa!]", $this->options)[1] == "Yes";

                // Boolean to decide if we should consider era.
                $era = explode("[!Era!]", $this->options)[1] == "On";

                return DateField::buildQuery($arg, $circa, $era, $this->flid);
                break;

            case Field::_SCHEDULE:
                return ScheduleField::where("flid", "=", $this->flid)->whereRaw("MATCH (`events`) AGAINST (? IN BOOLEAN MODE)", [$arg]);
                break;

            case Field::_GEOLOCATOR:
                return GeolocatorField::where("flid", "=", $this->flid)->whereRaw("MATCH (`locations`) AGAINST (? IN BOOLEAN MODE)", [$arg]);
                break;

            case Field::_DOCUMENTS:
                return DocumentsField::where("flid", "=", $this->flid)->whereRaw("MATCH (`documents`) AGAINST (? IN BOOLEAN MODE)", [$arg]);
                break;

            case Field::_GALLERY:
                return GalleryField::where("flid", "=", $this->flid)->whereRaw("MATCH (`images`) AGAINST (? IN BOOLEAN MODE)", [$arg]);
                break;

            case Field::_3D_MODEL:
                return ModelField::where("flid", "=", $this->flid)->whereRaw("MATCH (`model`) AGAINST (? IN BOOLEAN MODE)", [$arg]);
                break;

            case Field::_PLAYLIST:
                return PlaylistField::where("flid", "=", $this->flid)->whereRaw("MATCH (`audio`) AGAINST (? IN BOOLEAN MODE)", [$arg]);
                break;

            case Field::_VIDEO:
                return VideoField::where("flid", "=", $this->flid)->whereRaw("MATCH (`video`) AGAINST (? IN BOOLEAN MODE)", [$arg]);
                break;

            case Field::_COMBO_LIST:
                return ComboListField::where("flid", "=", $this->flid)->whereRaw("MATCH (`options`) AGAINST (? IN BOOLEAN MODE)", [$arg]);
                break;

            default: // Error occurred.
                throw new \Exception("Invalid field type in field::keywordSearchTyped.");
                break;
        }
    }

    /**
     *
     *
     * @param $arg string, the argument of the search
     * @param $method int, type of search.
     * @return \stdClass[], unfortunately an array of stdObjects are returned, with one member, and integer called "rid"
     * @throws \Exception if the field type is invalid.
     */
    public function keywordSearchTyped2($arg, $method) {
        switch($this->type) {
            case Field::_TEXT:
                return DB::table("text_fields")
                    ->select("rid")
                    ->where("fid", "=", $this->fid)
                    ->whereRaw("MATCH (`text`) AGAINST (? IN BOOLEAN MODE)", [$arg])
                    ->distinct();
                break;

            case Field::_RICH_TEXT:
                return DB::table("rich_text_fields")
                    ->select("rid")
                    ->where("fid", "=", $this->fid)
                    ->whereRaw("MATCH (`searchable_rawtext`) AGAINST (? IN BOOLEAN MODE)", [$arg])
                    ->distinct();
                break;

            case Field::_NUMBER:
                $arg = str_replace(["*", "\""], "", $arg);

                if (is_numeric($arg)) { // Only search if we're working with a number.
                    $arg = floatval($arg);

                    return DB::table("number_fields")
                        ->select("rid")
                        ->where("fid", "=", $this->fid)
                        ->whereBetween("number", [$arg - NumberField::EPSILON, $arg + NumberField::EPSILON])
                        ->distinct();
                }
                else {
                    return DB::table("number_fields")
                        ->select("rid")
                        ->where("id", "<", -1); // Purposefully impossible.
                }
                break;

            case Field::_LIST:
                return DB::table("list_fields")
                    ->select("rid")
                    ->where("fid", "=", $this->fid)
                    ->whereRaw("MATCH (`option`) AGAINST (? IN BOOLEAN MODE)", [$arg])
                    ->distinct();
                break;

            case Field::_MULTI_SELECT_LIST:
                return DB::table("multi_select_list_fields")
                    ->select("rid")
                    ->where("fid", "=", $this->fid)
                    ->whereRaw("MATCH (`options`) AGAINST (? IN BOOLEAN MODE)", [$arg])
                    ->distinct();
                break;

            case Field::_GENERATED_LIST:
                return DB::table("generated_list_fields")
                    ->select("rid")
                    ->where("fid", "=", $this->fid)
                    ->whereRaw("MATCH (`options`) AGAINST (? IN BOOLEAN MODE)", [$arg])
                    ->distinct();
                break;

            case Field::_DATE:
                $arg = str_replace(["*", "\""], "", $arg);

                // Boolean to decide if we should consider circa options.
                $circa = explode("[!Circa!]", $this->options)[1] == "Yes";

                // Boolean to decide if we should consider era.
                $era = explode("[!Era!]", $this->options)[1] == "On";

                return DateField::buildQuery2($arg, $circa, $era, $this->fid);
                break;

            case Field::_SCHEDULE:
                return DB::table("schedule_fields")
                    ->select("rid")
                    ->where("fid", "=", $this->fid)
                    ->whereRaw("MATCH (`events`) AGAINST (? IN BOOLEAN MODE)", [$arg])
                    ->distinct();
                break;

            case Field::_GEOLOCATOR:
                // We need to make sure only the actual words in the data are matched with, not the separators.
                $args = explode(" ", $arg);
                $args = array_filter($args, function($element) {
                    $element = str_replace(["*", "\""], "", $element);
                    return (ucwords($element) != "Address" && ucwords($element) != "Desc");
                });

                $arg = implode(" ", $args);

                if ($method != Search::SEARCH_EXACT) {
                    $args_description = explode(" ", $arg);
                    $args_address = $args_description;

                    for ($i = 0; $i < count($args_description); $i++) {
                        $args_description[$i] .= "[Desc]";
                        $args_address[$i] .= "[Address]";
                    }

                    $args_description = implode($args_description);
                    $args_address = implode($args_address);

                    return DB::table("geolocator_fields")
                        ->select("rid")
                        ->where("fid", "=", $this->fid)
                        ->whereRaw("MATCH (`locations`) AGAINST (? IN BOOLEAN MODE)", [$args_description])
                        ->whereRaw("MATCH (`locations`) AGAINST (? IN BOOLEAN MODE)", [$args_address])
                        ->distinct();
                }
                else {
                    return DB::table("geolocator_fields")
                        ->select("rid")
                        ->where("fid", "=", $this->fid)
                        ->whereRaw("MATCH (`locations`) AGAINST (? IN BOOLEAN MODE)", [$arg])
                        ->distinct();
                }
                break;

            case Field::_DOCUMENTS:
                $arg = self::processArgumentForFileField($arg, $method);

                return DB::table("documents_fields")
                    ->select("rid")
                    ->where("fid", "=", $this->fid)
                    ->whereRaw("MATCH (`documents`) AGAINST (? IN BOOLEAN MODE)", [$arg])
                    ->distinct();
                break;

            case Field::_GALLERY:
                $arg = self::processArgumentForFileField($arg, $method);

                return DB::table("gallery_fields")
                    ->select("rid")
                    ->where("fid", "=", $this->fid)
                    ->whereRaw("MATCH (`images`) AGAINST (? IN BOOLEAN MODE)", [$arg])
                    ->distinct();
                break;

            case Field::_3D_MODEL:
                $arg = self::processArgumentForFileField($arg, $method);

                return DB::table("model_fields")
                    ->select("rid")
                    ->where("fid", "=", $this->fid)
                    ->whereRaw("MATCH (`model`) AGAINST (? IN BOOLEAN MODE)", [$arg])
                    ->distinct();
                break;

            case Field::_PLAYLIST:
                $arg = self::processArgumentForFileField($arg, $method);

                return DB::table("playlist_fields")
                    ->select("rid")
                    ->where("fid", "=", $this->fid)
                    ->whereRaw("MATCH (`audio`) AGAINST (? IN BOOLEAN MODE)", [$arg])
                    ->distinct();
                break;

            case Field::_VIDEO:
                $arg = self::processArgumentForFileField($arg, $method);

                return DB::table("video_fields")
                    ->select("rid")
                    ->where("fid", "=", $this->fid)
                    ->whereRaw("MATCH (`video`) AGAINST (? IN BOOLEAN MODE)", [$arg])
                    ->distinct();
                break;

            case Field::_COMBO_LIST:
                return DB::table("combo_list_fields")
                    ->select("rid")
                    ->where("fid", "=", $this->fid)
                    ->whereRaw("MATCH (`options`) AGAINST (? IN BOOLEAN MODE)", [$arg])
                    ->distinct();
                break;

            default: // Error occurred.
                throw new \Exception("Invalid field type in field::keywordSearchTyped2.");
                break;
        }
    }

    /**
     * Processes an argument so it can be used in a file field.
     *
     * @param $arg string, argument to be processed.
     * @param $method int, search method.
     * @return string, the processed argument.
     */
    public static function processArgumentForFileField($arg, $method) {
        // We only want to match with actual data in the name field
        if ($method == Search::SEARCH_EXACT) {
            $arg = rtrim($arg, '"');
            $arg .= "[Name]\"";
        }
        else {
            $args = explode(" ", $arg);

            foreach($args as &$arg) {
                $arg .= "[Name]";
            }
            $arg = implode(" ",$args);
        }

        return $arg;
    }

    /**
     * Because the MyISAM engine doesn't support foreign keys we have to emulate cascading.
     */
    public function delete() {
        DB::table(BaseField::$MAPPED_FIELD_TYPES[$this->type])->where("flid", "=", $this->flid)->delete();
        DB::table("metadatas")->where("flid", "=", $this->flid)->delete();

        parent::delete();
    }
}

