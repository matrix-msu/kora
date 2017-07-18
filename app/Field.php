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
        if(!self::checkPermissions($this->fid, 'edit')) {
            return redirect('projects/'.$this->pid.'/forms/'.$this->fid.'/fields');
        }

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
        if(!self::checkPermissions($this->fid, 'edit')) {
            return redirect('projects/' . $this->pid . '/forms/' . $this->fid . '/fields');
        }

        $this->searchable = $request->searchable;
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
        if(!self::checkPermissions($this->fid, 'edit')) {
            return redirect('projects/'.$this->pid.'/forms/'.$this->fid.'/fields');
        }

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
        if(!self::checkPermissions($this->fid, 'edit')) {
            return redirect('projects/'.$this->pid.'/forms/'.$this->fid.'/fields');
        }

        $tag = '[!'.$opt.'!]';
        $array = explode($tag,$this->options);
        $this->options = $array[0].$tag.$value.$tag.$array[2];
        $this->save();

        //A field has been changed, so current record rollbacks become invalid.
        RevisionController::wipeRollbacks($this->fid);
    }

    /**
     * Checks a users permissions to be able to create and manipulate fields in a form.
     *
     * @param  int $fid - Form ID
     * @param  string $permission - Permission to check for
     * @return bool - Has the permission
     */
    private static function checkPermissions($fid, $permission='') {
        switch($permission) {
            case 'create':
                if(!(\Auth::user()->canCreateFields(FormController::getForm($fid))))  {
                    flash()->overlay(trans('controller_field.createper'), trans('controller_field.whoops'));
                    return false;
                }
                return true;
            case 'edit':
                if(!(\Auth::user()->canEditFields(FormController::getForm($fid)))) {
                    flash()->overlay(trans('controller_field.editper'), trans('controller_field.whoops'));
                    return false;
                }
                return true;
            case 'delete':
                if(!(\Auth::user()->canDeleteFields(FormController::getForm($fid)))) {
                    flash()->overlay(trans('controller_field.deleteper'), trans('controller_field.whoops'));
                    return false;
                }
                return true;
            default:
                if(!(\Auth::user()->inAFormGroup(FormController::getForm($fid)))) {
                    flash()->overlay(trans('controller_field.viewper'), trans('controller_field.whoops'));
                    return false;
                }
                return true;
        }
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
                throw new \Exception("Invalid field type in Field::getTypedField.");
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
                throw new \Exception("Invalid field type in Field::getTypedField.");
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
                throw new \Exception("Invalid field type in Field::getTypedField.");
        }
    }
}

