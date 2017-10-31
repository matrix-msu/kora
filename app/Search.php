<?php namespace App;

class Search {

    /*
    |--------------------------------------------------------------------------
    | Search
    |--------------------------------------------------------------------------
    |
    | This class contains core search functionality in Kora3
    |
    */

    /**
     * @var int - At least one argument must be in some record's field
     */
    const SEARCH_OR = 0;
    /**
     * @var int - All arguments must be in a particular record's fields
     */
    const SEARCH_AND = 1;
    /**
     * @var int - The whole phrase must be in some field
     */
    const SEARCH_EXACT = 2;
    /**
     * @var int - The advanced search operator
     */
    const ADVANCED_METHOD = self::SEARCH_EXACT;
    /**
     * @var int - Id of the project we're searching in
     */
    private $pid;
    /**
     * @var int - Id of the form we're searching in
     */
    private $fid;
    /**
     * @var string - The query as input by the user
     */
    private $arg;
    /**
     * @var int - Method of search, see the search operators
     */
    private $method;

    /**
     * Search constructor.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  string $arg - The query of the search
     * @param  int $method - The method of search, see search operators
     */
    public function __construct($pid, $fid, $arg, $method) {
        $this->pid = $pid;
        $this->fid = $fid;
        $this->arg = $arg;
        $this->method = $method;
    }

    /**
     * Runs the keyword search routine on all field types.
     *
     * @return array - Array of rids satisfying search parameters
     */
    public function formKeywordSearch($flids=null, $external=false) {
        if($this->arg == "")
            return [];

        $used_types = []; // Array to keep track of types of fields we have searched already.

        if(is_null($flids))
            $fields = Field::where("fid", "=", $this->fid)->get();
        else
            $fields = Field::where("flid", "IN", $flids)->get();
        $rids = [];

        $processed = Search::processArgument($this->arg, $this->method);

        if($this->method != Search::SEARCH_AND) {
            foreach($fields as $field) {
                //This will account for both cases:
                // If internal and searchable
                // If external (api, korasearch) and ext-searchable
                if( (!$external && $field->isSearchable()) | ($external && $field->isExternalSearchable()) ) {
                    if(!isset($used_types[$field->type]))
                        $used_types[$field->type] = true;

                    $rids += $field->getTypedField()->keywordSearchTyped($field->fid, $processed, $this->method)->get();
                }
            }

            // For some reason the query returns all the rids in an stdObject, this extracts the rid.
            $rids = array_map(function ($result) {
                return $result->rid;
            }, $rids);
        } else {
            $rids_array = []; // Stores the results of each individual search.
            $args = explode(" ", $processed);
            foreach($args as $arg) {
                //Were building these arrays of essentially, results for each individual search before intersecting
                $rids_array[$arg] = array();
            }

            foreach($fields as $field) {
                //This will account for both cases:
                // If internal and searchable
                // If external (api, korasearch) and ext-searchable
                if( (!$external && $field->isSearchable()) | ($external && $field->isExternalSearchable()) ) {
                    if(!isset($used_types[$field->type]))
                        $used_types[$field->type] = true;

                    foreach($args as $arg) {
                        //For this field, search with each argument and store in perspective results array
                        $currArray = $rids_array[$arg];
                        $result = $field->getTypedField()->keywordSearchTyped($field->fid, $arg, $this->method)->get();
                        $currArray = array_merge($currArray,$result);
                        $rids_array[$arg] = $currArray;
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

                if($c_a == $c_b)
                    return 0;
                else
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
     * @param  string $arg - The argument to be processed
     * @param  int $method - The search method (or, and, exact)
     * @return string - Processed argument
     */
    public static function processArgument($arg, $method) {
        switch($method) {
            case self::SEARCH_OR:
                break;
            case self::SEARCH_AND:
                $args = explode(" ", $arg);

                foreach($args as &$piece) {
                    $piece .= "* "; // Boolean fulltext wildcard
                }

                $arg = trim(implode($args));
                break;
            case self::SEARCH_EXACT:
                $arg = "\"" . $arg . "\"";
                break;
        }

        return $arg;
    }

    /**
     * Returns an array of values that will be ignored by the full text index.
     *
     * @param  string $string - The input to the search
     * @return array -Tthe intersection of the input (as an array) and self::$STOP_WORDS
     */
    public static function showIgnoredArguments($string) {
        $args = explode(" ", $string);

        $short = [];
        foreach($args as $arg) {
            if (strlen($arg) <= 3)
                $short[] = $arg;
        }

        return array_unique(array_merge(array_values(array_intersect($args, self::$STOP_WORDS)), $short));
    }

    /**
     * Converts characters in a string to their close english only non-accented, non-diacritical matches.
     * The actual conversion is not super important, however consistency is, this is used to ensure a word like
     * "manana" matches what the search probably meant, "mañana".
     *
     * @param  string $string - String to convert
     * @return string - The converted string
     */
    static public function convertCloseChars($string) {
        return str_replace(self::$SPECIALS, self::$CLOSE_ASCII, $string);
    }

    /**
     * Prints the help link for the flash message.
     *
     * @return string - The html element
     */
    static public function searchHelpLink() {
        return "<span class='pull-right'><a href='" . action("HelpController@search") . "' target='_blank'>Help</a>&nbsp;</span>";
    }

    /**
     * @var array - Special characters the user might enter
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
     * @var array - Their translations deemed "close" by a skilled observer
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
     * @var array - Array of MyISAM stopwords that are completely ignored by a search on a field with a fulltext index (CONTAINS statement)
     */
    public static $STOP_WORDS = [
        "a's", "able", "about", "above", "according", "accordingly", "across", "actually", "after", "afterwards",
        "again", "against", "ain't", "all", "allow", "allows", "almost", "alone", "along", "already", "also",
        "although", "always", "am", "among", "amongst", "an", "and", "another", "any", "anybody", "anyhow",
        "anyone", "anything", "anyway", "anyways", "anywhere", "apart", "appear", "appreciate", "appropriate",
        "are", "aren't", "around", "as", "aside", "ask", "asking", "associated", "at", "available", "away",
        "awfully", "be", "became", "because", "become", "becomes", "becoming", "been", "before", "beforehand",
        "behind", "being", "believe", "below", "beside", "besides", "best", "better", "between", "beyond", "both",
        "brief", "but", "by", "c'mon", "c's", "came", "can", "can't", "cannot", "cant", "cause", "causes",
        "certain", "certainly", "changes", "clearly", "co", "com", "come", "comes", "concerning", "consequently",
        "consider", "considering", "contain", "containing", "contains", "corresponding", "could", "couldn't",
        "course", "currently", "definitely", "described", "despite", "did", "didn't", "different", "do", "does",
        "doesn't", "doing", "don't", "done", "down", "downwards", "during", "each", "edu", "eg", "eight", "either",
        "else", "elsewhere", "enough", "entirely", "especially", "et", "etc", "even", "ever", "every", "everybody",
        "everyone", "everything", "everywhere", "ex", "exactly", "example", "except", "far", "few", "fifth", "first",
        "five", "followed", "following", "follows", "for", "former", "formerly", "forth", "four", "from", "further",
        "furthermore", "get", "gets", "getting", "given", "gives", "go", "goes", "going", "gone", "got", "gotten",
        "greetings", "had", "hadn't", "happens", "hardly", "has", "hasn't", "have", "haven't", "having", "he",
        "he's", "hello", "help", "hence", "her", "here", "here's", "hereafter", "hereby", "herein", "hereupon",
        "hers", "herself", "hi", "him", "himself", "his", "hither", "hopefully", "how", "howbeit", "however", "i'd",
        "i'll", "i'm", "i've", "ie", "if", "ignored", "immediate", "in", "inasmuch", "inc", "indeed", "indicate",
        "indicated", "indicates", "inner", "insofar", "instead", "into", "inward", "is", "isn't", "it", "it'd",
        "it'll", "it's", "its", "itself", "just", "keep", "keeps", "kept", "know", "known", "knows", "last",
        "lately", "later", "latter", "latterly", "least", "less", "lest", "let", "let's", "like", "liked",
        "likely", "little", "look", "looking", "looks", "ltd", "mainly", "many", "may", "maybe", "me", "mean",
        "meanwhile", "merely", "might", "more", "moreover", "most", "mostly", "much", "must", "my", "myself",
        "name", "namely", "nd", "near", "nearly", "necessary", "need", "needs", "neither", "never", "nevertheless",
        "new", "next", "nine", "no", "nobody", "non", "none", "noone", "nor", "normally", "not", "nothing", "novel",
        "now", "nowhere", "obviously", "of", "off", "often", "oh", "ok", "okay", "old", "on", "once", "one", "ones",
        "only", "onto", "or", "other", "others", "otherwise", "ought", "our", "ours", "ourselves", "out", "outside",
        "over", "overall", "own", "particular", "particularly", "per", "perhaps", "placed", "please", "plus",
        "possible", "presumably", "probably", "provides", "que", "quite", "qv", "rather", "rd", "re", "really",
        "reasonably", "regarding", "regardless", "regards", "relatively", "respectively", "right", "said", "same",
        "saw", "say", "saying", "says", "second", "secondly", "see", "seeing", "seem", "seemed", "seeming", "seems",
        "seen", "self", "selves", "sensible", "sent", "serious", "seriously", "seven", "several", "shall", "she",
        "should", "shouldn't", "since", "six", "so", "some", "somebody", "somehow", "someone", "something", "sometime",
        "sometimes", "somewhat", "somewhere", "soon", "sorry", "specified", "specify", "specifying", "still",
        "sub", "such", "sup", "sure", "t's", "take", "taken", "tell", "tends", "th", "than", "thank", "thanks",
        "thanx", "that", "that's", "thats", "the", "their", "theirs", "them", "themselves", "then", "thence",
        "there", "there's", "thereafter", "thereby", "therefore", "therein", "theres", "thereupon", "these",
        "they", "they'd", "they'll", "they're", "they've", "think", "third", "this", "thorough", "thoroughly",
        "those", "though", "three", "through", "throughout", "thru", "thus", "to", "together", "too", "took",
        "toward", "towards", "tried", "tries", "truly", "try", "trying", "twice", "two", "un", "under", "unfortunately",
        "unless", "unlikely", "until", "unto", "up", "upon", "us", "use", "used", "useful", "uses", "using", "usually",
        "value", "various", "very", "via", "viz", "vs", "want", "wants", "was", "wasn't", "way", "we", "we'd", "we'll",
        "we're", "we've", "welcome", "well", "went", "were", "weren't", "what", "what's", "whatever", "when", "whence",
        "whenever", "where", "where's", "whereafter", "whereas", "whereby", "wherein", "whereupon", "wherever",
        "whether", "which", "while", "whither", "who", "who's", "whoever", "whole", "whom", "whose", "why", "will",
        "willing", "wish", "with", "within", "without", "won't", "wonder", "would", "wouldn't", "yes", "yet", "you",
        "you'd", "you'll", "you're", "you've", "your", "yours", "yourself", "yourselves", "zero"
    ];
}