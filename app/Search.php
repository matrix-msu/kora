<?php
/**
 * Created by PhpStorm.
 * User: Ian Whalen
 * Date: 5/9/2016
 * Time: 1:15 PM
 */

namespace App;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class Search
 *
 * Utility class to handle some common search things.
 *
 * @package App
 */
class Search
{
    /**
     * Search operators.
     * OR: at least one argument must be in some record's field.
     * AND: all arguments must be in a particular record's fields.
     * EXACT: the whole phrase must be in some field.
     */
    const SEARCH_OR = 0;
    const SEARCH_AND = 1;
    const SEARCH_EXACT = 2;

    /**
     * Search constructor.
     *
     * @param $pid, project id.
     * @param $fid, form id.
     * @param $arg, the query of the search.
     * @param $method, the method of search, see search operators.
     */
    public function __construct($pid, $fid, $arg, $method) {
        $this->pid = $pid;
        $this->fid = $fid;
        $this->arg = $arg;
        $this->method = $method;
    }

    private $pid;           ///< The id of the project we're searching.
    private $fid;           ///< The id of the form we're searching.
    private $arg;           ///< The search query in array form.
    private $method;        ///< Method of search, see search operators.

    /**
     * Depending on the method, we need to apply some full text operators to the query string.
     */
    public function processArgument() {
        switch($this->method) {
            case self::SEARCH_OR:
            case self::SEARCH_AND: // These stars allow for searching finding "apple" inside the word "applet".
                return "*" . $this->arg . "*";
                break;

            case self::SEARCH_EXACT:
                return '"' . $this->arg . '"'; // Double quotes correspond to searching for an exact phrase.
                break;
        }

        return $this->arg;
    }

    /**
     * Keyword search our database for the queries given in the constructor.
     *
     *  Idea:    Eloquent is a fast system for form model binding and simple dumping of records.
     *  However it is obvious that in a system with potentially thousands of records (in the mysql sense)
     *  would be overburdened by getting every record and then searching through individually.
     *  So we let SQL do some work for us and then refine our search with a some extra functions.
     *
     * @return Collection, the results of the search.
     */
    public function formKeywordSearch() {
        $fields = Field::where("fid", "=", $this->fid)->get();

        $results = new Collection();

        $this->processArgument();

        foreach($fields as $field) {
            if ($field->isSearchable()) {
                $results = $results->merge($field->keywordSearchTyped($this->processArgument())->get());
            }
        }

        return $this->filterKeywordResults($results); // This now has the typed fields that satisfied the search.
    }

    /**
     * Filters the results of a keyword search.
     *
     * Typed fields all have a keywordSearch function, so we utilize this and the eloquent
     * method filter down to a collection of typed fields that all have the desired contents.
     *
     * @param Collection $results
     * @return Collection, the filtered collection.
     */
    public function filterKeywordResults(Collection $results) {
        // Determine if the search should be partial at a typed field level.
        $partial = ($this->method == Search::SEARCH_AND || $this->method == Search::SEARCH_OR) ? true : false;

        if ($partial) {
            $arg = explode(" ", $this->arg);
        }
        else {
            $arg = [$this->arg];
        }

        return $results->filter( function(BaseField $element) use ($arg, $partial) {
            return $element->keywordSearch($arg, $partial); // This is why we use OOP :)
        });
    }

    /**
     * Collects the records that are necessary from a collection of fields.
     * This function depends on the method of search, the distinction is important, see the search operators above for more.
     *
     * Exact is treated as OR at this point because the actual search in SQL will deal with exact phrases.
     *
     * @param Collection $fields
     * @return Collection $records
     */
    public function gatherRecords(Collection $fields) {
        $records = new Collection();

        // Sort the fields by record id.
        $fields->sortBy("rid");

        switch ($this->method) {
            case self::SEARCH_OR:
            case self::SEARCH_EXACT:
                //
                // The field was flagged by the SQL search, so it is the right field here, we don't need to consider
                // anything else other than getting the records needed in an efficient fashion.
                //
                // The possibly strange loop below assures we only pull records we need once from the database.
                //
                $rid = $fields->pop()->rid;
                $records = $records->merge(Record::where('rid', '=', $rid)->get());

                while(! $fields->isEmpty()) {
                    $temp = $fields->pop()->rid;
                    while ($rid == $temp) {
                        $temp = $fields->pop()->rid;
                    }

                    // We will have a new record's field by this point.
                    $rid = $temp;
                    $records = $records->merge(Record::where('rid', '=', $rid)->get());
                }
                break;
            case self::SEARCH_AND:
                //TODO: This.
                break;
        }

        return $records;
    }

    /**
     * Special characters the user might enter.
     *
     * @var array
     */
    public static $SPECIALS = ['À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð',
        'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è',
        'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā',
        'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė',
        'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ',
        'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń',
        'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ',
        'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų',
        'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ',
        'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ', 'Ά', 'ά', 'Έ', 'έ',
        'Ό', 'ό', 'Ώ', 'ώ', 'Ί', 'ί', 'ϊ', 'ΐ', 'Ύ', 'ύ', 'ϋ', 'ΰ', 'Ή', 'ή'];

    /**
     * Their translations deemed "close" by a skilled observer.
     *
     * Anything that does not get converted properly could just be added here.
     * @var array
     */
    public static $CLOSE_ASCII = ['A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D',
        'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e',
        'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a',
        'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e',
        'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i',
        'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n',
        'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S',
        's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U',
        'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i',
        'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o', 'Α', 'a', 'Ε', 'e',
        'Ο', 'ο', 'O', 'w', 'Ι', 'i', 'i', 'i', 'Υ', 'u', 'u', 'u', 'Η', 'n'];

    /**
     * Converts characters in a string to their close english only non-accented, non-diacritical matches.
     * The actual conversion is not super important, however consistency is, this is used to ensure a word like
     * "manana" matches what the search probably meant, "mañana".
     *
     * @param string $string
     * @return string, the converted string.
     */
    static public function convertCloseChars($string) {
        return str_replace(self::$SPECIALS, self::$CLOSE_ASCII, $string);
    }
}