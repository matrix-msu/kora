<?php

//THIS TOOL IS PRIMARILY (see first class for exception) THE CONVERTER FUNCTION FOR USING OLD KORA 2 KORA_Search AND KORA_Clause FUNCTIONS
//THIS WORKS IF YOU HAVE USED EITHER Exodus OR THE K2 Importer TOOLS TO MIGRATE YOUR KORA 2 DATA
//Step 1
////Copy this file into your local site, and change your php includes of koraSearch.php from K2 to point at this file
//Step 2
////Replace your token, pid, and sid with a new search token, a K3 pid, and fid
//Step 3
////If you are pointing to a K3 installation that needs http auth, as the 9th variable of KORA_Search, place an
////array in the format ["user"=>"{your_username}", "pass"=>"{your_password}"]
//Step 4
////You may need to modify URLs for file and image fields to properly point at their new Kora3 locations

//This class has a bunch of functions that can help build the json required for a form to search with the API. NOTE: This
//can be used separately from it's use in the koraSearch conversion.

/**
 * SITE VARIABLES - Fill these out to use remotely
 * @var string - Kora3 API URL
 */

define("kora3ApiURL","FILL_THIS"); //"http://www.myKora3Install.com/api/search"

class kora3ApiExternalTool {

    /*
    |--------------------------------------------------------------------------
    | Kora 3 Api External Tool
    |--------------------------------------------------------------------------
    |
    | This class helps generate the query string for the forms variable in the
    | RESTful API for Kora3
    |
    */

    /**
     * Builds the query string for a keyword search.
     *
     * @param  string $keyString - Keywords for the search
     * @param  string $method - Defines if search is AND, OR, or EXACT
     * @param  bool $not - Get the negative results of the search
     * @param  array $fields - Specific fields to search in
     * @param  bool $customWildCards - Is the user providing wildcards
     * @return array - The query array
     */
    static function keywordQueryBuilder($keys,$method,$not=false,$fields=array(),$customWildCards=false) {
        $qkey = array();
        $qkey["search"] = "keyword";
        $qkey["key_words"] = $keys;
        $qkey["key_method"] = $method;
        if($not)
            $qkey["not"] = $not;
        if(!empty($fields))
            $qkey["key_fields"] = $fields;
        if($customWildCards)
            $qkey["custom_wildcards"] = $customWildCards;

        return $qkey;
    }

    /**
     * Builds the query string for a KID search.
     *
     * @param  array $kids - KIDs we are searching for
     * @param  bool $not - Get the negative results of the search
     * @param  bool $legacy - Search for legacy kid instead
     * @return array - The query array
     */
    static function kidQueryBuilder($kids,$not=false,$legacy=false) {
        $qkid = array();
        if(!$legacy) {
            $qkid["search"] = "kid";
            $qkid["kids"] = $kids;
        } else {
            $qkid["search"] = "legacy_kid";
            $qkid["legacy_kids"] = $kids;
        }
        if($not)
            $qkid["not"] = $not;

        return $qkid;
    }

    /**
     * Builds the query string for an advanced search.
     *
     * @param  array $advData - Array with search parameters for advanced search (SEE API DOCUMENTATION FOR ADVANCED STRUCTURE)
     * @param  bool $not - Get the negative results of the search
     * @return array - The query array
     */
    static function advancedQueryBuilder($advData,$not=false) {
        $qadv = array();
        $qadv["search"] = "advanced";

        $qadv["adv_fields"] = $advData;

        if($not)
            $qadv["not"] = $not;

        return $qadv;
    }

    /**
     * Builds simple array with two queries and a comparison operator.
     *
     * @param  array $queryObj1 - Index of query object in your query array, or another logic array
     * @param  string $operator - Comparison operator
     * @param  array $queryObj2 - Index of 2nd query object in your query array, or another logic array
     * @return array - Logic array
     */
    static function queryLogicBuilder($queryObj1,$operator,$queryObj2) {
        return [$operator => [$queryObj1,$queryObj2]];
    }

    /**
     * Takes queries and other information to build the full forms string value in an array.
     *
     * @param  string $fid - Form ID
     * @param  string $token - Token to authenticate search
     * @param  array $flags - Array of flags that customize the search further
     * @param  array $fields - For each record, the fields that should actually be returned
     * @param  array $sort - Defines what fields we are sorting by
     * @param  array $queries - The collection of query arrays in the search
     * @param  array $qLogic - Logic array for the search
     * @param  int $index - In final result set, what record should we start at
     * @param  int $count - Determines, starting from $index, how many records to return
     * @param  int $filterCount - Determines what the minimum threshold us for a filter to appear
     * @param  array $fitlerFields - Determines what fields are processed for filters
     * @param  array $assocFields - Determines what fields are returned for associated records
     * @return array - Array representation of the form search for the API
     */
    static function formSearchBuilder($fid,$token,$flags,$fields,$sort,$queries,$qLogic,$index=null,$count=null,$filterCount=null,$fitlerFields=null,$assocFields=null) {
        $form = array();
        $form["form"] = $fid;
        $form["bearer_token"] = $token;

        $form["data"] = in_array("data",$flags) ? in_array("data",$flags) : false;
        $form["meta"] = in_array("meta",$flags) ? in_array("meta",$flags) : false;
        $form["size"] = in_array("size",$flags) ? in_array("size",$flags) : false;

        $form["filters"] = in_array("filters",$flags) ? in_array("filters",$flags) : false;
        if(!is_null($filterCount))
            $form["filter_count"] = $filterCount;
        if(is_array($fitlerFields) && empty($fitlerFields))
            $form["filter_fields"] = "ALL";
        else
            $form["filter_fields"] = $fitlerFields;

        $form["assoc"] = in_array("assoc",$flags) ? in_array("assoc",$flags) : false;
        if(is_array($assocFields) && empty($assocFields))
            $form["assoc_fields"] = "ALL";
        else
            $form["assoc_fields"] = $assocFields;
        $form["reverse_assoc"] = in_array("reverse_assoc",$flags) ? in_array("reverse_assoc",$flags) : false;

        $form["real_names"] = in_array("real_names",$flags) ? in_array("real_names",$flags) : false;
        $form["under"] = in_array("under",$flags) ? in_array("under",$flags) : false;

        if(is_array($fields) && empty($fields))
            $form["return_fields"] = "ALL";
        else
            $form["return_fields"] = $fields;
        if(!empty($sort))
            $form["sort"] = $sort;

        if(!is_null($index))
            $form["index"] = $index;
        if(!is_null($count))
            $form["count"] = $count;

        $form["queries"] = $queries;
        if(!is_null($qLogic))
            $form["logic"] = $qLogic;

        return $form;
    }
}

class KORA_Clause {

    /*
    |--------------------------------------------------------------------------
    | Kora Clause
    |--------------------------------------------------------------------------
    |
    | Replication class of KORA_Clause from Kora 2
    |
    */

    /**
     * @var array - Queries involved in the clause
     */
    var $queries = array();

    /**
     * @var array - Logic for the clause
     */
    var $logic = null;

    /**
     * Constructs the Kora Clause.
     *
     * @param  mixed $arg1 - Main argument for the clause
     * @param  string $op - Operator to compare arguments
     * @param  mixed $arg2 - Compared argument for the clause
     */
    function __construct($arg1, $op, $arg2) {
        $op = strtolower($op);

        if($op == "and" | $op == "or") {
            if(!$arg1 instanceof self) {
                die("The first query clause you provided must be an object of class KORA_Clause");
            }
            if(!$arg2 instanceof self) {
                die("The second query clause you provided must be an object of class KORA_Clause");
            }
            $argQue1 = $arg1->getQueries();
            $argQue2 = $arg2->getQueries();
            $this->queries = array_merge($argQue1,$argQue2);

            //Logic stuff
            $argLogic1 = $arg1->getLogic();
            $argLogic2 = $arg2->getLogic();
            $newLogic = array();

            //first argument
            if(is_null($argLogic1)) {
                //first argument is a single query, so lets set it as index 0 in the logic
                $newLog1 = 0;
                $size = 1;
            } else {
                //first argument already has a complex query logic, so store that and record size of queries
                $newLog1 = $argLogic1;
                $size = sizeof($argQue1);
            }

            //second argument
            if(is_null($argLogic2)) {
                //second argument is a single query, so lets set it's index as the size of query 1
                $newLog2 = $size;
            } else {
                //second argument has complex query logic. We need to loop through and build new array where every index
                //is increased by the size of query 1
                $newLog2 = $this->recursizeLogicIndex($argLogic2,$size);
            }

            //store the operation
            $newLogic[$op] = [$newLog1,$newLog2];

            $this->logic = $newLogic;
        }
        else {
            $tool = new kora3ApiExternalTool();
            if(strtoupper($arg1)=="KID") {
                if($arg2 == "")
                    $arg2 = array();
                else if(!is_array($arg2))
                    $arg2 = array($arg2);

                if($op=="="|$op=="=="|strtoupper($op)=="IN")
                    $not = false;
                else if(strtoupper($op)=="NOT IN"|$op=="!="|$op=="!==")
                    $not = true;
                else
                    die("Illegal KID operator provided: ".$op);

                $query = $tool::kidQueryBuilder($arg2, $not);
                array_push($this->queries,$query);
            } else if($arg1=="legacy_kid") {
                if($arg2 == "")
                    $arg2 = array();
                else if(!is_array($arg2))
                    $arg2 = array($arg2);

                if($op=="="|$op=="=="|strtoupper($op)=="IN")
                    $not = false;
                else if(strtoupper($op)=="NOT IN"|$op=="!="|$op=="!==")
                    $not = true;
                else
                    die("Illegal KID operator provided: ".$op);

                $query = $tool::kidQueryBuilder($arg2, $not, true);
                array_push($this->queries,$query);
            } else {
                if($op=="="|$op=="=="|strtoupper($op)=="LIKE") {
                    $not = false;
                    $arg2 = [$this->dateCleaner($arg2)];
                } else if($op=="!="|$op=="!=="|strtoupper($op)=="NOT LIKE") {
                    $not = true;
                    $arg2 = [$this->dateCleaner($arg2)];
                } else if(strtoupper($op)=="IN") {
                    $not = false;
                } else if(strtoupper($op)=="NOT IN") {
                    $not = true;
                } else
                    die("Illegal keyword operator provided: ".$op);

                $query = $tool::keywordQueryBuilder($arg2, "OR", $not, array($arg1));
                array_push($this->queries,$query);
            }
        }
    }

    /**
     * Cleans up the way dates used to be searched.
     *
     * @param  string $keyword - The keyword to filter
     * @return string - The filtered date keyword
     */
    private function dateCleaner($keyword) {
        $keyword = str_replace("%","",$keyword);
        $hasDate = false;
        $dateArray = ['month'=>01,'day'=>01,'year'=>0001];

        if(strpos($keyword,'<month>') !== false) {
            $hasDate = true;
            $p1 = explode('<month>',$keyword)[1];
            $dateArray['month'] = explode('</month>',$p1)[0];
        }

        if(strpos($keyword,'<day>') !== false) {
            $hasDate = true;
            $p1 = explode('<day>',$keyword)[1];
            $dateArray['day'] = explode('</day>',$p1)[0];
        }

        if(strpos($keyword,'<year>') !== false) {
            $hasDate = true;
            $p1 = explode('<year>',$keyword)[1];
            $dateArray['year'] = explode('</year>',$p1)[0];
        }

        if($hasDate)
            return $dateArray['year'].'-'.$dateArray['month'].'-'.$dateArray['day'];
        else
            return $keyword;
    }

    /**
     * Recursively reindexes the logic query to match any new queries added to the array.
     *
     * @param  array $logicArray - The logic to reindex
     * @param  int $size - Size of array at top level of recursion
     * @return array - The newly indexed logic array
     */
    private function recursizeLogicIndex($logicArray,$size) {
        $returnArray = array();

        foreach($logicArray as $op => $clauses) {
            $operator = $op;
            $logicOne = $clauses[0];
            $logicTwo = $clauses[1];
        }

        //part1
        if(is_array($logicOne)) {
            $newLog1 = $this->recursizeLogicIndex($logicOne,$size);
        } else {
            $newLog1 = $logicOne+$size;
        }

        //part2
        if(is_array($logicTwo)) {
            $newLog2 = $this->recursizeLogicIndex($logicTwo,$size);
        } else {
            $newLog2 = $logicTwo+$size;
        }

        $returnArray[$operator] = [$newLog1, $newLog2];

        return $returnArray;
    }

    /**
     * Getter function for query variable.
     *
     * @return array - Query variable
     */
    public function getQueries() {
        return $this->queries;
    }

    /**
     * Getter function for logic variable.
     *
     * @return array - Logic varible
     */
    public function getLogic() {
        return $this->logic;
    }
}

/**
 * Converts an old KORA_Search from Kora 2 into a Kora3 search, provided steps at top of page were completed properly.
 *
 * @param  string $token - Kora3 token to authenticate the search
 * @param  int $pid - Kora3 project ID
 * @param  int $sid - Kora3 form ID relative to old scheme ID
 * @param  KORA_Clause $koraClause - The new represented Kora Clause
 * @param  array $fields - Array of new flids relative to their old control names
 * @param  array $order - Old Kora 2 sort array that will be converted by this function
 * @param  int $start - In final result set, what record should we start at
 * @param  int $number - Determines, starting from $index, how many records to return
 * @param  array $userInfo - Server authentication for connecting to private servers
 * @param  bool $underScores - Determines if a search should return the field names with underscores or spaces
 * @return array - The records to return from the search
 */
function KORA_Search($token,$pid,$sid,$koraClause,$fields,$order=array(),$start=0,$number=0,$userInfo = array(),$underScores=false) {
    if(!$koraClause instanceof KORA_Clause)
        die("The query clause you provided must be an object of class KORA_Clause");

    //Format sort array and map controls to fields
    $newOrder = array();
    foreach($order as $o) {
        if($o["field"]=="systimestamp")
            $sortField = "updated_at";
        else
            $sortField = $o["field"];

        $dir = $o["direction"];
        if($dir==SORT_DESC)
            $newDir = "DESC";
        else
            $newDir = "ASC";
        array_push($newOrder,[$sortField => $newDir]);
    }

    //Covers the case that ALL is in the fields array
    if(is_array($fields)) {
        if(empty($fields) | $fields[0]=="ALL") {
            $fields = "ALL";
        }
    }

    //Format the start/number for legacy.
    if($start==0)
        $start=null;
    if($number==0)
        $number=null;

    //Filters
    if($underScores)
        $filters = array("data","meta","under");
    else
        $filters = array("data","meta");

    $output = array();
    $tool = new kora3ApiExternalTool();

    $fsArray = $tool->formSearchBuilder(
        $sid,
        $token,
        $filters,
        $fields,
        $newOrder,
        $koraClause->getQueries(),
        $koraClause->getLogic(),
        $start,
        $number
    );

    array_push($output,$fsArray);

    $data = array();
    $data["forms"] = json_encode($output);
    $data["format"] = "KORA_OLD";

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, kora3ApiURL);
    if(!empty($userInfo)) {
        curl_setopt($curl, CURLOPT_USERPWD, $userInfo["user"].":".$userInfo["pass"]);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    }
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

    if(!$result = curl_exec($curl))
        return curl_error($curl);

    curl_close($curl);

    $result = json_decode($result,true);

    if(isset($result['records']))
        return $result['records'][0];
    else
        return $result;
}

/**
 * Converts an old KORA_Search from Kora 2 into a Kora3 search, provided steps at top of page were completed properly.
 *
 * @param  string $token - Kora3 token to authenticate the search
 * @param  array $pidList - Array of Kora3 project IDs
 * @param  array $sidList - Array of Kora3 form IDs relative to old scheme IDs
 * @param  KORA_Clause $koraClause - The new represented Kora Clause
 * @param  array $fields - Array of new flids relative to their old control names
 * @param  array $order - Old Kora 2 sort array that will be converted by this function
 * @param  int $start - In final result set, what record should we start at
 * @param  int $number - Determines, starting from $index, how many records to return
 * @param  array $userInfo - Server authentication for connecting to private servers
 * @param  bool $underScores - Determines if a search should return the field names with underscores or spaces
 * @return array - The records to return from the search
 */
function MPF_Search($token,$pidList,$sidList,$koraClause,$fields,$order=array(),$start=0,$number=0,$userInfo = array(),$underScores=false) {
    if(!$koraClause instanceof KORA_Clause)
        die("The query clause you provided must be an object of class KORA_Clause");

    //Format sort array and map controls to fields
    $newOrder = array();
    $mergeRules = array();
    foreach($order as $o) {
        if($o["field"]=="systimestamp") {
            $sortField = "updated_at";
        } else {
            $sortField = $o["field"];
            $mergeFields = array();
            foreach ($pidList as $i => $pid) {
                array_push($mergeFields, $o["field"]);
            }
            $mergeRules[$o["field"]] = $mergeFields;
        }

        $dir = $o["direction"];
        if($dir==SORT_DESC)
            $newDir = "DESC";
        else
            $newDir = "ASC";

        array_push($newOrder,[$sortField => $newDir]);
    }

    // Build forms information for each project to be searched
    $output = array();
    foreach ($pidList as $i => $pid) {
        $sid = $sidList[$i];

        //Covers the case that ALL is in the fields array
        if(is_array($fields)) {
            if(empty($fields) | $fields[0]=="ALL") {
                $fields = "ALL";
            }
        }

        $flag = ["data", "meta"];
        if($underScores)
            $flag[] = "under";

        $tool = new kora3ApiExternalTool();
        $fsArray = $tool->formSearchBuilder(
            $sid,
            $token,
            $flag,
            $fields,
            null,
            $koraClause->getQueries(),
            $koraClause->getLogic(),
            null,
            null
        );
        array_push($output,$fsArray);
    }
    $data = array();
    $data["forms"] = json_encode($output);
    $data["merge"] = json_encode($mergeRules);
    $data["sort"] = json_encode($newOrder);
    $data["index"] = $start;
    $data["count"] = $number;
    $data["format"] = "KORA_OLD";

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, kora3ApiURL);
    if(!empty($userInfo)) {
        curl_setopt($curl, CURLOPT_USERPWD, $userInfo["user"].":".$userInfo["pass"]);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    }
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

    if(!$result = curl_exec($curl))
        return curl_error($curl);

    curl_close($curl);

    $result = json_decode($result,true);

    if(isset($result['records']))
        return $result['records'];
    else
        return $result;
}

?>