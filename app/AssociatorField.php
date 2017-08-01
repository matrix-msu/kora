<?php namespace App;

use App\Http\Controllers\FieldController;
use App\Http\Controllers\RecordController;
use App\Http\Controllers\RevisionController;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssociatorField extends BaseField {

    /*
    |--------------------------------------------------------------------------
    | Associator Field
    |--------------------------------------------------------------------------
    |
    | This model represents the associator field in Kora3
    |
    */

    /**
     * @var string - Support table name
     */
    const SUPPORT_NAME = "associator_support";
    /**
     * @var string - Views for the typed field options
     */
    const FIELD_OPTIONS_VIEW = "fields.options.associator";
    const FIELD_ADV_OPTIONS_VIEW = "partials.field_option_forms.associator";

    /**
     * @var array - Attributes that can be mass assigned to model
     */
    protected $fillable = [
        'rid',
        'flid',
        'records'
    ];

    /**
     * Get the field options view.
     *
     * @return string - The view
     */
    public function getFieldOptionsView() {
        return self::FIELD_OPTIONS_VIEW;
    }

    /**
     * Get the field options view for advanced field creation.
     *
     * @return string - The view
     */
    public function getAdvancedFieldOptionsView() {
        return self::FIELD_ADV_OPTIONS_VIEW;
    }

    /**
     * Gets the default options string for a new field.
     *
     * @param  Request $request
     * @return string - The default options
     */
    public function getDefaultOptions(Request $request) {
        return '[!SearchForms!][!SearchForms!]';
    }

    /**
     * Update the options for a field
     *
     * @param  Field $field - Field to update options
     * @param  Request $request
     * @param  bool $return - Are we returning an error by string or redirect
     * @return mixed - The result
     */
    public function updateOptions($field, Request $request, $return=true) {
        if(is_null($request->default)) {
            $default = '';
        } else {
            $reqDefs = array_values(array_unique($request->default));
            $default = implode('[!]',$reqDefs);
        }

        $field->updateRequired($request->required);
        $field->updateSearchable($request);
        $field->updateDefault($default);
        $field->updateOptions('SearchForms', $request->searchforms);

        if($return) {
            flash()->overlay(trans('controller_field.optupdate'), trans('controller_field.goodjob'));
            return redirect('projects/' . $field->pid . '/forms/' . $field->fid . '/fields/' . $field->flid . '/options');
        } else {
            return '';
        }
    }

    /**
     * Creates a typed field to store record data.
     *
     * @param  Field $field - The field to represent record data
     * @param  Record $record - Record being created
     * @param  string $value - Data to add
     * @param  Request $request
     */
    public function createNewRecordField($field, $record, $value, $request){
        $this->flid = $field->flid;
        $this->rid = $record->rid;
        $this->fid = $field->fid;
        $this->save();
        $this->addRecords($value);
    }

    /**
     * Edits a typed field that has record data.
     *
     * @param  string $value - Data to add
     * @param  Request $request
     */
    public function editRecordField($value, $request) {
        if(!is_null($this) && !is_null($value)) {
            $this->updateRecords($value);
        } else if(!is_null($this) && is_null($value)) {
            $this->delete();
            $this->deleteRecords();
        }
    }

    /**
     * Takes data from a mass assignment operation and applies it to an individual field.
     *
     * @param  Field $field - The field to represent record data
     * @param  Record $record - Record being written to
     * @param  String $formFieldValue - The value to be assigned
     * @param  Request $request
     * @param  bool $overwrite - Overwrite if data exists
     */
    public function massAssignRecordField($field, $record, $formFieldValue, $request, $overwrite=0) {
        $matching_record_fields = $record->associatorfields()->where("flid", '=', $field->flid)->get();
        $record->updated_at = Carbon::now();
        $record->save();
        if($matching_record_fields->count() > 0) {
            $associatorfield = $matching_record_fields->first();
            if($overwrite == true || $associatorfield->hasRecords()) {
                $revision = RevisionController::storeRevision($record->rid, 'edit');
                $associatorfield->updateRecords($formFieldValue);
                $associatorfield->save();
                $revision->oldData = RevisionController::buildDataArray($record);
                $revision->save();
            }
        } else {
            $this->createNewRecordField($field, $record, $formFieldValue, $request);
            $revision = RevisionController::storeRevision($record->rid, 'edit');
            $revision->oldData = RevisionController::buildDataArray($record);
            $revision->save();
        }
    }

    /**
     * For a test record, add test data to field.
     *
     * @param  Field $field - The field to represent record data
     * @param  Record $record - Test record being created
     */
    public function createTestRecordField($field, $record) {
        $this->flid = $field->flid;
        $this->rid = $record->rid;
        $this->fid = $field->fid;
        $this->save();

        $this->addRecords(array('1-3-37','1-3-37','1-3-37','1-3-37'));
    }

    /**
     * Validates the record data for a field against the field's options.
     *
     * @param  Field $field - The
     * @param  mixed $value - Record data
     * @param  Request $request
     * @return string - Potential error message
     */
    public function validateField($field, $value, $request) {
        $req = $field->required;

        if($req==1 && ($value==null | $value==""))
            return $field->name.trans('fieldhelpers_val.req');
    }

    /**
     * Performs a rollback function on an individual field's record data.
     *
     * @param  Field $field - The field being rolled back
     * @param  Revision $revision - The revision being rolled back
     * @param  bool $exists - Field for record exists
     */
    public function rollbackField($field, Revision $revision, $exists=true) {
        if(!is_array($revision->data))
            $revision->data = json_decode($revision->data, true);

        if(is_null($revision->data[Field::_ASSOCIATOR][$field->flid]['data']))
            return null;

        // If the field doesn't exist or was explicitly deleted, we create a new one.
        if($revision->type == Revision::DELETE || !$exists) {
            $this->flid = $field->flid;
            $this->fid = $revision->fid;
            $this->rid = $revision->rid;
        }

        $this->save();
        $updated = explode('[!]',$revision->data[Field::_ASSOCIATOR][$field->flid]['data']);
        $this->updateRecords($updated);
    }

    /**
     * Get the arrayed version of the field data to store in a record preset.
     *
     * @param  array $data - The data array representing the record preset
     * @param  bool $exists - Typed field exists and has data
     * @return array - The updated $data
     */
    public function getRecordPresetArray($data, $exists=true) {
        if($exists)
            $data['records'] = explode('[!]', $this->records);
        else
            $data['records'] = null;

        return $data;
    }

    /**
     * Get the required information for a revision data array.
     *
     * @param  Field $field - Optional field to get storage options for certain typed fields
     * @return mixed - The revision data
     */
    public function getRevisionData($field = null) {
        $pieces = array();
        $records = $this->records()->get();
        foreach($records as $record) {
            $rid = $record->record;
            $model = RecordController::getRecord($rid);
            array_push($pieces,$model->kid);
        }

        $formatted = implode("[!]", $pieces);
        return $formatted;
    }

    /**
     * Provides an example of the field's structure in an export to help with importing records.
     *
     * @param  string $slug - Field nickname
     * @param  string $expType - Type of export
     * @return mixed - The example
     */
    public function getExportSample($slug,$type) {
        switch($type) {
            case "XML":
                //TODO::add sample
                break;
            case "JSON":
                //TODO::add sample
                break;
        }
    }

    /**
     * Updates the request for an API search to mimic the advanced search structure.
     *
     * @param  array $data - Data from the search
     * @param  int $flid - Field ID
     * @param  Request $request
     * @return Request - The update request
     */
    public function setRestfulAdvSearch($data, $flid, $request) {
        $request->request->add([$flid.'_input' => $data->input]);

        return $request;
    }

    /**
     * Updates the request for an API to mimic record creation .
     *
     * @param  array $jsonField - JSON representation of field data
     * @param  int $flid - Field ID
     * @param  Request $recRequest
     * @param  int $uToken - Custom generated user token for file fields and tmp folders
     * @return Request - The update request
     */
    public function setRestfulRecordData($jsonField, $flid, $recRequest, $uToken=null) {
        $recRequest[$flid] = $jsonField->records;

        return $recRequest;
    }

    /**
     * Performs a keyword search on this field and returns any results.
     *
     * @param  int $fid - Form ID
     * @param  string $arg - The keywords
     * @param  string $method - Type of keyword search
     * @return Builder - The RIDs that match search
     */
    public function keywordSearchTyped($fid, $arg, $method) {
        return DB::table(self::SUPPORT_NAME)
            ->select("rid")
            ->where("fid", "=", $fid)
            ->whereRaw("MATCH (`record`) AGAINST (? IN BOOLEAN MODE)", [$arg])
            ->distinct();
    }

    /**
     * Performs an advanced search on this field and returns any results.
     *
     * @param  int $flid - Field ID
     * @param  array $query - The advance search user query
     * @return Builder - The RIDs that match search
     */
    public function getAdvancedSearchQuery($flid, $query) {
        $inputs = $query[$flid."_input"];

        $query = DB::table(self::SUPPORT_NAME)
            ->select("rid")
            ->where("flid", "=", $flid);

        self::buildAdvancedAssociatorQuery($query, $inputs);

        return $query->distinct();
    }

    /**
     * Build the advanced search query for an associator's list of records.
     *
     * @param  Builder $dbQuery - Pointer to current query
     * @param  array $inputs - Values of the list
     */
    private static function buildAdvancedAssociatorQuery(Builder &$dbQuery, $inputs) {
        $dbQuery->where(function($dbQuery) use ($inputs) {
            foreach($inputs as $input) {
                $rid = explode('-',$input)[2];
                $dbQuery->orWhereRaw("MATCH (`record`) AGAINST (? IN BOOLEAN MODE)",
                    [Search::processArgument($rid, Search::ADVANCED_METHOD)]);
            }
        });
    }

    ///////////////////////////////////////////////END ABSTRACT FUNCTIONS///////////////////////////////////////////////

    /**
     * Gets the default values for an associator field.
     *
     * @param  Field $field - Field to pull defaults from
     * @return array - The defaults
     */
    public static function getAssociatorList($field) {
        $def = $field->default;
        return self::getListOptionsFromString($def);
    }

    /**
     * Overrides the delete function to first delete support fields.
     */
    public function delete() {
        $this->deleteRecords();
        parent::delete();
    }

    /**
     * Returns the records for a record's associator value.
     *
     * @return Builder - Query of values
     */
    public function records() {
        return DB::table(self::SUPPORT_NAME)->select("*")
            ->where("flid", "=", $this->flid)
            ->where("rid", "=", $this->rid);
    }

    /**
     * Determine if this field has data in the support table.
     *
     * @return bool - Has data
     */
    public function hasRecords() {
        return !! $this->records()->count();
    }

    /**
     * Adds records to the support table.
     *
     * @param  array $records - Records to add
     */
    public function addRecords(array $records) {
        $now = date("Y-m-d H:i:s");
        foreach($records as $record) {
            $recInfo = explode("-",$record);

            DB::table(self::SUPPORT_NAME)->insert(
                [
                    'rid' => $this->rid,
                    'fid' => $this->fid,
                    'flid' => $this->flid,
                    'record' => $recInfo[2],
                    'created_at' => $now,
                    'updated_at' => $now
                ]
            );
        }
    }

    /**
     * Updates the current list of records by deleting the old ones and adding the array that has both new and old.
     *
     * @param  array $records - Records to add
     */
    public function updateRecords(array $records) {
        $this->deleteRecords();
        $this->addRecords($records);
    }

    /**
     * Deletes records from the support table.
     */
    public function deleteRecords() {
        DB::table(self::SUPPORT_NAME)
            ->where("rid", "=", $this->rid)
            ->where("flid", "=", $this->flid)
            ->delete();
    }

    /**
     * For a record that was searched for in an associator, grab the data of the field that was assigned as a preview
     * for this record.
     *
     * @param  int $rid - Record ID
     * @return string - Html structure of the preview field's value
     */
    public function getPreviewValues($rid) {
        //individual kid elements
        $recModel = RecordController::getRecord($rid);
        $pid = $recModel->pid;
        $fid = $recModel->fid;
        $rid = $recModel->rid;
        $kid = $recModel->kid;

        //get the preview flid structure of this associator
        $activeForms = array();
        $field = FieldController::getField($this->flid);
        $option = FieldController::getFieldOption($field,'SearchForms');
        if($option!='') {
            $options = explode('[!]',$option);

            foreach($options as $opt) {
                $opt_fid = explode('[fid]',$opt)[1];
                $opt_search = explode('[search]',$opt)[1];
                $opt_flids = explode('[flids]',$opt)[1];
                $opt_flids = explode('-',$opt_flids);

                if($opt_search == 1)
                    $flids = array();

                foreach($opt_flids as $flid) {
                    $field = FieldController::getField($flid);
                    $flids[$flid] = $field->type;
                }
                $activeForms[$opt_fid] = ['flids' => $flids];
            }
        }

        //grab the preview fields associated with the form of this kid
        //make sure one is selected first
        $preview = array();
        if(isset($activeForms[$fid])) {
            $details = $activeForms[$fid];
            foreach($details['flids'] as $flid => $type) {
                if($type == Field::_TEXT) {
                    $text = TextField::where("flid", "=", $flid)->where("rid", "=", $rid)->first();
                    if($text->text != '')
                        array_push($preview, $text->text);
                } else if($type == Field::_LIST) {
                    $list = ListField::where("flid", "=", $flid)->where("rid", "=", $rid)->first();
                    if($list->option != '')
                        array_push($preview, $list->option);
                }
            }
        } else {
            array_push($preview, "NO PREVIEW AVAILABLE");
        }

        $html = "<a href='".env('BASE_URL')."projects/".$pid."/forms/".$fid."/records/".$rid."'>".$kid."</a>";

        foreach($preview as $val) {
            $html .= " | ".$val;
        }

        return $html;
    }
}
