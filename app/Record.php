<?php namespace App;

use App\Http\Controllers\RecordController;
use App\KoraFields\AssociatorField;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class Record extends Model {

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
        'kid',
        'legacy_kid',
        'project_id',
        'form_id',
        'owner'
    ];

    public function __construct(array $attributes = array(), $fid = null) {
        parent::__construct($attributes);
        $this->table = "records_$fid";
    }

    public function getTable() {
        //The second case is where we make a new model. The constructor has built the table for us
        //The first case is where we pull Record objs from the DB, they don't pass in a Form ID, but since they exist,
            //we will just use the one assigned to the data.

        if($this->table=='records_')
            return 'records_'.$this->form_id;
        else
            return parent::getTable();
    }

    /**
     * Returns the form associated with a Record.
     *
     * @return BelongsTo
     */
    public function form() {
        return $this->belongsTo('App\Form', 'form_id');
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
        //Delete reverse associations for everyone's sake
        DB::table(AssociatorField::Reverse_Cache_Table)
            ->where('associated_kid','=',$this->kid)
            ->orWhere('source_kid','=',$this->kid)
            ->delete();

        parent::delete();
    }

    /**
     * Determines if the record is a record preset.
     *
     * @return bool - Is a preset
     */
    public function isPreset() {
        return (RecordPreset::where('record_kid',$this->kid)->count()>0);
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
     * Gets a list of records that associate to this record along with preview data
     *
     * @return array - Records that associate it
     */
    public function getAssociatedRecordData() {
        $assoc = DB::table(AssociatorField::Reverse_Cache_Table)
            ->distinct()
            ->where('associated_kid','=',$this->kid)->get();

        $records = $formToLayout = array();
        foreach($assoc as $af) {
            $kid = $af->source_kid;
            $rec = RecordController::getRecord($kid);
            $recData = [
                'kid' => $kid,
                'id' => $rec->id,
                'form_id' => $rec->form_id,
                'project_id' => $rec->project_id,
            ];

            $fid = $af->source_form_id;
            if(!array_key_exists($fid, $formToLayout)) {
                $form = $rec->form()->first();
                $layout = $form->layout;
                $formToLayout[$fid] = $layout;
            } else {
                $layout = $formToLayout[$fid];
            }

            $recData['preview'] = 'No Preview Field Available';
            foreach($layout['pages'] as $page) {
                foreach($page['flids'] as $flid) {
                    $field = $layout['fields'][$flid];

                    //Can this field be previewed?
                    if(!in_array($field['type'], Form::$validAssocFields) | is_null($rec->{$flid}))
                        continue;

                    $recData['preview'] = $rec->{$flid};
                    break 2;
                }
            }

            array_push($records,$recData);
        }

        return $records;
    }

    /**
     * Gets a number of records that associate to this record
     *
     * @return int - Number of records that associate it
     */
    public function getAssociatedRecordsCount() {
        return DB::table(AssociatorField::Reverse_Cache_Table)
            ->distinct()
            ->where('associated_kid','=',$this->kid)->count();
    }
}

