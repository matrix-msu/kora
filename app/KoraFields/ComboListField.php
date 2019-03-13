<?php namespace App\KoraFields;

use App\Form;
use App\Http\Controllers\AssociationController;
use App\Http\Controllers\FieldController;
use App\Http\Controllers\FormController;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

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
    const FIELD_OPTIONS_VIEW = "partials.fields.options.combolist";
    const FIELD_ADV_OPTIONS_VIEW = null;
    const FIELD_ADV_INPUT_VIEW = "partials.records.advanced.combolist";
    const FIELD_INPUT_VIEW = "partials.records.input.combolist";
    const FIELD_DISPLAY_VIEW = "partials.records.display.combolist";

    /**
     * @var array - This is an array of combo list field type values for creation
     */
    static public $validComboListFieldTypes = [
        'Text Fields' => array('Text' => 'Text', 'Number' => 'Number'),
        'Date Fields' => array('Date' => 'Date'),
        'List Fields' => array('List' => 'List', 'Multi-Select List' => 'Multi-Select List', 'Generated List' => 'Generated List'),
        'Other' => array('Associator' => 'Associator')
    ];

    private $fieldToDBFuncAssoc = [
        'Text' => 'addTextColumn',
        'List' => 'addEnumColumn'
    ];

    private $fieldModel = [
        'Text' => 'App\KoraFields\TextField',
        'List' => 'App\KoraFields\ListField'
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
     * Get the field input view for advanced field search.
     *
     * @return string - The view
     */
    public function getAdvancedSearchInputView() {
        return self::FIELD_ADV_INPUT_VIEW;
    }

    /**
     * Get the field input view for record creation.
     *
     * @return string - The view
     */
    public function getFieldInputView() {
        return self::FIELD_INPUT_VIEW;
    }

    /**
     * Get the field input view for record creation.
     *
     * @return string - The view
     */
    public function getFieldDisplayView() {
        return self::FIELD_DISPLAY_VIEW;
    }

    /**
     * Gets the default options string for a new field.
     *
     * @param  int $fid - Form ID
     * @param  string $slug - Name of database column based on field internal name
     * @param  array $options - Extra information we may need to set up about the field
     * @return array - The default options
     */
    public function addDatabaseColumn($fid, $slug, $options = null) {
        $table = new \CreateRecordsTable(
            ['tablePrefix' => $slug]
        );
        $table->createComboListTable($fid);

        foreach ($options as $option) {
            $method = $this->fieldToDBFuncAssoc[$option['type']];
            $table->{$method}($fid, $option['name']);
        }
    }


    /**
     * Gets the default options string for a new field.
     *
     * @return array - The default options
     */
    public function getDefaultOptions($types = null) {
        // TODO::@andrew.joye might change arg to options
        $defaultOptions = [];

        foreach ($types as $key => $type) {
            $className = $this->fieldModel[$type['type']];
            $object = new $className;
            $defaultOptions[$key] = $object->getDefaultOptions();
        }

        return $defaultOptions;
    }

    /**
     * Update the options for a field
     *
     * @param  array $field - Field to update options
     * @param  Request $request
     * @param  int $flid - The field internal name
     * @return array - The updated field array
     */
    public function updateOptions($field, Request $request, $flid = null) {

        foreach (['one', 'two'] as $seq) {
            $options = array();

            switch($request->{'type' . $seq}) {
                case Form::_TEXT:
                    $regex = $request->{'regex_' . $seq};
                    $multi = $request->{'multi_' . $seq};
                    if($regex!='') {
                        $regArray = str_split($regex);
                        if($regArray[0]!=end($regArray))
                            $regex = '/'.$regex.'/';
                    } else {
                        $regex = null;
                    }
                    $options['Regex'] = $regex;
                    $options['MultiLine'] = isset($multi) && $multi ? 1 : 0;
                    break;
                case Form::_INTEGER:
                case Form::_FLOAT:
                    $min = $request->{'min_' . $seq};
                    $max = $request->{'max_' . $seq};

                    if(($min != '' && $max != '') && ($min >= $max))
                        $max = $min = '';

                    $options['Max'] = $max;
                    $options['Min'] = $min;
                    $options['Unit'] = $request->{'unit_' . $seq};
                    break;
                case Form::_LIST:
                case Form::_MULTI_SELECT_LIST:
                    $listopts = $request->{'options_' . $seq};
                    if(is_null($listopts)) {
                        $listopts = array();
                    }
                    $options['Options'] = $listopts;
                    break;
            }

            $field['default'][$seq] = $request->{'default_combo_' . $seq};
            $field['options'][$seq] = $options;
        }

        return $field;
    }

    /**
     * Takes data from a mass assignment operation and applies it to an individual field.
     *
     * @param  Form $form - Form model
     * @param  string $flid - Field ID
     * @param  String $formFieldValue - The value to be assigned
     * @param  Request $request
     * @param  bool $overwrite - Overwrite if data exists
     */
    public function massAssignRecordField($form, $flid, $formFieldValue, $request, $overwrite=0) {
        //Get array of all RIDs in form
        $rids = Record::where('fid','=',$field->fid)->pluck('rid')->toArray();
        //Get list of RIDs that have the value for that field
        $ridsValue = ComboListField::where('flid','=',$field->flid)->pluck('rid')->toArray();
        //Subtract to get RIDs with no value
        $ridsNoVal = array_diff($rids, $ridsValue);

        //Modify Data
        $newData = array();
        foreach($request->input($field->flid.'_val') as $entry) {
            $newEntry = array(
                explode('[!f1!]', $entry)[1],
                explode('[!f2!]', $entry)[1]
            );

            array_push($newData, $newEntry);
        }

        foreach(array_chunk($ridsNoVal,1000) as $chunk) {
            //Create data array and store values for no value RIDs
            $fieldArray = [];
            $dataArray = [];
            $now = date("Y-m-d H:i:s");
            $one_is_num = self::getComboFieldType($field, 'one') == 'Number';
            $two_is_num = self::getComboFieldType($field, 'two') == 'Number';
            foreach($chunk as $rid) {
                $fieldArray[] = [
                    'rid' => $rid,
                    'fid' => $field->fid,
                    'flid' => $field->flid
                ];
                $i = 0;
                foreach($newData as $entry) {
                    $dataArray[] = [
                        'rid' => $rid,
                        'fid' => $field->fid,
                        'flid' => $field->flid,
                        'field_num' => 1,
                        'list_index' => $i,
                        'data' => (!$one_is_num) ? $entry[0] : null,
                        'number' => ($one_is_num) ? $entry[0] : null,
                        'created_at' => $now,
                        'updated_at' => $now
                    ];
                    $dataArray[] = [
                        'rid' => $rid,
                        'fid' => $field->fid,
                        'flid' => $field->flid,
                        'field_num' => 2,
                        'list_index' => $i,
                        'data' => (!$two_is_num) ? $entry[1] : null,
                        'number' => ($two_is_num) ? $entry[1] : null,
                        'created_at' => $now,
                        'updated_at' => $now
                    ];
                    $i++;
                }
            }
            ComboListField::insert($fieldArray);
            DB::table(self::SUPPORT_NAME)->insert($dataArray);
        }

        if($overwrite) {
            foreach(array_chunk($ridsValue,1000) as $chunk) {
                DB::table(self::SUPPORT_NAME)->where('flid', '=', $field->flid)->whereIn('rid', 'in', $ridsValue)->delete();

                $dataArray = [];
                foreach($chunk as $rid) {
                    $i = 0;
                    foreach($newData as $entry) {
                        $dataArray[] = [
                            'rid' => $rid,
                            'fid' => $field->fid,
                            'flid' => $field->flid,
                            'field_num' => 1,
                            'list_index' => $i,
                            'data' => (!$one_is_num) ? $entry[0] : null,
                            'number' => ($one_is_num) ? $entry[0] : null,
                            'created_at' => $now,
                            'updated_at' => $now
                        ];
                        $dataArray[] = [
                            'rid' => $rid,
                            'fid' => $field->fid,
                            'flid' => $field->flid,
                            'field_num' => 2,
                            'list_index' => $i,
                            'data' => (!$two_is_num) ? $entry[1] : null,
                            'number' => ($two_is_num) ? $entry[1] : null,
                            'created_at' => $now,
                            'updated_at' => $now
                        ];
                        $i++;
                    }
                }

                DB::table(self::SUPPORT_NAME)->insert($dataArray);
            }
        }
    }

    /**
     * Validates the record data for a field against the field's options.
     *
     * @param  int $flid - The field internal name
     * @param  array $field - The field data array to validate
     * @param  Request $request
     * @param  bool $forceReq - Do we want to force a required value even if the field itself is not required?
     * @return array - Array of errors
     */
    public function validateField($flid, $field, $request, $forceReq = false) {
        $req = $field['required'];

        if(($req==1 | $forceReq) && !isset($request[$flid.'_combo_one']))
            return [$flid => $field['name'].' is required'];

        return array();
    }

    /**
     * Formats data for record entry.
     *
     * @param  array $field - The field to represent record data
     * @param  string $value - Data to add
     * @param  Request $request
     *
     * @return mixed - Processed data
     */
    public function processRecordData($field, $value, $request) {
        dd($value);
        if($value=='')
            $value = null;
        return $value;
    }

    /**
     * Formats data for revision display.
     *
     * @param  mixed $data - The data to store
     * @param  Request $request
     *
     * @return mixed - Processed data
     */
    public function processRevisionData($data) {
        return $data;
    }

    /**
     * Formats data for record entry.
     *
     * @param  string $flid - Field ID
     * @param  array $field - The field to represent record data
     * @param  array $value - Data to add
     * @param  Request $request
     *
     * @return Request - Processed data
     */
    public function processImportData($flid, $field, $value, $request) {
        $request[$flid] = $value;

        return $request;
    }

    /**
     * Formats data for record entry.
     *
     * @param  string $flid - Field ID
     * @param  array $field - The field to represent record data
     * @param  \SimpleXMLElement $value - Data to add
     * @param  Request $request
     * @param  bool $simple - Is this a simple xml field value
     *
     * @return Request - Processed data
     */
    public function processImportDataXML($flid, $field, $value, $request, $simple = false) {
        $request[$flid] = (string)$value;

        return $request;
    }

    /**
     * Formats data for record display.
     *
     * @param  array $field - The field to represent record data
     * @param  string $value - Data to display
     *
     * @return mixed - Processed data
     */
    public function processDisplayData($field, $value) {
        if($field['options']['MultiLine'])
            return nl2br($value);
        else
            return $value;
    }

    /**
     * Formats data for XML record display.
     *
     * @param  string $field - Field ID
     * @param  string $value - Data to format
     *
     * @return mixed - Processed data
     */
    public function processXMLData($field, $value) {
        return "<$field>".htmlspecialchars($value, ENT_XML1, 'UTF-8')."</$field>";
    }

    /**
     * Formats data for XML record display.
     *
     * @param  string $value - Data to format
     *
     * @return mixed - Processed data
     */
    public function processLegacyData($value) {
        return $value;
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
                } else if($typeone == 'Date') {
                    $xml .= utf8_encode('MM/DD/YYYY');
                } else if($typeone == 'Multi-Select List' | $typeone == 'Generated List' | $typeone == 'Associator') {
                    $xml .= '<value>'.utf8_encode('VALUE 1').'</value>';
                    $xml .= '<value>'.utf8_encode('VALUE 2').'</value>';
                    $xml .= '<value>'.utf8_encode('so on..').'</value>';
                }
                $xml .= '</' . Field::xmlTagClear($nameone) . '>';
                $xml .= '<' . Field::xmlTagClear($nametwo) . '>';
                if($typetwo == 'Text' | $typetwo == 'Number' | $typetwo == 'List') {
                    $xml .= utf8_encode('VALUE');
                } else if($typetwo == 'Date') {
                    $xml .= utf8_encode('MM/DD/YYYY');
                } else if($typetwo == 'Multi-Select List' | $typetwo == 'Generated List' | $typetwo == 'Associator') {
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
                } else if($typeone == 'Date') {
                    $valArray[$nameone] = 'MM/DD/YYYY';
                } else if($typeone == 'Multi-Select List' | $typeone == 'Generated List' | $typeone == 'Associator') {
                    $valArray[$nameone] = array('VALUE 1','VALUE 2','so on...');
                }

                if($typetwo == 'Text' | $typetwo == 'Number' | $typetwo == 'List') {
                    $valArray[$nametwo] = 'VALUE';
                } else if($typetwo == 'Date') {
                    $valArray[$nametwo] = 'MM/DD/YYYY';
                } else if($typetwo == 'Multi-Select List' | $typetwo == 'Generated List' | $typetwo == 'Associator') {
                    $valArray[$nametwo] = array('VALUE 1','VALUE 2','so on...');
                }

                $fieldArray[$slug]['value'][] = $valArray;

                return $fieldArray;
                break;
        }
    }

    /**
     * For a test record, add test data to field.
     *
     * @param  string $url - Url for File Type Fields
     * @return mixed - The data
     */
    public function getTestData($url = null) {
        return '';
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
     * Performs a keyword search on this field and returns any results.
     *
     * @param  string $flid - Field ID
     * @param  string $arg - The keywords
     * @param  Record $recordMod - Model to search through
     * @param  boolean $negative - Get opposite results of the search
     * @return array - The RIDs that match search
     */
    public function keywordSearchTyped($flid, $arg, $recordMod, $negative = false) {
        return DB::table(self::SUPPORT_NAME)
            ->select("rid")
            ->where("flid", "=", $flid)
            ->where(function($query) use ($arg) {
                $num = floatval($arg);

                $query->where('data','LIKE',"%$arg%")
                    ->orWhereBetween("number", [$num - NumberField::EPSILON, $num + NumberField::EPSILON]);
            })
            ->distinct()
            ->pluck('rid')
            ->toArray();
    }

    /**
     * Build the advanced query for a text field.
     *
     * @param  $flid, field id
     * @param  $query, contents of query.
     * @param  Record $recordMod - Model to search through
     * @param  boolean $negative - Get opposite results of the search
     * @return array - The RIDs that match search
     */
    public function advancedSearchTyped($flid, $query, $recordMod, $negative = false) {
        $field = Field::where("flid", "=", $flid)->first();
        $type_1 = self::getComboFieldType($field, 'one');
        $type_2 = self::getComboFieldType($field, 'two');

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

        return $db_query->distinct()
            ->pluck('rid')
            ->toArray();
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
        $db_prefix = config('database.connections.mysql.prefix');

        switch($type){
            case Field::_NUMBER:
                NumberField::buildAdvancedNumberQuery($db_query,
                    $query[$flid . "_" . $field_num . "_left"],
                    $query[$flid . "_" . $field_num . "_right"],
                    isset($query[$flid . "_" . $field_num . "_invert"]),
                    $db_prefix. $prefix);
                break;
            case Field::_DATE:
                $input = $query[$flid . "_" . $field_num . "_month"].'/'
                    .$query[$flid . "_" . $field_num . "_day"].'/'
                    .$query[$flid . "_" . $field_num . "_year"];

                $prefix = ($prefix == "") ? self::SUPPORT_NAME : substr($prefix, 0, -1);
                $input = Search::prepare($input);
                $db_query->orWhereRaw("`" . $db_prefix . $prefix . "`.`data` LIKE %?%", [$input]);
                break;
            case Field::_MULTI_SELECT_LIST:
            case Field::_GENERATED_LIST:
            case Field::_ASSOCIATOR:
                $inputs = $query[$flid . "_" . $field_num . "_input[]"];

                $prefix = ($prefix == "") ? self::SUPPORT_NAME : substr($prefix, 0, -1);
                $db_query->where(function($db_query) use ($inputs, $prefix, $db_prefix) {
                    foreach($inputs as $input) {
                        $input = Search::prepare($input);
                        $db_query->orWhereRaw("`" . $db_prefix . $prefix . "`.`data` LIKE %?%", [$input]);
                    }
                });
                break;
            default: //Text and List
                $input = $query[$flid . "_" . $field_num . "_input"];

                $prefix = ($prefix == "") ? self::SUPPORT_NAME : substr($prefix, 0, -1);
                $input = Search::prepare($input);
                $db_query->orWhereRaw("`" . $db_prefix . $prefix . "`.`data` LIKE %?%", [$input]);
                break;
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
        $options = array();
        foreach (self::getComboFieldOption($field, 'Options', $fnum) as $option) {
            $options[$option] = $option;
        }
        return $options;
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
    // DEPRECIATED, REMOVE
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
    // DEPRECIATED, REMOVE
    public static function getComboFieldType($field, $num) {
        $options = $field['options'];

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
        return $field['options'][$num][$key];
    }
}
