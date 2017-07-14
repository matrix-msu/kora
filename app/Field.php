<?php namespace App;

use App\Http\Controllers\FieldController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\RevisionController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
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
     */ //TODO::static? File typed?
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

