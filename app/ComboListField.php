<?php namespace App;

use App\Http\Controllers\AssociationController;
use App\Http\Controllers\FieldController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\RevisionController;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ComboListField extends BaseField {

    /*
    |--------------------------------------------------------------------------
    | Combo List Field
    |--------------------------------------------------------------------------
    |
    | This model represents the combo list field in Kora3
    |
    */

    /**
     * @var string - Support table name
     */
    const SUPPORT_NAME = "combo_support";
    /**
     * @var string - Views for the typed field options
     */
    const FIELD_OPTIONS_VIEW = "fields.options.combolist";
    const FIELD_ADV_OPTIONS_VIEW = "partials.field_option_forms.combolist";

    /**
     * @var array - Attributes that can be mass assigned to model
     */
    protected $fillable = [
        'rid',
        'flid',
        'ftype1',
        'ftype2'
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
        $type1 = $request->cftype1;
        $type2 = $request->cftype2;
        $name1 = $request->cfname1;
        $name2 = $request->cfname2;

        $options = "[!Field1!]";
        $options .= $this->getSubFieldDefaultOptions($type1,$name1);
        $options .= "[!Field1!]";

        $options .= "[!Field2!]";
        $options .= $this->getSubFieldDefaultOptions($type2,$name2);
        $options .= "[!Field2!]";

        return $options;
    }

    /**
     * Helper function to process default options for sub field.
     *
     * @param  string $type - Type of field
     * @param  string $name - Name of sub field
     * @return string - The default options
     */
    private function getSubFieldDefaultOptions($type, $name) {
        $options = "[Type]".$type."[Type][Name]".$name."[Name]";
        $typedField = Field::getTypedFieldStatic($type);
        $options .= "[Options]".$typedField->getDefaultOptions(new Request())."[Options]";

        return $options;
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
        $flopt_one ='[Type]'.$request->typeone.'[Type][Name]'.$request->nameone.'[Name]';
        $flopt_one .= $this->formatUpdatedSubOptions($request,"one",$field->fid);

        $flopt_two ='[Type]'.$request->typetwo.'[Type][Name]'.$request->nametwo.'[Name]';
        $flopt_two .= $this->formatUpdatedSubOptions($request,"two",$field->fid);

        $default='';
        if(!is_null($request->defvalone) && $request->defvalone != '') {
            $default .= '[!f1!]'.$request->defvalone[0].'[!f1!]';
            $default .= '[!f2!]'.$request->defvaltwo[0].'[!f2!]';

            for($i=1;$i<sizeof($request->defvalone);$i++) {
                $default .= '[!def!]';
                $default .= '[!f1!]'.$request->defvalone[$i].'[!f1!]';
                $default .= '[!f2!]'.$request->defvaltwo[$i].'[!f2!]';
            }
        }

        $field->updateRequired($request->required);
        $field->updateSearchable($request);
        $field->updateDefault($default);
        $field->updateOptions('Field1', $flopt_one);
        $field->updateOptions('Field2', $flopt_two);

        if($return) {
            return redirect('projects/' . $field->pid . '/forms/' . $field->fid . '/fields/' . $field->flid . '/options')
                ->with('k3_global_success', 'field_options_updated');
        } else {
            return response()->json(["status"=>true,"message"=>"field_options_updated"],200);
        }
    }

    /**
     * Helper function to format updated options for sub field.
     *
     * @param  Request $request
     * @param  string $seq - Is this the first or second sub field
     * @param  int $fid - Form ID, mostly for associator use
     * @return string - The updated options
     */
    private function formatUpdatedSubOptions($request, $seq, $fid) {
        $options = "[Options]";
        $type = $request->{"type".$seq};
        switch($type) {
            case Field::_TEXT:
                $options .= '[!Regex!]'.$request->{"regex_".$seq}.'[!Regex!]';
                $options .= '[!MultiLine!]'.$request->{"multi_".$seq}.'[!MultiLine!]';
                break;
            case Field::_NUMBER:
                $options .= '[!Max!]'.$request->{"max_".$seq}.'[!Max!]';
                $options .= '[!Min!]'.$request->{"min_".$seq}.'[!Min!]';
                $options .= '[!Increment!]'.$request->{"inc_".$seq}.'[!Increment!]';
                $options .= '[!Unit!]'.$request->{"unit_".$seq}.'[!Unit!]';
                break;
            case Field::_LIST:
            case Field::_MULTI_SELECT_LIST:
                $options .= '[!Options!]';

                $reqOpts = $request->{"options_".$seq};
                $options .= implode("[!]",$reqOpts);
                $options .= '[!Options!]';
                break;
            case Field::_GENERATED_LIST:
                $options .= '[!Options!]';

                $reqOpts = $request->{"options_".$seq};
                $options .= implode("[!]",$reqOpts);
                $options .= '[!Options!]';
                $options .= '[!Regex!]'.$request->{"regex_".$seq}.'[!Regex!]';
                break;
            case Field::_ASSOCIATOR:
                $options .= '[!SearchForms!]';
                $opt = array();

                foreach(AssociationController::getAvailableAssociations($fid) as $a) {
                    $f = FormController::getForm($a->dataForm);
                    $box = 'checkbox_'.$f->fid.'_'.$seq;
                    $preview = 'preview_'.$f->fid.'_'.$seq;

                    $val = '[fid]'.$f->fid.'[fid]';
                    if(!is_null($request->{$box}))
                        $val .= '[search]1[search]';
                    else
                        $val .= '[search]0[search]';

                    if(!is_null($request->{$preview}))
                        $val .= '[flids]'.implode('-',$request->{$preview}).'[flids]';
                    else
                        $val .= '[flids][flids]';

                    array_push($opt,$val);
                }

                $options .= implode('[!]',$opt);
                $options .= '[!SearchForms!]';
                break;
        }
        $options .= "[Options]";

        return $options;
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
        if($request->input($field->flid.'_val') != null) {
            $this->flid = $field->flid;
            $this->rid = $record->rid;
            $this->fid = $field->fid;
            $this->save();

            $type_1 = self::getComboFieldType($field, 'one');
            $type_2 = self::getComboFieldType($field, 'two');

            // Add combo data to support table.
            $this->addData($request->input($field->flid.'_val'), $type_1, $type_2);
        }
    }

    /**
     * Edits a typed field that has record data.
     *
     * @param  string $value - Data to add
     * @param  Request $request
     */
    public function editRecordField($value, $request) {
        if(!is_null($this) && !is_null($request->input($this->flid.'_val'))) {
            $field = FieldController::getField($this->flid);
            $type_1 = self::getComboFieldType($field, 'one');
            $type_2 = self::getComboFieldType($field, 'two');

            $this->updateData($request->{$field->flid.'_val'}, $type_1, $type_2);
        } else if(!is_null($this) && is_null($request->input($this->flid.'_val'))) {
            $this->delete();
            $this->deleteData();
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
        $matching_record_fields = $record->combolistfields()->where('flid','=',$field->flid)->get();
        $record->updated_at = Carbon::now();
        $record->save();

        if($matching_record_fields->count() > 0) {
            $combolistfield = $matching_record_fields->first();
            if($overwrite == true || $combolistfield->options == "" || is_null($combolistfield->options)) {
                $revision = RevisionController::storeRevision($record->rid,'edit');

                $combolistfield->updateData($request->input($field->flid.'_val'));

                $revision->oldData = RevisionController::buildDataArray($record);
                $revision->save();
            }
        } else {
            $this->createNewRecordField($field, $record, $formFieldValue, $request);
            $revision = RevisionController::storeRevision($record->rid,'edit');
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
        $val1 = '';
        $val2 = '';
        $type1 = self::getComboFieldType($field,'one');
        $type2 = self::getComboFieldType($field,'two');
        switch($type1) {
            case Field::_TEXT:
                $val1 = 'K3TR: This is a test record';
                break;
            case Field::_LIST:
                $val1 = 'K3TR';
                break;
            case Field::_NUMBER:
                $val1 = 1337;
                break;
            case Field::_MULTI_SELECT_LIST:
            case Field::_GENERATED_LIST:
                $val1 = 'K3TR[!]1337[!]Test[!]Record';
                break;
        }
        switch($type2) {
            case Field::_TEXT:
                $val2 = 'K3TR: This is a test record';
                break;
            case Field::_LIST:
                $val2 = 'K3TR';
                break;
            case Field::_NUMBER:
                $val2 = 1337;
                break;
            case Field::_MULTI_SELECT_LIST:
            case Field::_GENERATED_LIST:
                $val2 = 'K3TR[!]1337[!]Test[!]Record';
                break;
        }
        $this->save();

        $this->addData(["[!f1!]".$val1."[!f1!][!f2!]".$val2."[!f2!]", "[!f1!]".$val1."[!f1!][!f2!]".$val2."[!f2!]"], $type1, $type2);
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
        $flid = $field->flid;

        if($req==1 && !isset($request[$flid.'_val']))
            return $field->name."_required";

        return 'field_validated';
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

        if(is_null($revision->data[Field::_COMBO_LIST][$field->flid]['data']))
            return null;

        // If the field doesn't exist or was explicitly deleted, we create a new one.
        if($revision->type == Revision::DELETE || !$exists) {
            $this->flid = $field->flid;
            $this->fid = $revision->fid;
            $this->rid = $revision->rid;
        }

        $this->save();

        $type_1 = self::getComboFieldType($field, "one");
        $type_2 = self::getComboFieldName($field, "two");

        $this->updateData($revision->data[Field::_COMBO_LIST][$field->flid]['data']['options'], $type_1, $type_2);
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
            $data['combolists'] = ComboListField::dataToOldFormat($this->data()->get());
        else
            $data['combolists'] = null;

        return $data;
    }

    /**
     * Get the required information for a revision data array.
     *
     * @param  Field $field - Optional field to get storage options for certain typed fields
     * @return mixed - The revision data
     */
    public function getRevisionData($field = null) {
        $field = Field::where('flid', '=', $this->flid)->first();

        $name_1 = self::getComboFieldName($field, 'one');
        $name_2 = self::getComboFieldName($field, 'two');

        return [
            'options' => self::dataToOldFormat($this->data()->get()),
            'name_1' => $name_1,
            'name_2' => $name_2
        ];
    }

    /**
     * Provides an example of the field's structure in an export to help with importing records.
     *
     * @param  string $slug - Field nickname
     * @param  string $expType - Type of export
     * @return mixed - The example
     */
    public function getExportSample($slug,$type) {
        $field = Field::where('slug','=',$slug)->first();

        $typeone = ComboListField::getComboFieldType($field, 'one');
        $typetwo = ComboListField::getComboFieldType($field, 'two');
        $nameone = ComboListField::getComboFieldName($field, 'one');
        $nametwo = ComboListField::getComboFieldName($field, 'two');

        switch($type) {
            case "XML":
                $xml = '<' . Field::xmlTagClear($slug) . ' type="Combo List">';

                $xml .= '<Value>';
                $xml .= '<' . Field::xmlTagClear($nameone) . '>';
                if($typeone == 'Text' | $typeone == 'Number' | $typeone == 'List') {
                    $xml .= utf8_encode('VALUE');
                } else if($typeone == 'Multi-Select List' | $typeone == 'Generated List') {
                    $xml .= '<value>'.utf8_encode('VALUE 1').'</value>';
                    $xml .= '<value>'.utf8_encode('VALUE 2').'</value>';
                    $xml .= '<value>'.utf8_encode('so on..').'</value>';
                }
                $xml .= '</' . Field::xmlTagClear($nameone) . '>';
                $xml .= '<' . Field::xmlTagClear($nametwo) . '>';
                if($typetwo == 'Text' | $typetwo == 'Number' | $typetwo == 'List') {
                    $xml .= utf8_encode('VALUE');
                } else if($typetwo == 'Multi-Select List' | $typetwo == 'Generated List') {
                    $xml .= '<value>'.utf8_encode('VALUE 1').'</value>';
                    $xml .= '<value>'.utf8_encode('VALUE 2').'</value>';
                    $xml .= '<value>'.utf8_encode('so on..').'</value>';
                }
                $xml .= '</' . Field::xmlTagClear($nametwo) . '>';
                $xml .= '</Value>';
                $xml .= '</' . Field::xmlTagClear($slug) . '>';

                return $xml;
                break;
            case "JSON":
                $fieldArray = [$slug => ['type' => 'Combo List']];

                $valArray = array();
                if($typeone == 'Text' | $typeone == 'Number' | $typeone == 'List') {
                    $valArray[$nameone] = 'VALUE';
                } else if($typeone == 'Multi-Select List' | $typeone == 'Generated List') {
                    $valArray[$nameone] = array('VALUE 1','VALUE 2','so on...');
                }

                if($typetwo == 'Text' | $typetwo == 'Number' | $typetwo == 'List') {
                    $valArray[$nametwo] = 'VALUE';
                } else if($typetwo == 'Multi-Select List' | $typetwo == 'Generated List') {
                    $valArray[$nametwo] = array('VALUE 1','VALUE 2','so on...');
                }

                $fieldArray[$slug]['value'][] = $valArray;

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
        $field = FieldController::getField($flid);
        $type1 = self::getComboFieldType($field,'one');
        switch($type1) {
            case Field::_NUMBER:
                if(isset($data->left_one))
                    $leftNum = $data->left_one;
                else
                    $leftNum = '';
                $request->request->add([$field->flid.'_1_left' => $leftNum]);
                if(isset($data->right_one))
                    $rightNum = $data->right_one;
                else
                    $rightNum = '';
                $request->request->add([$field->flid.'_1_right' => $rightNum]);
                if(isset($data->invert_one))
                    $invert = $data->invert_one;
                else
                    $invert = 0;
                $request->request->add([$field->flid.'_1_invert' => $invert]);
                break;
            default:
                $request->request->add([$field->flid.'_1_input' => $data->input_one]);
                break;
        }
        $type2 = self::getComboFieldType($field,'two');
        switch($type2) {
            case Field::_NUMBER:
                if(isset($data->left_two))
                    $leftNum = $data->left_two;
                else
                    $leftNum = '';
                $request->request->add([$field->flid.'_2_left' => $leftNum]);
                if(isset($data->right_two))
                    $rightNum = $data->right_two;
                else
                    $rightNum = '';
                $request->request->add([$field->flid.'_2_right' => $rightNum]);
                if(isset($data->invert_two))
                    $invert = $data->invert_two;
                else
                    $invert = 0;
                $request->request->add([$field->flid.'_2_invert' => $invert]);
                break;
            default:
                $request->request->add([$field->flid.'_2_input' => $data->input_two]);
                break;
        }
        $request->request->add([$field->flid.'_operator' => $data->operator]);

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
        $values = array();
        $field = FieldController::getField($flid);
        $nameone = self::getComboFieldName($field, 'one');
        $nametwo = self::getComboFieldName($field, 'two');
        foreach($jsonField->values as $val) {
            if(!is_array($val[$nameone]))
                $fone = '[!f1!]' . $val[$nameone] . '[!f1!]';
            else
                $fone = '[!f1!]' . implode("[!]",$val[$nameone]) . '[!f1!]';
            if(!is_array($val[$nametwo]))
                $ftwo = '[!f2!]' . $val[$nametwo] . '[!f2!]';
            else
                $ftwo = '[!f2!]' . implode("[!]",$val[$nametwo]) . '[!f2!]';
            array_push($values, $fone . $ftwo);
        }
        $recRequest[$flid] = '';
        $recRequest[$flid . '_val'] = $values;

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
        return DB::table(self::SUPPORT_NAME)
            ->select("rid")
            ->where("flid", "=", $flid)
            ->where(function($query) use ($arg) {
                $num = $arg = str_replace(["*", "\""], "", $arg);
                $num = floatval($num);

                $query->whereRaw("MATCH (`data`) AGAINST (? IN BOOLEAN MODE)", [$arg])
                    ->orWhereBetween("number", [$num - NumberField::EPSILON, $num + NumberField::EPSILON]);
            })
            ->distinct()
            ->lists('rid');
    }

    /**
     * Performs an advanced search on this field and returns any results.
     *
     * @param  int $flid - Field ID
     * @param  array $query - The advance search user query
     * @return Builder - The RIDs that match search
     */
    public function getAdvancedSearchQuery($flid, $query) {
        $field = Field::where("flid", "=", $flid)->first();
        $type_1 = self::getComboFieldType($field, 'one');
        $type_2 = self::getComboFieldType($field, 'two');

        $one_valid = $query[$flid . "_1_valid"] == "1";
        $two_valid = $query[$flid . "_2_valid"] == "1";

        // Return an impossible query if the two fields are somehow both invalid.
        // May seem extraneous, but this is required for chaining calls elsewhere.
        if(! ($one_valid || $two_valid)) {
            return DB::table(self::SUPPORT_NAME)->select("*")->where("id", "<", 0);
        } else if($one_valid && $two_valid) {
            if($query[$flid . "_operator"] == "and") {
                //
                // We need to join combo_support with itself.
                // Since each entry represents one sub-field in the combo list, an "and" operation
                // on a combo list would be impossible without two copies of everything.
                //
                $first_prefix = "one.";
                $second_prefix = "two.";

                $db_query = DB::table(self::SUPPORT_NAME." AS " . substr($first_prefix, 0, -1))
                    ->select($first_prefix . "rid")
                    ->where($first_prefix . "flid", "=", $flid)
                    ->join(self::SUPPORT_NAME." AS " . substr($second_prefix, 0, -1),
                        $first_prefix . "rid",
                        "=",
                        $second_prefix . "rid");

                $db_query->where(function($db_query) use ($flid, $query, $type_1, $first_prefix) {
                    self::buildAdvancedQueryRoutine($db_query, "1", $flid, $query, $type_1, $first_prefix);
                });
                $db_query->where(function($db_query) use ($flid, $query, $type_2, $second_prefix) {
                    self::buildAdvancedQueryRoutine($db_query, "2", $flid, $query, $type_2, $second_prefix);
                });

            } else { // OR operation.
                $db_query = self::makeAdvancedQueryRoutine($flid);
                $db_query->where(function($db_query) use ($flid, $query, $type_1) {
                    self::buildAdvancedQueryRoutine($db_query, "1", $flid, $query, $type_1);
                });
                $db_query->orWhere(function($db_query) use ($flid, $query, $type_2) {
                    self::buildAdvancedQueryRoutine($db_query, "2", $flid, $query, $type_2);
                });
            }
        } else if ($one_valid) {
            $db_query = self::makeAdvancedQueryRoutine($flid);
            self::buildAdvancedQueryRoutine($db_query, "1", $flid, $query, $type_1);
        } else { // two valid
            $db_query = self::makeAdvancedQueryRoutine($flid);
            self::buildAdvancedQueryRoutine($db_query, "2", $flid, $query, $type_2);
        }

        return $db_query->distinct();
    }

    /**
     * Helper function to make the initial advanced DB query.
     *
     * @param  int $flid - Field ID
     * @return Builder - Initial query
     */
    private static function makeAdvancedQueryRoutine($flid) {
        return DB::table(self::SUPPORT_NAME)
            ->select("rid")
            ->where("flid", "=", $flid);
    }

    /**
     * Helper function with logic to build up an advanced query.
     *
     * @param  Builder $db_query - Pointer reference to the current query
     * @param  mixed $field_num - First or second field in the combo list
     * @param  int $flid - Field ID
     * @param  array $query - Query array from the form
     * @param  string $type - The type of the combo field
     * @param  string $prefix - To deal with joined tables
     */
    private static function buildAdvancedQueryRoutine(Builder &$db_query, $field_num, $flid, $query, $type, $prefix = "") {
        $db_query->where($prefix . "field_num", "=", $field_num);

        if($type == Field::_NUMBER) {
            NumberField::buildAdvancedNumberQuery($db_query,
                $query[$flid . "_" . $field_num . "_left"],
                $query[$flid . "_" . $field_num . "_right"],
                isset($query[$flid . "_" . $field_num . "_invert"]),
                $prefix);
        } else {
            if($type == Field::_LIST || $type == Field::_TEXT)
                $inputs = [$query[$flid . "_" . $field_num . "_input"]];
            else // Generated or Multi-Select List
                $inputs = $query[$flid . "_" . $field_num . "_input"];

            // Since we're using a raw query, we have to get the database prefix to match our alias.
            $db_prefix = DB::getTablePrefix();
            $prefix = ($prefix == "") ? self::SUPPORT_NAME : substr($prefix, 0, -1);
            $db_query->where(function($db_query) use ($inputs, $prefix, $db_prefix) {
                foreach($inputs as $input) {
                    $db_query->orWhereRaw("MATCH (`" . $db_prefix . $prefix . "`.`data`) AGAINST (? IN BOOLEAN MODE)",
                        ["\"" . $input . "\""]);
                }
            });
        }
    }

    ///////////////////////////////////////////////END ABSTRACT FUNCTIONS///////////////////////////////////////////////

    /**
     * Gets the list options for a combo list field.
     *
     * @param  Field $field - Field to pull options from
     * @param  bool $blankOpt - Has blank option as first array element
     * @return array - The list options
     */
    public static function getComboList($field, $blankOpt=false, $fnum) {
        $dbOpt = self::getComboFieldOption($field, 'Options', $fnum);
        if(is_null($dbOpt))
            $dbOpt = '';
        return self::getListOptionsFromString($dbOpt,$blankOpt);
    }

    /**
     * Overrides the delete function to first delete support fields.
     */
    public function delete() {
        $this->deleteData();
        parent::delete();
    }

    /**
     * Returns the data for a record's combo list value.
     *
     * @return Builder - Query of values
     */
    public function data() {
        return DB::table(self::SUPPORT_NAME)->select("*")
            ->where("rid", "=", $this->rid)
            ->where("flid", "=", $this->flid)
            ->orderBy('list_index');
    }

    /**
     * Determine if this field has data in the support table.
     *
     * @return bool - Has data
     */
    public function hasData() {
        return !! $this->data()->count();
    }

    /**
     * Adds data to the support table.
     *
     * @param  array $data - Data to add
     * @param  string $type1 - Field type of sub-field 1
     * @param  string $type2 - Field type of sub-field 2
     */
    public function addData(array $data, $type1, $type2) {
        $now = date("Y-m-d H:i:s");

        $inserts = [];

        $one_is_num = $type1 == 'Number';
        $two_is_num = $type2 == 'Number';

        $i = 0;
        foreach($data as $entry) {
            $field_1_data = explode('[!f1!]', $entry)[1];
            $field_2_data = explode('[!f2!]', $entry)[1];

            $inserts[] = [
                'fid' => $this->fid,
                'rid' => $this->rid,
                'flid' => $this->flid,
                'field_num' => 1,
                'list_index' => $i,
                'data' => (!$one_is_num) ? $field_1_data : null,
                'number' => ($one_is_num) ? $field_1_data : null,
                'created_at' => $now,
                'updated_at' => $now
            ];

            $inserts[] = [
                'fid' => $this->fid,
                'rid' => $this->rid,
                'flid' => $this->flid,
                'list_index' => $i,
                'field_num' => 2,
                'data' => (!$two_is_num) ? $field_2_data : null,
                'number' => ($two_is_num) ? $field_2_data : null,
                'created_at' => $now,
                'updated_at' => $now
            ];

            $i++;
        }

        DB::table(self::SUPPORT_NAME)->insert($inserts);
    }

    /**
     * Updates the current list of data by deleting the old ones and adding the array that has both new and old.
     *
     * @param  array $data - Data to add
     */
    public function updateData(array $data, $type_1, $type_2) {
        $this->deleteData();
        $this->addData($data, $type_1, $type_2);
    }

    /**
     * Deletes data from the support table.
     */
    public function deleteData() {
        DB::table(self::SUPPORT_NAME)
            ->where("rid", "=", $this->rid)
            ->where("flid", "=", $this->flid)
            ->delete();
    }

    /**
     * Turns the support table into the old format beforehand.
     *
     * @param  array $data - Data from support
     * @param  bool $array_string - Array of old format or string of old format
     * @return mixed - String or array of old format
     */
    public static function dataToOldFormat(array $data, $array_string = false) {
        $formatted = [];
        for($i = 0; $i < count($data); $i++) {
            $op1 = $data[$i];
            $op2 = $data[++$i];

            if($op1->field_num == 2) {
                $tmp = $op1;
                $op1 = $op2;
                $op2 = $tmp;
            }

            if(! is_null($op1->data))
                $val1 = $op1->data;
            else
                $val1 = $op1->number + 0;

            if(! is_null($op2->data))
                $val2 = $op2->data;
            else
                $val2 = $op2->number + 0;

            $formatted[] = "[!f1!]"
                . $val1
                . "[!f1!]"
                . "[!f2!]"
                . $val2
                . "[!f2!]";
        }

        if($array_string)
            return implode("[!val!]", $formatted);

        return $formatted;
    }

    /**
     * Validates record data for a Combo List Field.
     *
     * @param  int $flid - Field ID
     * @param  Request $request
     * @return JsonResponse - Returns success/error message
     */
    public static function validateComboListOpt($flid, $request) {
        $field = FieldController::getField($flid);

        $valone = $request->valone;
        $valtwo = $request->valtwo;
        $typeone = $request->typeone;
        $typetwo = $request->typetwo;

        if($valone=="" | $valtwo=="")
            return response()->json(["status"=>false,"message"=>"combo_value_missing"],500);

        $validateOne = self::validateComboListField($field,$typeone,$valone);
        if($validateOne!="sub_field_validated") {
            $name = self::getComboFieldName($field,'one');
            return response()->json(["status"=>false,"message"=>$validateOne,"sub_field_name"=>$name],500);
        }

        $validateTwo = self::validateComboListField($field,$typetwo,$valtwo);
        if($validateTwo!="sub_field_validated") {
            $name = self::getComboFieldName($field,'two');
            return response()->json(["status"=>false,"message"=>$validateTwo,"sub_field_name"=>$name],500);
        }

        return response()->json(["status"=>true,"message"=>"combo_field_validated"],200);
    }

    /**
     * Validates record data for a specific Combo List sub-field.
     *
     * @param  Field $field - Field model for the combo list
     * @param  Field $type - Sub field type
     * @param  Field $val - Sub field value to validate
     * @return string - Returns success/error message
     */
    private static function validateComboListField($field, $type, $val) {
        switch($type) {
            case "Text":
                $regex = self::getComboFieldOption($field, 'Regex', 'one');
                if(($regex!=null | $regex!="") && !preg_match($regex, $val))
                    return "regex_value_mismatch";
                break;
            case "Number":
                $max = self::getComboFieldOption($field, 'Max', 'one');
                $min = self::getComboFieldOption($field, 'Min', 'one');
                $inc = self::getComboFieldOption($field, 'Increment', 'one');

                if($val < $min | $val > $max)
                    return "number_range_error";

                if(fmod(floatval($val), floatval($inc)) != 0)
                    return "number_increment_error";
                break;
            case "List":
                $opts = explode('[!]', self::getComboFieldOption($field, 'Options', 'one'));

                if(!in_array($val, $opts))
                    return "invalid_list_option";
                break;
            case "Multi-Select List":
                $opts = explode('[!]', self::getComboFieldOption($field, 'Options', 'one'));

                if(sizeof(array_diff($val, $opts)) > 0)
                    return "invalid_list_option";
                break;
            case "Generated List":
                $regex = self::getComboFieldOption($field, 'Regex', 'one');

                if($regex != null | $regex != "") {
                    foreach ($val as $val) {
                        if(!preg_match($regex, $val))
                            return "regex_values_mismatch.";
                    }
                }
                break;
            default:
                return "combo_type_error";
        }

        return "sub_field_validated";
    }

    /**
     * Gets the name of a combo list sub field
     *
     * @param  Field $field - Combo field to inspect
     * @param  int $num - Sequence of sub field
     * @return string - Name
     */
    public static function getComboFieldName($field, $num) {
        $options = $field->options;

        if($num=='one') {
            $oneOpts = explode('[!Field1!]', $options)[1];
            $name = explode('[Name]', $oneOpts)[1];
        } else if($num=='two') {
            $twoOpts = explode('[!Field2!]', $options)[1];
            $name = explode('[Name]', $twoOpts)[1];
        }

        return $name;
    }

    /**
     * Gets the type of a combo list sub field
     *
     * @param  Field $field - Combo field to inspect
     * @param  int $num - Sequence of sub field
     * @return string - Type
     */
    public static function getComboFieldType($field, $num) {
        $options = $field->options;

        if($num=='one') {
            $oneOpts = explode('[!Field1!]', $options)[1];
            $type = explode('[Type]', $oneOpts)[1];
        } else if($num=='two') {
            $twoOpts = explode('[!Field2!]', $options)[1];
            $type = explode('[Type]', $twoOpts)[1];
        }

        return $type;
    }

    /**
     * Gets an option of a combo list sub field
     *
     * @param  Field $field - Combo field to inspect
     * @param  string $key - The option we want
     * @param  int $num - Sequence of sub field
     * @return string - The option
     */
    public static function getComboFieldOption($field, $key, $num) {
        $options = $field->options;
        if($num=='one')
            $opt = explode('[!Field1!]',$options)[1];
        else if($num=='two')
            $opt = explode('[!Field2!]',$options)[1];

        $tag = '[!'.$key.'!]';

        $exploded = explode($tag, $opt);

        if(sizeof($exploded) < 2)
            return null;

        $value = explode($tag,$opt)[1];

        return $value;
    }
}
