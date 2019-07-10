<?php namespace App\KoraFields;

use App\Form;
use App\Record;
use App\Search;
use App\Http\Controllers\FieldController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ComboListField extends BaseField {

    /*
    |--------------------------------------------------------------------------
    | Combo List Field
    |--------------------------------------------------------------------------
    |
    | This model represents the combo list field in kora
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
        'Boolean' => 'boolean'
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
    public function massAssignRecordField($form, $flid, $formFieldValue, $request, $overwrite=0) { //TODO::CASTLE

    }

    /**
     * Takes data from a mass assignment operation and applies it to an individual field for a set of records.
     *
     * @param  Form $form - Form model
     * @param  string $flid - Field ID
     * @param  String $formFieldValue - The value to be assigned
     * @param  Request $request
     * @param  array $kids - The KIDs to update
     */
    public function massAssignSubsetRecordField($form, $flid, $formFieldValue, $request, $kids) { //TODO::CASTLE

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
        // Assume formatted values
        if (is_array($value))
            return $value;

        $values = array();
        foreach(['_combo_one' => 'one', '_combo_two' => 'two'] as $suffix => $seq) {
            $value = $request->{$field['flid'] . $suffix};
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
        return null;
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
        $request[$flid] = $flid;

        // Setting up for return request
        foreach (['_combo_one', '_combo_two'] as $suffix) {
            $request[$flid . $suffix] = [];
        }

        foreach ($value as $json) {
            foreach ($json as $name => $subValue) {
                $type = $subFlid = $subSeq = '';
                foreach (['one', 'two'] as $seq) {
                    if ($field[$seq]['name'] == $name) {
                        $type = $field[$seq]['type'];
                        $subFlid = $field[$seq]['flid'];
                        $subSeq = $seq;
                    }
                }
                $className = $this->fieldModel[$type];
                $object = new $className;
                $request = $object->processImportData($subFlid, $field, $subValue, $request);
                $values = $request->{$flid . '_combo_' . $subSeq};
                $processedData = $object->processRecordData($field[$subSeq], $request->{$subFlid}, $request);
                array_push($values, $processedData);
                $request[$flid . '_combo_' . $subSeq] = $values;
            }
        }

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
    public function processImportDataXML($flid, $field, $value, $request) {
        $request[$flid] = $flid;

        // Setting up for return request
        foreach (['_combo_one', '_combo_two'] as $suffix) {
            $request[$flid . $suffix] = [];
        }

        foreach ($value as $xml) {
            foreach ($xml as $name => $subValue) {
                $type = $subFlid = $subSeq = '';
                foreach (['one', 'two'] as $seq) {
                    if ($field[$seq]['name'] == $name) {
                        $type = $field[$seq]['type'];
                        $subFlid = $field[$seq]['flid'];
                        $subSeq = $seq;
                    }
                }
                $className = $this->fieldModel[$type];
                $object = new $className;
                $request = $object->processImportDataXML($subFlid, $field, $subValue, $request);
                $values = $request->{$flid . '_combo_' . $subSeq};
                $processedData = $object->processRecordData($field[$subSeq], $request->{$subFlid}, $request);
                array_push($values, $processedData);
                $request[$flid . '_combo_' . $subSeq] = $values;
            }
        }

        return $request;
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
    public function processImportDataCSV($flid, $field, $value, $request) { // TODO::CASTLE
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
    public function processXMLData($field, $value) { //TODO::CASTLE
        return "<$field>".''."</$field>";
    }

    /**
     * Formats data for XML record display.
     *
     * @param  string $value - Data to format
     *
     * @return mixed - Processed data
     */
    public function processLegacyData($value) {
        return null;
    }

    /**
     * Updates the request for an API search to mimic the advanced search structure.
     *
     * @param  array $data - Data from the search
     * @return array - The update request
     */
    public function setRestfulAdvSearch($data) {
        $return = [];

        $flid = $data->{$flid};

        $field = FieldController::getField($data->{$flid}, $data->{$fid});

        foreach (['one', 'two'] as $seq) {
            $type = $field[$seq]['type'];
            $className = $this->fieldModel[$type];
            $object = new $className;
            $return[$seq] = $object->setRestfulAdvSearch($data->{$seq});
        }

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
    public function keywordSearchTyped($flid, $arg, $recordMod, $form, $negative = false) {
        if($negative)
            $param = 'NOT LIKE';
        else
            $param = 'LIKE';

        $layout = $form->layout['fields'][$flid];

        return DB::table($flid . $form->id)
            ->select("record_id")
            ->where(function($query) use ($arg, $layout, $param, $negative) {
                foreach(['one', 'two'] as $seq) {
                    $tmpArg = str_replace("%","",$arg);
                    $flid = $layout[$seq]['flid'];
                    if(is_numeric($tmpArg)) {
                        // Dealing with numbers
                        $tmpArg = [$tmpArg - self::EPSILON, $tmpArg + self::EPSILON];
                        if ($negative)
                            $query->whereNotBetween($flid, $tmpArg);
                        else
                            $query->whereBetween($flid, $tmpArg);
                    } else {
                        $query->orWhere($flid, $param,"$arg");
                    }
                }
            })
            ->pluck('record_id')
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
    public function advancedSearchTyped($flid, $query, $recordMod, $form, $negative = false) {
        $layout = $form->layout['fields'][$flid];

        return DB::table($flid . $form->id)
            ->select("record_id")
            ->where(function($db_query) use ($query, $layout, $negative) {
                foreach(['one', 'two'] as $field_num) {
                    $flid = $layout[$field_num]['flid'];
                    if (!array_key_exists($flid . "_" . $field_num, $query)) {
                        continue;
                    }
                    $type = $layout[$field_num]['type'];
                    $values = $query[$flid . "_" . $field_num];
                    switch($type){
                        case Form::_INTEGER:
                        case Form::_FLOAT:
                            IntegerField::buildAdvancedNumberQuery(
                                $db_query,
                                $values['left'],
                                $values['right'],
                                isset($values['invert'])
                            );
                            break;
                        case Form::_DATE:
                            $from = date($values['begin_year'].'-'.$values['begin_month'].'-'.$values['begin_day']);
                            $to = date($values['end_year'].'-'.$values['end_month'].'-'.$values['end_day']);

                            if($negative)
                                $db_query->whereNotBetween($flid, [$from, $to]);
                            else
                                $db_query->whereBetween($flid, [$from, $to]);
                            break;
                        case Form::_MULTI_SELECT_LIST:
                        case Form::_GENERATED_LIST:
                        case Form::_ASSOCIATOR:
                            $inputs = $values['input'];
                            if($negative) {
                                foreach($inputs as $a)
                                    $db_query->orWhereRaw("JSON_SEARCH(`$flid`,'one','$a') IS NULL");
                            } else {
                                foreach($inputs as $a)
                                    $db_query->whereRaw("JSON_SEARCH(`$flid`,'one','$a') IS NOT NULL");
                            }
                            break;
                        default: //Text, List, and Bool
                            if($negative)
                                $param = '!=';
                            else
                                $param = '=';

                            $input = $values['input'];
                            $input = Search::prepare([$input])[0];
                            $db_query->orWhere($flid, $param, "$input");
                            break;
                    }
                }
            })
            ->pluck('record_id')
            ->toArray();
    }

    ///////////////////////////////////////////////END ABSTRACT FUNCTIONS///////////////////////////////////////////////

    /**
     * Gets the list options for a combo list field.
     *
     * @param  array $field - Field to pull options from
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
     * Gets an option of a combo list sub field
     *
     * @param  array $field - Combo field to inspect
     * @param  string $key - The option we want
     * @param  int $seq - Sequence of sub field
     * @return array - The option
     */
    public static function getComboFieldOption($field, $key, $seq) {
        return $field[$seq]['options'][$key];
    }

    public function save(array $options = array()) {
        $field = $options['field'];
        $values = $options['values'];
        $table = $field['flid'] . $options['fid'];
        $rid = $options['rid'];

        if($values['one']) {
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
    }

    public function retrieve($flid, $fid, $ids) {
        $this->setTable($flid . $fid);
        return $this->findMany(json_decode($ids));
    }
}
