<?php namespace App;

use App\Http\Controllers\FieldController;
use App\Http\Controllers\RevisionController;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class GeneratedListField extends BaseField {

    /*
    |--------------------------------------------------------------------------
    | Generated List Field
    |--------------------------------------------------------------------------
    |
    | This model represents the generated list field in Kora3
    |
    */

    /**
     * @var string - Views for the typed field options
     */
    const FIELD_OPTIONS_VIEW = "partials.fields.options.genlist";
    const FIELD_ADV_OPTIONS_VIEW = "partials.fields.advanced.genlist";
    const FIELD_ADV_INPUT_VIEW = "partials.records.advanced.genlist";
    const FIELD_INPUT_VIEW = "partials.records.input.genlist";
    const FIELD_DISPLAY_VIEW = "partials.records.display.genlist";

    /**
     * @var array - Attributes that can be mass assigned to model
     */
    protected $fillable = [
        'rid',
        'flid',
        'options'
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
        return '[!Regex!][!Regex!][!Options!][!Options!]';
    }

    /**
     * Gets an array of all the fields options.
     *
     * @param  Field $field
     * @return array - The options array
     */
    public function getOptionsArray(Field $field) {
        $options = array();

        $options['Regex'] = FieldController::getFieldOption($field, 'Regex');
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
        $reqDefs = $request->default;
        $default = $reqDefs[0];
        if(!is_null($default)) {
            for($i = 1; $i < sizeof($reqDefs); $i++) {
                $default .= '[!]' . $reqDefs[$i];
            }
        }

        if($request->regex!='') {
            $regArray = str_split($request->regex);
            if($regArray[0]!=end($regArray))
                $request->regex = '/'.$request->regex.'/';
        }

        $reqOpts = $request->options;
        $options = $reqOpts[0];
        if(!is_null($options)) {
            for($i = 1; $i < sizeof($reqOpts); $i++) {
                if($request->regex != '' && !preg_match($request->regex, $reqOpts[$i])) {
                    return redirect('projects/' . $field->pid . '/forms/' . $field->fid . '/fields/' . $field->flid . '/options')
                        ->withInput()->with('k3_global_error', 'default_regex_mismatch')->with('default_regex_mismatch', $reqOpts[$i]);
                }
                $options .= '[!]' . $reqOpts[$i];
            }
        }

        $field->updateRequired($request->required);
        $field->updateSearchable($request);
        $field->updateDefault($default);
        $field->updateOptions('Regex', $request->regex);
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
        $this->options = implode("[!]",$value);
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
            $this->options = implode("[!]",$value);
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
        $matching_record_fields = $record->generatedlistfields()->where("flid", '=', $field->flid)->get();
        $record->updated_at = Carbon::now();
        $record->save();
        if($matching_record_fields->count() > 0) {
            $generatedlistfield = $matching_record_fields->first();
            if($overwrite == true || $generatedlistfield->options == "" || is_null($generatedlistfield->options)) {
                $revision = RevisionController::storeRevision($record->rid, Revision::EDIT);
                $generatedlistfield->options = implode("[!]", $formFieldValue);
                $generatedlistfield->save();
                $revision->oldData = RevisionController::buildDataArray($record);
                $revision->save();
            }
        } else {
            $this->createNewRecordField($field, $record, $formFieldValue, $request);
            $revision = RevisionController::storeRevision($record->rid, Revision::EDIT);
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
        $this->options = 'K3TR[!]1337[!]Test[!]Record';
        $this->save();
    }

    /**
     * Validates the record data for a field against the field's options.
     *
     * @param  Field $field - The field to validate
     * @param  Request $request
     * @return array - Array of errors
     */
    public function validateField($field, $request) {
        $req = $field->required;
        $value = $request->{$field->flid};
        $regex = FieldController::getFieldOption($field, 'Regex');

        if($req==1 && ($value==null | $value==""))
            return ['list'.$field->flid.'_chosen' => $field->name.' is required'];

        foreach($value as $opt) {
            if(($regex!=null | $regex!="") && !preg_match($regex,$opt))
                return ['list'.$field->flid.'_chosen' => $field->name.' value, '.$opt.', must match the regex pattern: '.$regex];
        }

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
        if(!is_array($revision->data))
            $revision->data = json_decode($revision->data, true);

        if(is_null($revision->data[Field::_GENERATED_LIST][$field->flid]['data']))
            return null;

        //If the field doesn't exist or was explicitly deleted, we create a new one.
        if($revision->type == Revision::DELETE || !$exists) {
            $this->flid = $field->flid;
            $this->rid = $revision->rid;
            $this->fid = $revision->fid;
        }

        $this->options = $revision->data[Field::_GENERATED_LIST][$field->flid]['data'];
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
            $data['options'] = explode('[!]', $this->options);
        else
            $data['options'] = null;

        return $data;
    }

    /**
     * Get the required information for a revision data array.
     *
     * @param  Field $field - Optional field to get storage options for certain typed fields
     * @return mixed - The revision data
     */
    public function getRevisionData($field = null) {
        return $this->options;
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
                $xml = '<' . Field::xmlTagClear($slug) . ' type="Generated List">';
                $xml .= '<value>' . utf8_encode('LIST VALUE 1') . '</value>';
                $xml .= '<value>' . utf8_encode('LIST VALUE 2') . '</value>';
                $xml .= '<value>' . utf8_encode('so on...') . '</value>';
                $xml .= '</' . Field::xmlTagClear($slug) . '>';

                return $xml;
                break;
            case "JSON":
                $fieldArray = [$slug => ['type' => 'Generated List']];
                $fieldArray[$slug]['value'] = array('LIST VALUE 1','LIST VALUE 2','so on...');

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
        $recRequest[$flid] = $jsonField->options;

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
        return DB::table("generated_list_fields")
            ->select("rid")
            ->where("flid", "=", $flid)
            ->whereRaw("MATCH (`options`) AGAINST (? IN BOOLEAN MODE)", [$arg])
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
        $inputs = $query[$flid."_input"];

        $query = DB::table("generated_list_fields")
            ->select("rid")
            ->where("flid", "=", $flid);

        self::buildAdvancedGeneratedListQuery($query, $inputs);

        return $query->distinct();
    }

    /**
     * Build the advanced search query for a generated list.
     *
     * @param  Builder $db_query - Pointer to builder query
     * @param  array $inputs - Input values
     */
    private static function buildAdvancedGeneratedListQuery(Builder &$db_query, $inputs) {
        $db_query->where(function($db_query) use ($inputs) {
            foreach($inputs as $input) {
                $db_query->orWhereRaw("MATCH (`options`) AGAINST (? IN BOOLEAN MODE)",
                    ["\"" . $input . "\""]);
            }
        });
    }

    ///////////////////////////////////////////////END ABSTRACT FUNCTIONS///////////////////////////////////////////////

    /**
     * Gets the list options for a generated list field.
     *
     * @param  Field $field - Field to pull options from
     * @param  bool $blankOpt - Has blank option as first array element
     * @return array - The list options
     */
    public static function getList($field, $blankOpt=false) {
        $dbOpt = FieldController::getFieldOption($field, 'Options');
        return self::getListOptionsFromString($dbOpt,$blankOpt);
    }
}
