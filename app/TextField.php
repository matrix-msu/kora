<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class TextField extends BaseField {

    protected $fillable = [
        'rid',
        'flid',
        'text'
    ];

    /**
     * Keyword search for a text field. Depending on the value of partial we have two procedures:
     *  True: find occurrences of any particular argument, including partial results.
     *  False: find occurrences, matching the exact argument.
     *
     * @param array $args, Array of arguments for the search to use.
     * @param bool $partial, True if partial values should be considered in the search.
     * @return bool, True if the search parameters are satisfied.
     */
    public function keywordSearch(array $args, $partial)
    {
        return self::keywordRoutine($args, $partial, $this->text);
    }
}