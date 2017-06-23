<?php

namespace App\FieldHelpers;

//THIS TOOL IS PRIMARILY (see first class for exception) THE CONVERTER FUNCTION FOR USING OLD KORA 2 KORA_Search AND KORA_Clause FUNCTIONS
//IDEALLY YOU HAVE USED EITHER Exodus OR THE K2 Importer TOOLS TO MIGRATE YOUR KORA 2 DATA
//Step 1
////Change your php includes of koraSearch.php from K2 to point at this file
////In your file, use the namespace tag "namespace App\FieldHelpers"
//Step 2
////Replace your token, pid, and sid with a new search token, a k3 pid, and fid
//Step 3
////For base KORA_Clauses (i.e. ones that do not use AND/OR), change the control name to the new nickname of the
////corresponding field
////Do the same for field names in the order array if used
//Step 4
////If you are pointing to a K3 installation that needs http auth, as the 9th variable of KORA_Search, place an
////array in the format ["user"=>"{your_username}", "pass"=>"{your_password}"]

//This class has a bunch of functions that can help build the json required for a form to search with the API. NOTE: This
//can be used separately from it's use in the koraSearch conversion.
class kora3ApiExternalTool{

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
     * @param  array $flids - Specific fields to search in
     * @return array - The query array
     */
    static function keywordQueryBuilder($keyString,$method,$not=false,$flids=array()){
        $qkey = array();
        $qkey["search"] = "keyword";
        $qkey["keys"] = $keyString;
        $qkey["method"] = $method;
        if($not)
            $qkey["not"] = $not;
        if(!empty($flids))
            $qkey["fields"] = $flids;

        return $qkey;
    }

    /**
     * Builds the query string for a KID search.
     *
     * @param  array $kids - KIDs we are searching for
     * @param  bool $not - Get the negative results of the search
     * @return array - The query array
     */
    static function kidQueryBuilder($kids,$not=false){
        $qkid = array();
        $qkid["search"] = "KID";
        $qkid["kids"] = $kids;
        if($not)
            $qkid["not"] = $not;

        return $qkid;
    }

    /**
     * TODO::Builds the query string for an advanced search.
     */
    static function advancedQueryBuilder(){
        /* $qadv = array();
        $qadv["search"] = "advanced";
        $advFields = array();
        $fText = array();
        $fText["input"] = "Mr. Sister";
        $advFields["title"] = $fText;
        $qadv["fields"] = $advFields; */
    }

    /**
     * Builds simple array with two queries and a comparison operator.
     *
     * @param  array $queryObj1 - Index of query object in your query array, or another logic array
     * @param  string $operator - Comparison operator
     * @param  array $queryObj2 - Index of 2nd query object in your query array, or another logic array
     * @return array - Logic array
     */
    static function queryLogicBuilder($queryObj1,$operator,$queryObj2){
        return array($queryObj1,$operator,$queryObj2);
    }

    /**
     * Takes queries and other information to build the full forms string value in an array.
     *
     * @param  string $fid - Form ID
     * @param  string $token - Token to authenticate search
     * @param  array $flags - Array of flags that customize the search further
     * @param  array $fields - For each record, the fields that should actually be returned
     * @param  array $sort - Defines what fields we are sorting by
     * @param  array $queries - The collection of queries in the search
     * @param  array $qLogic - Logic array for the search
     * @param  int $index - In final result set, what record should we start at
     * @param  int $count - Determines, starting from $index, how many records to return
     * @return array - Array representation of the form search for the API
     */
    static function formSearchBuilder($fid,$token,$flags,$fields,$sort,$queries,$qLogic,$index=null,$count=null){
        $form = array();
        $form["form"] = $fid;
        $form["token"] = $token;

        $form["data"] = in_array("data",$flags) ? in_array("data",$flags) : false;
        $form["meta"] = in_array("meta",$flags) ? in_array("meta",$flags) : false;
        $form["size"] = in_array("size",$flags) ? in_array("size",$flags) : false;

        $form["index"] = $index;
        $form["count"] = $count;

        if(is_array($fields) && empty($fields))
            $form["fields"] = "ALL";
        else
            $form["fields"] = $fields;

        if(!empty($sort))
            $form["sort"] = $sort;

        $form["query"] = $queries;
        $form["logic"] = $qLogic;

        return $form;
    }

}

class KORA_Clause{

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
    function __construct($arg1, $op, $arg2){
        $op = strtoupper($op);

        if($op == "AND" | $op == "OR") {
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
                array_push($newLogic,0);
                $size = 1;
            } else {
                //first argument already has a complex query logic, so store that and record size of queries
                array_push($newLogic,$argLogic1);
                $size = sizeof($argQue1);
            }

            //store the operation
            array_push($newLogic,$op);

            //second argument
            if(is_null($argLogic2)) {
                //second argument is a single query, so lets set it's index as the size of query 1
                array_push($newLogic,$size);
            } else {
                //second argument has complex query logic. We need to loop through and build new array where every index
                //is increased by the size of query 1
                $tmp = $this->recursizeLogicIndex($argQue2,$size);
                array_push($newLogic,$tmp);
            }

            $this->logic = $newLogic;
        }
        else {
            $tool = new kora3ApiExternalTool();
            if($arg1=="KID") {
                if(!is_array($arg2))
                    $arg2 = array($arg2);

                if($op=="="|$op=="=="|$op=="IN")
                    $not = false;
                else if($op=="NOT IN"|$op=="!="|$op=="!==")
                    $not = true;
                else
                    die("Illegal KID operator provided: ".$op);

                $query = $tool::kidQueryBuilder($arg2, $not);
                array_push($this->queries,$query);
            } else {
                if($op=="="|$op=="==") {
                    $not = false;
                    $method = "EXACT";
                } else if($op=="!="|$op=="!==") {
                    $not = true;
                    $method = "EXACT";
                } else if($op=="LIKE") {
                    $not = false;
                    $method = "OR";
                } else if($op=="NOT LIKE") {
                    $not = true;
                    $method = "OR";
                } else
                    die("Illegal keyword operator provided: ".$op);

                $query = $tool::keywordQueryBuilder($arg2, $method, $not, array($arg1));
                array_push($this->queries,$query);
            }
        }
    }

    /**
     * Recursively reindexes the logic query to match any new queries added to the array.
     *
     * @param  array $queryArray - The queries to reindex by
     * @param  int $size - Size of array at top level of recursion
     * @return array - The newly indexed logic array
     */
    private function recursizeLogicIndex($queryArray,$size){
        $returnArray = array();

        //part1
        if(is_array($queryArray[0])) {
            $tmp = $this->recursizeLogicIndex($queryArray[0],$size);
            $returnArray[0] = $tmp;
        } else {
            $returnArray[0] = $queryArray[0]+$size;
        }

        //operation
        $returnArray[1] = $queryArray[1];

        //part2
        if(is_array($queryArray[2])) {
            $tmp = $this->recursizeLogicIndex($queryArray[2],$size);
            $returnArray[2] = $tmp;
        } else {
            $returnArray[2] = $queryArray[2]+$size;
        }

        return $returnArray;
    }

    /**
     * Getter function for query variable.
     *
     * @return array - Query variable
     */
    public function getQueries(){
        return $this->queries;
    }

    /**
     * Getter function for logic variable.
     *
     * @return array - Logic varible
     */
    public function getLogic(){
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
 * @return array - The records to return from the search
 */
function KORA_Search($token,$pid,$sid,$koraClause,$fields,$order=array(),$start=null,$number=null,$userInfo = array()){
    if(!$koraClause instanceof KORA_Clause) {
        die("The query clause you provided must be an object of class KORA_Clause");
    }

    $newOrder = array();
    foreach($order as $o) {
        array_push($newOrder,$o["field"]);
        $dir = $o["direction"];
        if($dir==SORT_DESC)
            $newDir = "DESC";
        else
            $newDir = "ASC";
        array_push($newOrder,$newDir);
    }

    $output = array();
    $tool = new kora3ApiExternalTool();

    $fsArray = $tool->formSearchBuilder(
        $sid,
        $token,
        array("data","meta"),
        $fields,
        $newOrder,
        $koraClause->getQueries(),
        $koraClause->getLogic(),
        $start,
        $number
    );

    array_push($output,$fsArray);

    //We need the url out of the env file
    $env = array();
    $handle = fopen(__DIR__.'/../../.env', "r");
    if($handle) {
        while(($line = fgets($handle)) !== false) {
            if(!ctype_space($line)) {
                $parts = explode("=", $line);
                $env[trim($parts[0])] = trim($parts[1]);
            }
        }

        fclose($handle);
    } else {
        return "Error processing environment file.";
    }

    $data = array();
    $data["forms"] = json_encode($output);

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $env["BASE_URL"]."api/search");
    if(!empty($userInfo)) {
        curl_setopt($curl, CURLOPT_USERPWD, $userInfo["user"].":".$userInfo["pass"]);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    }
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

    $result = curl_exec($curl);

    return json_decode($result);
}

?>