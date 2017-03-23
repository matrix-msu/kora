<?php namespace App;

use Illuminate\Database\Query\Builder;
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

        self::buildAdvancedNumberQuery($query, $left, $right, $invert);

        return $query->distinct();
    }

    /**
     * Build an advanced search number field query.
     *
     * @param Builder $query, query to build upon.
     * @param string $left, input from the form, left index.
     * @param string $right, input from the form, right index.
     * @param string $invert, inverts the search range if true.
     * @param string $prefix, for dealing with joined tables.
     */
    public static function buildAdvancedNumberQuery(Builder &$query, $left, $right, $invert, $prefix = "") {
        // Determine the interval we should search over. With epsilons to account for float rounding.
        if ($left == "") {
            if ($invert) { // (right, inf)
                $query->where($prefix . "number", ">", floatval($right) - NumberField::EPSILON);
            }
            else { // (-inf, right]
                $query->where($prefix . "number", "<=", floatval($right) + NumberField::EPSILON);
            }
        }
        else if ($right == "") {
            if ($invert) { // (-inf, left)
                $query->where($prefix . "number", "<", floatval($left) + NumberField::EPSILON);
            }
            else { // [left, inf)
                $query->where($prefix . "number", ">=", floatval($left) - NumberField::EPSILON);
            }
        }
        else {
            if ($invert) { // (-inf, left) union (right, inf)
                $query->whereNotBetween($prefix . "number", [floatval($left) - NumberField::EPSILON,
                    floatval($right) + NumberField::EPSILON]);
            }
            else { // [left, right]
                $query->whereBetween($prefix . "number", [floatval($left) - NumberField::EPSILON,
                    floatval($right) + NumberField::EPSILON]);
            }
        }
    }
}
