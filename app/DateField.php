<?php namespace App;

use App\Http\Controllers\FieldController;
use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class DateField extends BaseField {

    /**
     * Month day year format.
     * @var string
     */
    const MONTH_DAY_YEAR = "MMDDYYYY";

    /**
     * Day month year format.
     * @var string
     */
    const DAY_MONTH_YEAR = "DDMMYYYY";

    /**
     * Year month day format.
     * @var string
     */
    const YEAR_MONTH_DAY = "YYYYMMDD";

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
     * Builds the query for a date field.
     *
     * @param $search string, the query, a space separated string.
     * @param $circa bool, should we search for date fields with circa turned on?
     * @param $era bool, should we search for date fields with era turned on?
     * @param $flid int, the field id.
     * @return Builder, the query for the date field.
     */
    public static function buildQuery($search, $circa, $era, $flid) {
        $args = explode(" ", $search);

        $query = DateField::where("flid", "=", $flid);

        $query->where(function($query) use($args, $circa, $era) {
            foreach($args as $arg) {
                $query->orWhere("day", "=", intval($arg))
                    ->orWhere("year", "=", intval($arg));

                if (self::isMonth($arg)) {
                    $query->orWhere("month", "=", intval(self::monthToNumber($arg)));
                }

                if ($era && self::isValidEra($arg)) {
                    $query->orWhere("era", "=", strtoupper($arg));
                }
            }

            if ($circa) {
                $query->orWhere("circa", "=", 1);
            }
        });

        return $query;
    }

    /*
     *
     */
    public static function buildQuery2($search, $circa, $era, $fid) {
        $args = explode(" ", $search);

        $query = DB::table("date_fields")
            ->select("rid")
            ->where("fid", "=", $fid);

        // This function acts as parenthesis around the or's of the date field requirements.
        $query->where(function($query) use ($args, $circa, $era) {
            foreach($args as $arg) {
                $query->orWhere("day", "=", intval($arg))
                    ->orWhere("year", "=", intval($arg));

                if (self::isMonth($arg)) {
                    $query->orWhere("month", "=", intval(self::monthToNumber($arg)));
                }

                if ($era && self::isValidEra($arg)) {
                    $query->orWhere("era", "=", strtoupper($arg));
                }
            }

            if ($circa && self::isCirca($arg)) {
                $query->orWhere("circa", "=", 1);
            }
        });

        return $query->distinct();
    }

    /**
     * Tests if a string is a valid era.
     *
     * @param $string
     * @return bool, true if valid.
     */
    public static function isValidEra($string) {
        $string = strtoupper($string);
        return ($string == "BCE" || $string == "CE");
    }

    /**
     * Test if a string is equal to circa.
     *
     * @param $string
     * @return bool, true if valid.
     */
    public static function isCirca($string) {
        $string = strtoupper($string);
        return ($string == "CIRCA");
    }

    /**
     * Determines if a string is a value month name.
     * Using the month to number function, if the string is turned to a number
     * we know it is determined to be a valid month name.
     * The original string should also not be a number itself. As searches for
     * the numbers 1 through 12 should not return dates based on some month Jan-Dec.
     *
     * @param $string string, the string to test.
     * @return bool, true if the string is a valid month.
     */
    public static function isMonth($string) {
        $monthToNumber = self::monthToNumber($string);
        return is_numeric($monthToNumber) && $monthToNumber != $string;
    }

    /**
     * The months of the year in different languages.
     * These are listed without special characters because the input will be converted to close characters.
     * Formatted with regular expression tags to find only the exact month so "march" does not match "marches" for example.
     *
     * @var array
     */
    private static $months = [
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
     * We currently support 3 languages, so this is an array of 3 copies of the number of 1 through 12.
     *
     * @var array
     */
    private static $monthNumbers = [ 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 ];

    /**
     * Converts a month to the number corresponding to the month.
     *
     * @param $month, the month to be converted.
     * @return array, processed collection of months.
     */
    public static function monthToNumber($month) {
        foreach(self::$months as $monthRegex) {
            $month = preg_replace($monthRegex, self::$monthNumbers, $month);
        }

        return $month;
    }

    public static function validateDate($m,$d,$y){
        if($d!='' && !is_null($d)) {
            if ($m == '' | is_null($m)) {
                return false;
            } else {
                if($y=='')
                    $y=1;
                return checkdate($m, $d, $y);
            }
        }

        return true;
    }

    /**
     * Determine if to metadata can be called on the field.
     *
     * @return bool
     */
    public function isMetafiable() {
        return $this->month > 0;
    }

    /**
     * Returns the date in the proper format accounting for circa and era.
     *
     * @param Field $field
     * @return string
     */
    public function toMetadata(Field $field) {
        $options = $field->options;

        $circa = explode("[!Circa!]", $options)[1] == "Yes";
        $era = explode("[!Era!]", $options)[1] == "On";
        $format = explode("[!Format!]", $options)[1];

        $date_string = "";

        if ($circa) {
            $date_string .= "Circa ";
        }

        switch($format) {
            case self::MONTH_DAY_YEAR:
                $date_string .= $this->month . "-" . $this->day . "-" . $this->year;
                break;

            case self::DAY_MONTH_YEAR:
                $date_string .= $this->day . "-" . $this->month . "-" . $this->year;
                break;

            case self::YEAR_MONTH_DAY:
                $date_string .= $this->year . "-" . $this->month . "-" . $this->day;
                break;

            default:
                break;
        }

        if ($era) {
            $date_string .= " " . $this->era;
        }

        return $date_string;
    }

    /**
     * Override the save method to allow for storing the date as an object.
     *
     * @param array $options
     * @return bool
     */
    public function save(array $options = array()) {
        $date = DateTime::createFromFormat("Y-m-d", $this->year."-".$this->month."-".$this->day);
        $this->date_object = date_format($date, "Y-m-d");

        return parent::save($options);
    }

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
     * Rollback a date field based on a revision.
     *
     * ** Assumes $revision->data is json decoded. **
     *
     * @param Revision $revision
     * @param Field $field
     * @return DateField
     */
    public static function rollback(Revision $revision, Field $field) {
        $datefield = DateField::where("flid", "=", $field->flid)->where("rid", "=", $revision->rid)->first();

        if (!is_array($revision->data)) {
            $revision->data = json_decode($revision->data, true);
        }

        // If the field doesn't exist or was explicitly deleted, we create a new one.
        if ($revision->type == Revision::DELETE || is_null($datefield)) {
            $datefield = new DateField();
            $datefield->flid = $field->flid;
            $datefield->fid = $revision->fid;
            $datefield->rid = $revision->rid;
        }

        $datefield->circa = $revision->data[Field::_DATE][$field->flid]['circa'];
        $datefield->month = $revision->data[Field::_DATE][$field->flid]['month'];
        $datefield->day = $revision->data[Field::_DATE][$field->flid]['day'];
        $datefield->year = $revision->data[Field::_DATE][$field->flid]['year'];
        $datefield->era = $revision->data[Field::_DATE][$field->flid]['era'];
        $datefield->save();

        return $datefield;
    }

    /**
     * Build the advanced search query.
     *
     * @param $flid, field id.
     * @param $query
     * @return Builder
     */
    public static function getAdvancedSearchQuery($flid, $query) {
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

        if ($begin_era == "BCE" && $end_era == "BCE") { // Date interval flipped, dates are decreasing.
            $begin = DateTime::createFromFormat("Y-m-d", $end_year."-".$end_month."-".$end_day); // End is beginning now.
            $end = DateTime::createFromFormat("Y-m-d", $begin_year."-".$begin_month."-".$begin_day); // Begin is end now.

            $query->where("era", "=", "BCE")
                ->whereBetween("date_object", [$begin, $end]);
        }
        else if ($begin_era == "BCE" && $end_era == "CE") { // Have to use two interval and era clauses.
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
        }
        else { // Normal case, both are CE, the other choice of CE then BCE is invalid.
            $begin = DateTime::createFromFormat("Y-m-d", $begin_year."-".$begin_month."-".$begin_day);
            $end = DateTime::createFromFormat("Y-m-d", $end_year."-".$end_month."-".$end_day);

            $query->where("era", "=", "CE")
                ->whereBetween("date_object", [$begin, $end]);
        }

        return $query->distinct();
    }
}
