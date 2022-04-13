<?php namespace App\KoraFields;

use App\Form;
use App\Record;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HistoricalDateField extends BaseField {

    /*
    |--------------------------------------------------------------------------
    | Historical Date Field
    |--------------------------------------------------------------------------
    |
    | This model represents the historical date field in kora
    |
    */

    /**
     * @var string - Views for the typed field options
     */
    const FIELD_OPTIONS_VIEW = "partials.fields.options.historicdate";
    const FIELD_ADV_OPTIONS_VIEW = "partials.fields.advanced.historicdate";
    const FIELD_ADV_INPUT_VIEW = "partials.records.advanced.historicdate";
    const FIELD_INPUT_VIEW = "partials.records.input.historicdate";
    const FIELD_DISPLAY_VIEW = "partials.records.display.historicdate";

    /**
     * @var string - Method from CreateRecordsTable() for adding to DB
     */
    const FIELD_DATABASE_METHOD = 'addJSONColumn';

    /**
     * @var string - The year that represent 0 BP/KYA BP
     */
    const BEFORE_PRESENT_REFERENCE = 1950;

    /**
     * Epsilon value for comparison purposes. Used to match between values in MySQL.
     *
     * @type float
     */
    CONST EPSILON = 0.0001;

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
     * @return array - The default options
     */
    public function getDefaultOptions($type = null) {
        return [
            'ShowPrefix' => 0,
            'ShowEra' => 0,
            'Start' => 1900,
            'End' => 2030,
            'Format' => 'YYYYMMDD'
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
        $testMonth = $request->default_month == '0' ? date("m") : $request->default_month;
        $testDay = $request->default_day == '0' ? date("d") : $request->default_day;
        $testYear = $request->default_year == '0' ? date("Y") : $request->default_year;
        if(self::validateDate($testMonth,$testDay,$testYear)) {
            $default = [
                'month' => $request->default_month,
                'day' => $request->default_day,
                'year' => $request->default_year,
                'prefix' => !is_null($request->default_prefix) ? $request->default_prefix : '',
                'era' => !is_null($request->default_era) ? $request->default_era : 'CE'
            ];
        } else {
            $default = null;
        }

        if($request->start=='')
            $request->start = 1;

        if($request->end=='')
            $request->end = 9999;

        // If the years don't make sense, flip em
        // Use temp start & end vars to keep 0 (current year) value
        $start = $request->start;
        if ($start == 0)
            $start = date("Y");

        $end = $request->end;
        if ($end == 0)
            $end = date("Y");

        if ($start > $end) {
            $pivot = $request->start;
            $request->start = $request->end;
            $request->end = $pivot;
        }

        $field['default'] = $default;
        $field['options']['ShowPrefix'] = $request->prefix;
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

        // A year set to 0 is actually 'Current Year'
        if ($year == 0)
            $year = date("Y");

        if ($start == 0)
            $start = date("Y");

        if ($end == 0)
            $end = date("Y");

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
        //Must have a year
        //No day without a month.
        if(
            ($y=='') | ($d!='' && $m=='')
        ) {
            return false;
        }

        //Next we need to make sure the date provided is legal (i.e. no Feb 30th, etc)
        //For the check we need to default any blank values to 1, cause checkdate doesn't like partial dates
        if($m=='') {$m=1;}
        if($d=='') {$d=1;}

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
            'prefix' => !is_null($request->{'prefix_'.$value}) ? $request->{'prefix_'.$value} : '',
            'era' => !is_null($request->{'era_'.$value}) ? $request->{'era_'.$value} : 'CE'
        ];
        if(!self::validateDate($date['month'],$date['day'],$date['year']))
            return null;
        else {
            $date['sort'] = $this->getDateSortValue($date['era'], $date['year'], $date['month'], $date['day']);
            return json_encode($date);
        }
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
        $return = $date['prefix']!='' ? $date['prefix'].' ' : '';
        $return .= $date['year'].'-'.$date['month'].'-'.$date['day'];
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
        $request['prefix_'.$flid] = isset($value['prefix']) ? $value['prefix'] : '';
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
     *
     * @return Request - Processed data
     */
    public function processImportDataXML($flid, $field, $value, $request) {
        $request[$flid] = $flid;
        $request['month_'.$flid] = isset($value->Month) ? (string)$value->Month : '';
        $request['day_'.$flid] = isset($value->Day) ? (string)$value->Day : '';
        $request['year_'.$flid] = isset($value->Year) ? (string)$value->Year : '';
        $request['prefix_'.$flid] = isset($value->Prefix) ? (string)$value->Prefix : '';
        $request['era_'.$flid] = isset($value->Era) ? (string)$value->Era : 'CE';

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
    public function processImportDataCSV($flid, $field, $value, $request) {
        $request[$flid] = $flid;

        $request['prefix_'.$flid] = '';
        $request['era_'.$flid] = 'CE';
        foreach(['month_', 'day_', 'year_'] as $part) {
            $request[$part.$flid] = '';
        }

        if(Str::startsWith($value, 'circa')) {
            $request['prefix_'.$flid] = 'circa';
            $value = trim(explode('circa', $value)[1]);
        } else if(Str::startsWith($value, 'pre')) {
            $request['prefix_'.$flid] = 'pre';
            $value = trim(explode('pre', $value)[1]);
        } else if(Str::startsWith($value, 'post')) {
            $request['prefix_'.$flid] = 'post';
            $value = trim(explode('post', $value)[1]);
        }

        // Era order matters here
        foreach(['BCE', 'KYA BP', 'CE', 'BP'] as $era) {
            if(Str::endsWith($value, $era)) {
                $request['era_'.$flid] = $era;
                $value = trim(explode($era, $value)[0]);
                break;
            }
        }

        $year = $value;
        if(Str::contains($value, '-')) {
            $value = explode('-', $value);
            $year = $value[0];

            $request['month_'.$flid] = $value[1];

            if(count($value) == 3)
                $request['day_'.$flid] = $value[2];
        }

        $request['year_'.$flid] = $year;

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
     * @param  int $fid - Form ID
     *
     * @return mixed - Processed data
     */
    public function processXMLData($field, $value, $fid = null) {
        $date = json_decode($value,true);
        $xml = "<$field>";
        $xml .= '<Prefix>'.$date['prefix'].'</Prefix>';
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
        $date = json_decode($value,true);
        return [
            'prefix' => $date['prefix'],
            'month' => $date['month'],
            'day' => $date['day'],
            'year' => $date['year'],
            'era' => $date['era'],
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
            'prefix' => !is_null($request->{'prefix_'.$formFieldValue}) ? $request->{'prefix_'.$formFieldValue} : '',
            'era' => !is_null($request->{'era_'.$formFieldValue}) ? $request->{'era_'.$formFieldValue} : 'CE'
        ];

        if(!self::validateDate($date['month'],$date['day'],$date['year']))
            $date = null;
        else
            $date['sort'] = $this->getDateSortValue($date['era'],$date['year'],$date['month'],$date['day']);

        $recModel = new Record(array(),$form->id);
        if($overwrite)
            $recModel->newQuery()->update([$flid => $date]);
        else
            $recModel->newQuery()->whereNull($flid)->update([$flid => $date]);
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
    public function massAssignSubsetRecordField($form, $flid, $formFieldValue, $request, $kids) {
        $date = [
            'month' => $request->input('month_'.$formFieldValue,''),
            'day' => $request->input('day_'.$formFieldValue,''),
            'year' => $request->input('year_'.$formFieldValue,''),
            'prefix' => !is_null($request->{'prefix_'.$formFieldValue}) ? $request->{'prefix_'.$formFieldValue} : '',
            'era' => !is_null($request->{'era_'.$formFieldValue}) ? $request->{'era_'.$formFieldValue} : 'CE'
        ];

        if(!self::validateDate($date['month'],$date['day'],$date['year']))
            $date = null;
        else
            $date['sort'] = $this->getDateSortValue($date['era'],$date['year'],$date['month'],$date['day']);

        $recModel = new Record(array(),$form->id);
        $recModel->newQuery()->whereIn('kid',$kids)->update([$flid => $date]);
    }

    /**
     * Performs a keyword search on this field and returns any results.
     *
     * @param  array $flids - Field ID
     * @param  string $arg - The keywords
     * @param  Record $recordMod - Model to search through
     * @param  boolean $negative - Get opposite results of the search
     * @return array - The RIDs that match search
     */
    public function keywordSearchTyped($flids, $arg, $recordMod, $form, $negative = false) {
        if($negative)
            $param = 'NOT LIKE';
        else
            $param = 'LIKE';

        $dbQuery = $recordMod->newQuery()
            ->select("id");

        foreach($flids as $f) {
            if($negative) {
                $dbQuery = $dbQuery->orWhere(function($query) use ($f, $param, $arg) {
                    $query = $query->where($f, $param, "%\"month\": \"$arg\"%");
                    $query = $query->where($f, $param, "%\"day\": \"$arg\"%");
                    $query = $query->where($f, $param, "%\"year\": \"$arg\"%");
                    $arg = strtolower($arg); //Solves the JSON mysql case-insensitive issue
                    $query = $query->whereRaw("LOWER($f) $param ?", ["%\"era\": \"$arg\"%"]);
                    $query = $query->whereRaw("LOWER($f) $param ?", ["%\"prefix\": \"$arg\"%"]);
                });
            } else {
                $dbQuery = $dbQuery->orWhere(function($query) use ($f, $param, $arg) {
                    $query = $query->orWhere($f, $param, "%\"month\": \"$arg\"%");
                    $query = $query->orWhere($f, $param, "%\"day\": \"$arg\"%");
                    $query = $query->orWhere($f, $param, "%\"year\": \"$arg\"%");
                    $arg = strtolower($arg); //Solves the JSON mysql case-insensitive issue
                    $query = $query->orWhereRaw("LOWER($f) $param ?", ["%\"era\": \"$arg\"%"]);
                    $query = $query->orWhereRaw("LOWER($f) $param ?", ["%\"prefix\": \"$arg\"%"]);
                });
            }
        }

        return $dbQuery->pluck('id')
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

        if(isset($data->begin_era) && in_array($data->begin_era, ['CE','BCE','BP','KYA BP']))
            $request['begin_era'] = $data->begin_era;
        if(isset($data->end_era) && in_array($data->end_era, ['CE','BCE','BP','KYA BP']))
            $request['end_era'] = $data->end_era;

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
    public function advancedSearchTyped($flid, $query, $recordMod, $form, $negative = false) {
        $beginEra = isset($query['begin_era']) ? $query['begin_era'] : 'CE';
        $endEra = isset($query['end_era']) ? $query['end_era'] : 'CE';

        //We need to create a mathematical represenation of each date to make MYSQL comparisons
        $beginMonth = isset($query['begin_month']) ? $query['begin_month'] : 1;
        $endMonth = isset($query['end_month']) ? $query['end_month'] : 12;
        $beginDay = isset($query['begin_day']) ? $query['begin_day'] : 1;
        $endDay = isset($query['end_day']) ? $query['end_day'] : 31;
        $beginYear = $query['begin_year'];
        $endYear = $query['end_year'];

        $beginValue = $this->getDateSortValue($beginEra, $beginYear, $beginMonth, $beginDay);
        $endValue = $this->getDateSortValue($endEra, $endYear, $endMonth, $endDay);

        $query = $recordMod->newQuery()
            ->select("id");

        self::buildAdvancedHistoricalDateQuery($query, $flid, $beginValue, $endValue);

        return $query->pluck('id')
            ->toArray();
    }

    /**
     * Build an advanced search number field query.
     *
     * @param  Builder $query - Query to build upon
     * @param  string $flid - Field ID
     * @param  string $left - Input from the form, left index
     * @param  string $right - Input from the form, right index
     */
    private static function buildAdvancedHistoricalDateQuery(&$query, $flid, $left, $right) {
        // Determine the interval we should search over. With epsilons to account for float rounding.
        if($left == "")
            $query->whereRaw("`$flid`->\"$.sort\" <= ".(floatval($right) + self::EPSILON));
        else if($right == "")
            $query->whereRaw("`$flid`->\"$.sort\" >= ".(floatval($left) - self::EPSILON));
        else
            $query->whereRaw("`$flid`->\"$.sort\" BETWEEN ".(floatval($left) - self::EPSILON)." AND ".(floatval($right) + self::EPSILON));
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
        $date['month'] = $date['month']!='' ? sprintf('%02d', $date['month']) : '';
        $date['day'] = $date['day']!='' ? sprintf('%02d', $date['day']) : '';
        $date['year'] = sprintf('%04d', $date['year']);

        if($date['prefix']!='' && $field['options']['ShowPrefix'])
            $dateString .= $date['prefix'].' ';

        if($date['month']=='' && $date['day']=='')
            $dateString .= $date['year'];
        else if($date['day']=='')
            $dateString .= $date['year'].'-'.$date['month'];
        else if($field['options']['Format']=='YYYYMMDD')
            $dateString .= $date['year'].'-'.$date['month'].'-'.$date['day'];
        else if($field['options']['Format']=='MMDDYYYY')
            $dateString .= $date['month'].'-'.$date['day'].'-'.$date['year'];
        else if($field['options']['Format']=='DDMMYYYY')
            $dateString .= $date['day'].'-'.$date['month'].'-'.$date['year'];

        if($field['options']['ShowEra'])
            $dateString .= ' '.$date['era'];

        return $dateString;
    }

    /**
     * Takes a historical date value and generates a numerical representation to use in search and sort.
     *
     * @param  string $era - Takes date array and processes it for display
     * @param  int $y - Field data
     * @param  int $m - Field data
     * @param  int $d - Field data
     * @return float - The numerical representation
     */
    public function getDateSortValue($era, $y, $m=1, $d=1) {
        //Block bad dates
        if($y=="")
            return 0;

        //If month or date is blank, set to 1
        $m = $m=="" ? 1 : (int)$m;
        $d = $d=="" ? 1 : (int)$d;

        switch($era) {
            case 'CE':
                return $y + ($m*0.01) + ($d*0.0001);
                break;
            case 'BCE':
                return -1*($y + ($m*0.01) + ($d*0.0001));
                break;
            case 'BP':
                return self::BEFORE_PRESENT_REFERENCE - $y;
                break;
            case 'KYA BP':
                return self::BEFORE_PRESENT_REFERENCE - ($y*1000);
                break;
        }

        return 0;
    }
}
