<?php namespace App\KoraFields;

use App\Form;
use App\Http\Controllers\FormController;
use App\Record;
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
     * @var string - Views for the typed field options
     */
    const FIELD_OPTIONS_VIEW = "partials.fields.options.combolist";
    const FIELD_ADV_OPTIONS_VIEW = null;
    const FIELD_ADV_INPUT_VIEW = "partials.records.advanced.combolist";
    const FIELD_INPUT_VIEW = "partials.records.input.combolist";
    const FIELD_DISPLAY_VIEW = "partials.records.display.combolist";

    /**
     * @var string - Method from CreateRecordsTable() for adding to DB
     */
    const FIELD_DATABASE_METHOD = 'addJSONColumn';

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
     * Create DB column for this field. Combo overwrites parent to add sub field columns to combo table
     *
     * @param  int $fid - Form ID
     * @param  string $slug - Name of database column based on field internal name
     * @param  string $method - The add column function from CreateRecordsTable to be used
     * @param  array $options - Extra information we may need to set up about the field
     */
    public function addDatabaseColumn($fid, $slug, $method, $options = null) {
        parent::addDatabaseColumn($fid, $slug, $method, $options);

        $ctable = new \CreateRecordsTable(
            ['tablePrefix' => $slug]
        );
        $ctable->createComboListTable($fid);

        $form = FormController::getForm($fid);
        foreach($options as $option) {
            $fieldMod = $form->getFieldModel($option['type']);
            $ctable->{$fieldMod::FIELD_DATABASE_METHOD}($fid, $option['name']);
        }
    }


    /**
     * Gets the default options string for a new field.
     *
     * @return array - The default options
     */
    public function getDefaultOptions($type = null) {
        $modName = 'App\\KoraFields\\'.Form::$fieldModelMap[$type];
        $object = new $modName();
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
    public function updateOptions($field, Request $request, $flid = null, $prefix = 'records_') { //TODO::COMBO
        $requestData = array_keys($request->all());
        //dd($request->all());
        foreach (['one', 'two'] as $seq) {
            $updateIndices = preg_grep('/\w+_'.$seq.'/',$requestData);
            $type = $request->{'type' . $seq};

//            if (
//                (
//                    $type == Form::_GENERATED_LIST ||
//                    $type == Form::_MULTI_SELECT_LIST ||
//                    $type == Form::_ASSOCIATOR
//                ) &&
//                !is_null($request->{'default_combo_' . $seq})
//            ) {
//                $values = array();
//                foreach ($request->{'default_combo_' . $seq} as $value) {
//                    array_push($values, json_decode($value));
//                }
//                $request->{'default_combo_' . $seq} = $values;
//            }

            $form = new Form();
            $fieldRequest = new Request();
            $className = $form->getFieldModel($type);
            $object = new $className;
            foreach($updateIndices as $index) {
                $fieldRequest->{str_replace("_$seq",'',$index)} = $request->{$index};
            }
            $field[$seq] = $object->updateOptions(
                $field[$seq],
                $fieldRequest,
                $field[$seq]['flid'],
                $flid
            );

//            if ($type == Form::_DATE || $type == Form::_HISTORICAL_DATE) {
//                $size = 0;
//                $field[$seq]['default'] = [];
//
//                // Determine the largest size of default
//                foreach ($parts as $part) {
//                    if ($request->{'default_' . $part .'_combo_' . $seq} && count($request->{'default_' . $part .'_combo_' . $seq}) > $size)
//                        $size = count($request->{'default_' . $part .'_combo_' . $seq});
//                }
//
//                // Build and add default date
//                for ($i=0; $i < $size; $i++) {
//                    $defaultDate = [];
//                    foreach ($parts as $part) {
//                        $defaultDate[$part] = $request->{'default_' . $part .'_combo_' . $seq}[$i];
//                    }
//                    array_push($field[$seq]['default'], $defaultDate);
//                }
//            } else {
//                $field[$seq]['default'] = $request->{'default_combo_' . $seq};
//            }

            $field[$seq]['default'] = $request->{'default_combo_' . $seq};
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
    public function massAssignRecordField($form, $flid, $formFieldValue, $request, $overwrite=0) { //TODO::COMBO

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
    public function massAssignSubsetRecordField($form, $flid, $formFieldValue, $request, $kids) { //TODO::COMBO

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
        if(is_array($value))
            return $value;

        $values = array();
        foreach(['_combo_one' => 'one', '_combo_two' => 'two'] as $suffix => $seq) {
            $value = $request->{$field['flid'] . $suffix};
            if($value == '')
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
    public function processRevisionData($data) { // TODO::COMBO
        $return = '';
        foreach($data as $d) {
            $return .= '<div>'.$d['cfOne'].' --- '.$d['cfTwo'].'</div>';
        }
        return $return;
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
    public function processImportData($flid, $field, $value, $request) { //TODO::COMBO
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
    public function processImportDataXML($flid, $field, $value, $request) { //TODO::COMBO
        $request[$flid] = $flid;

        // Setting up for return request
        foreach (['_combo_one', '_combo_two'] as $suffix) {
            $request[$flid . $suffix] = [];
        }

        foreach ($value as $xml) {
            foreach ($xml as $name => $subValue) {
                $type = $subFlid = $subSeq = '';
                foreach (['one', 'two'] as $seq) {
                    if ($field[$seq]['name'] == str_replace('_', ' ', $name)) {
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
    public function processImportDataCSV($flid, $field, $value, $request) { //TODO::COMBO
        $request[$flid] = $flid;

        // Setting up for return request
        foreach (['_combo_one', '_combo_two'] as $suffix) {
            $request[$flid . $suffix] = [];
        }
        $value = simplexml_load_string('<?xml version="1.0" encoding="utf-8"?><document>'. $value . '</document>');

        foreach ($value as $json) {
            foreach ($json as $name => $subValue) {
                $type = $subFlid = $subSeq = '';
                foreach (['one', 'two'] as $seq) {
                    if ($field[$seq]['name'] == str_replace('_', ' ', $name)) {
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
     * Formats data for record display.
     *
     * @param  array $field - The field to represent record data
     * @param  string $value - Data to display
     *
     * @return mixed - Processed data
     */
    public function processDisplayData($field, $value) { //TODO::COMBO
        // See retrieve()
        return $value;
    }

    /**
     * Formats data for XML record display.
     *
     * @param  string $field - Field ID
     * @param  string $value - Data to format
     * @param  int $fid - Form ID
     *
     * @return mixed - Processed data
     */
    public function processXMLData($field, $value, $fid = null) { //TODO::COMBO
        $form = FieldController::getField($field, '1');
        $records = $this->retrieve($field, $fid, $value);
        $xml = "<$field>";
        foreach($records as $record) {
            $value = '<Value>';
            foreach (['one', 'two'] as $seq) {
                $className = $this->fieldModel[$form[$seq]['type']];
                $object = new $className;
                $value .= $object->processXMLData(
                    $form[$seq]['name'],
                    $record->{$form[$seq]['flid']}
                );
            }
            $value .= '</Value>';
            $xml .= $value;
        }
        $xml .= "</$field>";

        return $xml;
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
    public function setRestfulAdvSearch($data) { //TODO::COMBO
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
    public function keywordSearchTyped($flid, $arg, $recordMod, $form, $negative = false) { //TODO::COMBO
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
    public function advancedSearchTyped($flid, $query, $recordMod, $form, $negative = false) { //TODO::COMBO
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
    public static function getComboList($field, $blankOpt=false, $fnum) { //TODO::COMBO
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
    public static function getComboFieldOption($field, $key, $seq) { //TODO::COMBO
        return $field[$seq]['options'][$key];
    }

    public function save(array $options = array()) { //TODO::COMBO
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

    public function retrieve($flid, $fid, $ids) { //TODO::COMBO
        $this->setTable($flid . $fid);
        return $this->findMany(json_decode($ids));
    }
}
