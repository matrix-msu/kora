<?php namespace App;

use App\Http\Controllers\FieldController;
use App\Http\Controllers\RevisionController;
use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DateField extends BaseField {

    /*
    |--------------------------------------------------------------------------
    | Date Field
    |--------------------------------------------------------------------------
    |
    | This model represents the date field in Kora3
    |
    */

    /**
     * @var string - Views for the typed field options
     */
    const FIELD_OPTIONS_VIEW = "fields.options.date";
    const FIELD_ADV_OPTIONS_VIEW = "partials.field_option_forms.date";

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
        // English
        ['/(\\W|^)january(\\W|$)/i', "/(\\W|^)february(\\W|$)/i", "/(\\W|^)march(\\W|$)/i",
            "/(\\W|^)april(\\W|$)/i", "/(\\W|^)may(\\W|$)/i", "/(\\W|^)june(\\W|$)/i", "/(\\W|^)july(\\W|$)/i",
            "/(\\W|^)august(\\W|$)/i", "/(\\W|^)september(\\W|$)/i", "/(\\W|^)october(\\W|$)/i",
            "/(\\W|^)november(\\W|$)/i", "/(\\W|^)december(\\W|$)/i"],

        // Spanish
        ["/(\\W|^)enero(\\W|$)/i", "/(\\W|^)febrero(\\W|$)/i", "/(\\W|^)marzo(\\W|$)/i",
            "/(\\W|^)abril(\\W|$)/i", "/(\\W|^)mayo(\\W|$)/i", "/(\\W|^)junio(\\W|$)/i", "/(\\W|^)julio(\\W|$)/i",
            "/(\\W|^)agosto(\\W|$)/i", "/(\\W|^)septiembre(\\W|$)/i", "/(\\W|^)octubre(\\W|$)/i",
            "/(\\W|^)noviembre(\\W|$)/i", "/(\\W|^)diciembre(\\W|$)/i"],

        // French
        ["/(\\W|^)janvier(\\W|$)/i", "/(\\W|^)fevrier(\\W|$)/i", "/(\\W|^)mars(\\W|$)/i",
            "/(\\W|^)avril(\\W|$)/i", "/(\\W|^)mai(\\W|$)/i", "/(\\W|^)juin(\\W|$)/i", "/(\\W|^)juillet(\\W|$)/i",
            "/(\\W|^)aout(\\W|$)/i", "/(\\W|^)septembre(\\W|$)/i", "/(\\W|^)octobre(\\W|$)/i",
            "/(\\W|^)novembre(\\W|$)/i", "/(\\W|^)decembre(\\W|$)/i"]
    ];

    /**
     * @var array - We currently support 3 languages, so this is an array of 3 copies of the number of 1 through 12
     */
    const MONTH_NUMBERS = [ 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 ];

    /**
     * @var array - Attributes that can be mass assigned to model
     */
    protected $fillable = [
        'rid',
        'flid',
        'month',
        'day',
        'year',
        'era',
        'circa',
        'date_object'
    ];

    /**
     * Get the field options view.
     *
     * @return string - The view
     */
    public function getFieldOptionsView(){
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
        return '[!Circa!]No[!Circa!][!Start!]1900[!Start!][!End!]2020[!End!][!Format!]MMDDYYYY[!Format!][!Era!]No[!Era!]';
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
        if(DateField::validateDate($request->default_month,$request->default_day,$request->default_year)) {
            $default = '[M]' . $request->default_month . '[M][D]' . $request->default_day . '[D][Y]' . $request->default_year . '[Y]';
        } else {
            if($return) {
                return redirect('projects/' . $field->pid . '/forms/' . $field->fid . '/fields/' . $field->flid . '/options')
                    ->withInput()->with('k3_global_error', 'default_invalid_date');
            } else {
                $default = '';
                return response()->json(["status"=>false,"message"=>"default_invalid_date"],500);
            }
        }

        if($request->start=='' | $request->start==0)
            $request->start = 1;

        if($request->end=='')
            $request->end = 9999;

        $field->updateRequired($request->required);
        $field->updateSearchable($request);
        $field->updateDefault($default);
        $field->updateOptions('Format', $request->format);
        $field->updateOptions('Start', $request->start);
        $field->updateOptions('End', $request->end);
        $field->updateOptions('Circa', $request->circa);
        $field->updateOptions('Era', $request->era);

        if($return) {
            return redirect('projects/' . $field->pid . '/forms/' . $field->fid . '/fields/' . $field->flid . '/options')
                ->with('k3_global_success', 'field_options_updated');
        } else {
            return response()->json(["status"=>true,"message"=>"field_options_updated"],200);
        }
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
        if($request->input('year_' . $field->flid) != '') {
            $this->flid = $field->flid;
            $this->rid = $record->rid;
            $this->fid = $field->fid;
            $this->circa = $request->input('circa_' . $field->flid, '');
            $this->month = $request->input('month_' . $field->flid);
            $this->day = $request->input('day_' . $field->flid);
            $this->year = $request->input('year_' . $field->flid);
            $this->era = $request->input('era_' . $field->flid, 'CE');
            $this->save();
        }
    }

    /**
     * Edits a typed field that has record data.
     *
     * @param  string $value - Data to add
     * @param  Request $request
     */
    public function editRecordField($value, $request) {
        if(!is_null($this) && !(empty($request->input('month_'.$this->flid)) && empty($request->input('day_'.$this->flid)) && empty($request->input('year_'.$this->flid)))) {
            $this->circa = $request->input('circa_'.$this->flid, '');
            $this->month = $request->input('month_'.$this->flid);
            $this->day = $request->input('day_'.$this->flid);
            $this->year = $request->input('year_'.$this->flid);
            $this->era = $request->input('era_'.$this->flid, 'CE');
            $this->save();
        } else if(!is_null($this) && (empty($request->input('month_'.$this->flid)) && empty($request->input('day_'.$this->flid)) && empty($request->input('year_'.$this->flid)))) {
            $this->delete();
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
        $flid = $field->flid;
        $matching_record_fields = $record->datefields()->where("flid", '=', $flid)->get();
        $record->updated_at = Carbon::now();
        $record->save();
        if($matching_record_fields->count() > 0) {
            $datefield = $matching_record_fields->first();
            if($overwrite == true || $datefield->month == "" || is_null($datefield->month)) {
                $revision = RevisionController::storeRevision($record->rid, 'edit');
                $datefield->circa = $request->input('circa_' . $flid, '');
                $datefield->month = $request->input('month_' . $flid);
                $datefield->day = $request->input('day_' . $flid);
                $datefield->year = $request->input('year_' . $flid);
                $datefield->era = $request->input('era_' . $flid, 'CE');
                $datefield->save();
                $revision->oldData = RevisionController::buildDataArray($record);
                $revision->save();
            }
        } else {
            $this->createNewRecordField($field, $record, $formFieldValue, $request);
            $revision = RevisionController::storeRevision($record->rid, 'edit');
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
        $this->circa = 1;
        $this->month = 1;
        $this->day = 3;
        $this->year = 1937;
        $this->era = 'CE';
        $this->save();
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
        $start = FieldController::getFieldOption($field,'Start');
        $end = FieldController::getFieldOption($field,'End');
        $month = $request->input('month_'.$field->flid,'');
        $day = $request->input('day_'.$field->flid,'');
        $year = $request->input('year_'.$field->flid,'');

        if($req==1 && $month=='' && $day=='' && $year=='')
            return $field->name."_required";

        if(($year<$start | $year>$end) && ($month!='' | $day!=''))
            return $field->name."_year_range";

        if(!DateField::validateDate($month,$day,$year))
            return $field->name."_day_month_error";

        return 'field_validated';
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
        if($d!='' && !is_null($d) && $d!=0) {
            if ($m == '' | is_null($m) | $m==0) {
                return false;
            } else {
                if($y=='' | $y==0)
                    $y=1;
                return checkdate($m, $d, $y);
            }
        }

        return true;
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

        if(is_null($revision->data[Field::_DATE][$field->flid]['data']))
            return null;

        // If the field doesn't exist or was explicitly deleted, we create a new one.
        if($revision->type == Revision::DELETE || $exists) {
            $this->flid = $field->flid;
            $this->fid = $revision->fid;
            $this->rid = $revision->rid;
        }

        $this->circa = $revision->data[Field::_DATE][$field->flid]['data']['circa'];
        $this->month = $revision->data[Field::_DATE][$field->flid]['data']['month'];
        $this->day = $revision->data[Field::_DATE][$field->flid]['data']['day'];
        $this->year = $revision->data[Field::_DATE][$field->flid]['data']['year'];
        $this->era = $revision->data[Field::_DATE][$field->flid]['data']['era'];
        $this->save();
    }

    /**
     * Get the arrayed version of the field data to store in a record preset.
     *
     * @param  array $data - The data array representing the record preset
     * @param  bool $exists - Typed field exists and has data
     * @return array - The updated $data
     */
    public function getRecordPresetArray($data, $exists=true) {
        $date_array = array();

        if($exists) {
            $date_array['circa'] = $this->circa;
            $date_array['era'] = $this->era;
            $date_array['day'] = $this->day;
            $date_array['month'] = $this->month;
            $date_array['year'] = $this->year;
        } else {
            $date_array['circa'] = null;
            $date_array['era'] = null;
            $date_array['day'] = null;
            $date_array['month'] = null;
            $date_array['year'] = null;
        }

        $data['data'] = $date_array;

        return $data;
    }

    /**
     * Get the required information for a revision data array.
     *
     * @param  Field $field - Optional field to get storage options for certain typed fields
     * @return mixed - The revision data
     */
    public function getRevisionData($field = null) {
        return [
            'day' => $this->day,
            'month' => $this->month,
            'year' => $this->year,
            'format' => FieldController::getFieldOption($field, 'Format'),
            'circa' => FieldController::getFieldOption($field, 'Circa') == 'Yes' ? $this->circa : '',
            'era' => FieldController::getFieldOption($field, 'Era') == 'Yes' ? $this->era : ''
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
        switch($type) {
            case "XML":
                $xml = '<' . Field::xmlTagClear($slug) . ' type="Date">';
                $value = '<Circa>' . utf8_encode('1 IF CIRCA. 0 IF NOT') . '</Circa>';
                $value .= '<Month>' . utf8_encode('NUMERIC VALUE OF MONTH (i.e. 08)') . '</Month>';
                $value .= '<Day>' . utf8_encode('19') . '</Day>';
                $value .= '<Year>' . utf8_encode('1990') . '</Year>';
                $value .= '<Era>' . utf8_encode('CE OR BCE') . '</Era>';
                $xml .= $value;
                $xml .= '</' . Field::xmlTagClear($slug) . '>';

                return $xml;
                break;
            case "JSON":
                $fieldArray = array('name' => $slug, 'type' => 'Date');
                $fieldArray['circa'] = '1 IF CIRCA. 0 IF NOT';
                $fieldArray['month'] = 'NUMERIC VALUE OF MONTH (i.e. 08)';
                $fieldArray['day'] = 19;
                $fieldArray['year'] = 1990;
                $fieldArray['era'] = 'CE OR BCE';

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
        if(isset($data->begin_month))
            $beginMonth = $data->begin_month;
        else
            $beginMonth = '';

        if(isset($data->begin_day))
            $beginDay = $data->begin_day;
        else
            $beginDay = '';

        if(isset($data->begin_year))
            $beginYear = $data->begin_year;
        else
            $beginYear = '';

        $request->request->add([$flid.'_begin_month' => $beginMonth]);
        $request->request->add([$flid.'_begin_day' => $beginDay]);
        $request->request->add([$flid.'_begin_year' => $beginYear]);

        if(isset($data->end_month))
            $endMonth = $data->end_month;
        else
            $endMonth = '';

        if(isset($data->end_day))
            $endDay = $data->end_day;
        else
            $endDay = '';

        if(isset($data->end_year))
            $endYear = $data->end_year;
        else
            $endYear = '';

        $request->request->add([$flid.'_end_month' => $endMonth]);
        $request->request->add([$flid.'_end_day' => $endDay]);
        $request->request->add([$flid.'_end_year' => $endYear]);

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
        $recRequest['circa_' . $flid] = $jsonField->circa;
        $recRequest['month_' . $flid] = $jsonField->month;
        $recRequest['day_' . $flid] = $jsonField->day;
        $recRequest['year_' . $flid] = $jsonField->year;
        $recRequest['era_' . $flid] = $jsonField->era;
        $recRequest[$flid] = '';

        return $recRequest;
    }

    /**
     * Performs a keyword search on this field and returns any results.
     *
     * @param  int $fid - Form ID
     * @param  string $arg - The keywords
     * @param  string $method - Type of keyword search
     * @return Builder - The RIDs that match search
     */
    public function keywordSearchTyped($fid, $arg, $method) {
        $arg = str_replace(["*", "\""], "", $arg);

        $field = FieldController::getField($this->flid);

        // Boolean to decide if we should consider circa options.
        $circa = explode("[!Circa!]", $field->options)[1] == "Yes";

        // Boolean to decide if we should consider era.
        $era = explode("[!Era!]", $field->options)[1] == "On";

        return self::buildQuery($arg, $circa, $era, $fid);
    }

    /**
     * Builds the query for a date field.
     *
     * @param $search string - The query, a space separated string
     * @param $circa bool - Should we search for date fields with circa turned on?
     * @param $era bool - Should we search for date fields with era turned on?
     * @param $fid int - Form ID
     * @return Builder - The query for the date field
     */
    private static function buildQuery($search, $circa, $era, $fid) {
        $args = explode(" ", $search);

        $query = DB::table("date_fields")
            ->select("rid")
            ->where("fid", "=", $fid);

        // This function acts as parenthesis around the or's of the date field requirements.
        $query->where(function($query) use ($args, $circa, $era) {
            foreach($args as $arg) {
                $query->orWhere("day", "=", intval($arg))
                    ->orWhere("year", "=", intval($arg));

                if(self::isMonth($arg))
                    $query->orWhere("month", "=", intval(self::monthToNumber($arg)));

                if($era && self::isValidEra($arg))
                    $query->orWhere("era", "=", strtoupper($arg));
            }

            if($circa && self::isCirca($arg))
                $query->orWhere("circa", "=", 1);
        });

        return $query->distinct();
    }

    /**
     * Performs an advanced search on this field and returns any results.
     *
     * @param  int $flid - Field ID
     * @param  array $query - The advance search user query
     * @return Builder - The RIDs that match search
     */
    public function getAdvancedSearchQuery($flid, $query) {
        $begin_month = ($query[$flid."_begin_month"] == "") ? 1 : intval($query[$flid."_begin_month"]);
        $begin_day = ($query[$flid."_begin_day"] == "") ? 1 : intval($query[$flid."_begin_day"]);
        $begin_year = ($query[$flid."_begin_year"] == "") ? 1 : intval($query[$flid."_begin_year"]);
        $begin_era = isset($query[$flid."_begin_era"]) ? $query[$flid."_begin_era"] : "CE";

        $end_month = ($query[$flid."_end_month"] == "") ? 1 : intval($query[$flid."_end_month"]);
        $end_day = ($query[$flid."_end_day"] == "") ? 1 : intval($query[$flid."_end_day"]);
        $end_year = ($query[$flid."_end_year"] == "") ? 1 : intval($query[$flid."_end_year"]);
        $end_era = isset($query[$flid."_end_era"]) ? $query[$flid."_end_era"] : "CE";

        $query = DB::table("date_fields")
            ->select("rid")
            ->where("flid", "=", $flid);

        if($begin_era == "BCE" && $end_era == "BCE") { // Date interval flipped, dates are decreasing.
            $begin = DateTime::createFromFormat("Y-m-d", $end_year."-".$end_month."-".$end_day); // End is beginning now.
            $end = DateTime::createFromFormat("Y-m-d", $begin_year."-".$begin_month."-".$begin_day); // Begin is end now.

            $query->where("era", "=", "BCE")
                ->whereBetween("date_object", [$begin, $end]);
        } else if($begin_era == "BCE" && $end_era == "CE") { // Have to use two interval and era clauses.
            $begin = DateTime::createFromFormat("Y-m-d", $begin_year."-".$begin_month."-".$begin_day);
            $era_bound = DateTime::createFromFormat("Y-m-d", "1-1-1"); // There is no year 0 on Gregorian calendar.
            $end = DateTime::createFromFormat("Y-m-d", $end_year."-".$end_month."-".$end_day);

            $query->where(function($query) use($begin, $era_bound, $end) {
                $query->where("era", "=", "BCE")
                    ->whereBetween("date_object", [$era_bound, $begin]);

                $query->orWhere(function($query) use($era_bound, $end) {
                    $query->where("era", "=", "CE")
                        ->whereBetween("date_object", [$era_bound, $end]);
                });
            });
        } else { // Normal case, both are CE, the other choice of CE then BCE is invalid.
            $begin = DateTime::createFromFormat("Y-m-d", $begin_year."-".$begin_month."-".$begin_day);
            $end = DateTime::createFromFormat("Y-m-d", $end_year."-".$end_month."-".$end_day);

            $query->where("era", "=", "CE")
                ->whereBetween("date_object", [$begin, $end]);
        }

        return $query->distinct();
    }

    ///////////////////////////////////////////////END ABSTRACT FUNCTIONS///////////////////////////////////////////////

    /**
     * Gets formatted value of record field to compare for sort. Only implement if field is sortable.
     *
     * @return string - The value
     */
    public function getValueForSort() {
        return DateTime::createFromFormat("Y-m-d", $this->year . "-" . $this->month . "-" . $this->day);
    }

    /**
     * Overwrites model save to save the record data as a date object that search will use.
     *
     * @param  array $options - Record data to save
     * @return bool - Return value from save
     */
    public function save(array $options = array()) {
        $dT = new DateTime();
        if($this->year=='')
            $year = 1;
        else
            $year = $this->year;
        if($this->month=='')
            $month = 1;
        else
            $month = $this->month;
        if($this->day=='')
            $day = 1;
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
    public static function isMonth($string) {
        $monthToNumber = self::monthToNumber($string);
        return is_numeric($monthToNumber) && $monthToNumber != $string;
    }

    /**
     * Converts a month to the number corresponding to the month.
     *
     * @param $month - The month to be converted
     * @return array - Processed collection of months
     */
    public static function monthToNumber($month) {
        foreach(self::MONTHS_IN_LANG as $monthRegex) {
            $month = preg_replace($monthRegex, self::MONTH_NUMBERS, $month);
        }

        return $month;
    }

    /**
     * Tests if a string is a valid era.
     *
     * @param $string - Era string
     * @return bool - True if valid
     */
    public static function isValidEra($string) {
        $string = strtoupper($string);
        return ($string == "BCE" || $string == "CE");
    }

    /**
     * Test if a string is equal to circa.
     *
     * @param $string - Circa string
     * @return bool - True if valid
     */
    public static function isCirca($string) {
        $string = strtoupper($string);
        return ($string == "CIRCA");
    }
}
