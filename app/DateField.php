<?php namespace App;

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
        'circa'
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
     * Keyword search for a date field. Similarly to number field, this only matches
     *  exact dates.
     *
     * @param array $args, array of arguments for the search to use.
     * @param bool $partial, does not effect the search.
     * @return bool, True if the search parameters are satisfied.
     */
    public function keywordSearch(array $args, $partial) {
        $field = Field::where('flid', '=', $this->flid)->first();
        $circa = (explode('[!Circa!]', $field->options)[1]) == "Yes";

        $searchEra = (explode('[!Era!]', $field->options)[1]) == "Yes"; // Boolean to determine if we should search for era.
        $searchCirca = ($circa && $this->circa); // Boolean to determine if we should search for circa.

        foreach ($args as $arg) {
            $arg = strip_tags($arg);
            $arg = Search::convertCloseChars($arg);

            if ($searchCirca && strtolower($arg) == "circa" ||
                $searchEra && strtoupper($arg) == strtoupper($this->era)) {
                return true;
            }

            if (self::monthToNumber($arg) == $this->month) {
                return true;
            }

            if ($arg == $this->day) {
                return true;
            }

            if ($arg == $this->year) {
                return true;
            }
        }

        return false;
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
}
