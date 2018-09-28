<?php namespace App;

use App\Http\Controllers\FieldController;
use App\Http\Controllers\RecordController;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

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
    const FIELD_OPTIONS_VIEW = "partials.fields.options.associator";
    const FIELD_ADV_OPTIONS_VIEW = "partials.fields.advanced.associator";
    const FIELD_ADV_INPUT_VIEW = "partials.records.advanced.associator";
    const FIELD_INPUT_VIEW = "partials.records.input.associator";
    const FIELD_DISPLAY_VIEW = "partials.records.display.associator";

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
     * Gets an array of all the fields options.
     *
     * @param  Field $field
     * @return array - The options array
     */
    public function getOptionsArray(Field $field) {
        $options = array();

        $searchForms = FieldController::getFieldOption($field, 'SearchForms');
        if($searchForms != "") {
            $searchForms = explode('[!]', $searchForms);
            $sfResult = array();
            foreach($searchForms as $sForm) {
                $res = array();
                $res['FormID'] = explode('[fid]',$sForm)[1];
                $res['Searchable'] = explode('[search]',$sForm)[1];
                $res['PreviewFieldIDs'] = explode('-',explode('[flids]',$sForm)[1]);
                array_push($sfResult,$res);
            }
            $options['SearchForms'] = $sfResult;
        } else {
            $options['SearchForms'] = array();
        }

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
        if(is_null($request->default)) {
            $default = '';
        } else {
            $reqDefs = array_values(array_unique($request->default));
            $default = implode('[!]',$reqDefs);
        }

        $searchForms = array();
        foreach($request->all() as $key=>$value) {
            if(substr( $key, 0, 8 ) === "checkbox") {
                $fid = explode('_',$key)[1];
                $preview = $request->input("preview_".$fid);
                $val = "[fid]{$fid}[fid][search]1[search][flids]{$preview}[flids]";

                array_push($searchForms,$val);
            }
        }

        $field->updateRequired($request->required);
        $field->updateSearchable($request);
        $field->updateDefault($default);
        $field->updateOptions('SearchForms', implode('[!]',$searchForms));

        return redirect('projects/' . $field->pid . '/forms/' . $field->fid . '/fields/' . $field->flid . '/options')
            ->with('k3_global_success', 'field_options_updated');
    }

    /**
     * Creates a typed field to store record data.
     *
     * @param  Field $field - The field to represent record data
     * @param  Record $record - Record being created
     * @param  array $value - Data to add
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
     * @param  String $formFieldValue - The value to be assigned
     * @param  Request $request
     * @param  bool $overwrite - Overwrite if data exists
     */
    public function massAssignRecordField($field, $formFieldValue, $request, $overwrite=0) {
        //Get array of all RIDs in form
        $rids = Record::where('fid','=',$field->fid)->pluck('rid')->toArray();
        //Get list of RIDs that have the value for that field
        $ridsValue = AssociatorField::where('flid','=',$field->flid)->pluck('rid')->toArray();
        //Subtract to get RIDs with no value
        $ridsNoVal = array_diff($rids, $ridsValue);

        //Modify Data
        $newData = array();
        foreach($formFieldValue as $record) {
            array_push($newData, explode("-", $record));
        }

        foreach(array_chunk($ridsNoVal,1000) as $chunk) {
            //Create data array and store values for no value RIDs
            $fieldArray = [];
            $dataArray = [];
            $now = date("Y-m-d H:i:s");
            foreach($chunk as $rid) {
                $fieldArray[] = [
                    'rid' => $rid,
                    'fid' => $field->fid,
                    'flid' => $field->flid
                ];
                foreach($newData as $record) {
                    $dataArray[] = [
                        'rid' => $rid,
                        'fid' => $field->fid,
                        'flid' => $field->flid,
                        'record' => $record,
                        'created_at' => $now,
                        'updated_at' => $now
                    ];
                }
            }
            AssociatorField::insert($fieldArray);
            DB::table(self::SUPPORT_NAME)->insert($dataArray);
        }

        if($overwrite) {
            foreach(array_chunk($ridsValue,1000) as $chunk) {
                DB::table(self::SUPPORT_NAME)->where('flid', '=', $field->flid)->whereIn('rid', 'in', $ridsValue)->delete();

                $dataArray = [];
                foreach($chunk as $rid) {
                    foreach($newData as $record) {
                        $dataArray[] = [
                            'rid' => $rid,
                            'fid' => $field->fid,
                            'flid' => $field->flid,
                            'record' => $record,
                            'created_at' => $now,
                            'updated_at' => $now
                        ];
                    }
                }

                DB::table(self::SUPPORT_NAME)->insert($dataArray);
            }
        }
    }

    /**
     * Takes data from a mass assignment operation and applies it to an individual field for a record subset.
     *
     * @param  Field $field - The field to represent record data
     * @param  String $formFieldValue - The value to be assigned
     * @param  Request $request
     * @param  array $rids - Overwrite if data exists
     */
    public function massAssignSubsetRecordField($field, $formFieldValue, $request, $rids) {
        //Delete the old data
        AssociatorField::where('flid','=',$field->flid)->whereIn('rid', $rids)->delete();
        DB::table(self::SUPPORT_NAME)->where('flid','=',$field->flid)->whereIn('rid','in', $rids)->delete();

        //Modify Data
        $newData = array();
        foreach($formFieldValue as $record) {
            array_push($newData, explode("-", $record));
        }

        foreach(array_chunk($rids,1000) as $chunk) {
            //Create data array and store values for no value RIDs
            $fieldArray = [];
            $dataArray = [];
            $now = date("Y-m-d H:i:s");
            foreach($chunk as $rid) {
                $fieldArray[] = [
                    'rid' => $rid,
                    'fid' => $field->fid,
                    'flid' => $field->flid
                ];
                foreach($newData as $record) {
                    $dataArray[] = [
                        'rid' => $rid,
                        'fid' => $field->fid,
                        'flid' => $field->flid,
                        'record' => $record,
                        'created_at' => $now,
                        'updated_at' => $now
                    ];
                }
            }

            AssociatorField::insert($fieldArray);
            DB::table(self::SUPPORT_NAME)->insert($dataArray);
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
     * @param  Field $field - The field to validate
     * @param  Request $request
     * @param  bool $forceReq - Do we want to force a required value even if the field itself is not required?
     * @return array - Array of errors
     */
    public function validateField($field, $request, $forceReq = false) {
        $req = $field->required;
        $value = $request->{$field->flid};

        if(($req==1 | $forceReq) && ($value==null | $value==""))
            return [$field->flid.'_chosen' => $field->name.' is required'];

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

        if(is_null($revision->oldData[Field::_ASSOCIATOR][$field->flid]['data']))
            return null;

        // If the field doesn't exist or was explicitly deleted, we create a new one.
        if($revision->type == Revision::DELETE || !$exists) {
            $this->flid = $field->flid;
            $this->fid = $revision->fid;
            $this->rid = $revision->rid;
        }

        $this->save();
        $updated = explode('[!]',$revision->oldData[Field::_ASSOCIATOR][$field->flid]['data']);
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
            $data['records'] = explode('[!]', $this->getRevisionData());
        else
            $data['records'] = null;

        //Protects against the last associated record being deleted in a particular record
        if($data["records"][0]=="")
            $data = null;

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
            if(!is_null($model))
                array_push($pieces,$model->kid);
            else
                Log::info("Associator value does not exist");
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
                $xml = '<' . Field::xmlTagClear($slug) . ' type="Associator">';
                $xml .= "<Record>".utf8_encode('0-0-0')."</Record>";
                $xml .= "<Record>".utf8_encode('0-0-1')."</Record>";
                $xml .= '</' . Field::xmlTagClear($slug) . '>';

                return $xml;
                break;
            case "JSON":
                $fieldArray = [$slug => ['type' => 'Associator']];
                $fieldArray[$slug]['value'] = array("0-0-0","0-0-1");

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
        $arg = explode('-',$arg);
        $rid = end($arg); //This way, whether they supply a KID or RID, the RID will always be the last element
        return DB::table(self::SUPPORT_NAME)
            ->select("rid")
            ->where("flid", "=", $flid)
            ->where('record','=', $rid)
            ->distinct()
            ->pluck('rid')
            ->toArray();
    }

    /**
     * Performs an advanced search on this field and returns any results.
     *
     * @param  int $flid - Field ID
     * @param  array $query - The advance search user query
     * @return array - The RIDs that match search
     */
    public function advancedSearchTyped($flid, $query) {
        $inputs = $query[$flid."_input"];

        $query = DB::table(self::SUPPORT_NAME)
            ->select("rid")
            ->where("flid", "=", $flid);

        self::buildAdvancedAssociatorQuery($query, $inputs);

        return $query->distinct()
            ->pluck('rid')
            ->toArray();
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
                $ridArr = explode('-', $input);
                $rid = end($ridArr); //This way, whether they supply a KID or RID, the RID will always be the last element
                $dbQuery->orWhere('record', '=', $rid);
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
        if(is_null($recModel))
            return '';

        $pid = $recModel->pid;
        $fid = $recModel->fid;
        $rid = $recModel->rid;
        $kid = $recModel->kid;

        //get the preview flid structure of this associator
        $activeForms = array();
        $field = FieldController::getField($this->flid);
        print_r($field);
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
                    //Make sure there actually is a preview field
                    if($flid=="")
                        continue;
                    $field = FieldController::getField($flid);
                    $flids[$flid] = $field->type;
                }
                $activeForms[$opt_fid] = ['flids' => $flids];
            }
        }

        $form = \App\Http\Controllers\PageController::getFormLayout($fid);
        $form = $form[0]["fields"];
        //grab the preview fields associated with the form of this kid
        //make sure one is selected first
        $preview = array();
        if(isset($activeForms[$fid])) {
            $details = $activeForms[$fid];
//print_r($details);
            foreach($details['flids'] as $flid => $type) {
                if($type == Field::_TEXT) {
                    $text = TextField::where("flid", "=", $flid)->where("rid", "=", $rid)->first();

                    foreach ($form as $field) {
                        if ($field->type == 'Text')
                            array_push($preview, $field->name);
                    }

                    if(!is_null($text) && $text->text != '')
                        array_push($preview, $text->text);
                    else
                        array_push($preview, "Preview Field Empty");
                } else if($type == Field::_LIST) {
                    $list = ListField::where("flid", "=", $flid)->where("rid", "=", $rid)->first();

                    foreach ($form as $field) {
                        if ($field->type == 'List')
                            array_push($preview, $field->name);
                    }

                    if(!is_null($list) && $list->option != '')
                        array_push($preview, $list->option);
                    else
                        array_push($preview, "Preview Field Empty");
                }
            }
        } else {
            array_push($preview, "No Preview Field Available");
        }

        $html = "<div class='header'><a class='mt-xxxs documents-link underline-middle-hover' href='".config('app.url')."projects/".$pid."/forms/".$fid."/records/".$rid."'>".$kid."</a><div class='card-toggle-wrap'><a class='card-toggle assoc-card-toggle-js'><i class='icon icon-chevron active'></i></a></div></div><div class='body'><div class='overlay'></div>";

        foreach($preview as $val) {
            $html .= "<div>".$val."</div>";
        }

        $html = $html .= "</div>";

        return $html;
    }
}
