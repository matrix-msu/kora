<?php namespace App;

use App\Http\Controllers\FormController;
use App\Http\Controllers\RevisionController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Field extends Model {

    /*
    |--------------------------------------------------------------------------
    | Field
    |--------------------------------------------------------------------------
    |
    | This model represents a field and all its data
    |
    */

    /**
     * @var array - Attributes that can be mass assigned to model
     */
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
        'advsearch',
        'extsearch',
        'viewable',
        'viewresults',
        'extview',
    ];

    /**
     * @var string - Database column that represents the primary key
     */
    protected $primaryKey = "flid";

    /**
     * @var string - These are the possible field types at the moment
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
    * @var array - This is an array of all possible typed fields
    */
    static public $ENUM_TYPED_FIELDS = [
        self::_TEXT, self::_RICH_TEXT, self::_NUMBER, self::_LIST,
        self::_MULTI_SELECT_LIST, self::_GENERATED_LIST, self::_DATE,
        self::_SCHEDULE, self::_GEOLOCATOR, self::_DOCUMENTS, self::_GALLERY,
        self::_3D_MODEL, self::_PLAYLIST, self::_VIDEO, self::_COMBO_LIST,
        self::_ASSOCIATOR
    ];

    /**
     * @var array - This is an array of field type values for creation
     */
    static public $validFieldTypes = [
        'Text Fields' => array('Text' => 'Text', 'Rich Text' => 'Rich Text', 'Number' => 'Number'),
        'List Fields' => array('List' => 'List', 'Multi-Select List' => 'Multi-Select List', 'Generated List' => 'Generated List', 'Combo List' => 'Combo List'),
        'Date Fields' => array('Date' => 'Date', 'Schedule' => 'Schedule'),
        'File Fields' => array('Documents' => 'Documents','Gallery' => 'Gallery (jpg, gif, png)','Playlist' => 'Playlist (mp3, wav, oga)', 'Video' => 'Video (mp4, ogv)','3D-Model' => '3D-Model (obj, stl)'),
        'Specialty Fields' => array('Geolocator' => 'Geolocator (latlon, utm, textual)','Associator' => 'Associator')
    ];

    /**
     * @var array - This is an array of all sortable typed fields
     */
    const VALID_SORT = [self::_TEXT,self::_NUMBER,self::_LIST,self::_DATE];

    /**
     * Returns a field's form.
     *
     * @return BelongsTo
     */
    public function form() {
        return $this->belongsTo('App\Form');
    }

    /**
     * Returns a field's metadata.
     *
     * @return HasOne
     */
    public function metadata() {
        return $this->hasOne('App\Metadata','flid');
    }

    /**
     * Searchable variable getter.
     *
     * @return bool - Is searchable
     */
    public function isSearchable() {
        return $this->searchable;
    }

    /**
     * Advanced Search variable getter.
     *
     * @return bool - Is advanced searchable
     */
    public function isAdvancedSearchable() {
        return $this->advsearch;
    }

    /**
     * Searchable variable getter.
     *
     * @return bool - Is searchable externally
     */
    public function isExternalSearchable() {
        return $this->extsearch;
    }

    /**
     * Checks if field type is sortable.
     *
     * @return bool - Is sortable
     */
    public function isSortable() {
        return in_array($this->type, self::VALID_SORT);
    }

    /**
     * Update the field for if data is required in the field.
     *
     * @param  bool $req - Is the field required?
     */
    public function updateRequired($req) {
        $this->required = $req;
        $this->save();

        //A field has been changed, so current record rollbacks become invalid.
        RevisionController::wipeRollbacks($this->fid);
    }

    /**
     * Update the field for what context field's data can be searched and viewed.
     *
     * @param  Request $request
     */
    public function updateSearchable(Request $request) {
        $this->searchable = $request->searchable;
        $this->advsearch = $request->advsearch;
        $this->extsearch = $request->extsearch;
        $this->viewable = $request->viewable;
        $this->viewresults = $request->viewresults;
        $this->extview = $request->extview;
        $this->save();

        //A field has been changed, so current record rollbacks become invalid.
        RevisionController::wipeRollbacks($this->fid);
    }

    /**
     * Update the field's default value.
     *
     * @param  string $def - Default value of field
     */
    public function updateDefault($def) {
        $this->default = $def;
        $this->save();

        //A field has been changed, so current record rollbacks become invalid.
        RevisionController::wipeRollbacks($this->fid);
    }

    /**
     * Update an option for a field.
     *
     * @param  string $opt - Option to update
     * @param  string $value - Value for option
     */
    public function updateOptions($opt, $value) {
        $tag = '[!'.$opt.'!]';
        $array = explode($tag,$this->options);
        $this->options = $array[0].$tag.$value.$tag.$array[2];
        $this->save();

        //A field has been changed, so current record rollbacks become invalid.
        RevisionController::wipeRollbacks($this->fid);
    }

    /**
     * Checks to see if slug is already in use.
     *
     * @param  string $slug - The slug to check
     * @return bool - Does exist
     */
    public static function slugExists($slug) {
        $field = self::where('slug','=',$slug)->get()->first();
        if(is_null($field))
            return false;
        else
            return true;
    }

    /**
     * Sanitizes data for an xml to prevent injection.
     *
     * @param  string $value - XML data string
     * @return string - The sanitized XML string
     */
    public static function xmlTagClear($value) {
        $value = htmlentities($value);
        $value = str_replace(' ','_',$value);

        return $value;
    }

    /**
     * Deletes the field's typed fields and metadata, then deletes self
     */
    public function delete() {
        BaseField::deleteBaseFieldsByFLID($this->flid);

        DB::table("metadatas")->where("flid", "=", $this->flid)->delete();

        //A field has been deleted, so current record rollbacks become invalid.
        RevisionController::wipeRollbacks($this->fid);

        parent::delete();
    }

    /////////////
    //UTILITIES//
    /////////////
    /// These functions help retrieve a fields typed model. Any new fields should be represented here.

    /**
     * Get back the typed field model. Mostly used for typed field functionality that exists before the model 
     * exists in a record.
     *
     * @return BaseField - The requested typed field
     */
    public function getTypedField() {
        switch($this->type) {
            case self::_TEXT:
                return new TextField();
                break;
            case self::_RICH_TEXT:
                return new RichTextField();
                break;
            case self::_NUMBER:
                return new NumberField();
                break;
            case self::_LIST:
                return new ListField();
                break;
            case self::_MULTI_SELECT_LIST:
                return new MultiSelectListField();
                break;
            case self::_GENERATED_LIST:
                return new GeneratedListField();
                break;
            case self::_DATE:
                //Workaround for keyword search (Don't ask)
                $df = new DateField();
                $df->flid = $this->flid;
                return $df;
                break;
            case self::_SCHEDULE:
                return new ScheduleField();
                break;
            case self::_GEOLOCATOR:
                return new GeolocatorField();
                break;
            case self::_DOCUMENTS:
                return new DocumentsField();
                break;
            case self::_GALLERY:
                return new GalleryField();
                break;
            case self::_3D_MODEL:
                return new ModelField();
                break;
            case self::_PLAYLIST:
                return new PlaylistField();
                break;
            case self::_VIDEO:
                return new VideoField();
                break;
            case self::_COMBO_LIST:
                return new ComboListField();
                break;
            case self::_ASSOCIATOR:
                return new AssociatorField();
                break;
            default:
                throw new \Exception("field_type_exception");
        }
    }

    /**
     * Can call this instead if the field doesn't exist yet and you know the type you need.
     *
     * @return BaseField - The requested typed field
     */
    public static function getTypedFieldStatic($type) {
        switch($type) {
            case self::_TEXT:
                return new TextField();
                break;
            case self::_RICH_TEXT:
                return new RichTextField();
                break;
            case self::_NUMBER:
                return new NumberField();
                break;
            case self::_LIST:
                return new ListField();
                break;
            case self::_MULTI_SELECT_LIST:
                return new MultiSelectListField();
                break;
            case self::_GENERATED_LIST:
                return new GeneratedListField();
                break;
            case self::_DATE:
                return new DateField();
                break;
            case self::_SCHEDULE:
                return new ScheduleField();
                break;
            case self::_GEOLOCATOR:
                return new GeolocatorField();
                break;
            case self::_DOCUMENTS:
                return new DocumentsField();
                break;
            case self::_GALLERY:
                return new GalleryField();
                break;
            case self::_3D_MODEL:
                return new ModelField();
                break;
            case self::_PLAYLIST:
                return new PlaylistField();
                break;
            case self::_VIDEO:
                return new VideoField();
                break;
            case self::_COMBO_LIST:
                return new ComboListField();
                break;
            case self::_ASSOCIATOR:
                return new AssociatorField();
                break;
            default:
                throw new \Exception("field_type_exception");
        }
    }

    /**
     * For a record, gets back the typed field model that holds the actual data.
     *
     * @param  int $rid - Record ID
     * @return BaseField - The requested typed field
     */
    public function getTypedFieldFromRID($rid) {
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
                throw new \Exception("field_type_exception");
        }
    }
}

