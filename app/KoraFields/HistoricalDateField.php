<?php namespace App\KoraFields;

use App\Form;
use App\Http\Controllers\FieldController;
use App\Record;
use Illuminate\Http\Request;

class HistoricalDateField extends BaseField {

    /*
    |--------------------------------------------------------------------------
    | Historical Date Field
    |--------------------------------------------------------------------------
    |
    | This model represents the historical date field in Kora3
    |
    */

    /**
     * @var string - Views for the typed field options
     */
    const FIELD_OPTIONS_VIEW = "partials.fields.options.historicdate";
    const FIELD_ADV_OPTIONS_VIEW = "partials.fields.advanced.historicdate";
    const FIELD_ADV_INPUT_VIEW = "partials.records.advanced.historicdate"; //TODO::CASTLE
    const FIELD_INPUT_VIEW = "partials.records.input.historicdate"; //TODO::CASTLE
    const FIELD_DISPLAY_VIEW = "partials.records.display.historicdate"; //TODO::CASTLE

    /**
     * @var string - Month day year format
     */
    const MONTH_DAY_YEAR = "MMDDYYYY";
    /**
     * @var string - Day month year format
     */
    const DAY_MONTH_YEAR = "DDMMYYYY";
    /**
     * @var string - Year month day format
     */
    const YEAR_MONTH_DAY = "YYYYMMDD";

    /**
     * @var array - The months of the year in different languages
     *
     * These are listed without special characters because the input will be converted to close characters.
     * Formatted with regular expression tags to find only the exact month so "march" does not match "marches" for example.
     */
    const MONTHS_IN_LANG = [
        "/(\\W|^)january(\\W|$)/i", "/(\\W|^)february(\\W|$)/i", "/(\\W|^)march(\\W|$)/i",
        "/(\\W|^)april(\\W|$)/i", "/(\\W|^)may(\\W|$)/i", "/(\\W|^)june(\\W|$)/i",
        "/(\\W|^)july(\\W|$)/i", "/(\\W|^)august(\\W|$)/i", "/(\\W|^)september(\\W|$)/i",
        "/(\\W|^)october(\\W|$)/i", "/(\\W|^)november(\\W|$)/i", "/(\\W|^)december(\\W|$)/i"
    ];

    /**
     * @var array - We currently support 3 languages, so this is an array of 3 copies of the number of 1 through 12
     */
    const MONTH_NUMBERS = [ 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 ];

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
    }

    /**
     * Gets the default options string for a new field.
     *
     * @return array - The default options
     */
    public function getDefaultOptions() {
        return [
            'ShowCirca' => 0,
            'ShowEra' => 0,
            'Start' => 1900,
            'End' => 2030,
            'Format' => 'MMDDYYYY'
        ];
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
        if(self::validateDate($request->default_month,$request->default_day,$request->default_year)) {
            $default = [
                'month' => $request->default_month,
                'day' => $request->default_day,
                'year' => $request->default_year,
                'circa' => !is_null($request->default_circa) ? $request->default_circa : 0,
                'era' => !is_null($request->default_era) ? $request->default_era : 'CE'
            ];
        } else {
            $default = null;
        }

        if($request->start=='' | $request->start==0)
            $request->start = 1;

        if($request->end=='')
            $request->end = 9999;

        //If the years don't make sense, flip em
        if($request->start > $request->end) {
            $pivot = $request->start;
            $request->start = $request->end;
            $request->end = $pivot;
        }

        $field['default'] = $default;
        $field['options']['ShowCirca'] = $request->circa;
        $field['options']['ShowEra'] = $request->era;
        $field['options']['Start'] = $request->start;
        $field['options']['End'] = $request->end;
        $field['options']['Format'] = $request->format;

        return $field;
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
        $start = $field['options']['Start'];
        $end = $field['options']['End'];
        $month = $request->input('month_'.$flid,'');
        $day = $request->input('day_'.$flid,'');
        $year = $request->input('year_'.$flid,'');

        if(($req==1 | $forceReq) && $month=='' && $day=='' && $year=='')
            return [
                'month_'.$flid.'_chosen' => $field['name'].' is required',
                'day_'.$flid.'_chosen' => ' ',
                'year_'.$flid.'_chosen' => ' '
            ];

        if(($year<$start | $year>$end) && $year!='')
            return [
                'year_'.$flid.'_chosen' => $field['name'].'\'s year is outside of the expected range'
            ];

        if(!self::validateDate($month,$day,$year))
            return [
                'month_'.$flid.'_chosen' => $field['name'].' is an invalid date',
                'day_'.$flid.'_chosen' => ' ',
                'year_'.$flid.'_chosen' => ' '
            ];

        return array();
    }

    /**
     * Validates the month, day, year combonations so illegal dates can't happen.
     *
     * @param  int $m - Month
     * @param  int $d - Day
     * @param  int $y - Year
     * @return bool - Is valid
     */
    private static function validateDate($m,$d,$y) {
        //No blank date
        //No month without a year.
        //No day without a month.
        if(
            ($m=='' && $d=='' && $y=='') | ($m!='' && $y=='') | ($d!='' && $m=='')
        ) {
            return false;
        }

        //Next we need to make sure the date provided is legal (i.e. no Feb 30th, etc)
        //For the check we need to default any blank values to 1, cause checkdate doesn't like partial dates
        if($m=='') {$m=1;}
        if($d=='') {$d=1;}
        if($y=='') {$y=1;}

        return checkdate($m, $d, $y);
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
    public function processRecordData($field, $value, $request) { //TODO::CASTLE
        if(empty($value))
            $value = null;
        return json_encode($value);
    }

    /**
     * Formats data for revision display.
     *
     * @param  mixed $data - The data to store
     * @param  Request $request
     *
     * @return mixed - Processed data
     */
    public function processRevisionData($data) { //TODO::CASTLE
        $data = json_decode($data,true);
        $return = '';
        foreach($data as $record) {
            $return .= "<div>".$record."</div>";
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
    public function processImportData($flid, $field, $value, $request) { //TODO::CASTLE
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
    public function processImportDataXML($flid, $field, $value, $request, $simple = false) { //TODO::CASTLE
        $request[$flid] = (array)$value->Record;

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
    public function processDisplayData($field, $value) { //TODO::CASTLE
        return json_decode($value,true);
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
        $recs = json_decode($value,true);
        $xml = "<$field>";
        foreach($recs as $rec) {
            $xml .= '<Record>'.$rec.'</Record>';
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
    public function processLegacyData($value) { //TODO::CASTLE
        return $value;
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
        $recModel = new Record(array(),$form->id);
        if($overwrite)
            $recModel->newQuery()->update([$flid => $formFieldValue]);
        else
            $recModel->newQuery()->whereNull($flid)->update([$flid => $formFieldValue]);
    }

    /**
     * For a test record, add test data to field.
     *
     * @param  string $url - Url for File Type Fields
     * @return mixed - The data
     */
    public function getTestData($url = null) { //TODO::CASTLE
        return json_encode(array('0-3-0','0-3-1','0-3-2','0-3-3'));
    }

    /**
     * Provides an example of the field's structure in an export to help with importing records.
     *
     * @param  string $slug - Field nickname
     * @param  string $expType - Type of export
     * @return mixed - The example
     */
    public function getExportSample($slug,$type) { //TODO::CASTLE
        switch($type) {
            case "XML":
                $xml = '<' . $slug . '>';
                $xml .= "<Record>".utf8_encode('0-3-0')."</Record>";
                $xml .= "<Record>".utf8_encode('0-3-1')."</Record>";
                $xml .= "<Record>".utf8_encode('0-3-2')."</Record>";
                $xml .= "<Record>".utf8_encode('0-3-3')."</Record>";
                $xml .= '</' . $slug . '>';

                return $xml;
                break;
            case "JSON":
                $fieldArray[$slug] = array('0-3-0','0-3-1','0-3-2','0-3-3');

                return $fieldArray;
                break;
        }
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
        if($negative)
            $param = 'NOT LIKE';
        else
            $param = 'LIKE';

        return $recordMod->newQuery()
            ->select("id")
            ->where($flid, $param,"%$arg%")
            ->pluck('id')
            ->toArray();
    }

    /**
     * Updates the request for an API search to mimic the advanced search structure.
     *
     * @param  array $data - Data from the search
     * @param  int $flid - Field ID
     * @param  Request $request
     * @return Request - The update request
     */
    public function setRestfulAdvSearch($data, $flid, $request) { //TODO::CASTLE
        $request->request->add([$flid.'_input' => $data->value]);

        return $request;
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
    public function advancedSearchTyped($flid, $query, $recordMod, $negative = false) { //TODO::CASTLE
        $inputs = $query[$flid . "_input"];

        if($negative)
            $param = 'NOT LIKE';
        else
            $param = 'LIKE';

        $dbQuery = $recordMod->newQuery()
            ->select("id");

        $dbQuery->where(function($dbQuery) use ($flid, $param, $inputs) {
            foreach($inputs as $arg) {
                $dbQuery->where($flid, $param, "%$arg%");
            }
        });

        return $dbQuery->pluck('id')
            ->toArray();
    }

    ///////////////////////////////////////////////END ABSTRACT FUNCTIONS///////////////////////////////////////////////

    /**
     * Formatted display of a date field value.
     *
     * @return string - The formatted string
     */
    public function displayDate() { //TODO::CASTLE
        $dateString = '';
        $fieldPreviewMod = FieldController::getField($this->flid);

        if($this->circa==1 && FieldController::getFieldOption($fieldPreviewMod,'Circa')=='Yes')
            $dateString .= 'circa ';

        if($this->month==0 && $this->day==0)
            $dateString .= $this->year;
        else if($this->day==0 && $this->year==0)
            $dateString .= \DateTime::createFromFormat('m', $this->month)->format('F');
        else if($this->day==0)
            $dateString .= \DateTime::createFromFormat('m', $this->month)->format('F').', '.$this->year;
        else if($this->year==0)
            $dateString .= \DateTime::createFromFormat('m', $this->month)->format('F').' '.$this->day;
        else if(FieldController::getFieldOption($fieldPreviewMod,'Format')=='MMDDYYYY')
            $dateString .= $this->month.'-'.$this->day.'-'.$this->year;
        else if(FieldController::getFieldOption($fieldPreviewMod,'Format')=='DDMMYYYY')
            $dateString .= $this->day.'-'.$this->month.'-'.$this->year;
        else if(FieldController::getFieldOption($fieldPreviewMod,'Format')=='YYYYMMDD')
            $dateString .= $this->year.'-'.$this->month.'-'.$this->day;

        if(\App\Http\Controllers\FieldController::getFieldOption($fieldPreviewMod,'Era')=='Yes')
            $dateString .= ' '.$this->era;

        return $dateString;
    }

    /**
     * Overwrites model save to save the record data as a date object that search will use.
     *
     * @param  array $options - Record data to save
     * @return bool - Return value from save
     */
    public function save(array $options = array()) { //TODO::CASTLE
        $dT = new DateTime();
        if($this->year=='')
            $year = 0;
        else
            $year = $this->year;
        if($this->month=='')
            $month = 0;
        else
            $month = $this->month;
        if($this->day=='')
            $day = 0;
        else
            $day = $this->day;
        $date = $dT->setDate($year,$month,$day);
        $this->date_object = date_format($date, "Y-m-d");

        return parent::save($options);
    }

    /**
     * Determines if a string is a value month name.
     * Using the month to number function, if the string is turned to a number
     * we know it is determined to be a valid month name.
     * The original string should also not be a number itself. As searches for
     * the numbers 1 through 12 should not return dates based on some month Jan-Dec.
     *
     * @param $string string - The string to test
     * @return bool - Is string valid month
     */
    public static function isMonth($string) { //TODO::CASTLE
        $monthToNumber = self::monthToNumber($string);
        return is_numeric($monthToNumber) && $monthToNumber != $string;
    }

    /**
     * Converts a month to the number corresponding to the month.
     *
     * @param $month - The month to be converted
     * @return array - Processed collection of months
     */
    public static function monthToNumber($month) { //TODO::CASTLE
        $month = preg_replace(self::MONTHS_IN_LANG, self::MONTH_NUMBERS, $month);

        return $month;
    }

    /**
     * Tests if a string is a valid era.
     *
     * @param $string - Era string
     * @return bool - True if valid
     */
    public static function isValidEra($string) { //TODO::CASTLE
        $string = strtoupper($string);
        $eras = array("CE", "BCE", "BP", "KYA BP");
        return in_array($string,$eras);
    }

    /**
     * Test if a string is equal to circa.
     *
     * @param $string - Circa string
     * @return bool - True if valid
     */
    public static function isCirca($string) { //TODO::CASTLE
        $string = strtoupper($string);
        return ($string == "CIRCA");
    }
}
