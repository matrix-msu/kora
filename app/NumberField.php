<?php namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NumberField extends BaseField {

    /**
     * Epsilon value for comparison purposes.
     * Used to match between values in MySQL.
     *
     * @type float
     */
    CONST EPSILON = 0.0001;

    protected $fillable = [
        'rid',
        'flid',
        'number'
    ];

    /**
     * Keyword search for a number field.
     * Regardless of the partial flag, this matches only the exact number.
     * This function will work if $args is any combination of floats, integers, or strings.
     *
     * @param array $args, Array of arguments for the search routine to use.
     * @param bool $partial, Only passed for consistency, doesn't matter for this specific search.
     * @return bool, True if the number matches the search.
     */
    public function keywordSearch(array $args, $partial)
    {
        $number = floatval($this->number);

        foreach($args as $arg) {
            if (is_numeric($arg) && $number === floatval($arg))
                return true; // Found a match
        }

        return false; // No matches
    }

    /**
     * Determines if to metadata can be called on the NumberField.
     *
     * @return bool
     */
    public function isMetafiable() {
        return ! empty($this->number);
    }

    /**
     * Returns the field's number while removing trailing zeros.
     *
     * @param Field $field, unneeded.
     * @return double
     */
    public function toMetadata(Field $field) {
        return $this->number + 0; // + 0 to remove trailing zeros.
    }

    /**
     * Builds the advanced query for a number field.
     * More explicitly, this will build a search range in MySQL based off the inputs.
     *
     * @param $flid, field id
     * @param $query, query array
     * @return Builder
     */
    public static function getAdvancedSearchQuery($flid, $query) {
        $left = $query[$flid . "_left"];
        $right = $query[$flid . "_right"];
        $invert = isset($query[$flid . "_invert"]);

        $query = DB::table("number_fields")
            ->select("rid")
            ->where("flid", "=", $flid);

        // Determine the interval we should search over. With epsilons to account for float rounding.
        if ($left == "") {
            if ($invert) { // (right, inf)
                $query->where("number", ">", floatval($right) - NumberField::EPSILON);
            }
            else { // (-inf, right]
                $query->where("number", "<=", floatval($right) + NumberField::EPSILON);
            }
        }
        else if ($right == "") {
            if ($invert) { // (-inf, left)
                $query->where("number", "<", floatval($left) + NumberField::EPSILON);
            }
            else { // [left, inf)
                $query->where("number", ">=", floatval($left) - NumberField::EPSILON);
            }
        }
        else {
            if ($invert) { // (-inf, left) union (right, inf)
                $query->whereNotBetween("number", [floatval($left) - NumberField::EPSILON,
                                                   floatval($right) + NumberField::EPSILON]);
            }
            else { // [left, right]
                $query->whereBetween("number", [floatval($left) - NumberField::EPSILON,
                                                floatval($right) + NumberField::EPSILON]);
            }
        }

        return $query;
    }
}
