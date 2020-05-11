<?php

//Welcome to the kora search API helper for v3.0.0
//While the API wrapper assists in generating any API call, we are going to focus this tutorial on record search
//A lot of information is provided, but the goal is to teach simple use cases for record search, and then expand into the vast customization available

//First thing we need to do is bring in all the wrapper classes
include("../includes.php");

//Next in order to invoke the API, we need to define which kora API we are accessing
define('KORA_URL',"http://www.myKoraInstall.com/api/");

//Once we do that, we can instantiate the API wrapper
$api = new koraApiWrapper(KORA_URL);

//GETTING RECORDS OUT OF KORA//
//The next step is to actually build the search
//When instantiating the search, we need to define a form and a token
$form_id = 1; //This is the ID of the form that we want to search
$token = "2h7f83g44g78ggb872r3"; //This is the access token that gives us permission to search the form
$search = new koraFormSearch($form_id, $token);

//There are also some optional parameters to refine the results that are returned
$return_fields = ["Field 1", "Field 2"]; //For each record in the result, we can limit what field data is returned
$search = new koraFormSearch($form_id, $token, $return_fields);

$sort = [ //Sorts the returned records by a certain field. Sorts by first rule, and then use successive ones to break ties
     ["Field name" => "ASC"],
     ["Other field name" => "DESC"],
     ["created_at" => "ASC"]
]; //NOTE: Sorting is available for certain metadata fields: created_at, updated_at, and kid
$search = new koraFormSearch($form_id, $token, $return_fields, $sort);

//These variables help us do pagination on the search by defining...
$index = 0; //...the record index we start at
$count = 10; //...the number of records returned (starting at the index)
$search = new koraFormSearch($form_id, $token, $return_fields, $sort, 0, 10);

//To execute the Search, we are going to simply pass $search into our API wrapper
$final_result = $api->formRecordSearch($search);

//While they default records are in JSON format (which you receive as a php array), we have a couple of other return formats
$final_result = $api->formRecordSearch($search, koraApiWrapper::XML); //Get the records back as an XML
$final_result = $api->formRecordSearch($search, koraApiWrapper::KORA_OLD); //Get the records back in legacy kora 2 array format

//QUERIES//
//The search we have defined so far hasn't actually searched anything, in fact it will just give you all the records in the form
//If you want to drill down to a set of records that meet a search criteria, then we want to add some queries

//The first type of search is a keyword search
$keywords = ["key1", "key 2"]; //This array is keywords you want to search for within the form
$keyID = $search->addKeywordSearch($keywords);

//Like the wrapper itself, there are several optional parameters for keyword searches
$keywords = ["key1%", "key 2"]; //example of wildcard only at end of keyword, and a keyword with no wildcard (see $customWildCards below)
$method = "AND"; //By default, method is set to OR and returns records with at least one keyword, but setting method to AND will only return records that have all keywords
$keyFields = ["Field 2"]; //This array limits the fields that evaluated by the keyword search
$customWildCards = true; //By default, wildcards (%) are added to beginning and end of each keyword, but like the $keywords above, setting this to true will allow custom wildcards to be defined
$not = true; //Returns all records that do meet the keyword search parameters
$search->addKeywordSearch($keywords, $method, $keyFields, $customWildCards, $not);

//The next type of search is KID search, which can be used to get back a specific set of records
$kids = ["1-1-0","1-1-4"]; //The array of record kora IDs to return
$kidID = $search->addKIDSearch($kids);

//Here are the optional parameters for KID searches
$legacy = true; //Retrieves records by their kora v2 legacy IDs, instead of their kora v3 IDs
$not = true; //Returns all records that do meet the keyword search parameters
$search->addKIDSearch($kids, $legacy, $not);

//The last type of search is an advanced search, which allows us to search uniquely within record field data based on the type of field
//NOTE: The koraFormSearch.php file has examples of all field types, but this introduction will demonstrate a simple Text and Date field
$advFields = [ //Array of N number of fields, returning records that meet the search requirements for all provided fields
    "Some Text field" => "value_to_search",
    "Some Date field" => [
        "begin_month" => 1, "begin_day" => 13, "begin_year" => 1337,
        "end_month" => 8, "end_day" => 19, "end_year" => 1990
    ]
];
$advID = $search->addAdvancedSearch($advFields);

//Here are the optional parameters for advanced searches
$not = true; //Returns all records that do meet the keyword search parameters
$search->addAdvancedSearch($advFields, $not);

//Lastly, we want to be able to define the order the queries are applied, as well as which query results should be either merged, or intersected together
//To accomplish this, we need to build a logic array using the index of each added query
//NOTE: Notice that some of the queries above were saved into an ID variable to make this step easier
//NOTE: If you want to simply MERGE ALL query results, you can skip this step
$logic = ["or"=> ["and"=> [$keyID, $kidID], $advID ] ]; //i.e. intersect the results of the keyword and kid search, and then merge that with the results of the advanced search
$logic = ["and"=> [$keyID, $kidID, $advID] ]; //i.e. intersect the results of every query
$logic = ["and"=> ["or"=> [$keyID, $kidID], $advID ] ]; //i.e. merge the results of the keyword and kid search, and then intersect that with the results of the advanced search
$search->addQueryLogic($logic);

//ADDITIONAL FUNCTIONALITY//
//There is still much more configuration that can be added to a search

//First, there are a set of public boolean flags that can be changed to customize the results even further
//NOTE: Default values shown below
$search->meta = true; //Determines if record metadata is returned with the records
$search->size = false; //Determines if the number of returned records is stored for this search result set
                       //NOTE: This is recorded before the the count and index parameters take affect
$search->alt_names = false; //Determines if field data within a record is indexed by its field name or alternative field name
$search->reverse_assoc = true; //Determines if an array of kora IDs that associate to a returned record are returned with that record

//Some record data contains associations to other records
//This function will allow us to return the associated records' data, instead of just its kora ID
$search->enableAssociationData();

//If you only want certain field data returned for those associated records, you can define the field set
$assocFields = ["aField 1", "aField 2"];
$search->enableAssociationData($assocFields);

//Lastly, we can enable record filters on our search
//Looking at the entire record set returned from a search, we can see for each field, how many times a particular value appears in that field
//NOTE: This is recorded before the the count and index parameters take affect
$search->enableFilters();

//There are a view optional parameters for this as well
$filter_count = 5; //Number of times a value has to appear in order to be added to the record filters array
$filter_fields = ["Field 3", "Field 5"]; //Array of specific fields we want record filter data for
$search->enableFilters($filter_count, $filter_fields);

?>