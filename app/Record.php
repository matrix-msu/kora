<?php namespace App;

use App\Http\Controllers\RecordController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class Record extends Model {

    //TODO::CASTLE
    /*
    |--------------------------------------------------------------------------
    | Record
    |--------------------------------------------------------------------------
    |
    | This model represents the data for a Record
    |
    */

    /**
     * @var array - Attributes that can be mass assigned to model
     */
    protected $fillable = [
        'id',
        'pid',
        'fid',
        'owner',
        'kid'
    ];

    /**
     * @var string - Database column that represents the primary key
     */
    protected $primaryKey = "rid";

    /**
     * Returns the Preset associated with a Record.
     *
     * @return BelongsTo
     */
    public function preset() {
        return $this->belongsTo('App/Preset');
    }

    /**
     * Returns the form associated with a Record.
     *
     * @return BelongsTo
     */
    public function form() {
        return $this->belongsTo('App\Form', 'fid');
    }

    /**
     * Returns the text fields associated with a Record.
     *
     * @return HasMany
     */
    public function textfields() {
        return $this->hasMany('App\TextField', 'rid');
    }

    /**
     * Returns the rich text fields associated with a Record.
     *
     * @return HasMany
     */
    public function richtextfields() {
        return $this->hasMany('App\RichTextField', 'rid');
    }

    /**
     * Returns the number fields associated with a Record.
     *
     * @return HasMany
     */
    public function numberfields() {
        return $this->hasMany('App\NumberField', 'rid');
    }

    /**
     * Returns the list fields associated with a Record.
     *
     * @return HasMany
     */
    public function listfields() {
        return $this->hasMany('App\ListField', 'rid');
    }

    /**
     * Returns the multi-select list fields associated with a Record.
     *
     * @return HasMany
     */
    public function multiselectlistfields() {
        return $this->hasMany('App\MultiSelectListField', 'rid');
    }

    /**
     * Returns the generated list fields associated with a Record.
     *
     * @return HasMany
     */
    public function generatedlistfields() {
        return $this->hasMany('App\GeneratedListField', 'rid');
    }

    /**
     * Returns the combo list fields associated with a Record.
     *
     * @return HasMany
     */
    public function combolistfields() {
        return $this->hasMany('App\ComboListField', 'rid');
    }

    /**
     * Returns the date fields associated with a Record.
     *
     * @return HasMany
     */
    public function datefields() {
        return $this->hasMany('App\DateField', 'rid');
    }

    /**
     * Returns the schedule fields associated with a Record.
     *
     * @return HasMany
     */
    public function schedulefields() {
        return $this->hasMany('App\ScheduleField', 'rid');
    }

    /**
     * Returns the geolocator fields associated with a Record.
     *
     * @return HasMany
     */
    public function geolocatorfields() {
        return $this->hasMany('App\GeolocatorField', 'rid');
    }

    /**
     * Returns the documents fields associated with a Record.
     *
     * @return HasMany
     */
    public function documentsfields() {
        return $this->hasMany('App\DocumentsField', 'rid');
    }

    /**
     * Returns the gallery fields associated with a Record.
     *
     * @return HasMany
     */
    public function galleryfields() {
        return $this->hasMany('App\GalleryField', 'rid');
    }

    /**
     * Returns the playlist fields associated with a Record.
     *
     * @return HasMany
     */
    public function playlistfields() {
        return $this->hasMany('App\PlaylistField', 'rid');
    }

    /**
     * Returns the video fields associated with a Record.
     *
     * @return HasMany
     */
    public function videofields() {
        return $this->hasMany('App\VideoField', 'rid');
    }

    /**
     * Returns the model fields associated with a Record.
     *
     * @return HasMany
     */
    public function modelfields() {
        return $this->hasMany('App\ModelField', 'rid');
    }

    /**
     * Returns the associator fields associated with a Record.
     *
     * @return HasMany
     */
    public function associatorfields() {
        return $this->hasMany('App\AssociatorField', 'rid');
    }

    /**
     * Returns the owner associated with a Record.
     *
     * @return HasOne
     */
    public function owner() {
        return $this->hasOne('App\User', 'owner');
    }

    /**
     * Deletes all data fields belonging to a record, then deletes self.
     */
    public function delete() {
        BaseField::deleteBaseFieldsByRID($this->rid);

        //Delete reverse associations for everyones sake
        DB::table(AssociatorField::SUPPORT_NAME)
            ->where("record", "=", $this->rid)
            ->delete();

        parent::delete();
    }

    /**
     * Determines if the record is a record preset.
     *
     * @return bool - Is a preset
     */
    public function isPreset() {
        return (RecordPreset::where('rid',$this->rid)->count()>0);
    }

    /**
     * Determines if a string is a KID pattern.
     * For reference, the KID pattern is PID-FID-RID, i.e. three sets of integers separated by hyphens.
     *
     * @param $string - Kora ID to validate
     * @return bool - Matches pattern
     */
    public static function isKIDPattern($string) {
        $pattern = "/^([0-9]+)-([0-9]+)-([0-9]+)/"; // Match exactly with KID pattern.
        return preg_match($pattern, $string) != false;
    }

    /**
     * Gets a list of records that associate to this record
     *
     * @return array - Records that associate it
     */
    public function getAssociatedRecords() {
        $assoc = DB::table(AssociatorField::SUPPORT_NAME)
            ->select("rid")
            ->distinct()
            ->where('record','=',$this->rid)->get();
        $records = array();
        foreach($assoc as $af) {
            $rid = $af->rid;
            $rec = RecordController::getRecord($rid);
            array_push($records,$rec);
        }

        return $records;
    }

    /**
     * Gets a preview value for the record when displaying in a reverse association.
     *
     * @return string - The preview value
     */
    public function getReversePreview() {
        $form = $this->form()->first();

        $firstPage = Page::where('fid','=',$form->fid)->where('sequence','=',0)->first();
        $firstField = Field::where('page_id','=',$firstPage->id)->where('sequence','=',0)->first();

        $value = AssociatorField::previewData($firstField->flid, $this->rid, $firstField->type);

        if(!is_null($value) && $value!='')
            return $value;
        else
            return 'No Preview Field Available';
    }
}

