<?php namespace App;

use Illuminate\Database\Eloquent\Model;

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
}
