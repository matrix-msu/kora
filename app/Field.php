<?php namespace App;

use Illuminate\Database\Eloquent\Model;

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

    /**
     * This is an array of all possible typed fields.
     * @var array
     */
    static public $ENUM_TYPED_FIELDS = [
        Field::_TEXT, Field::_RICH_TEXT, Field::_NUMBER, Field::_LIST,
        Field::_MULTI_SELECT_LIST, Field::_GENERATED_LIST, Field::_DATE,
        Field::_SCHEDULE, Field::_GEOLOCATOR, Field::_DOCUMENTS, Field::_GALLERY,
        Field::_3D_MODEL, Field::_PLAYLIST, Field::_VIDEO, Field::_COMBO_LIST
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
     * Gets the field governed by this field.
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
}

