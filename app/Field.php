<?php namespace App;

use App\Http\Controllers\FieldController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use PhpSpec\Exception\Exception;

class Field extends Model {

    protected $fillable = [
        'pid',
        'fid',
        'page_id',
        'sequence',
        'type',
        'name',
        'slug',
        'desc',
        'required',
        'searchable',
        'extsearch',
        'viewable',
        'viewresults',
        'extview',
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
        self::_TEXT, self::_RICH_TEXT, self::_NUMBER, self::_LIST,
        self::_MULTI_SELECT_LIST, self::_GENERATED_LIST, self::_DATE,
        self::_SCHEDULE, self::_GEOLOCATOR, self::_DOCUMENTS, self::_GALLERY,
        self::_3D_MODEL, self::_PLAYLIST, self::_VIDEO, self::_COMBO_LIST,
        self::_ASSOCIATOR
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
     * Searchable variable getter.
     */
    public function isExternalSearchable() {
        return $this->extsearch;
    }

    /**
     * Gets the typed field governed by this field.
     * E.g. if this->type == "Text" it will find the TextField it is associated with in the database.
     *
     * This function is the most necessary evil the way our database is set up, but we should transition to using this
     * function rather than having our huge switch like the one in this function statements all over our app.
     *
     * @throws \Exception if the field type is invalid.
     * @param $rid int, the id of the record associated with the typed field.
     * @return \App\BaseField | null, some typed field or null if the typed field does not exist.
     */
    public function getTypedField($rid) {
        switch($this->type) {
            case self::_TEXT:
                return TextField::where("flid", "=", $this->flid)->where("rid", "=", $rid)->first();
                break;

            case self::_RICH_TEXT:
                return RichTextField::where("flid", "=", $this->flid)->where("rid", "=", $rid)->first();
                break;

            case self::_NUMBER:
                return NumberField::where("flid", "=", $this->flid)->where("rid", "=", $rid)->first();
                break;

            case self::_LIST:
                return ListField::where("flid", "=", $this->flid)->where("rid", "=", $rid)->first();
                break;

            case self::_MULTI_SELECT_LIST:
                return MultiSelectListField::where("flid", "=", $this->flid)->where("rid", "=", $rid)->first();
                break;

            case self::_GENERATED_LIST:
                return GeneratedListField::where("flid", "=", $this->flid)->where("rid", "=", $rid)->first();
                break;

            case self::_DATE:
                return DateField::where("flid", "=", $this->flid)->where("rid", "=", $rid)->first();
                break;

            case self::_SCHEDULE:
                return ScheduleField::where("flid", "=", $this->flid)->where("rid", "=", $rid)->first();
                break;

            case self::_GEOLOCATOR:
                return GeolocatorField::where("flid", "=", $this->flid)->where("rid", "=", $rid)->first();
                break;

            case self::_DOCUMENTS:
                return DocumentsField::where("flid", "=", $this->flid)->where("rid", "=", $rid)->first();
                break;

            case self::_GALLERY:
                return GalleryField::where("flid", "=", $this->flid)->where("rid", "=", $rid)->first();
                break;

            case self::_3D_MODEL:
                return ModelField::where("flid", "=", $this->flid)->where("rid", "=", $rid)->first();
                break;

            case self::_PLAYLIST:
                return PlaylistField::where("flid", "=", $this->flid)->where("rid", "=", $rid)->first();
                break;

            case self::_VIDEO:
                return VideoField::where("flid", "=", $this->flid)->where("rid", "=", $rid)->first();
                break;

            case self::_COMBO_LIST:
                return ComboListField::where("flid", "=", $this->flid)->where("rid", "=", $rid)->first();
                break;

            case self::_ASSOCIATOR:
                return AssociatorField::where("flid", "=", $this->flid)->where("rid", "=", $rid)->first();
                break;

            default:
                throw new \Exception("Invalid field type in Field::getTypedField.");
        }
    }

    /**
     *
     *
     * @param $arg string, the argument of the search
     * @param $method int, type of search.
     * @return Builder
     * @throws \Exception if the field type is invalid.
     */
    public function keywordSearchTyped($arg, $method) {
        switch($this->type) {
            case self::_TEXT:
                return DB::table("text_fields")
                    ->select("rid")
                    ->where("fid", "=", $this->fid)
                    ->whereRaw("MATCH (`text`) AGAINST (? IN BOOLEAN MODE)", [$arg])
                    ->distinct();
                break;

            case self::_RICH_TEXT:
                return DB::table("rich_text_fields")
                    ->select("rid")
                    ->where("fid", "=", $this->fid)
                    ->whereRaw("MATCH (`searchable_rawtext`) AGAINST (? IN BOOLEAN MODE)", [$arg])
                    ->distinct();
                break;

            case self::_NUMBER:
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

            case self::_LIST:
                return DB::table("list_fields")
                    ->select("rid")
                    ->where("fid", "=", $this->fid)
                    ->whereRaw("MATCH (`option`) AGAINST (? IN BOOLEAN MODE)", [$arg])
                    ->distinct();
                break;

            case self::_MULTI_SELECT_LIST:
                return DB::table("multi_select_list_fields")
                    ->select("rid")
                    ->where("fid", "=", $this->fid)
                    ->whereRaw("MATCH (`options`) AGAINST (? IN BOOLEAN MODE)", [$arg])
                    ->distinct();
                break;

            case self::_GENERATED_LIST:
                return DB::table("generated_list_fields")
                    ->select("rid")
                    ->where("fid", "=", $this->fid)
                    ->whereRaw("MATCH (`options`) AGAINST (? IN BOOLEAN MODE)", [$arg])
                    ->distinct();
                break;

            case self::_DATE:
                $arg = str_replace(["*", "\""], "", $arg);

                // Boolean to decide if we should consider circa options.
                $circa = explode("[!Circa!]", $this->options)[1] == "Yes";

                // Boolean to decide if we should consider era.
                $era = explode("[!Era!]", $this->options)[1] == "On";

                return DateField::buildQuery($arg, $circa, $era, $this->fid);
                break;

            case self::_SCHEDULE:
                return DB::table(ScheduleField::SUPPORT_NAME)
                    ->select("rid")
                    ->where("fid", "=", $this->fid)
                    ->whereRaw("MATCH (`desc`) AGAINST (? IN BOOLEAN MODE)", [$arg])
                    ->distinct();
                break;

            case self::_GEOLOCATOR:
                return DB::table(GeolocatorField::SUPPORT_NAME)
                    ->select("rid")
                    ->where("fid", "=", $this->fid)
                    ->where(function($query) use ($arg) {
                        $query->whereRaw("MATCH (`desc`) AGAINST (? IN BOOLEAN MODE)", [$arg])
                            ->orWhereRaw("MATCH (`address`) AGAINST (? IN BOOLEAN MODE)", [$arg]);
                    })
                    ->distinct();
                break;

            case self::_DOCUMENTS:
                $arg = self::processArgumentForFileField($arg, $method);

                return DB::table("documents_fields")
                    ->select("rid")
                    ->where("fid", "=", $this->fid)
                    ->whereRaw("MATCH (`documents`) AGAINST (? IN BOOLEAN MODE)", [$arg])
                    ->distinct();
                break;

            case self::_GALLERY:
                $arg = self::processArgumentForFileField($arg, $method);

                return DB::table("gallery_fields")
                    ->select("rid")
                    ->where("fid", "=", $this->fid)
                    ->whereRaw("MATCH (`images`) AGAINST (? IN BOOLEAN MODE)", [$arg])
                    ->distinct();
                break;

            case self::_3D_MODEL:
                $arg = self::processArgumentForFileField($arg, $method);

                return DB::table("model_fields")
                    ->select("rid")
                    ->where("fid", "=", $this->fid)
                    ->whereRaw("MATCH (`model`) AGAINST (? IN BOOLEAN MODE)", [$arg])
                    ->distinct();
                break;

            case self::_PLAYLIST:
                $arg = self::processArgumentForFileField($arg, $method);

                return DB::table("playlist_fields")
                    ->select("rid")
                    ->where("fid", "=", $this->fid)
                    ->whereRaw("MATCH (`audio`) AGAINST (? IN BOOLEAN MODE)", [$arg])
                    ->distinct();
                break;

            case self::_VIDEO:
                $arg = self::processArgumentForFileField($arg, $method);

                return DB::table("video_fields")
                    ->select("rid")
                    ->where("fid", "=", $this->fid)
                    ->whereRaw("MATCH (`video`) AGAINST (? IN BOOLEAN MODE)", [$arg])
                    ->distinct();
                break;

            case self::_COMBO_LIST:
                return DB::table("combo_support")
                    ->select("rid")
                    ->where("fid", "=", $this->fid)
                    ->where(function($query) use ($arg) {
                        $num = $arg = str_replace(["*", "\""], "", $arg);
                        $num = floatval($num);

                        $query->whereRaw("MATCH (`data`) AGAINST (? IN BOOLEAN MODE)", [$arg])
                            ->orWhereBetween("number", [$num - NumberField::EPSILON, $num + NumberField::EPSILON]);
                    })
                    ->distinct();
                break;

            case self::_ASSOCIATOR:
                return DB::table(AssociatorField::SUPPORT_NAME)
                    ->select("rid")
                    ->where("fid", "=", $this->fid)
                    ->whereRaw("MATCH (`record`) AGAINST (? IN BOOLEAN MODE)", [$arg])
                    ->distinct();
                break;

            default: // Error occurred.
                throw new \Exception("Invalid field type in field::keywordSearchTyped.");
                break;
        }
    }

    /**
     * Execute an advanced search.
     *
     * @param $flid, field id
     * @param $field_type, field type
     * @param array $query, search query
     * @throws \Exception on invalid field type.
     * @return Builder
     */
    public static function advancedSearch($flid, $field_type, array $query) {
        switch($field_type) {
            case self::_TEXT:
                return TextField::getAdvancedSearchQuery($flid, $query);
                break;

            case self::_RICH_TEXT:
                return RichTextField::getAdvancedSearchQuery($flid, $query);
                break;

            case self::_NUMBER:
                return NumberField::getAdvancedSearchQuery($flid, $query);
                break;

            case self::_LIST:
                return ListField::getAdvancedSearchQuery($flid, $query);
                break;

            case self::_MULTI_SELECT_LIST:
                return MultiSelectListField::getAdvancedSearchQuery($flid, $query);
                break;

            case self::_GENERATED_LIST:
                return GeneratedListField::getAdvancedSearchQuery($flid, $query);
                break;

            case self::_DATE:
                return DateField::getAdvancedSearchQuery($flid, $query);
                break;

            case self::_SCHEDULE:
                return ScheduleField::getAdvancedSearchQuery($flid, $query);
                break;

            case self::_GEOLOCATOR:
                return GeolocatorField::getAdvancedSearchQuery($flid, $query);
                break;

            case self::_DOCUMENTS:
                return DocumentsField::getAdvancedSearchQuery($flid, $query);
                break;

            case self::_GALLERY:
                return GalleryField::getAdvancedSearchQuery($flid, $query);
                break;

            case self::_3D_MODEL:
                return ModelField::getAdvancedSearchQuery($flid, $query);
                break;

            case self::_PLAYLIST:
                return PlaylistField::getAdvancedSearchQuery($flid, $query);
                break;

            case self::_VIDEO:
                return VideoField::getAdvancedSearchQuery($flid, $query);
                break;

            case self::_COMBO_LIST:
                return ComboListField::getAdvancedSearchQuery($flid, $query);
                break;

            case self::_ASSOCIATOR:
                return AssociatorField::getAdvancedSearchQuery($flid, $query);
                break;

            default: // Error occurred.
                throw new \Exception("Invalid field type in field::advancedSearch.");
                break;
        }
    }

    static function validateField($field, $value, $request){
        $field = FieldController::getField($field);
        $field_type = $field->type;
        switch($field_type) {
            case self::_TEXT:
                return TextField::validate($field, $value);
                break;

            case self::_RICH_TEXT:
                return RichTextField::validate($field, $value);
                break;

            case self::_NUMBER:
                return NumberField::validate($field, $value);
                break;

            case self::_LIST:
                return ListField::validate($field, $value);
                break;

            case self::_MULTI_SELECT_LIST:
                return MultiSelectListField::validate($field, $value);
                break;

            case self::_GENERATED_LIST:
                return GeneratedListField::validate($field, $value);
                break;

            case self::_DATE:
                return DateField::validate($field, $request);
                break;

            case self::_SCHEDULE:
                return ScheduleField::validate($field, $value);
                break;

            case self::_GEOLOCATOR:
                return GeolocatorField::validate($field, $value);
                break;

            case self::_DOCUMENTS:
                return DocumentsField::validate($field, $value);
                break;

            case self::_GALLERY:
                return GalleryField::validate($field, $value);
                break;

            case self::_3D_MODEL:
                return ModelField::validate($field, $value);
                break;

            case self::_PLAYLIST:
                return PlaylistField::validate($field, $value);
                break;

            case self::_VIDEO:
                return VideoField::validate($field, $value);
                break;

            case self::_COMBO_LIST:
                return ComboListField::validate($field, $request);
                break;

            case self::_ASSOCIATOR:
                return AssociatorField::validate($field, $value);
                break;

            default: // Error occurred.
                throw new \Exception("Invalid field type in field::field validation.");
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

        if ($this->type == self::_SCHEDULE) {
            DB::table(ScheduleField::SUPPORT_NAME)->where("flid", "=", $this->flid)->delete();
        }
        else if ($this->type == self::_GEOLOCATOR) {
            DB::table(GeolocatorField::SUPPORT_NAME)->where("flid", "=", $this->flid)->delete();
        }
        else if ($this->type == self::_COMBO_LIST) {
            DB::table(ComboListField::SUPPORT_NAME)->where("flid", "=", $this->flid)->delete();
        }
        else if ($this->type == self::_ASSOCIATOR) {
            DB::table(AssociatorField::SUPPORT_NAME)->where("flid", "=", $this->flid)->delete();
        }

        DB::table("metadatas")->where("flid", "=", $this->flid)->delete();

        parent::delete();
    }

    /**
     * Determine if a field has a metadata association.
     *
     * @param $flid
     * @return bool
     */
    public static function hasMetadata($flid) {
        return !! Metadata::where("flid", "=", $flid)->count();
    }

    public static function slugExists($slug){
        $field = self::where('slug','=',$slug)->get()->first();
        if(is_null($field))
            return false;
        else
            return true;
    }
}

