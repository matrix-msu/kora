<?php namespace App;
/**
 * Created by PhpStorm.
 * User: ian.whalen
 * Date: 3/22/2016
 * Time: 11:12 AM
 */

use Illuminate\Database\Eloquent\Model;

/**
 * Class BaseField
 * @package App
 */
abstract class BaseField extends Model
{
    protected $primaryKey = "id";

    /**
     * Record that the field belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function record(){
        return $this->belongsTo('App\Record');
    }

    /**
     * Pure virtual keyword search method for a general field.
     *
     * @param array $args, Array of arguments for the search to use.
     * @param bool $partial, True if partial values should be considered in the search.
     * @return bool, True if the field has satisfied the search parameters.
     */
    abstract public function keywordSearch(array $args, $partial);

    /**
     * The routine that drives the keyword search for most fields.
     *
     * @param array $args, Array of arguments for the search routine to use.
     * @param bool $partial, True if partial values should be considered in the search.
     * @param string $haystack, The string to be searched through.
     * @return bool, True if the search parameters are satisfied.
     */
    static public function keywordRoutine(array $args, $partial, $haystack) {
        $text = Search::convertCloseChars($haystack);

        if ($partial) {
            foreach ($args as $arg) {
                if (strlen($arg) && stripos($text, $arg) !== false) {
                    return true; // Text contains a partial match.
                }

            }
        }
        else {
            foreach ($args as $arg) {
                $arg = preg_quote($arg, "\\"); // Escape regular expression characters.

                $pattern = "/(\\W|^)" . $arg . "(\\W|$)/i";

                $result = preg_match($pattern, $text);
                if (strlen($arg) && $result !== false) { // Continue if preg_match did not error.
                    if ($result) {
                        return true; // Text contains a complete match.
                    }
                }
            }
        }

        return false; // Text contains no matches.
    }

    /****************************************************************
     *            Moved convertCloseChars to App/Search             *
     ****************************************************************/
}