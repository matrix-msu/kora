<?php namespace App\KoraFields;

use App\Form;
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
    const FIELD_INPUT_VIEW = "partials.records.input.historicdate";
    const FIELD_DISPLAY_VIEW = "partials.records.display.historicdate";

    //TODO::CASTLE Might use for advanced search?
//    /**
//     * @var string - Month day year format
//     */
//    const MONTH_DAY_YEAR = "MMDDYYYY";
//    /**
//     * @var string - Day month year format
//     */
//    const DAY_MONTH_YEAR = "DDMMYYYY";
//    /**
//     * @var string - Year month day format
//     */
//    const YEAR_MONTH_DAY = "YYYYMMDD";
//
//    /**
//     * @var array - The months of the year in different languages
//     *
//     * These are listed without special characters because the input will be converted to close characters.
//     * Formatted with regular expression tags to find only the exact month so "march" does not match "marches" for example.
//     */
//    const MONTHS_IN_LANG = [
//        "/(\\W|^)january(\\W|$)/i", "/(\\W|^)february(\\W|$)/i", "/(\\W|^)march(\\W|$)/i",
//        "/(\\W|^)april(\\W|$)/i", "/(\\W|^)may(\\W|$)/i", "/(\\W|^)june(\\W|$)/i",
//        "/(\\W|^)july(\\W|$)/i", "/(\\W|^)august(\\W|$)/i", "/(\\W|^)september(\\W|$)/i",
//        "/(\\W|^)october(\\W|$)/i", "/(\\W|^)november(\\W|$)/i", "/(\\W|^)december(\\W|$)/i"
//    ];
//
//    /**
//     * @var array - We currently support 3 languages, so this is an array of 3 copies of the number of 1 through 12
//     */
//    const MONTH_NUMBERS = [ 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 ];

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
    public function getDefaultOptions($type = null) {
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
    public function updateOptions($field, Request $request, $flid = null, $prefix = 'records_') {
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

        $dateNotProvided = ($month=='' && $day=='' && $year=='');

        if(($req==1 | $forceReq) && $dateNotProvided) {
            return [
                'month_' . $flid . '_chosen' => $field['name'] . ' is required',
                'day_' . $flid . '_chosen' => ' ',
                'year_' . $flid . '_chosen' => ' '
            ];
        } else if($dateNotProvided) {
            return array();
        }

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
     * Validates the month, day, year combinations so illegal dates can't happen.
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
    public function processRecordData($field, $value, $request) {
        $date = [
            'month' => $request->input('month_'.$value,''),
            'day' => $request->input('day_'.$value,''),
            'year' => $request->input('year_'.$value,''),
            'circa' => !is_null($request->{'circa_'.$value}) ? $request->{'circa_'.$value} : 0,
            'era' => !is_null($request->{'era_'.$value}) ? $request->{'era_'.$value} : 'CE'
        ];
        if(!self::validateDate($date['month'],$date['day'],$date['year']))
            return null;
        else
            return json_encode($date);
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
        $date = json_decode($data,true);
        $return = ($date['circa']) ? 'circa ' : '';
        $return .= $date['month'].'/'.$date['day'].'/'.$date['year'];
        $return .= ' '.$date['era'];

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
    public function processImportData($flid, $field, $value, $request) {
        $request[$flid] = $flid;
        $request['month_'.$flid] = isset($value['month']) ? $value['month'] : '';
        $request['day_'.$flid] = isset($value['day']) ? $value['day'] : '';
        $request['year_'.$flid] = isset($value['year']) ? $value['year'] : '';
        $request['circa_'.$flid] = isset($value['circa']) ? $value['circa'] : 0;
        $request['era_'.$flid] = isset($value['era']) ? $value['era'] : 'CE';

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
        $request[$flid] = $flid;
        $request['month_'.$flid] = isset($value->Month) ? (string)$value->Month : '';
        $request['day_'.$flid] = isset($value->Day) ? (string)$value->Day : '';
        $request['year_'.$flid] = isset($value->Year) ? (string)$value->Year : '';
        $request['circa_'.$flid] = isset($value->Circa) ? (string)$value->Circa : 0;
        $request['era_'.$flid] = isset($value->Era) ? (string)$value->Era : 'CE';

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
        $date = json_decode($value,true);
        return $this->displayDate($date, $field);
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
        $date = json_decode($value,true);
        $xml = "<$field>";
        $xml .= '<Circa>'.$date['circa'].'</Circa>';
        $xml .= '<Month>'.$date['month'].'</Month>';
        $xml .= '<Day>'.$date['day'].'</Day>';
        $xml .= '<Year>'.$date['year'].'</Year>';
        $xml .= '<Era>'.$date['era'].'</Era>';
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
        return [
            'prefix' => $value['circa'],
            'month' => $value['month'],
            'day' => $value['day'],
            'year' => $value['year'],
            'era' => $value['era'],
            'suffix' => ''
        ];
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
        $date = [
            'month' => $request->input('month_'.$formFieldValue,''),
            'day' => $request->input('day_'.$formFieldValue,''),
            'year' => $request->input('year_'.$formFieldValue,''),
            'circa' => !is_null($request->{'circa_'.$formFieldValue}) ? $request->{'circa_'.$formFieldValue} : 0,
            'era' => !is_null($request->{'era_'.$formFieldValue}) ? $request->{'era_'.$formFieldValue} : 'CE'
        ];
        if(!self::validateDate($date['month'],$date['day'],$date['year']))
            $date = null;

        $recModel = new Record(array(),$form->id);
        if($overwrite)
            $recModel->newQuery()->update([$flid => $date]);
        else
            $recModel->newQuery()->whereNull($flid)->update([$flid => $date]);
    }

    /**
     * For a test record, add test data to field.
     *
     * @param  string $url - Url for File Type Fields
     * @return mixed - The data
     */
    public function getTestData($url = null) {
        $date = [
            'month' => 3,
            'day' => 3,
            'year' => 2003,
            'circa' => 0,
            'era' => 'CE'
        ];
        return json_encode($date);
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
                $xml = '<' . $slug . '>';
                $xml .= '<Circa>' . utf8_encode('1 if CIRCA. 0 if NOT CIRCA (Tag is optional)') . '</Circa>';
                $xml .= '<Month>' . utf8_encode('NUMERIC VALUE OF MONTH (i.e. 03)') . '</Month>';
                $xml .= '<Day>' . utf8_encode('3') . '</Day>';
                $xml .= '<Year>' . utf8_encode('2003') . '</Year>';
                $xml .= '<Era>' . utf8_encode('CE, BCE, BP, or KYA BP (Tag is optional)') . '</Era>';
                $xml .= '</' . $slug . '>';

                return $xml;
                break;
            case "JSON":
                $fieldArray[$slug]['circa'] = '1 if CIRCA. 0 if NOT CIRCA (Index is optional)';
                $fieldArray[$slug]['month'] = 'NUMERIC VALUE OF MONTH (i.e. 03)';
                $fieldArray[$slug]['day'] = 3;
                $fieldArray[$slug]['year'] = 2003;
                $fieldArray[$slug]['era'] = 'CE, BCE, BP, or KYA BP (Index is optional)';

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
     * @param  array $date - Takes date array and processes it for display
     * @param  array $field - Field data
     * @return string - The formatted string
     */
    public function displayDate($date, $field) {
        $dateString = '';

        if($date['circa'] && $field['options']['ShowCirca'])
            $dateString .= 'circa ';

        if($date['month']=='' && $date['day']=='')
            $dateString .= $date['year'];
        else if($date['day']=='')
            $dateString .= \DateTime::createFromFormat('m', $date['month'])->format('F').', '.$date['year'];
        else if($date['year']=='')
            $dateString .= \DateTime::createFromFormat('m', $date['month'])->format('F').' '.$date['day'];
        else if($field['options']['Format']=='MMDDYYYY')
            $dateString .= $date['month'].'-'.$date['day'].'-'.$date['year'];
        else if($field['options']['Format']=='DDMMYYYY')
            $dateString .= $date['day'].'-'.$date['month'].'-'.$date['year'];
        else if($field['options']['Format']=='YYYYMMDD')
            $dateString .= $date['year'].'-'.$date['month'].'-'.$date['day'];

        if($field['options']['ShowEra'])
            $dateString .= ' '.$date['era'];

        return $dateString;
    }
}
