<?php namespace App\KoraFields;

use App\Form;
use App\Record;
use Illuminate\Http\Request;

class DateTimeField extends BaseField {

    /*
    |--------------------------------------------------------------------------
    | DateTime Field
    |--------------------------------------------------------------------------
    |
    | This model represents the datetime field in Kora3
    |
    */

    /**
     * @var string - Views for the typed field options
     */
    const FIELD_OPTIONS_VIEW = "partials.fields.options.datetime";
    const FIELD_ADV_OPTIONS_VIEW = "partials.fields.advanced.datetime";
    const FIELD_ADV_INPUT_VIEW = "partials.records.advanced.datetime";
    const FIELD_INPUT_VIEW = "partials.records.input.datetime";
    const FIELD_DISPLAY_VIEW = "partials.records.display.datetime";

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
        $table->addDateTimeColumn($fid, $slug);
    }

    /**
     * Gets the default options string for a new field.
     *
     * @return array - The default options
     */
    public function getDefaultOptions() {
        return [
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
                'hour' => $request->default_hour,
                'minute' => $request->default_minute,
                'second' => $request->default_second,
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
                'month_'.$flid.'_chosen' => $field['name'].' is an invalid date or is missing pieces',
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
        //Date requires all parts
        if($m=='' | $d=='' | $y=='')
            return false;

        //Next we need to make sure the date provided is legal (i.e. no Feb 30th, etc)
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
        $month = $request->input('month_'.$value,'');
        $day = $request->input('day_'.$value,'');
        $year = $request->input('year_'.$value,'');
        $hour = $request->input('hour_'.$value,0);
        $minute = $request->input('minute_'.$value,0);
        $second = $request->input('second_'.$value,0);
        if(!self::validateDate($month,$day,$year))
            return null;
        else
            return "$year-$month-$day $hour:$minute:$second";
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
        $request[$flid] = $flid;
        $parts = explode(' ',$value);
        $dparts = explode('-',$parts[0]);
        $tparts = explode(':',$parts[1]);
        $request['month_'.$flid] = $dparts[1];
        $request['day_'.$flid] = $dparts[2];
        $request['year_'.$flid] = $dparts[0];
        $request['hour_'.$flid] = $tparts[0];
        $request['minute_'.$flid] = $tparts[1];
        $request['second_'.$flid] = $tparts[2];

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
        $parts = explode(' ',(string)$value);
        $dparts = explode('-',$parts[0]);
        $tparts = explode(':',$parts[1]);
        $request['month_'.$flid] = $dparts[1];
        $request['day_'.$flid] = $dparts[2];
        $request['year_'.$flid] = $dparts[0];
        $request['hour_'.$flid] = $tparts[0];
        $request['minute_'.$flid] = $tparts[1];
        $request['second_'.$flid] = $tparts[2];

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
        return $this->displayDate($value, $field);
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
        $xml = "<$field>$value</$field>";

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
     * Takes data from a mass assignment operation and applies it to an individual field.
     *
     * @param  Form $form - Form model
     * @param  string $flid - Field ID
     * @param  String $formFieldValue - The value to be assigned
     * @param  Request $request
     * @param  bool $overwrite - Overwrite if data exists
     */
    public function massAssignRecordField($form, $flid, $formFieldValue, $request, $overwrite=0) {
        $month = $request->input('month_'.$formFieldValue,'');
        $day = $request->input('day_'.$formFieldValue,'');
        $year = $request->input('year_'.$formFieldValue,'');
        $hour = $request->input('hour_'.$formFieldValue,0);
        $minute = $request->input('minute_'.$formFieldValue,0);
        $second = $request->input('second_'.$formFieldValue,0);

        if(!self::validateDate($month,$day,$year))
            $date = null;
        else
            $date = "$year-$month-$day $hour:$minute:$second";

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
        return "2003-03-03 03:03:03";
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
                $xml .= 'YYYY-MM-DD HH:MM:SS';
                $xml .= '</' . $slug . '>';

                return $xml;
                break;
            case "JSON":
                $fieldArray[$slug] = 'YYYY-MM-DD HH:MM:SS';

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
    public function keywordSearchTyped($flid, $arg, $recordMod, $negative = false) {
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
     * @return array - The update request
     */
    public function setRestfulAdvSearch($data) {
        $request = [];

        if(isset($data->begin_month) && is_int($data->begin_month))
            $request['begin_month'] = $data->begin_month;
        if(isset($data->begin_day) && is_int($data->begin_day))
            $request['begin_day'] = $data->begin_day;
        if(isset($data->begin_year) && is_int($data->begin_year))
            $request['begin_year'] = $data->begin_year;

        if(isset($data->end_month) && is_int($data->end_month))
            $request['end_month'] = $data->end_month;
        if(isset($data->end_day) && is_int($data->end_day))
            $request['end_day'] = $data->end_day;
        if(isset($data->end_year) && is_int($data->end_year))
            $request['end_year'] = $data->end_year;

        if(isset($data->begin_hour) && is_int($data->begin_hour))
            $request['begin_hour'] = $data->begin_hour;
        if(isset($data->begin_minute) && is_int($data->begin_minute))
            $request['begin_minute'] = $data->begin_minute;
        if(isset($data->begin_second) && is_int($data->begin_second))
            $request['begin_second'] = $data->begin_second;

        if(isset($data->end_hour) && is_int($data->end_hour))
            $request['end_hour'] = $data->end_hour;
        if(isset($data->end_minute) && is_int($data->end_minute))
            $request['end_minute'] = $data->end_minute;
        if(isset($data->end_second) && is_int($data->end_second))
            $request['end_second'] = $data->end_second;

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
    public function advancedSearchTyped($flid, $query, $recordMod, $negative = false) {
        $from = date($query['begin_year'].'-'.$query['begin_month'].'-'.$query['begin_day'].' '.$query['begin_hour'].':'.$query['begin_minute'].':'.$query['begin_second']);
        $to = date($query['end_year'].'-'.$query['end_month'].'-'.$query['end_day'].' '.$query['end_hour'].':'.$query['end_minute'].':'.$query['end_second']);

        $return = $recordMod->newQuery()
            ->select("id");

        if($negative)
            $return->whereNotBetween($flid, [$from, $to]);
        else
            $return->whereBetween($flid, [$from, $to]);

        return $return->pluck('id')
            ->toArray();
    }

    ///////////////////////////////////////////////END ABSTRACT FUNCTIONS///////////////////////////////////////////////

    /**
     * Formatted display of a date field value.
     *
     * @param  string $dateValue - Takes date string and processes it for display
     * @param  array $field - Field data
     * @return string - The formatted string
     */
    public function displayDate($dateValue, $field) {
        $dateTime = explode(' ',$dateValue);
        $date = explode('-',$dateTime[0]);
        $dateString = '';

        if($field['options']['Format']=='MMDDYYYY')
            $dateString .= $date[1].'-'.$date[2].'-'.$date[0];
        else if($field['options']['Format']=='DDMMYYYY')
            $dateString .= $date[2].'-'.$date[1].'-'.$date[0];
        else if($field['options']['Format']=='YYYYMMDD')
            $dateString .= $date[0].'-'.$date[1].'-'.$date[2];

        $dateString .= " $dateTime[1]";

        return $dateString;
    }
}
