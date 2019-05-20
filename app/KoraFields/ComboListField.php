<?php namespace App\KoraFields;

use App\Form;
use App\Http\Controllers\AssociationController;
use App\Http\Controllers\FieldController;
use App\Http\Controllers\FormController;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Collection;
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
        'Text Fields' => array(
            'Text' => 'Text',
            'Integer' => 'Integer',
            'Float' => 'Float'
        ),
        'Date Fields' => array(
            'Date' => 'Date',
            'Historical Date' => 'Historical Date'
        ),
        'List Fields' => array(
            'List' => 'List',
            'Multi-Select List' => 'Multi-Select List',
            'Generated List' => 'Generated List'
        ),
        'Specialty Fields' => array(
            'Boolean' => 'Boolean',
            'Associator' => 'Associator'
        )
    ];

    static public $supportedViews = [
        'Text' => 'text',
        'List' => 'list',
        'Integer' => 'integer',
        'Float' => 'float',
        'Date' => 'date',
        'Historical Date' => 'historicdate',
        'Multi-Select List' => 'mslist',
        'Generated List' => 'genlist',
        'Associator' => 'associator',
        // 'Boolean' => 'boolean' TODO::CASTLE
    ];

    private $fieldToDBFuncAssoc = [
        'Text' => 'addTextColumn',
        'List' => 'addEnumColumn',
        'Integer' => 'addIntegerColumn',
        'Float' => 'addDoubleColumn',
        'Date' => 'addDateColumn',
        'Historical Date' => 'addJSONColumn',
        'Multi-Select List' => 'addJSONColumn',
        'Generated List' => 'addJSONColumn',
        'Associator' => 'addJSONColumn',
        'Boolean' => 'addBooleanColumn'
    ];

    private $fieldModel = [
        'Text' => 'App\KoraFields\TextField',
        'List' => 'App\KoraFields\ListField',
        'Integer' => 'App\KoraFields\IntegerField',
        'Float' => 'App\KoraFields\FloatField',
        'Date' => 'App\KoraFields\DateField',
        'Historical Date' => 'App\KoraFields\HistoricalDateField',
        'Multi-Select List' => 'App\KoraFields\MultiSelectListField',
        'Generated List' => 'App\KoraFields\GeneratedListField',
        'Associator' => 'App\KoraFields\AssociatorField',
        'Boolean' => 'App\KoraFields\BooleanField'
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
        $table = new \CreateRecordsTable();
        $table->addJSONColumn($fid, $slug);

        $ctable = new \CreateRecordsTable(
            ['tablePrefix' => $slug]
        );
        $ctable->createComboListTable($fid);

        foreach ($options as $option) {
            $method = $this->fieldToDBFuncAssoc[$option['type']];
            $ctable->{$method}($fid, $option['name']);
        }
    }


    /**
     * Gets the default options string for a new field.
     *
     * @return array - The default options
     */
    public function getDefaultOptions($type = null) {
        $className = $this->fieldModel[$type];
        $object = new $className;
        return $object->getDefaultOptions();
    }

    /**
     * Update the options for a field
     *
     * @param  array $field - Field to update options
     * @param  Request $request
     * @param  int $flid - The field internal name
     * @return array - The updated field array
     */
    public function updateOptions($field, Request $request, $flid = null, $prefix = 'records_') {

        foreach (['one', 'two'] as $seq) {
            $defaults = $parts = array();
            $type = $request->{'type' . $seq};

            switch($type) {
                case Form::_TEXT:
                    $defaults = array(
                        'default',
                        'regex',
                        'multi'
                    );
                    break;
                case Form::_INTEGER:
                case Form::_FLOAT:
                    $defaults = array(
                        'default',
                        'min',
                        'max',
                        'unit'
                    );
                    break;
                case Form::_MULTI_SELECT_LIST:
                case Form::_LIST:
                    $defaults = array(
                        'default',
                        'options'
                    );
                    break;
                case Form::_GENERATED_LIST:
                    $defaults = array(
                        'default',
                        'options',
                        'regex'
                    );
                    break;
                case Form::_DATE:
                    $defaults = array(
                        'default_month',
                        'default_day',
                        'default_year',
                        'start',
                        'end',
                        'format'
                    );
                    $parts = array(
                        'day',
                        'month',
                        'year'
                    );
                    break;
                case Form::_HISTORICAL_DATE:
                    $defaults = array(
                        'default_month',
                        'default_day',
                        'default_year',
                        'circa',
                        'era',
                        'start',
                        'end',
                        'format'
                    );
                    $parts = array(
                        'day',
                        'month',
                        'year',
                        'circa',
                        'era'
                    );
                    break;
                case Form::_ASSOCIATOR:
                    $fid = '';
                    foreach(array_keys($request->all()) as $key) {
                        if(substr( $key, 0, 8 ) === "checkbox") {
                            $fid = explode('_',$key)[1];
                            break;
                        }
                    }
                    $defaults = array(
                        'default',
                        'checkbox_' . $fid,
                        'preview_' . $fid
                    );
                    break;
                case Form::_BOOLEAN:
                    $defaults = array(
                        'default'
                    );
                    break;
            }

            if (
                (
                    $type == Form::_GENERATED_LIST ||
                    $type == Form::_MULTI_SELECT_LIST ||
                    $type == Form::_ASSOCIATOR
                ) &&
                !is_null($request->{'default_combo_' . $seq})
            ) {
                $values = array();
                foreach ($request->{'default_combo_' . $seq} as $value) {
                    array_push($values, json_decode($value));
                }
                $request->{'default_combo_' . $seq} = $values;
            }

            $className = $this->fieldModel[$request->{'type' . $seq}];
            $object = new $className;
            foreach($defaults as $default) {
                $request->merge(
                    [$default => $request->{$default . '_' . $seq}]
                );

            }
            $field[$seq] = $object->updateOptions(
                $field[$seq],
                $request,
                $field[$seq]['flid'],
                $flid
            );

            if ($type == Form::_DATE || $type == Form::_HISTORICAL_DATE) {
                $size = 0;
                $field[$seq]['default'] = [];

                // Determine the largest size of default
                foreach ($parts as $part) {
                    if ($request->{'default_' . $part .'_combo_' . $seq} && count($request->{'default_' . $part .'_combo_' . $seq}) > $size)
                        $size = count($request->{'default_' . $part .'_combo_' . $seq});
                }

                // Build and add default date
                for ($i=0; $i < $size; $i++) {
                    $defaultDate = [];
                    foreach ($parts as $part) {
                        $defaultDate[$part] = $request->{'default_' . $part .'_combo_' . $seq}[$i];
                    }
                    array_push($field[$seq]['default'], $defaultDate);
                }
            } else {
                $field[$seq]['default'] = $request->{'default_combo_' . $seq};
            }
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
            $one_is_num = $field['one']['type'] == 'Number';
            $two_is_num = $field['two']['type'] == 'Number';
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
        $values = array();
        foreach(['_combo_one' => 'one', '_combo_two' => 'two'] as $affix => $seq) {
            $value = $request->{$field['flid'] . $affix};
            if ($value == '')
                $value = null;
            $values[$seq] = $value;
        }
        return $values;
    }

    /**
     * Formats data for revision display.
     *
     * @param  mixed $data - The data to store
     * @param  Request $request
     *
     * @return mixed - Processed data
     */
    public function processRevisionData($data) { // TODO::CASTLE
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
    public function processImportData($flid, $field, $value, $request) { // TODO::CASTLE
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
    public function processImportDataXML($flid, $field, $value, $request, $simple = false) { // TODO::CASTLE
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
        // See retrieve()
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
        $fid = explode('_', $slug)[2];
        $form = FormController::getForm($fid);
        $field = $form->layout['fields'][$slug];

        $typeone = $field['one']['type'];
        $typetwo = $field['two']['type'];
        $nameone = $field['one']['name'];
        $nametwo = $field['two']['name'];

        switch($type) {
            case "XML":
                $xml = '<' . $slug . '>';
                $xml .= '<Value>';
                $xml .= '<' . $nameone . '>';
                if($typeone == 'Text' | $typeone == 'Integer' | $typeone == 'Float' | $typeone == 'List' | $typeone == 'Boolean') {
                    $xml .= utf8_encode('VALUE');
                } else if($typeone == 'Date') {
                    $xml .= utf8_encode('MM/DD/YYYY');
                } else if($typeone == 'Multi-Select List' | $typeone == 'Generated List' | $typeone == 'Associator') {
                    $xml .= '<value>'.utf8_encode('VALUE 1').'</value>';
                    $xml .= '<value>'.utf8_encode('VALUE 2').'</value>';
                    $xml .= '<value>'.utf8_encode('so on..').'</value>';
                }
                $xml .= '</' . $nameone . '>';
                $xml .= '</' . $nametwo . '>';
                if($typetwo == 'Text' | $typeone == 'Integer' | $typeone == 'Float' | $typetwo == 'List' | $typetwo == 'Boolean') {
                    $xml .= utf8_encode('VALUE');
                } else if($typetwo == 'Date') {
                    $xml .= utf8_encode('MM/DD/YYYY');
                } else if($typetwo == 'Multi-Select List' | $typetwo == 'Generated List' | $typetwo == 'Associator') {
                    $xml .= '<value>'.utf8_encode('VALUE 1').'</value>';
                    $xml .= '<value>'.utf8_encode('VALUE 2').'</value>';
                    $xml .= '<value>'.utf8_encode('so on..').'</value>';
                }
                $xml .= '</' . $nametwo . '>';
                $xml .= '</Value>';
                $xml .= '</' . $slug . '>';

                return $xml;
                break;
            case "JSON":
                $fieldArray = [$slug => ['type' => 'Combo List']];

                $valArray = array();
                if($typeone == 'Text' | $typeone == 'Integer' | $typeone == 'Float' | $typeone == 'List' | $typeone == 'Boolean') {
                    $valArray[$nameone] = 'VALUE';
                } else if($typeone == 'Date') {
                    $valArray[$nameone] = 'MM/DD/YYYY';
                } else if($typeone == 'Multi-Select List' | $typeone == 'Generated List' | $typeone == 'Associator') {
                    $valArray[$nameone] = array('VALUE 1','VALUE 2','so on...');
                }

                if($typetwo == 'Text' | $typetwo == 'Integer' | $typetwo == 'Float' | $typetwo == 'List' | $typetwo == 'Boolean') {
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
    public function getTestData($url = null) { // TODO::CASTLE
        return '';
    }

    /**
     * Updates the request for an API search to mimic the advanced search structure.
     *
     * @param  array $data - Data from the search
     * @return Request - The update request
     */
    public function setRestfulAdvSearch($data) {
        $return = [];

        $flid = $data->$flid;

        $field = FieldController::getField($flid);
        $type1 = $field['one']['type'];
        switch($type1) {
            case Field::_INTEGER:
            case Field::_FLOAT:
                if(isset($data->left_one))
                    $leftNum = $data->left_one;
                else
                    $leftNum = '';
                $return[$flid.'_1_left'] = $leftNum;
                if(isset($data->right_one))
                    $rightNum = $data->right_one;
                else
                    $rightNum = '';
                $return[$flid.'_1_right'] = $rightNum;
                if(isset($data->invert_one))
                    $invert = $data->invert_one;
                else
                    $invert = 0;
                $return[$flid.'_1_invert'] = $invert;
                break;
            default:
                $return[$flid.'_1_input'] = $data->input_one;
                break;
        }
        $type2 = $field['two']['type'];
        switch($type2) {
            case Field::_INTEGER:
            case Field::_FLOAT:
                if(isset($data->left_two))
                    $leftNum = $data->left_two;
                else
                    $leftNum = '';
                $return[$flid.'_2_left'] = $leftNum;
                if(isset($data->right_two))
                    $rightNum = $data->right_two;
                else
                    $rightNum = '';
                $return[$flid.'_2_right'] = $rightNum;
                if(isset($data->invert_two))
                    $invert = $data->invert_two;
                else
                    $invert = 0;
                $return[$flid.'_2_invert'] = $invert;
                break;
            default:
                $return[$flid.'_2_input'] = $data->input_two;
                break;
        }
        $return[$flid.'_operator'] = $data->operator;

        return $return;
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
    public function keywordSearchTyped($flid, $arg, $recordMod, $negative = false) { //TODO::CASTLE
        return [];
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
        $type_1 = $field['one']['type'];
        $type_2 = $field['two']['type'];

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
            $name = $field['one']['name'];
            return response()->json(["status"=>false,"message"=>$validateOne,"sub_field_name"=>$name],500);
        }

        $validateTwo = self::validateComboListField($field,$typetwo,$valtwo);
        if($validateTwo!="sub_field_validated") {
            $name = $field['two']['name'];
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
            case "Integer":
            case "Float":
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
     * Gets an option of a combo list sub field
     *
     * @param  Field $field - Combo field to inspect
     * @param  string $key - The option we want
     * @param  int $seq - Sequence of sub field
     * @return string - The option
     */
    public static function getComboFieldOption($field, $key, $seq) {
        return $field[$seq]['options'][$key];
    }

    public function save(array $options = array()) {
        $field = $options['field'];
        $values = $options['values'];
        $table = $field['flid'] . $options['fid'];
        $rid = $options['rid'];

        DB::transaction(function() use ($field, $rid, $values, $table) {
            DB::table($table)->where('record_id', '=', $rid)->delete();
            for($i=0; $i < count($values['one']); $i++) {
                DB::table($table)->insert(
                    [
                        'record_id' => $rid,
                        $field['one']['flid'] => $values['one'][$i],
                        $field['two']['flid'] => $values['two'][$i]
                    ]
                );
            }
        });

        $ids = DB::table($table)->where('record_id', $rid)->pluck('id');

        return $ids->toJson();
    }

    public function retrieve($flid, $fid, $ids) {
        $this->setTable($flid . $fid);
        return $this->findMany(json_decode($ids));
    }
}
