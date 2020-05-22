<?php

class koraFormSearch {

    /*
    |--------------------------------------------------------------------------
    | kora Form Search
    |--------------------------------------------------------------------------
    |
    | Builds and generates a single form search to be used in the kora API Wrapper
    |
    */

    /**
     * Private variables defined at construction that can't be changed.
     */
    private $form;
    private $bearer_token;
    private $return_fields;
    private $sort;
    private $index;
    private $count;

    /**
     * Queries applied to the search
     */
    private $queries = [];
    private $logic = null;

    /**
     * Public boolean variables that users can change to configure the search as needed.
     *
     * @var $meta - Determines if record metadata is returned with the records
     * @var $size - Determines if the number of returned records is stored for this search result set
 *                  NOTE: This is recorded before the the count and index parameters take affect
     * @var $alt_names - Determines if field data within a record is indexed by its field name or alternative field name
     * @var $reverse_assoc - Determines if an array of kora IDs that associate to a returned record are returned with that record
     */
    public $meta = true;
    public $size = false;
    public $alt_names = false;
    public $reverse_assoc = true;

    /**
     * Determines if field data for an associated field is returned as an array of records, or an array of kora IDs
     */
    private $assoc = false;
    private $assoc_fields = 'ALL';

    /**
     * Determines if we want filters back for the returned records
     */
    private $filters = false;
    private $filter_count = 1;
    private $filter_fields = 'ALL';

    /**
     * Constructs the class
     *
     * @param  int $form - Form ID
     * @param  string $bearer_token - Kora token w/ SEARCH permissions
     * @param  array $return_fields - Array of field names that limit the returned field data
     * @param  array $sort - Performs a sort on a search result set, either by a field name, or by common record metadata such as kora ID or timestamps
     *                       NOTE: Common metadata includes "kid", "created_at", and "updated_at"
     * [
     *     ["Field name" => "ASC"],
     *     ["Other field name" => "DESC"],
     *     ["created_at" => "ASC"],
     *     ...
     * ]
     * @param  int $index - Value that represents the index the search result set will start
     * @param  int $count - Value that represents the number of records returned
     */
    public function __construct($form, $bearer_token, $return_fields = 'ALL', $sort = null, $index = null, $count = null) {
        $this->form = $form;
        $this->bearer_token = $bearer_token;
        $this->return_fields = $return_fields;
        $this->sort = $sort;
        $this->index = $index;
        $this->count = $count;
    }

    /**
     * Add a keyword search to the queries array
     *
     * @param  array $key_words - Array of search terms to evaluate in a keyword search
     * @param  string $key_method - Defines how the results from each keyword term should be combined: OR | AND
     * @param  array $key_fields - Array of field names that limit the fields to be processed in a keyword search
     * @param  boolean $custom_wildcards - Determines if the terms in key_words will have wildcards provided by user, or automatically added
     * @param  boolean $not - Determines if the query returns the set of records that do not meet this search
     * @return int - Index of the query for use in query logic
     */
    public function addKeywordSearch($key_words, $key_method = 'OR', $key_fields = null, $custom_wildcards = false, $not = false) {
        $query = ['search' => 'keyword', 'key_words' => $key_words, 'key_method' => $key_method];

        !is_null($key_fields) ? $query['key_fields'] = $key_fields : null;
        $custom_wildcards ? $query['custom_wildcards'] = $custom_wildcards : null;
        $not ? $query['not'] = $not : null;

        $this->queries[] = $query;

        return sizeof($this->queries)-1;
    }

    /**
     * Add a keyword search to the queries array
     *
     * @param  array $kids - Array of kora IDs to search for
     * @param  boolean $legacy - Determines if array of kora IDs are legacy (pre v3) kora IDs
     * @param  boolean $not - Determines if the query returns the set of records that do not meet this search
     * @return int - Index of the query for use in query logic
     */
    public function addKIDSearch($kids, $legacy = false, $not = false) {
        !$legacy ? $query = ['search' => 'kid', 'kids' => $kids] : $query = ['search' => 'legacy_kid', 'legacy_kids' => $kids];

        $not ? $query['not'] = $not : null;

        $this->queries[] = $query;

        return sizeof($this->queries)-1;
    }

    /**
     * Add a keyword search to the queries array
     *
     * @param  array $advFields - Parameters for the advanced search (Examples of each field type, can combine multiple fields into one search)
     * [
     *     “Text field name" => [
     *         "input" => "Your search string",
     *         “partial” => false //do we want an exact or partial match
     *     ],
     *     “Rich Text field name" => [
     *         "input" => "Your search string"
     *     ],
     *     “Integer field name" => [
     *         "left" => -13, //left bound of search range, ignore for -infinity
     *         "right" => 37, //right bound of search range, ignore for infinity
     *         "invert" => false //determines if we look outside or inside the range
     *     ],
     *     “Float field name" => [
     *         "left" => -13.3, //left bound of search range, ignore for -infinity
     *         "right" => 37.1, //right bound of search range, ignore for infinity
     *         "invert" => false //determines if we look outside or inside the range
     *     ],
     *     “List field name" => [
     *         "input" => "List option"
     *     ],
     *     “Multi Select List field name" => [
     *         "input" => ["List option", "another List Option", ...]
     *     ],
     *     “Generated List field name" => [
     *         "input" => ["List option", "another List Option", ...]
     *     ],
     *     “Date field name" => [
     *         "begin_month" => 1, "begin_day" => 13, "begin_year" => 1337,
     *         "end_month" => 8, "end_day" => 19, "end_year" => 1990
     *     ],
     *     “Date Time field name" => [
     *         "begin_month" => 1, "begin_day" => 13, "begin_year" => 1337,
     *         "begin_hour" => 1, "begin_minute" => 0, "begin_second" => 0,
     *         "end_month" => 8, "end_day" => 19, "end_year" => 1990,
     *         "end_hour" => 5, "end_minute" => 12, "end_second" => 52
     *     ],
     *     “Historical Date field name" => //
     *         "begin_month" => 1, "begin_day" => 13, "begin_year" => 1337,
     *         "begin_era" => "CE", //options include CE, BCE, BP, and KYA BP
     *         "end_month" => 8, "end_day" => 19, "end_year" => 1990,
     *         "end_era" => "CE"
     *     ],
     *     “Boolean field name" => [
     *         "input" => true
     *     ],
     *     “Geolocator field name" => [
     *         "lat" => 42.7314094; //latitude of search point
     *         "lng" => -84.476258; //longitude of search point
     *         "range" => 500; //distance in kilometers from the search point
     *     ],
     *     “Associator field name" => [
     *         "input" => ["1-1-0","1-1-1","1-1-2",...],
     *         “any” => false //should the record contain all provided kora IDs or at least one
     *     ],
     *     “Combo List field name" => [
     *         “sub field name” => [
     *             //Use sub field's type structure using above examples above
     *         ],
     *     ]
     * ]
     * @param  boolean $not - Determines if the query returns the set of records that do not meet this search
     * @return int - Index of the query for use in query logic
     */
    public function addAdvancedSearch($advFields, $not = false) {
        $query = ['search' => 'advanced', 'adv_fields' => $advFields];

        $not ? $query['not'] = $not : null;

        $this->queries[] = $query;

        return sizeof($this->queries)-1;
    }

    /**
     * Takes the record results from all the added queries, and defines how the sets should be either merged and/or intersected
     * Each query is given an integer ID based on the order added to the search (i.e. 0, 1, 2, ...)
     * NOTE: This ID is also returned as an integer when using the addSearch functions above
     *
     * @param  array $logic - The structure of the query logic
     * ["and" => [1, ["or" => [0, 2]], 3]]
     *      1) The results of queries 0 and 2 are merged together (or)
     *      2) The results of 1, 3, and the step above are intersected together (and)
     */
    public function addQueryLogic($logic) {
        $this->logic = $logic;
    }

    /**
     * Enables field data for an associated field to be returned as an array of records
     *
     * @param  array $assoc_fields - Allows user to specify which fields actually return for the associated record
     */
    public function enableAssociationData($assoc_fields = 'ALL') {
        $this->assoc = true;
        $this->assoc_fields = $assoc_fields;
    }

    /**
     * Enables filter data to be provided for the returned records
     *
     * @param  int $filter_count - Determines how frequent a field value needs to occur to be returned as a filter
     * @param  array $filter_fields - Array of field names that limit the fields to be processed for filters
     */
    public function enableFilters($filter_count = 1, $filter_fields = 'ALL') {
        $this->filters = true;
        $this->filter_count = $filter_count;
        $this->filter_fields = $filter_fields;
    }

    /**
     * Builds the form array required for sending to the API
     *
     * @return array - The formatted form array
     */
    public function generateFormArray() {
        $form = [
            'form' => $this->form,
            'bearer_token' => $this->bearer_token,
            'return_fields' => $this->return_fields,
            'sort' => $this->sort,
            'index' => $this->index,
            'count' => $this->count,

            'meta' => $this->meta,
            'size' => $this->size,
            'alt_names' => $this->alt_names,
            'reverse_assoc' => $this->reverse_assoc,

            'assoc' => $this->assoc,
            'assoc_fields' => $this->assoc_fields,

            'filters' => $this->filters,
            'filter_count' => $this->filter_count,
            'filter_fields' => $this->filter_fields,
        ];

        if(!empty($this->queries)) {
            $form['queries'] = $this->queries;
            $form['logic'] = $this->logic;
        }

        return $form;
    }
}
?>