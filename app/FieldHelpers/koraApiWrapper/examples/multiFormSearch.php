<?php

//Welcome to the kora multi-form search API helper for v3.0.0
//This tutorial is a follow up to the singleFormSearch.php tutorial so we are going to assume you read it

//Start by bringing in all the wrapper classes
include("../includes.php");

//Next in order to invoke the API, we need to define which kora API we are accessing
define('KORA_URL',"http://www.myKoraInstall.com/api/");

//Once we do that, we can instantiate the API wrapper
$api = new koraApiWrapper(KORA_URL);

//GETTING RECORDS OUT OF KORA//
//The next step is to actually build the search
//Since we are going to search over multiple forms, let's make 3 searches.
$token = "2h7f83g44g78ggb872r3";
$searchOne = new koraFormSearch(4, $token);
$searchTwo = new koraFormSearch(24, $token);
$searchThree = new koraFormSearch(20, $token);

//We are also going to skip adding parameters to this search for the sake of the tutorial (see singleFormSearch.php)

//If we wanted to simply execute all three of these searches and get 3 result sets, then we invoke the api
$forms = [$searchOne, $searchTwo, $searchThree];
$api->multiFormRecordSearch($forms);

//And like we did with single search, we can choose a format
$api->multiFormRecordSearch($forms, koraApiWrapper::JSON);

//GLOBAL SEARCH//
//But what if we want to merge the results together into one result set
//This is particularly useful if the forms we are searching through basically have the same structure and format for their respective records

//If all the fields match perfectly, then we just need to define an empty array
$merge = [];

//If certain fields are suppose to matchup, but some/all have different field names, then we can unify them under a custom field name
//NOTE: the order of the following field names matches the order of the $forms array above, and every form must be represented
$merge = [
    "Title" => ["title", "Title", "Name"]
];

//We can also take a set of fields that are the same and rename them to fit our development context
//NOTE: If we had left "Notes" alone, like in the first example, the fields would have automatically merged
//NOTE: Any fields that are not mentioned, or automatically merged, will be appended to their respective record
$merge = [
    "Title" => ["title", "Title", "Name"],
    "Description" => ["Notes", "Notes", "Notes"]
];

//Of course we must add the $merge array
$api->multiFormRecordSearch($forms, koraApiWrapper::JSON, $merge);

//The next available feature is global sorting
//And it's very similar to sorting in koraFormSearch, but allows us to also use the new field names from the $merge array
$sort = [ //Sorts the returned records by a certain field. Sorts by first rule, and then use successive ones to break ties
    ["Title" => "ASC"],
    ["Other field name" => "DESC"],
    ["created_at" => "ASC"]
];
$api->multiFormRecordSearch($forms, koraApiWrapper::JSON, $merge, $sort);

//The last feature, like sort, also shares similar usage to index and count from koraFormSearch
//NOTE: This is applied after all form searches are executed and the records are globally combined and sorted
$index = 20;
$count = 10;
$api->multiFormRecordSearch($forms, koraApiWrapper::JSON, $merge, $sort, $index, $count);

?>