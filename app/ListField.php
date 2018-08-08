<?php namespace App;

use App\Http\Controllers\FieldController;
use App\Http\Controllers\RevisionController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class ListField extends BaseField {

    /*
    |--------------------------------------------------------------------------
    | List Field
    |--------------------------------------------------------------------------
    |
    | This model represents the list field in Kora3
    |
    */

    /**
     * @var string - Views for the typed field options
     */
    const FIELD_OPTIONS_VIEW = "partials.fields.options.list";
    const FIELD_ADV_OPTIONS_VIEW = "partials.fields.advanced.list";
    const FIELD_ADV_INPUT_VIEW = "partials.records.advanced.list";
    const FIELD_INPUT_VIEW = "partials.records.input.list";
    const FIELD_DISPLAY_VIEW = "partials.records.display.list";

    /**
     * @var array - Attributes that can be mass assigned to model
     */
    protected $fillable = [
        'rid',
        'flid',
        'option'
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
        return '[!Options!][!Options!]';
    }

    /**
     * Gets an array of all the fields options.
     *
     * @param  Field $field
     * @return array - The options array
     */
    public function getOptionsArray(Field $field) {
        $options = array();

        $options['Options'] = explode('[!]',FieldController::getFieldOption($field, 'Options'));

        return $options;
    }

    /**
     * Update the options for a field
     *
     * @param  Field $field - Field to update options
     * @param  Request $request
     * @return Redirect
     */
    public function updateOptions($field, Request $request) {
        $reqOpts = $request->options;
        $options = $reqOpts[0];
        if(!is_null($options)) {
            for($i = 1; $i < sizeof($reqOpts); $i++) {
                $options .= '[!]' . $reqOpts[$i];
            }
        }

        $field->updateRequired($request->required);
        $field->updateSearchable($request);
        $field->updateDefault($request->default);
        $field->updateOptions('Options', $options);

        return redirect('projects/' . $field->pid . '/forms/' . $field->fid . '/fields/' . $field->flid . '/options')
            ->with('k3_global_success', 'field_options_updated');
    }

    /**
     * Creates a typed field to store record data.
     *
     * @param  Field $field - The field to represent record data
     * @param  Record $record - Record being created
     * @param  string $value - Data to add
     * @param  Request $request
     */
    public function createNewRecordField($field, $record, $value, $request) {
        $this->flid = $field->flid;
        $this->rid = $record->rid;
        $this->fid = $field->fid;
        $this->option = $value;
        $this->save();
    }

    /**
     * Edits a typed field that has record data.
     *
     * @param  string $value - Data to add
     * @param  Request $request
     */
    public function editRecordField($value, $request) {
        if(!is_null($this) && !is_null($value)) {
            $this->option = $value;
            $this->save();
        } else if(!is_null($this) && is_null($value)) {
            $this->delete();
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
        $matching_record_fields = $record->listfields()->where("flid", '=', $field->flid)->get();
        $record->updated_at = Carbon::now();
        $record->save();
        if($matching_record_fields->count() > 0) {
            $listfield = $matching_record_fields->first();
            if($overwrite == true || $listfield->option == "" || is_null($listfield->option)) {
                $revision = RevisionController::storeRevision($record->rid, Revision::EDIT);
                $listfield->option = $formFieldValue;
                $listfield->save();
                $revision->data = RevisionController::buildDataArray($record);
                $revision->save();
            }
        } else {
            $this->createNewRecordField($field, $record, $formFieldValue, $request);
            $revision = RevisionController::storeRevision($record->rid, Revision::EDIT);
            $revision->data = RevisionController::buildDataArray($record);
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
        $this->option = 'K3TR';
        $this->save();
    }

    /**
     * Validates the record data for a field against the field's options.
     *
     * @param  Field $field - The field to validate
     * @param  Request $request
     * @param  bool $forceReq - Do we want to force a required value even if the field itself is not required?
     * @return array - Array of errors
     */
    public function validateField($field, $request, $forceReq = false) {
        $req = $field->required;
        $value = $request->{$field->flid};
        $list = ListField::getList($field);

        if(($req==1 | $forceReq) && ($value==null | $value==""))
            return ['list'.$field->flid.'_chosen' => $field->name.' is required'];

        if($value!='' && !in_array($value,$list))
            return ['list'.$field->flid.'_chosen' => $field->name.' has an invalid value not in the list'];

        return array();
    }

    /**
     * Performs a rollback function on an individual field's record data.
     *
     * @param  Field $field - The field being rolled back
     * @param  Revision $revision - The revision being rolled back
     * @param  bool $exists - Field for record exists
     */
    public function rollbackField($field, Revision $revision, $exists=true) {
        if(!is_array($revision->oldData))
            $revision->oldData = json_decode($revision->oldData, true);

        if(is_null($revision->oldData[Field::_LIST][$field->flid]['data']))
            return null;

        // If the field doesn't exist or was explicitly deleted, we create a new one.
        if($revision->type == Revision::DELETE || !$exists) {
            $this->flid = $field->flid;
            $this->rid = $revision->rid;
            $this->fid = $revision->fid;
        }

        $this->option = $revision->oldData[Field::_LIST][$field->flid]['data'];
        $this->save();
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
            $data['option'] = $this->option;
        else
            $data['option'] = null;

        return $data;
    }

    /**
     * Get the required information for a revision data array.
     *
     * @param  Field $field - Optional field to get storage options for certain typed fields
     * @return mixed - The revision data
     */
    public function getRevisionData($field = null) {
        return $this->option;
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
                $xml = '<' . Field::xmlTagClear($slug) . ' type="List">';
                $xml .= utf8_encode('LIST VALUE');
                $xml .= '</' . Field::xmlTagClear($slug) . '>';

                return $xml;
                break;
            case "JSON":
                $fieldArray = [$slug => ['type' => 'List']];
                $fieldArray[$slug]['value'] = 'VALUE';

                return $fieldArray;
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
        $request->request->add([$flid.'_input' => $data->option]);

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
        $recRequest[$flid] = $jsonField->value;

        return $recRequest;
    }

    /**
     * Performs a keyword search on this field and returns any results.
     *
     * @param  int $flid - Field ID
     * @param  string $arg - The keywords
     * @return array - The RIDs that match search
     */
    public function keywordSearchTyped($flid, $arg) {
        return DB::table("list_fields")
            ->select("rid")
            ->where("flid", "=", $flid)
            ->whereRaw("MATCH (`option`) AGAINST (? IN BOOLEAN MODE)", [$arg])
            ->distinct()
            ->pluck('rid')
            ->toArray();
    }

    /**
     * Performs an advanced search on this field and returns any results.
     *
     * @param  int $flid - Field ID
     * @param  array $query - The advance search user query
     * @return Builder - The RIDs that match search
     */
    public function getAdvancedSearchQuery($flid, $query) {
        $db_query = DB::table("list_fields")
            ->select("rid")
            ->where("flid", "=", $flid);
        $input = $query[$flid . "_input"];

        self::buildAdvancedListQuery($db_query, $input);

        return $db_query->distinct();
    }

    /**
     * Build and advanced query for list field.
     *
     * @param  Builder $db_query - Reference to query to build
     * @param  string - Input value from form.
     */
    private static function buildAdvancedListQuery(Builder &$db_query, $input) {
        $db_query->whereRaw("MATCH (`option`) AGAINST (? IN BOOLEAN MODE)",
            ["\"" . $input . "\""]);
    }

    ///////////////////////////////////////////////END ABSTRACT FUNCTIONS///////////////////////////////////////////////

    /**
     * Gets the list options for a list field.
     *
     * @param  Field $field - Field to pull options from
     * @param  bool $blankOpt - Has blank option as first array element
     * @return array - The list options
     */
    public static function getList($field, $blankOpt=false) {
        $dbOpt = FieldController::getFieldOption($field, 'Options');
        return self::getListOptionsFromString($dbOpt,$blankOpt);
    }

    /**
     * Gets list of RIDs and values for sort.
     *
     * @param $rids - Record IDs
     * @param $flid - Field ID
     * @return string - The value array
     */
    public function getRidValuesForSort($rids,$flid) {
        $prefix = env('DB_PREFIX');
        $ridArray = implode(',',$rids);
        return DB::select("SELECT `rid`, `option` AS `value` FROM ".$prefix."list_fields WHERE `flid`=$flid AND `rid` IN ($ridArray)");
    }

    /**
     * Gets list of RIDs and values for sort.
     *
     * @param $rids - Record IDs
     * @param $flids - Field IDs to sort by
     * @return string - The value array
     */
    public function getRidValuesForGlobalSort($rids,$flids) {
        $prefix = env('DB_PREFIX');
        $ridArray = implode(',',$rids);
        $flidArray = implode(',',$flids);
        return DB::select("SELECT `rid`, `option` AS `value` FROM ".$prefix."list_fields WHERE `flid` IN ($flidArray) AND `rid` IN ($ridArray)");
    }
}
