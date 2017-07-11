<?php namespace App;

use App\Http\Controllers\FieldController;
use App\Http\Requests\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
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
     * @var array - This is an array of all sortable typed fields
     */
    const VALID_SORT = ['Text','List','Number','Date'];

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
        DB::table(BaseField::$MAPPED_FIELD_TYPES[$this->type])->where("flid", "=", $this->flid)->delete();

        if($this->type == self::_SCHEDULE) {
            DB::table(ScheduleField::SUPPORT_NAME)->where("flid", "=", $this->flid)->delete();
        } else if($this->type == self::_GEOLOCATOR) {
            DB::table(GeolocatorField::SUPPORT_NAME)->where("flid", "=", $this->flid)->delete();
        } else if($this->type == self::_COMBO_LIST) {
            DB::table(ComboListField::SUPPORT_NAME)->where("flid", "=", $this->flid)->delete();
        } else if($this->type == self::_ASSOCIATOR) {
            DB::table(AssociatorField::SUPPORT_NAME)->where("flid", "=", $this->flid)->delete();
        }

        DB::table("metadatas")->where("flid", "=", $this->flid)->delete();

        parent::delete();
    }

    //TODO:: These are all the switch statements that we need to do something to simplify

    /////////////
    //UTILITIES//
    /////////////

    /**
     * For a record, gets back the typed field model that holds the actual data.
     *
     * @param  int $rid - Record ID
     * @return BaseField - The requested typed field
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

    ///////////
    //OPTIONS//
    ///////////

    /**
     * Gets the view name for the field options page.
     *
     * @param  string $fieldType - The field type
     * @return string - View name
     */
    public static function getFieldTypeView($fieldType) {
        switch ($fieldType) {
            case self::_TEXT:
                return TextField::FIELD_OPTIONS_VIEW;
                break;
            case self::_RICH_TEXT:
                return RichTextField::FIELD_OPTIONS_VIEW;
                break;
            case self::_NUMBER:
                return NumberField::FIELD_OPTIONS_VIEW;
                break;
            case self::_LIST:
                return ListField::FIELD_OPTIONS_VIEW;
                break;
            case self::_MULTI_SELECT_LIST:
                return MultiSelectListField::FIELD_OPTIONS_VIEW;
                break;
            case self::_GENERATED_LIST:
                return GeneratedListField::FIELD_OPTIONS_VIEW;
                break;
            case self::_DATE:
                return DateField::FIELD_OPTIONS_VIEW;
                break;
            case self::_SCHEDULE:
                return ScheduleField::FIELD_OPTIONS_VIEW;
                break;
            case self::_GEOLOCATOR:
                return GeolocatorField::FIELD_OPTIONS_VIEW;
                break;
            case self::_DOCUMENTS:
                return DocumentsField::FIELD_OPTIONS_VIEW;
                break;
            case self::_GALLERY:
                return GalleryField::FIELD_OPTIONS_VIEW;
                break;
            case self::_3D_MODEL:
                return ModelField::FIELD_OPTIONS_VIEW;
                break;
            case self::_PLAYLIST:
                return PlaylistField::FIELD_OPTIONS_VIEW;
                break;
            case self::_VIDEO:
                return VideoField::FIELD_OPTIONS_VIEW;
                break;
            case self::_COMBO_LIST:
                return ComboListField::FIELD_OPTIONS_VIEW;
                break;
            case self::_ASSOCIATOR:
                return AssociatorField::FIELD_OPTIONS_VIEW;
                break;
            default: // Error occurred.
                throw new \Exception("Invalid field type in field::field option.");
                break;
        }
    }

    /**
     * Gets the view name for advanced field creation.
     *
     * @param  string $fieldType - The field type
     * @return string - View name
     */
    public static function getAdvFieldTypeView($fieldType) {
        switch($fieldType) {
            case self::_TEXT:
                return TextField::FIELD_ADV_OPTIONS_VIEW;
                break;
            case self::_RICH_TEXT:
                return RichTextField::FIELD_ADV_OPTIONS_VIEW;
                break;
            case self::_NUMBER:
                return NumberField::FIELD_ADV_OPTIONS_VIEW;
                break;
            case self::_LIST:
                return ListField::FIELD_ADV_OPTIONS_VIEW;
                break;
            case self::_MULTI_SELECT_LIST:
                return MultiSelectListField::FIELD_ADV_OPTIONS_VIEW;
                break;
            case self::_GENERATED_LIST:
                return GeneratedListField::FIELD_ADV_OPTIONS_VIEW;
                break;
            case self::_DATE:
                return DateField::FIELD_ADV_OPTIONS_VIEW;
                break;
            case self::_SCHEDULE:
                return ScheduleField::FIELD_ADV_OPTIONS_VIEW;
                break;
            case self::_GEOLOCATOR:
                return GeolocatorField::FIELD_ADV_OPTIONS_VIEW;
                break;
            case self::_DOCUMENTS:
                return DocumentsField::FIELD_ADV_OPTIONS_VIEW;
                break;
            case self::_GALLERY:
                return GalleryField::FIELD_ADV_OPTIONS_VIEW;
                break;
            case self::_3D_MODEL:
                return ModelField::FIELD_ADV_OPTIONS_VIEW;
                break;
            case self::_PLAYLIST:
                return PlaylistField::FIELD_ADV_OPTIONS_VIEW;
                break;
            case self::_VIDEO:
                return VideoField::FIELD_ADV_OPTIONS_VIEW;
                break;
            case self::_COMBO_LIST:
                return ComboListField::FIELD_ADV_OPTIONS_VIEW;
                break;
            case self::_ASSOCIATOR:
                return AssociatorField::FIELD_ADV_OPTIONS_VIEW;
                break;
            default: // Error occurred.
                throw new \Exception("Invalid field type in field::field option.");
                break;
        }
    }

    /**
     * Gets the default options string for a new field.
     *
     * @param  string $type - The field type
     * @param  Request $request
     * @return string - The default options
     */
    public static function getOptions($type, $request) {
        $field_type = $type;
        switch($field_type) {
            case self::_TEXT:
                return TextField::getOptions();
                break;
            case self::_RICH_TEXT:
                return RichTextField::getOptions();
                break;
            case self::_NUMBER:
                return NumberField::getOptions();
                break;
            case self::_LIST:
                return ListField::getOptions();
                break;
            case self::_MULTI_SELECT_LIST:
                return MultiSelectListField::getOptions();
                break;
            case self::_GENERATED_LIST:
                return GeneratedListField::getOptions();
                break;
            case self::_DATE:
                return DateField::getOptions();
                break;
            case self::_SCHEDULE:
                return ScheduleField::getOptions();
                break;
            case self::_GEOLOCATOR:
                return GeolocatorField::getOptions();
                break;
            case self::_DOCUMENTS:
                return DocumentsField::getOptions();
                break;
            case self::_GALLERY:
                return GalleryField::getOptions();
                break;
            case self::_3D_MODEL:
                return ModelField::getOptions();
                break;
            case self::_PLAYLIST:
                return PlaylistField::getOptions();
                break;
            case self::_VIDEO:
                return VideoField::getOptions();
                break;
            case self::_COMBO_LIST:
                return ComboListField::getOptions($request);
                break;
            case self::_ASSOCIATOR:
                return AssociatorField::getOptions();
                break;
            default: // Error occurred.
                throw new \Exception("Invalid field type in field::field option.");
                break;
        }
    }

    /**
     * Update the options for a field
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  int $flid - Field ID
     * @param  string $fieldType - Type of field
     * @param  Request $request
     * @param  bool $return - Are we returning an error by string or redirect
     * @return string - The result
     */
    public static function updateOptions($pid, $fid, $flid, $fieldType, $request, $return=true) {
        switch($fieldType) {
            case self::_TEXT:
                $returnval = TextField::updateOptions($pid, $fid, $flid, $request, $return);
                break;
            case self::_RICH_TEXT:
                RichTextField::updateOptions($pid, $fid, $flid, $request);
                break;
            case self::_NUMBER:
                $returnval = NumberField::updateOptions($pid, $fid, $flid, $request, $return);
                break;
            case self::_LIST:
                ListField::updateOptions($pid, $fid, $flid, $request);
                break;
            case self::_MULTI_SELECT_LIST:
                MultiSelectListField::updateOptions($pid, $fid, $flid, $request);
                break;
            case self::_GENERATED_LIST:
                GeneratedListField::updateOptions($pid, $fid, $flid, $request);
                break;
            case self::_DATE:
                DateField::updateOptions($pid, $fid, $flid, $request);
                break;
            case self::_SCHEDULE:
                ScheduleField::updateOptions($pid, $fid, $flid, $request);
                break;
            case self::_GEOLOCATOR:
                GeolocatorField::updateOptions($pid, $fid, $flid, $request);
                break;
            case self::_DOCUMENTS:
                DocumentsField::updateOptions($pid, $fid, $flid, $request);
                break;
            case self::_GALLERY:
                GalleryField::updateOptions($pid, $fid, $flid, $request);
                break;
            case self::_3D_MODEL:
                ModelField::updateOptions($pid, $fid, $flid, $request);
                break;
            case self::_PLAYLIST:
                PlaylistField::updateOptions($pid, $fid, $flid, $request);
                break;
            case self::_VIDEO:
                VideoField::updateOptions($pid, $fid, $flid, $request);
                break;
            case self::_COMBO_LIST:
                ComboListField::updateOptions($pid, $fid, $flid, $request);
                break;
            case self::_ASSOCIATOR:
                AssociatorField::updateOptions($pid, $fid, $flid, $request);
                break;
            default: // Error occurred.
                throw new \Exception("Invalid field type in field::field option.");
                break;
        }

        if($return) {
            flash()->success(trans('controller_option.updated'));

            return redirect('projects/' . $pid . '/forms/' . $fid . '/fields/' . $flid . '/options');
        } else {
            if($field_type==self::_TEXT | $field_type==self::_NUMBER)
                return $returnval;
            else
                return '';
        }
    }

    ////////////////
    //MANIPULATION//
    ////////////////

    /**
     * Creates a typed field to store record data.
     *
     * @param  Field $field - The field to represent record data
     * @param  Record $record - Record being created
     * @param  string $value - Data to add
     * @param  Request $request
     */
    public static function createNewRecordField($field, $record, $value, $request) {
        $field_type = $field->type;
        switch($field_type) {
            case self::_TEXT:
                TextField::createNewRecordField($field, $record, $value);
                break;
            case self::_RICH_TEXT:
                RichTextField::createNewRecordField($field, $record, $value);
                break;
            case self::_NUMBER:
                NumberField::createNewRecordField($field, $record, $value);
                break;
            case self::_LIST:
                ListField::createNewRecordField($field, $record, $value);
                break;
            case self::_MULTI_SELECT_LIST:
                MultiSelectListField::createNewRecordField($field, $record, $value);
                break;
            case self::_GENERATED_LIST:
                GeneratedListField::createNewRecordField($field, $record, $value);
                break;
            case self::_DATE:
                DateField::createNewRecordField($field, $record, $request);
                break;
            case self::_SCHEDULE:
                ScheduleField::createNewRecordField($field, $record, $value);
                break;
            case self::_GEOLOCATOR:
                GeolocatorField::createNewRecordField($field, $record, $value);
                break;
            case self::_DOCUMENTS:
                DocumentsField::createNewRecordField($field, $record, $value, $request);
                break;
            case self::_GALLERY:
                GalleryField::createNewRecordField($field, $record, $value, $request);
                break;
            case self::_3D_MODEL:
                ModelField::createNewRecordField($field, $record, $value, $request);
                break;
            case self::_PLAYLIST:
                PlaylistField::createNewRecordField($field, $record, $value, $request);
                break;
            case self::_VIDEO:
                VideoField::createNewRecordField($field, $record, $value, $request);
                break;
            case self::_COMBO_LIST:
                ComboListField::createNewRecordField($field, $record, $request);
                break;
            case self::_ASSOCIATOR:
                AssociatorField::createNewRecordField($field, $record, $value);
                break;
            default: // Error occurred.
                throw new \Exception("Invalid field type in field::field validation.");
                break;
        }
    }

    /**
     * Edits a typed field that has record data.
     *
     * @param  Field $field - The field that contains record data
     * @param  Record $record - Record being edited
     * @param  string $value - Data to add
     * @param  Request $request
     */
    public static function editRecordField($field, $record, $value, $request) {
        $field_type = $field->type;
        switch($field_type) {
            case self::_TEXT:
                TextField::editRecordField($field, $record, $value);
                break;
            case self::_RICH_TEXT:
                RichTextField::editRecordField($field, $record, $value);
                break;
            case self::_NUMBER:
                NumberField::editRecordField($field, $record, $value);
                break;
            case self::_LIST:
                ListField::editRecordField($field, $record, $value);
                break;
            case self::_MULTI_SELECT_LIST:
                MultiSelectListField::editRecordField($field, $record, $value);
                break;
            case self::_GENERATED_LIST:
                GeneratedListField::editRecordField($field, $record, $value);
                break;
            case self::_DATE:
                DateField::editRecordField($field, $record, $request);
                break;
            case self::_SCHEDULE:
                ScheduleField::editRecordField($field, $record, $value);
                break;
            case self::_GEOLOCATOR:
                GeolocatorField::editRecordField($field, $record, $value);
                break;
            case self::_DOCUMENTS:
                DocumentsField::editRecordField($field, $record, $value, $request);
                break;
            case self::_GALLERY:
                GalleryField::editRecordField($field, $record, $value, $request);
                break;
            case self::_3D_MODEL:
                ModelField::editRecordField($field, $record, $value, $request);
                break;
            case self::_PLAYLIST:
                PlaylistField::editRecordField($field, $record, $value, $request);
                break;
            case self::_VIDEO:
                VideoField::editRecordField($field, $record, $value, $request);
                break;
            case self::_COMBO_LIST:
                ComboListField::editRecordField($field, $record, $request);
                break;
            case self::_ASSOCIATOR:
                AssociatorField::editRecordField($field, $record, $value);
                break;
            default: // Error occurred.
                throw new \Exception("Invalid field type in field::field validation.");
                break;
        }
    }

    /**
     * Takes data from a mass assignment operation and applies it to an individual field.
     *
     * @param  Field $field - The field to represent record data
     * @param  Record $record - Record being written to
     * @param  String $formFieldValue - The value to be assigned
     * @param  bool $overwrite - Overwrite if data exists
     * @param  Request $request
     */
    public static function massAssignRecordField($field, $record, $formFieldValue, $overwrite=0, $request) {
        $fieldType = $field->type;
        switch($fieldType) {
            case self::_TEXT:
                TextField::massAssignRecordField($field->flid, $record, $formFieldValue, $overwrite);
                break;
            case self::_RICH_TEXT:
                RichTextField::massAssignRecordField($field->flid, $record, $formFieldValue, $overwrite);
                break;
            case self::_NUMBER:
                NumberField::massAssignRecordField($field->flid, $record, $formFieldValue, $overwrite);
                break;
            case self::_LIST:
                ListField::massAssignRecordField($field->flid, $record, $formFieldValue, $overwrite);
                break;
            case self::_MULTI_SELECT_LIST:
                MultiSelectListField::massAssignRecordField($field->flid, $record, $formFieldValue, $overwrite);
                break;
            case self::_GENERATED_LIST:
                GeneratedListField::massAssignRecordField($field->flid, $record, $formFieldValue, $overwrite);
                break;
            case self::_DATE:
                DateField::massAssignRecordField($field->flid, $record, $request, $overwrite);
                break;
            case self::_SCHEDULE:
                ScheduleField::massAssignRecordField($field->flid, $record, $formFieldValue, $overwrite);
                break;
            case self::_GEOLOCATOR:
                GeolocatorField::massAssignRecordField($field->flid, $record, $formFieldValue, $overwrite);
                break;
            case self::_DOCUMENTS:
                DocumentsField::massAssignRecordField($field->flid, $record, $formFieldValue, $overwrite);
                break;
            case self::_GALLERY:
                GalleryField::massAssignRecordField($field->flid, $record, $formFieldValue, $overwrite);
                break;
            case self::_3D_MODEL:
                ModelField::massAssignRecordField($field->flid, $record, $formFieldValue, $overwrite);
                break;
            case self::_PLAYLIST:
                PlaylistField::massAssignRecordField($field->flid, $record, $formFieldValue, $overwrite);
                break;
            case self::_VIDEO:
                VideoField::massAssignRecordField($field->flid, $record, $formFieldValue, $overwrite);
                break;
            case self::_COMBO_LIST:
                ComboListField::massAssignRecordField($field->flid, $record, $request, $overwrite);
                break;
            case self::_ASSOCIATOR:
                AssociatorField::massAssignRecordField($field->flid, $record, $formFieldValue, $overwrite);
                break;
            default: // Error occurred.
                throw new \Exception("Invalid field type in field::field validation.");
                break;
        }
    }

    /**
     * For a test record, add test data to field.
     *
     * @param  Field $field - The field to represent record data
     * @param  Record $record - Test record being created
     */
    public static function createTestRecordField($field, $record) {
        $field_type = $field->type;
        switch($field_type) {
            case self::_TEXT:
                TextField::createTestRecordField($field, $record);
                break;
            case self::_RICH_TEXT:
                RichTextField::createTestRecordField($field, $record);
                break;
            case self::_NUMBER:
                NumberField::createTestRecordField($field, $record);
                break;
            case self::_LIST:
                ListField::createTestRecordField($field, $record);
                break;
            case self::_MULTI_SELECT_LIST:
                MultiSelectListField::createTestRecordField($field, $record);
                break;
            case self::_GENERATED_LIST:
                GeneratedListField::createTestRecordField($field, $record);
                break;
            case self::_DATE:
                DateField::createTestRecordField($field, $record);
                break;
            case self::_SCHEDULE:
                ScheduleField::createTestRecordField($field, $record);
                break;
            case self::_GEOLOCATOR:
                GeolocatorField::createTestRecordField($field, $record);
                break;
            case self::_DOCUMENTS:
                DocumentsField::createTestRecordField($field, $record);
                break;
            case self::_GALLERY:
                GalleryField::createTestRecordField($field, $record);
                break;
            case self::_3D_MODEL:
                ModelField::createTestRecordField($field, $record);
                break;
            case self::_PLAYLIST:
                PlaylistField::createTestRecordField($field, $record);
                break;
            case self::_VIDEO:
                VideoField::createTestRecordField($field, $record);
                break;
            case self::_COMBO_LIST:
                ComboListField::createTestRecordField($field, $record);
                break;
            case self::_ASSOCIATOR:
                AssociatorField::createTestRecordField($field, $record);
                break;
            default: // Error occurred.
                throw new \Exception("Invalid field type in field::field validation.");
                break;
        }
    }

    /**
     * Validates the record data for a field against the field's options.
     *
     * @param  Field $field - The
     * @param  string $value - Record data
     * @param  Request $request
     * @return string - Potential error message
     */
    public static function validateField($field, $value, $request) {
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
     * Performs a rollback function on an individual field's record data.
     *
     * @param  Revision $revision - The revision being rolled back
     * @param  Revision $field - Field to rollback
     */
    public static function rollbackField($revision, $field) {
        switch($field->type) {
            case Field::_TEXT:
                TextField::rollback($revision, $field);
                break;
            case Field::_RICH_TEXT:
                RichTextField::rollback($revision, $field);
                break;
            case Field::_NUMBER:
                NumberField::rollback($revision, $field);
                break;
            case Field::_LIST:
                ListField::rollback($revision, $field);
                break;
            case Field::_MULTI_SELECT_LIST:
                MultiSelectListField::rollback($revision, $field);
                break;
            case Field::_GENERATED_LIST:
                GeneratedListField::rollback($revision, $field);
                break;
            case Field::_DATE:
                DateField::rollback($revision, $field);
                break;
            case Field::_SCHEDULE:
                ScheduleField::rollback($revision, $field);
                break;
            case Field::_GEOLOCATOR:
                GeolocatorField::rollback($revision, $field);
                break;
            case Field::_DOCUMENTS:
                DocumentsField::rollback($revision, $field);
                break;
            case Field::_GALLERY:
                GalleryField::rollback($revision, $field);
                break;
            case Field::_3D_MODEL:
                ModelField::rollback($revision, $field);
                break;
            case Field::_PLAYLIST:
                PlaylistField::rollback($revision, $field);
                break;
            case Field::_VIDEO:
                VideoField::rollback($revision, $field);
                break;
            case Field::_ASSOCIATOR:
                AssociatorField::rollback($revision, $field);
                break;
            case Field::_COMBO_LIST:
                ComboListField::rollback($revision, $field);
                break;
        }
    }

    //////////////////
    //RECORD PRESETS//
    //////////////////

    /**
     * Get the arrayed version of the field data to store in a record preset.
     *
     * @param  Field $field - Field to convert
     * @param  Record $record - Record to get specific field instance
     * @param  array $data - The data array representing the record preset
     * @param  array $flidArray - The flid to add to field array
     * @return array - Contains both the updated $data and $flidArray arrays
     */
    public static function getRecordPresetArray($field, $record, $data, $flidArray) {
        switch($field->type) {
            case self::_TEXT:
                return TextField::getRecordPresetArray($field, $record, $data, $flidArray);
                break;
            case self::_RICH_TEXT:
                return RichTextField::getRecordPresetArray($field, $record, $data, $flidArray);
                break;
            case self::_NUMBER:
                return NumberField::getRecordPresetArray($field, $record, $data, $flidArray);
                break;
            case self::_LIST:
                return ListField::getRecordPresetArray($field, $record, $data, $flidArray);
                break;
            case self::_MULTI_SELECT_LIST:
                return MultiSelectListField::getRecordPresetArray($field, $record, $data, $flidArray);
                break;
            case self::_GENERATED_LIST:
                return GeneratedListField::getRecordPresetArray($field, $record, $data, $flidArray);
                break;
            case self::_DATE:
                return DateField::getRecordPresetArray($field, $record, $data, $flidArray);
                break;
            case self::_SCHEDULE:
                return ScheduleField::getRecordPresetArray($field, $record, $data, $flidArray);
                break;
            case self::_GEOLOCATOR:
                return GeolocatorField::getRecordPresetArray($field, $record, $data, $flidArray);
                break;
            case self::_DOCUMENTS:
                return DocumentsField::getRecordPresetArray($field, $record, $data, $flidArray);
                break;
            case self::_GALLERY:
                return GalleryField::getRecordPresetArray($field, $record, $data, $flidArray);
                break;
            case self::_3D_MODEL:
                return ModelField::getRecordPresetArray($field, $record, $data, $flidArray);
                break;
            case self::_PLAYLIST:
                return PlaylistField::getRecordPresetArray($field, $record, $data, $flidArray);
                break;
            case self::_VIDEO:
                return VideoField::getRecordPresetArray($field, $record, $data, $flidArray);
                break;
            case self::_COMBO_LIST:
                return ComboListField::getRecordPresetArray($field, $record, $data, $flidArray);
                break;
            case self::_ASSOCIATOR:
                return AssociatorField::getRecordPresetArray($field, $record, $data, $flidArray);
                break;
            default: // Error occurred.
                throw new \Exception("Invalid field type in field::field option.");
                break;
        }
    }

    //////////
    //EXPORT//
    //////////

    /**
     * Provides an example of the field's structure in an export to help with importing records.
     *
     * @param  Field $field - Field to build example
     * @param  string $expType - Type of export
     * @return string - The example
     */
    public static function getExportSample($field, $expType) {
        $field_type = $field->type;

        switch ($field_type) {
            case self::_TEXT:
                return TextField::getExportSample($field, $expType);
                break;
            case self::_RICH_TEXT:
                return RichTextField::getExportSample($field, $expType);
                break;
            case self::_NUMBER:
                return NumberField::getExportSample($field, $expType);
                break;
            case self::_LIST:
                return ListField::getExportSample($field, $expType);
                break;
            case self::_MULTI_SELECT_LIST:
                return MultiSelectListField::getExportSample($field, $expType);
                break;
            case self::_GENERATED_LIST:
                return GeneratedListField::getExportSample($field, $expType);
                break;
            case self::_DATE:
                return DateField::getExportSample($field, $expType);
                break;
            case self::_SCHEDULE:
                return ScheduleField::getExportSample($field, $expType);
                break;
            case self::_GEOLOCATOR:
                return GeolocatorField::getExportSample($field, $expType);
                break;
            case self::_DOCUMENTS:
                return DocumentsField::getExportSample($field, $expType);
                break;
            case self::_GALLERY:
                return GalleryField::getExportSample($field, $expType);
                break;
            case self::_3D_MODEL:
                return ModelField::getExportSample($field, $expType);
                break;
            case self::_PLAYLIST:
                return PlaylistField::getExportSample($field, $expType);
                break;
            case self::_VIDEO:
                return VideoField::getExportSample($field, $expType);
                break;
            case self::_COMBO_LIST:
                return ComboListField::getExportSample($field, $expType);
                break;
            case self::_ASSOCIATOR:
                return AssociatorField::getExportSample($field, $expType);
                break;
            default: // Error occurred.
                throw new \Exception("Invalid field type in field::field option.");
                break;
        }
    }

    //////////
    //SEARCH//
    //////////

    /**
     * Performs a keyword search on this field and returns any results.
     *
     * @param  string $arg - The keywords
     * @param  string $method - Type of keyword search
     * @return Collection - The RIDs that match search
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

                if(is_numeric($arg)) { // Only search if we're working with a number.
                    $arg = floatval($arg);

                    return DB::table("number_fields")
                        ->select("rid")
                        ->where("fid", "=", $this->fid)
                        ->whereBetween("number", [$arg - NumberField::EPSILON, $arg + NumberField::EPSILON])
                        ->distinct();
                } else {
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
     * Helps with keyword search for file typed fields.
     *
     * @param  string $arg - The keywords
     * @param  string $method - Type of keyword search
     * @return string - Updated keyword search
     */
    private static function processArgumentForFileField($arg, $method) {
        // We only want to match with actual data in the name field
        if($method == Search::SEARCH_EXACT) {
            $arg = rtrim($arg, '"');
            $arg .= "[Name]\"";
        } else {
            $args = explode(" ", $arg);

            foreach($args as &$arg) {
                $arg .= "[Name]";
            }
            $arg = implode(" ",$args);
        }

        return $arg;
    }

    /**
     * Performs an advanced search on this field and returns any results.
     *
     * @param  int $flid - Field ID
     * @param  string $fieldType - Field type
     * @param  array $query - The advance search user query
     * @return Builder - The RIDs that match search
     */
    public static function advancedSearch($flid, $fieldType, array $query) {
        switch($fieldType) {
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

    /**
     * Checks if the field has any value in order to be sorted.
     *
     * @param  Field $field - Field to check
     * @param  int $rid - Record ID
     * @param  array $newOrderArray - DESCRIPTION
     * @param  array $noSortValue - DESCRIPTION
     * @return array - The two modified arrays
     */
    public static function hasValueToSort($field, $rid, $newOrderArray, $noSortValue) {
        switch($field->type) {
            case self::_TEXT:
                $tf = TextField::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first();
                if(is_null($tf))
                    array_push($noSortValue, $rid);
                else
                    $newOrderArray[$rid] = $tf->text;
                break;
            case self::_LIST:
                $lf = ListField::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first();
                if(is_null($lf))
                    array_push($noSortValue, $rid);
                else
                    $newOrderArray[$rid] = $lf->option;
                break;
            case self::_NUMBER:
                $nf = NumberField::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first();
                if(is_null($nf))
                    array_push($noSortValue, $rid);
                else
                    $newOrderArray[$rid] = $nf->number;
                break;
            case self::_DATE:
                $df = DateField::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first();
                if(is_null($df))
                    array_push($noSortValue, $rid);
                else
                    $newOrderArray[$rid] = \DateTime::createFromFormat("Y-m-d", $df->year . "-" . $df->month . "-" . $df->day);;
                break;
            default:
                throw new \Exception("Invalid field type in field::has sort.");
                break;
        }

        return array($newOrderArray, $noSortValue);
    }

    ///////////////
    //RESTFUL API//
    ///////////////

    /**
     * Updates the request for an API search to mimic the advanced search structure.
     *
     * @param  array $data - Data from the search
     * @param  Field $field - DESCRIPTION
     * @param  Request $request
     * @return Request - The update request
     */
    public static function setRestfulAdvSearch($data, $field, $request) {
        switch($field->type) {
            case self::_TEXT:
                return TextField::setRestfulAdvSearch($data, $field, $request);
                break;
            case self::_RICH_TEXT:
                return RichTextField::setRestfulAdvSearch($data, $field, $request);
                break;
            case self::_NUMBER:
                return NumberField::setRestfulAdvSearch($data, $field, $request);
                break;
            case self::_LIST:
                return ListField::setRestfulAdvSearch($data, $field, $request);
                break;
            case self::_MULTI_SELECT_LIST:
                return MultiSelectListField::setRestfulAdvSearch($data, $field, $request);
                break;
            case self::_GENERATED_LIST:
                return GeneratedListField::setRestfulAdvSearch($data, $field, $request);
                break;
            case self::_DATE:
                return DateField::setRestfulAdvSearch($data, $field, $request);
                break;
            case self::_SCHEDULE:
                return ScheduleField::setRestfulAdvSearch($data, $field, $request);
                break;
            case self::_GEOLOCATOR:
                return GeolocatorField::setRestfulAdvSearch($data, $field, $request);
                break;
            case self::_DOCUMENTS:
                return DocumentsField::setRestfulAdvSearch($data, $field, $request);
                break;
            case self::_GALLERY:
                return GalleryField::setRestfulAdvSearch($data, $field, $request);
                break;
            case self::_3D_MODEL:
                return ModelField::setRestfulAdvSearch($data, $field, $request);
                break;
            case self::_PLAYLIST:
                return PlaylistField::setRestfulAdvSearch($data, $field, $request);
                break;
            case self::_VIDEO:
                return VideoField::setRestfulAdvSearch($data, $field, $request);
                break;
            case self::_COMBO_LIST:
                return ComboListField::setRestfulAdvSearch($data, $field, $request);
                break;
            case self::_ASSOCIATOR:
                return AssociatorField::setRestfulAdvSearch($data, $field, $request);
                break;
            default: // Error occurred.
                throw new \Exception("Invalid field type in field::field option.");
                break;
        }
    }

    /**
     * Updates the request for an API to mimic record creation .
     *
     * @param  Field $field - Field to add data to for record
     * @param  int $flid - Field ID
     * @param  Request $recRequest
     * @param  int $uToken - Custom generated user token for file fields and tmp folders
     * @return Request - The update request
     */
    public static function setRestfulRecordData($field, $flid, $recRequest, $uToken) {
        switch($field->type) {
            case self::_TEXT:
                return TextField::setRestfulRecordData($field, $flid, $recRequest);
                break;
            case self::_RICH_TEXT:
                return RichTextField::setRestfulRecordData($field, $flid, $recRequest);
                break;
            case self::_NUMBER:
                return NumberField::setRestfulRecordData($field, $flid, $recRequest);
                break;
            case self::_LIST:
                return ListField::setRestfulRecordData($field, $flid, $recRequest);
                break;
            case self::_MULTI_SELECT_LIST:
                return MultiSelectListField::setRestfulRecordData($field, $flid, $recRequest);
                break;
            case self::_GENERATED_LIST:
                return GeneratedListField::setRestfulRecordData($field, $flid, $recRequest);
                break;
            case self::_DATE:
                return DateField::setRestfulRecordData($field, $flid, $recRequest);
                break;
            case self::_SCHEDULE:
                return ScheduleField::setRestfulRecordData($field, $flid, $recRequest);
                break;
            case self::_GEOLOCATOR:
                return GeolocatorField::setRestfulRecordData($field, $flid, $recRequest);
                break;
            case self::_DOCUMENTS:
                return DocumentsField::setRestfulRecordData($field, $flid, $recRequest, $uToken);
                break;
            case self::_GALLERY:
                return GalleryField::setRestfulRecordData($field, $flid, $recRequest, $uToken);
                break;
            case self::_3D_MODEL:
                return ModelField::setRestfulRecordData($field, $flid, $recRequest, $uToken);
                break;
            case self::_PLAYLIST:
                return PlaylistField::setRestfulRecordData($field, $flid, $recRequest, $uToken);
                break;
            case self::_VIDEO:
                return VideoField::setRestfulRecordData($field, $flid, $recRequest, $uToken);
                break;
            case self::_COMBO_LIST:
                return ComboListField::setRestfulRecordData($field, $flid, $recRequest);
                break;
            case self::_ASSOCIATOR:
                return AssociatorField::setRestfulRecordData($field, $flid, $recRequest);
                break;
            default: // Error occurred.
                throw new \Exception("Invalid field type in field::field option.");
                break;
        }
    }
}

