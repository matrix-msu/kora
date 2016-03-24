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
     * @param array $args, Array of arguments for the search to use passed by reference.
     * @param bool $partial, True if partial values should be considered in the search.
     * @return bool, True if the search parameters are satisfied.
     */
    public function keyword_search(array &$args, $partial)
    {
        $text = $this->text;

        if ($partial) {
            foreach ($args as $arg) {
                $arg = strip_tags($arg);

                if (strpos($text, $arg) !== false)
                    return true; // Text contains a partial match.
            }

        }
        else {
            foreach ($args as $arg) {
                $arg = strip_tags($arg);
                $pattern = "/(\\W|^)" . $arg . "(\\W|$)/i";


                if (($result = preg_match($pattern, $text)) !== false) { // Continue if preg_match did not error.
                    if ($result) {
                        return true; // Text contains an complete match.
                    }
                }
            }
        }

        return false; // Text contains no matches.
    }
}
