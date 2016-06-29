<?php
/**
 * Created by PhpStorm.
 * User: Ian Whalen
 * Date: 5/9/2016
 * Time: 1:15 PM
 */

namespace App;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Debug\Dumper;
use Illuminate\Support\Facades\DB;

/**
 * Class Search.
 * Hold the important methods for searching.
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

    /** Id of the project we're searching in.
     * @var integer
     */
    private $pid;
    /** Id of the form we're searching in.
     * @var integer
     */
    private $fid;
    /** The query as input by the user.
     * @var string
     */
    private $arg;
    /** Method of search, see the search operators.
     * @var integer
     */
    private $method;


    /**
     * Keyword search our database for the queries given in the constructor.
     *
     *  Idea:    Eloquent is a fast system for form model binding and simple dumping of records.
     *  However it is obvious that in a system with potentially thousands of records (in the mysql sense)
     *  would be overburdened by getting every record and then searching through individually.
     *  So we let SQL do some work for us and then refine our search with a some extra functions.
     *
     * @return Collection, the results of the search, a collection of typed fields (e.g. TextField).
     */
    public function formKeywordSearch() {
        $fields = Field::where("fid", "=", $this->fid)->get();
        $results = new Collection();

        $processed = Search::processArgument($this->arg, $this->method);
        foreach($fields as $field) {
            if ($field->isSearchable()) {
                $results = $results->merge($field->keywordSearchTyped($processed)->get());
            }
        }

        if (! $results->isEmpty()) {
            return $this->gatherRecords($this->filterKeywordResults($results)); // This now has the typed fields that satisfied the search.
        }
        else {
            return $results;
        }
    }

    /*
     * Testing new keyword search function.
     */
    public function formKeywordSearch2() {
        if ($this->arg == "") {
            return [];
        }

        $used_types = []; // Array to keep track of types of fields we have searched already.

        $fields = Field::where("fid", "=", $this->fid)->get();
        $rids = [];

        $processed = Search::processArgument($this->arg, $this->method);

        if ($this->method != Search::SEARCH_AND) {
            foreach ($fields as $field) {
                if (! isset($used_types[$field->type]) && $field->isSearchable()) {
                    $used_types[$field->type] = true;

                    $rids += $field->keywordSearchTyped2($processed, $this->method)->get();
                }
            }

            // For some reason the query returns all the rids in an stdObject, this extracts the rid.
            $rids = array_map(function ($result) {
                return $result->rid;
            }, $rids);
        }
        else {
            $rids_array = []; // Stores the results of each individual search.

            foreach($fields as $field) {
                if (! isset($used_types[$field->type]) && $field->isSearchable()) {
                    $used_types[$field->type] = true;

                    foreach(explode(" ", $processed) as $arg) {
                        $rids_array[] = $field->keywordSearchTyped2($arg)->get();
                    }
                }
            }

            foreach($rids_array as &$rid_array) {
                $rid_array = array_map(function ($result) {
                    return $result->rid;
                }, $rid_array);
            }

            // Sorting by size of the array should make the intersection faster.
            usort($rids_array, function($a, $b) {
                $c_a = count($a);
                $c_b = count($b);

                if ($c_a == $c_b) return 0;
                return ($c_a < $c_b) ? -1 : 1;
            });

            $rids = array_shift($rids_array); // Get the first array.

            foreach($rids_array as $rid_array) { // Intersect until there are none left, this functions are the "and" portion of the search.
                $rids = array_intersect($rids, $rid_array);
            }
        }

        return array_unique($rids);
    }

    /**
     * Process the argument for full text searching based on the search method.
     *
     * OR and AND: "fish" => "fish*" to match with "fishing". Note: we don't apply an asterisk to the beginning because
     *         full text indexes do not apply backward due to the structure of the B-Tree.
     * EXACT: "large fish" => "\"large fish\"" to only match with the phrase "large fish".
     *
     * @param $arg, the argument to be processed.
     * @param $method, the search method (or, and, exact).
     * @return string, processed arguement.
     */
    public static function processArgument($arg, $method) {
        switch($method) {
            case Search::SEARCH_OR:
            case Search::SEARCH_AND:
                $args = explode(" ", $arg);

                foreach ($args as &$piece) {
                    $piece .= "* "; // Boolean fulltext wildcard
                }

                $arg = trim(implode($args));
                break;

            case Search::SEARCH_EXACT:
                $arg = "\"" . $arg . "\"";
                break;
        }

        return $arg;
    }


    /**
     * Filters the results of a keyword search.
     *
     * Typed fields all have a keywordSearch function, so we utilize this and the eloquent
     * method filter down to a collection of typed fields that all have the desired contents.
     *
     * @param Collection $results, a collection of typed fields.
     * @return Collection, the filtered collection of typed fields.
     */
    private function filterKeywordResults(Collection $results) {
        // Determine if the search should be partial at a typed field level.
        $partial = ($this->method == Search::SEARCH_AND || $this->method == Search::SEARCH_OR) ? true : false;

        if ($partial) {
            $arg = explode(" ", $this->arg);
        }
        else {
            $arg = [$this->arg];
        }

        // Only keep the fields that actually have the desired contents.
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
     * @param Collection $fields, a collection of BaseFields, assured to be
     * @return Collection $records, a collection of records associated with the BaseFields.
     */
    private function gatherRecords(Collection $fields) {
        $records = new Collection();

        if ($fields->isEmpty()) {
            return $records;
        }

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
                    while (! $fields->isEmpty() && $rid == $temp) {
                        $temp = $fields->pop()->rid;
                    }

                    // We will have a new record's field by this point.
                    $rid = $temp;
                    $records = $records->merge(Record::where('rid', '=', $rid)->get());
                }
                break;
            case self::SEARCH_AND:
                //
                // Again, the field was flagged by the sql search, however we need to consider a bit more.
                // For any particular record, all of the arguments in the query need to be in one or
                // more of the record's typed fields--all the arguments could be spread across all the fields.
                //
                // E.g. if a record has two text fields one containing the word "eldritch" and the other containing
                // "hideous" and an "AND" keyword search is executed the search will return the record if the search argument
                // was "eldritch hideous" but, will not return the record if the argument was "eldritch hideous Dunwich".
                //
                $args = explode(" ", $this->arg);
                $temp = 0; // Invalid rid for initializing purposes.

                while (! $fields->isEmpty()) {
                    $field = $fields->pop();

                    // Make sure the field we have is still from the same record as the previous.
                    if ($temp && $temp != $field->rid) {
                        $args = explode(" ", $this->arg);
                    }
                    $temp = $field->rid;

                    foreach ($args as $arg) {
                        if ($field->keywordSearch([$arg], false)) {
                            $args = array_diff($args, [$arg]);
                        }
                    }

                    // If all the arguments were found in a particular record's fields.
                    if (empty($args)) {
                        $records = $records->merge(Record::where('rid', '=', $field->rid)->get());

                        // Remove the rest of the fields that have the current rid.
                        while (! $fields->isEmpty() && $fields->last()->rid == $temp) {
                            $fields->pop();
                        }
                    }
                }
                break;
        }

        return $records;
    }

    /**
     * Returns an array of values that will be ignored by the full text index.
     *
     * @param $string string, the input to the search.
     * @return array, the intersection of the input (as an array) and self::$STOP_WORDS.
     */
    public static function showIgnoredArguments($string) {
        $args = explode(" ", $string);

        $short = [];
        foreach ($args as $arg) {
            if (strlen($arg) <= 3) {
                $short[] = $arg;
            }
        }

        return array_unique(array_merge(array_values(array_intersect($args, self::$STOP_WORDS)), $short));
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
     * Array of MyISAM stopwords.
     * These words are completely ignored by a search on a field with a fulltext index (CONTAINS statement).
     *
     * @var array
     */
    public static $STOP_WORDS = [
        "a's", "able", "about", "above", "according", "accordingly", "across", "actually", "after", "afterwards", "again", "against", "ain't", "all", "allow", "allows", "almost", "alone", "along", "already", "also", "although", "always", "am", "among", "amongst", "an", "and", "another", "any", "anybody", "anyhow", "anyone", "anything", "anyway", "anyways", "anywhere", "apart", "appear", "appreciate", "appropriate", "are", "aren't", "around", "as", "aside", "ask", "asking", "associated", "at", "available", "away", "awfully", "be", "became", "because", "become", "becomes", "becoming", "been", "before", "beforehand", "behind", "being", "believe", "below", "beside", "besides", "best", "better", "between", "beyond", "both", "brief", "but", "by", "c'mon", "c's", "came", "can", "can't", "cannot", "cant", "cause", "causes", "certain", "certainly", "changes", "clearly", "co", "com", "come", "comes", "concerning", "consequently", "consider", "considering", "contain", "containing", "contains", "corresponding", "could", "couldn't", "course", "currently", "definitely", "described", "despite", "did", "didn't", "different", "do", "does", "doesn't", "doing", "don't", "done", "down", "downwards", "during", "each", "edu", "eg", "eight", "either", "else", "elsewhere", "enough", "entirely", "especially", "et", "etc", "even", "ever", "every", "everybody", "everyone", "everything", "everywhere", "ex", "exactly", "example", "except", "far", "few", "fifth", "first", "five", "followed", "following", "follows", "for", "former", "formerly", "forth", "four", "from", "further", "furthermore", "get", "gets", "getting", "given", "gives", "go", "goes", "going", "gone", "got", "gotten", "greetings", "had", "hadn't", "happens", "hardly", "has", "hasn't", "have", "haven't", "having", "he", "he's", "hello", "help", "hence", "her", "here", "here's", "hereafter", "hereby", "herein", "hereupon", "hers", "herself", "hi", "him", "himself", "his", "hither", "hopefully", "how", "howbeit", "however", "i'd", "i'll", "i'm", "i've", "ie", "if", "ignored", "immediate", "in", "inasmuch", "inc", "indeed", "indicate", "indicated", "indicates", "inner", "insofar", "instead", "into", "inward", "is", "isn't", "it", "it'd", "it'll", "it's", "its", "itself", "just", "keep", "keeps", "kept", "know", "known", "knows", "last", "lately", "later", "latter", "latterly", "least", "less", "lest", "let", "let's", "like", "liked", "likely", "little", "look", "looking", "looks", "ltd", "mainly", "many", "may", "maybe", "me", "mean", "meanwhile", "merely", "might", "more", "moreover", "most", "mostly", "much", "must", "my", "myself", "name", "namely", "nd", "near", "nearly", "necessary", "need", "needs", "neither", "never", "nevertheless", "new", "next", "nine", "no", "nobody", "non", "none", "noone", "nor", "normally", "not", "nothing", "novel", "now", "nowhere", "obviously", "of", "off", "often", "oh", "ok", "okay", "old", "on", "once", "one", "ones", "only", "onto", "or", "other", "others", "otherwise", "ought", "our", "ours", "ourselves", "out", "outside", "over", "overall", "own", "particular", "particularly", "per", "perhaps", "placed", "please", "plus", "possible", "presumably", "probably", "provides", "que", "quite", "qv", "rather", "rd", "re", "really", "reasonably", "regarding", "regardless", "regards", "relatively", "respectively", "right", "said", "same", "saw", "say", "saying", "says", "second", "secondly", "see", "seeing", "seem", "seemed", "seeming", "seems", "seen", "self", "selves", "sensible", "sent", "serious", "seriously", "seven", "several", "shall", "she", "should", "shouldn't", "since", "six", "so", "some", "somebody", "somehow", "someone", "something", "sometime", "sometimes", "somewhat", "somewhere", "soon", "sorry", "specified", "specify", "specifying", "still", "sub", "such", "sup", "sure", "t's", "take", "taken", "tell", "tends", "th", "than", "thank", "thanks", "thanx", "that", "that's", "thats", "the", "their", "theirs", "them", "themselves", "then", "thence", "there", "there's", "thereafter", "thereby", "therefore", "therein", "theres", "thereupon", "these", "they", "they'd", "they'll", "they're", "they've", "think", "third", "this", "thorough", "thoroughly", "those", "though", "three", "through", "throughout", "thru", "thus", "to", "together", "too", "took", "toward", "towards", "tried", "tries", "truly", "try", "trying", "twice", "two", "un", "under", "unfortunately", "unless", "unlikely", "until", "unto", "up", "upon", "us", "use", "used", "useful", "uses", "using", "usually", "value", "various", "very", "via", "viz", "vs", "want", "wants", "was", "wasn't", "way", "we", "we'd", "we'll", "we're", "we've", "welcome", "well", "went", "were", "weren't", "what", "what's", "whatever", "when", "whence", "whenever", "where", "where's", "whereafter", "whereas", "whereby", "wherein", "whereupon", "wherever", "whether", "which", "while", "whither", "who", "who's", "whoever", "whole", "whom", "whose", "why", "will", "willing", "wish", "with", "within", "without", "won't", "wonder", "would", "wouldn't", "yes", "yet", "you", "you'd", "you'll", "you're", "you've", "your", "yours", "yourself", "yourselves", "zero"
    ];

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

    /**
     * Prints the help link for the flash message.
     *
     * @return string
     */
    static public function searchHelpLink() {
        return "<span class='pull-right'><a href='" . action("HelpController@search") . "' target='_blank'>Help</a>&nbsp;</span>";
    }
}