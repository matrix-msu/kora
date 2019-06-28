<?php namespace App\KoraFields;

use App\Form;
use App\Record;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

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
     * @var string - The year that represent 0 BP/KYA BP
     */
    const BEFORE_PRESENT_REFERENCE = 1950;

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
     *
     * @return Request - Processed data
     */
    public function processImportDataXML($flid, $field, $value, $request) {
        $request[$flid] = $flid;
        $request['month_'.$flid] = isset($value->Month) ? (string)$value->Month : '';
        $request['day_'.$flid] = isset($value->Day) ? (string)$value->Day : '';
        $request['year_'.$flid] = isset($value->Year) ? (string)$value->Year : '';
        $request['circa_'.$flid] = isset($value->Circa) ? (string)$value->Circa : 0;
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

        $request['circa_'.$flid] = 0;
        $request['era_'.$flid] = 'CE';
        foreach(['month_', 'day_', 'year_'] as $part) {
            $request[$part.$flid] = '';
        }

        if(Str::startsWith($value, 'circa')) {
            $request['circa_'.$flid] = 1;
            $value = explode('circa ', $value)[1];
        }

        // Era order matters here
        foreach(['BCE', 'KYA BP', 'CE', 'BP'] as $era) {
            if(Str::endsWith($value, $era)) {
                $request['era_'.$flid] = $era;
                $value = explode(' ' . $era, $value)[0];
                break;
            }
        }

        $year = $value;
        if(Str::contains($value, '-')) {
            $value = explode('-', $value);
            $year = $value[0];

            $request['month_'.$flid] = $value[1];

            if(count($value) == 3) {
                $request['day_'.$flid] = $value[2];
            }
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
        $date = json_decode($value,true);
        return [
            'prefix' => $date['circa'],
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
            'circa' => !is_null($request->{'circa_'.$formFieldValue}) ? $request->{'circa_'.$formFieldValue} : 0,
            'era' => !is_null($request->{'era_'.$formFieldValue}) ? $request->{'era_'.$formFieldValue} : 'CE'
        ];
        if(!self::validateDate($date['month'],$date['day'],$date['year']))
            $date = null;

        $recModel = new Record(array(),$form->id);
        $recModel->newQuery()->whereIn('kid',$kids)->update([$flid => $date]);
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

        $dbQuery = $recordMod->newQuery()
            ->select("id");

        if($negative) { //TODO::This may have to be rethought later
            $dbQuery->where($flid, $param, "%\"month\": \"$arg\"%");
            $dbQuery->where($flid, $param, "%\"day\": \"$arg\"%");
            $dbQuery->where($flid, $param, "%\"year\": \"$arg\"%");
            $dbQuery->where($flid, $param, "%\"era\": \"$arg\"%");
        } else {
            $dbQuery->orWhere($flid, $param, "%\"month\": \"$arg\"%");
            $dbQuery->orWhere($flid, $param, "%\"day\": \"$arg\"%");
            $dbQuery->orWhere($flid, $param, "%\"year\": \"$arg\"%");
            $dbQuery->orWhere($flid, $param, "%\"era\": \"$arg\"%");
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

        //Verify era
        if(
            ($beginEra == 'CE' && $endEra == 'CE') |
            ($beginEra == 'BCE' && ($endEra == 'BCE' | $endEra == 'CE')) |
            ($beginEra == 'BP' && $endEra == 'BP') |
            ($beginEra == 'KYA BP' && $endEra == 'KYA BP')
        ) {
            //We need to create a mathematical represenation of each date to make MYSQL comparisons
            $beginMonth = isset($query['begin_month']) ? $query['begin_month'] : 1;
            $endMonth = isset($query['end_month']) ? $query['end_month'] : 12;
            $beginDay = isset($query['begin_day']) ? $query['begin_day'] : 1;
            $endDay = isset($query['end_day']) ? $query['end_day'] : 31;
            $beginYear = $query['begin_year'];
            $endYear = $query['end_year'];

            switch($beginEra) {
                case 'CE':
                    $beginValue = $beginYear + ($beginMonth*0.01) + ($beginDay*0.0001);
                    break;
                case 'BCE':
                    $beginValue = -1*($beginYear + ($beginMonth*0.01) + ($beginDay*0.0001));
                    break;
                case 'BP':
                    $beginValue = self::BEFORE_PRESENT_REFERENCE - $beginYear;
                    break;
                case 'KYA BP':
                    $beginValue = self::BEFORE_PRESENT_REFERENCE - ($beginYear*1000);
                    break;
            }

            switch($endEra) {
                case 'CE':
                    $endValue = $endYear + ($endMonth*0.01) + ($endDay*0.0001);
                    break;
                case 'BCE':
                    $endValue = -1*($endYear + ($endMonth*0.01) + ($endDay*0.0001));
                    break;
                case 'BP':
                    $endValue = self::BEFORE_PRESENT_REFERENCE - $endYear;
                    break;
                case 'KYA BP':
                    $endValue = self::BEFORE_PRESENT_REFERENCE - ($endYear*1000);
                    break;
            }

            if($negative)
                $param = 'middleVal > `end` OR middleVal < `begin`';
            else
                $param = 'middleVal <= `end` AND middleVal >= `begin`';

            //This function determines if historical date is in between given date values
            DB::unprepared("DROP FUNCTION IF EXISTS `inDateRange`;
            CREATE FUNCTION `inDateRange`(`date` JSON, `begin` DOUBLE, `end` DOUBLE)
            RETURNS BOOL
            BEGIN
                DECLARE result BOOL DEFAULT false;
                DECLARE monthVal INT DEFAULT 1;
                DECLARE dayVal INT DEFAULT 1;
                DECLARE yearVal INT DEFAULT 1;
                DECLARE eraVal TEXT;
                DECLARE middleVal DOUBLE;

                IF `date`->\"$.month\" != \"\" THEN SET monthVal = `date`->\"$.month\";
                END IF;

                IF `date`->\"$.day\" != \"\" THEN SET dayVal = `date`->\"$.day\";
                END IF;

                IF `date`->\"$.year\" != \"\" THEN SET yearVal = `date`->\"$.year\";
                END IF;

                SET eraVal = `date`->\"$.era\";

                IF eraVal = '\"CE\"' THEN SET middleVal = (yearVal + (monthVal*0.01) + (dayVal*0.0001));
                ELSEIF eraVal = '\"BCE\"' THEN SET middleVal = (-1*(yearVal + (monthVal*0.01) + (dayVal*0.0001)));
                ELSEIF eraVal = '\"BP\"' THEN SET middleVal = (".self::BEFORE_PRESENT_REFERENCE." - yearVal);
                ELSEIF eraVal = '\"KYA BP\"' THEN SET middleVal = (".self::BEFORE_PRESENT_REFERENCE." - (yearVal*1000));
                END IF;

                IF $param THEN SET result = TRUE;
                END IF;

                RETURN result;
            END;");

            $dbQuery = $recordMod->newQuery()
                ->select("id")
                ->whereRaw("inDateRange(`$flid`,?,?)")
                ->setBindings([$beginValue, $endValue]);

            return $dbQuery->pluck('id')
                ->toArray();
        } else {
            return [];
        }
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
        $date['year'] = sprintf('%04d', $date['year']); //TODO::FORCE 4 DIGIT FORMAT

        if($date['circa'] && $field['options']['ShowCirca'])
            $dateString .= 'circa ';

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
}
