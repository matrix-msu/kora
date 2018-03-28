<?php namespace App\Http\Controllers;

use App\DateField;
use App\Field;
use App\NumberField;
use App\Record;
use App\Search;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Restful_1_5_Controller extends Controller {

    /*
    |--------------------------------------------------------------------------
    | Restful Controller v1.5
    |--------------------------------------------------------------------------
    |
    | This controller handles API requests to Kora3.
    |
    | Search: Improved search that is less reliant on internal Kora systems
    |
    */

    /**
     * @var string - Standard output formats
     */
    const JSON = "JSON";
    const KORA = "KORA_OLD";

    /**
     * @var array - Valid output formats
     */
    const VALID_FORMATS = [ self::JSON, self::KORA];

    /**
     * Performs an API search on Kora3.
     *
     * @param  Request $request
     * @return mixed - The records
     */
    public function search(Request $request) {
        //get the forms
        $forms = json_decode($request->forms);
        if(is_null($forms) || !is_array($forms))
            return response()->json(["status"=>false,"error"=>"Unable to process forms array"],500);

        //get the format
        if(isset($request->format))
            $apiFormat = $request->format;
        else
            $apiFormat = self::JSON;
        $apiFormat = strtoupper($apiFormat);
        if(!self::isValidFormat($apiFormat))
            return response()->json(["status"=>false,"error"=>"Invalid format provided: $apiFormat"],500);;

        //next, we authenticate each form
        foreach($forms as $f) {
            //next, we authenticate the form
            $form = FormController::getForm($f->form);
            if(is_null($form))
                return response()->json(["status"=>false,"error"=>"Invalid Form: ".$f->form],500);

            $validated = $this->validateToken($form->pid,$f->token,"search");
            //Authentication failed
            if(!$validated)
                return response()->json(["status"=>false,"error"=>"Invalid search token provided for form: ".$form->name],500);
        }

        //now we actually do searches per form
        $resultsGlobal = [];
        $countArray = array();
        $countGlobal = 0;
        $minorErrors = array(); //Some errors we may not want to error out on

        foreach($forms as $f) {
            //initialize form
            $form = FormController::getForm($f->form);

            //things we will be returning
            $filters = array();
            $filters['data'] = isset($f->data) ? $f->data : true; //do we want data, or just info about the records theme selves
            $filters['meta'] = isset($f->meta) ? $f->meta : false; //get meta data about record
            $filters['size'] = isset($f->size) ? $f->size : false; //do we want the number of records in the search result returned instead of data
            $filters['assoc'] = isset($f->assoc) ? $f->assoc : false; //do we want information back about associated records
            $filters['fields'] = isset($f->fields) ? $f->fields : 'ALL'; //which fields do we want data for
            $filters['sort'] = isset($f->sort) ? $f->sort : null; //how should the data be sorted
            $filters['index'] = isset($f->index) ? $f->index : null; //where the array of results should start
            $filters['count'] = isset($f->count) ? $f->count : null; //how many records we should grab from that index
            //WARNING::IF FIELD NAMES SHARE A TITLE WITHIN THE SAME FIELD, THIS WOULD IN THEORY BREAK
            $filters['realnames'] = isset($f->realnames) ? $f->realnames : false; //do we want records indexed by titles rather than slugs
            //THIS SOLELY SERVES LEGACY. YOU PROBABLY WILL NEVER USE THIS. DON'T THINK ABOUT IT
            $filters['under'] = isset($f->under) ? $f->under : false; //Replace field spaces with underscores

            //parse the query
            if(!isset($f->query)) {
                //return all records
                $returnRIDS = Record::where("fid","=",$form->fid)->pluck('rid')->all();
                if(!is_null($filters['sort'])) {
                    $returnRIDS = $this->sort_rids($returnRIDS,$filters['sort']);
                    if(!$returnRIDS)
                        return response()->json(["status"=>false,"error"=>"Invalid field type, or invalid field, provided for sort in form: ". $form->name],500);
                }
                //see if we are returning the size
                if($filters['size']) {
                    $countGlobal += sizeof($returnRIDS);
                    $countArray[$form->fid] = sizeof($returnRIDS);
                }

                $resultsGlobal[] = json_decode($this->populateRecords($returnRIDS, $filters, $apiFormat));
            } else {
                $queries = $f->query;
                $resultSets = array();
                foreach($queries as $query) {
                    //determine our search type
                    switch($query->search) {
                        case 'keyword':
                            //do a keyword search
                            if(!isset($query->keys))
                                return response()->json(["status"=>false,"error"=>"No keywords supplied in a keyword search for form: ". $form->name],500);
                            $keys = $query->keys;
                            //Check for limiting fields
                            $searchFields = array();
                            if(isset($query->fields)) {
                                //takes care of converting slugs to flids
                                foreach($query->fields as $qfield) {
                                    $fieldMod = FieldController::getField($qfield);
                                    if(is_null($fieldMod)) {
                                        array_push($minorErrors, "The following field in keyword search does not exist: " . $qfield);
                                        continue;
                                    }
                                    if($fieldMod->fid != $form->fid) {
                                        array_push($minorErrors, "The following field in keyword search is not apart of the requested form: " . $fieldMod->name);
                                        continue;
                                    }
                                    array_push($searchFields,$fieldMod);
                                }
                            } else {
                                $searchFields = $form->fields()->get();
                            }
                            //Determine type of keyword search
                            $method = isset($query->method) ? $query->method : 'OR';
                            switch($method) {
                                case 'OR':
                                    $method = Search::SEARCH_OR;
                                    $keys = explode(" ",$keys);
                                    break;
                                case 'AND':
                                    $method = Search::SEARCH_AND;
                                    $keys = explode(" ",$keys);
                                    break;
                                case 'EXACT':
                                    $method = Search::SEARCH_EXACT;
                                    $keys = array($keys);
                                    break;
                                default:
                                    return response()->json(["status"=>false,"error"=>"Invalid method, ".$method.", provided for keyword search for form: ". $form->name],500);
                                    break;
                            }

                            /// HERES WHERE THE NEW SEARCH WILL HAPPEN
                            $rids = $this->apiKeywordSearch($searchFields, $keys, $method);

                            $negative = isset($query->not) ? $query->not : false;
                            if($negative)
                                $rids = $this->negative_results($form,$rids);
                            array_push($resultSets,$rids);
                            break;
                        case 'advanced':
                           //do an advanced search
                        case 'kid':
                            //do a kid search
                        case 'legacy_kid':
                            //do a kid search
                        default:
                            return response()->json(["status"=>false,"error"=>"No search query type supplied for form: ". $form->name],500);
                            break;
                    }
                }
                //perform all the and/or logic for search types
                $returnRIDS = array();
                if(!isset($f->logic)) {
                    //OR IT ALL TOGETHER
                    foreach($resultSets as $result) {
	                    $this->imitateMerge($returnRIDS,$result);
                    }
                    $returnRIDS = array_flip(array_flip($returnRIDS));
                } else {
                    //do the work!!!!
                    $logic = $f->logic;
                    $returnRIDS = $this->logic_recursive($logic,$resultSets);
                }
                //sort
                if(!is_null($filters['sort']) && !empty($returnRIDS)) {
                    $returnRIDS = $this->sort_rids($returnRIDS,$filters['sort']);
                    if(!$returnRIDS)
                        return response()->json(["status"=>false,"error"=>"Invalid field type or invalid field provided for sort in form: ". $form->name],500);
                }
                //see if we are returning the size
                if($filters['size']) {
                    $countGlobal += sizeof($returnRIDS);
                    $countArray[$form->fid] = sizeof($returnRIDS);
                }

                $resultsGlobal[] = json_decode($this->populateRecords($returnRIDS, $filters, $apiFormat));
            }
        }

        $countArray["global"] = $countGlobal;
        return [
            'counts' => $countArray,
            'records' => $resultsGlobal,
            'warnings' => $minorErrors
        ];
    }

    private function apiKeywordSearch($searchFields, $keys, $method) {
        $results = array();
        foreach($keys as $key) {
            $selectFinal = [];

            foreach($searchFields as $field) {
                //TODO::modular?
                switch($field->type) {
                    case Field::_TEXT:
                        $key = $key.'*';
                        $where = "MATCH (`text`) AGAINST (\"$key\" IN BOOLEAN MODE)";
                        $select = "SELECT DISTINCT `rid` from ".env('DB_PREFIX')."text_fields where `flid`=".$field->flid." AND $where";
                        $selectFinal[] = $select;
                        break;
                    case Field::_RICH_TEXT:
                        $key = $key.'*';
                        $where = "MATCH (`searchable_rawtext`) AGAINST (\"$key\" IN BOOLEAN MODE)";
                        $select = "SELECT DISTINCT `rid` from ".env('DB_PREFIX')."rich_text_fields where `flid`=".$field->flid." AND $where";
                        $selectFinal[] = $select;
                        break;
                    case Field::_NUMBER:
                        $bottom = $key - NumberField::EPSILON;
                        $top = $key + NumberField::EPSILON;
                        $where = "`number` BETWEEN $bottom AND $top";
                        $select = "SELECT DISTINCT `rid` from ".env('DB_PREFIX')."number_fields where `flid`=".$field->flid." AND $where";
                        $selectFinal[] = $select;
                        break;
                    case Field::_LIST:
                        $key = $key.'*';
                        $where = "MATCH (`option`) AGAINST (\"$key\" IN BOOLEAN MODE)";
                        $select = "SELECT DISTINCT `rid` from ".env('DB_PREFIX')."list_fields where `flid`=".$field->flid." AND $where";
                        $selectFinal[] = $select;
                        break;
                    case Field::_MULTI_SELECT_LIST:
                        $key = $key.'*';
                        $where = "MATCH (`options`) AGAINST (\"$key\" IN BOOLEAN MODE)";
                        $select = "SELECT DISTINCT `rid` from ".env('DB_PREFIX')."multi_select_list_fields where `flid`=".$field->flid." AND $where";
                        $selectFinal[] = $select;
                        break;
                    case Field::_GENERATED_LIST:
                        $key = $key.'*';
                        $where = "MATCH (`options`) AGAINST (\"$key\" IN BOOLEAN MODE)";
                        $select = "SELECT DISTINCT `rid` from ".env('DB_PREFIX')."generated_list_fields where `flid`=".$field->flid." AND $where";
                        $selectFinal[] = $select;
                        break;
                    case Field::_COMBO_LIST:
                        $bottom = $key - NumberField::EPSILON;
                        $top = $key + NumberField::EPSILON;
                        $key = $key.'*';
                        $where = "(MATCH (`data`) AGAINST (\"$key\" IN BOOLEAN MODE) OR `number` BETWEEN $bottom AND $top)";
                        $select = "SELECT DISTINCT `rid` from ".env('DB_PREFIX')."combo_list_fields where `flid`=".$field->flid." AND $where";
                        $selectFinal[] = $select;
                        break;
                    case Field::_DATE:
                        // Boolean to decide if we should consider circa options.
                        $circa = explode("[!Circa!]", $field->options)[1] == "Yes";
                        // Boolean to decide if we should consider era.
                        $era = explode("[!Era!]", $field->options)[1] == "On";
                        //Checks to prevent false positives with default mysql values
                        $intVal = intval($key);
                        if($intVal == 0)
                            $intVal = 999999;
                        $intMonth = intval(DateField::monthToNumber($key));
                        if($intMonth == 0)
                            $intMonth = 999999;
                        $where = "`day`=$intVal OR `year`=$intVal";
                        if(DateField::isMonth($key))
                            $where .= " OR `month`=$intMonth";
                        if($era && self::isValidEra($key))
                            $where .= " OR `era`=".strtoupper($key);
                        if($circa && self::isCirca($key))
                            $where .= " OR `circa`=1";

                        $select = "SELECT DISTINCT `rid` from ".env('DB_PREFIX')."date_fields where `flid`=".$field->flid." AND ($where)";
                        $selectFinal[] = $select;
                        break;
                    case Field::_SCHEDULE:
                        $key = $key.'*';
                        $where = "MATCH (`desc`) AGAINST (\"$key\" IN BOOLEAN MODE)";
                        $select = "SELECT DISTINCT `rid` from ".env('DB_PREFIX')."schedule_support where `flid`=".$field->flid." AND $where";
                        $selectFinal[] = $select;
                        break;
                    case Field::_DOCUMENTS:
                        $key = $key.'*';
                        $where = "MATCH (`documents`) AGAINST (\"$key\" IN BOOLEAN MODE)";
                        $select = "SELECT DISTINCT `rid` from ".env('DB_PREFIX')."documents_fields where `flid`=".$field->flid." AND $where";
                        $selectFinal[] = $select;
                        break;
                    case Field::_GALLERY:
                        $key = $key.'*';
                        $where = "MATCH (`images`) AGAINST (\"$key\" IN BOOLEAN MODE)";
                        $select = "SELECT DISTINCT `rid` from ".env('DB_PREFIX')."gallery_fields where `flid`=".$field->flid." AND $where";
                        $selectFinal[] = $select;
                        break;
                    case Field::_PLAYLIST:
                        $key = $key.'*';
                        $where = "MATCH (`audio`) AGAINST (\"$key\" IN BOOLEAN MODE)";
                        $select = "SELECT DISTINCT `rid` from ".env('DB_PREFIX')."playlist_fields where `flid`=".$field->flid." AND $where";
                        $selectFinal[] = $select;
                        break;
                    case Field::_VIDEO:
                        $key = $key.'*';
                        $where = "MATCH (`video`) AGAINST (\"$key\" IN BOOLEAN MODE)";
                        $select = "SELECT DISTINCT `rid` from ".env('DB_PREFIX')."video_fields where `flid`=".$field->flid." AND $where";
                        $selectFinal[] = $select;
                        break;
                    case Field::_3D_MODEL:
                        $key = $key.'*';
                        $where = "MATCH (`model`) AGAINST (\"$key\" IN BOOLEAN MODE)";
                        $select = "SELECT DISTINCT `rid` from ".env('DB_PREFIX')."model_fields where `flid`=".$field->flid." AND $where";
                        $selectFinal[] = $select;
                        break;
                    case Field::_GEOLOCATOR:
                        $key = $key.'*';
                        $where = "MATCH (`desc`) AGAINST (\"$key\" IN BOOLEAN MODE) OR MATCH (`address`) AGAINST ($key IN BOOLEAN MODE)";
                        $select = "SELECT DISTINCT `rid` from ".env('DB_PREFIX')."geolocator_fields where `flid`=".$field->flid." AND ($where)";
                        $selectFinal[] = $select;
                        break;
                    case Field::_ASSOCIATOR:
                        $key = explode('-',$key);
                        $rid = end($key);
                        $where = "MATCH (`record`) AGAINST (\"$rid\" IN BOOLEAN MODE)";
                        $select = "SELECT DISTINCT `rid` from ".env('DB_PREFIX')."associator_fields where `flid`=".$field->flid." AND $where";
                        $selectFinal[] = $select;
                        break;
                    default:
                        break;
                }
            }

			//Union statements together and run SQL statement
            $selectFinal = implode(' UNION ', $selectFinal);
            $ridsUnclean = DB::select($selectFinal);
            //echo sizeof($ridsUnclean),"<br>";
            $rids = [];
            
            foreach($ridsUnclean as $rid) {
	            $rids[]=$rid->rid;
            }

			//Apply method
            if(empty($results)) {
                $results = array_flip(array_flip($rids));
            } else {
                if($method==Search::SEARCH_OR) {
	                $this->imitateMerge($results,$rids);
                    $results = array_flip(array_flip($results));
                } else {
                    $results = array_flip(array_flip($this->imitateIntersect($results,$rids)));
                }
            }
        }

        return $results;
    }
    
    private function imitateMerge(&$array1, &$array2) {
	    foreach($array2 as $i) {
	        $array1[] = $i;
	    }
	}
	
	private function imitateIntersect($s1,$s2) { 
		sort($s1);
		sort($s2);
		$i=0; 
		$j=0; 
		$N = count($s1); 
		$M = count($s2); 
		$intersection = array(); 
		 
		while($i<$N && $j<$M) { 
		if($s1[$i]<$s2[$j]) $i++; 
		else if($s1[$i]>$s2[$j]) $j++; 
		else { 
		  $intersection[] = $s1[$i]; 
		  $i++; 
		  $j++; 
		} 
		}  
		
		return $intersection;  
	}

    /**
     * Recursively goes through the search logic tree and does the and/or comparisons of each query.
     *
     * @param  array $logicArray - Query logic for the search
     * @param  array $ridSets - The rids to be compared at current level
     * @return array - A unique set of RIDs that fit the search query logic
     */
    private function logic_recursive($logicArray, $ridSets) {
        $returnRIDS = array();
        $firstRIDS = array();
        $secondRIDS = array();
        //get first array of rids, or recurse till it becomes array
        if(is_array($logicArray[0]))
            $firstRIDS = $this->logic_recursive($logicArray[0],$ridSets);
        else
            $firstRIDS = $ridSets[$logicArray[0]];
        //get second array of rids, or recurse till it becomes array
        if(is_array($logicArray[2]))
            $secondRIDS = $this->logic_recursive($logicArray[2],$ridSets);
        else
            $secondRIDS = $ridSets[$logicArray[2]];
        $operator = $logicArray[1];
        if(strtoupper($operator)=="AND") {
            $returnRIDS = $this->imitateIntersect($firstRIDS,$secondRIDS);
        } else if(strtoupper($operator)=="OR") {
	        $this->imitateMerge($firstRIDS,$secondRIDS);
            $returnRIDS = $firstRIDS;
        }
        return array_flip(array_flip($returnRIDS));
    }

    /**
     * Based on set of RIDs from a search result, return all RIDs that do not fit that search.
     *
     * @param  Form $form - Form being searched
     * @param  array $rids - Record IDs we don't want
     * @return Collection - The RIDs not in the given set
     */
    private function negative_results($form, $rids) {
        $negatives = Record::where('fid','=',$form->fid)->whereNotIn('rid',$rids)->pluck('rid')->all();
        return $negatives;
    }

    /**
     * Sorts RIDs by fields.
     *
     * @param  array $rids - The RIDs to sort
     * @param  array $sortFields - The fields to sort by
     * @return array - The new array with sorted RIDs
     */
    private function sort_rids($rids, $sortFields) {
        //get field
        $fieldSlug = $sortFields[0];
        $direction = $sortFields[1];
        $newOrderArray = array();

        if($fieldSlug=='kora_meta_owner') {
            $userRecords = DB::table('records')->join('users','users.id','=','records.owner')
                ->select('records.rid','users.username')
                ->whereIn('records.rid',$rids)
                ->orderBy('users.username', $direction)
                ->get()->toArray();

            foreach($userRecords as $rec) {
                $newOrderArray[$rec->rid] = $rec->username;
            }
        } else if($fieldSlug=='kora_meta_created') {
            $createdRecords = DB::table('records')
                ->select('rid','created_at')
                ->whereIn('rid',$rids)
                ->orderBy('created_at', $direction)
                ->get()->toArray();

            foreach($createdRecords as $rec) {
                $newOrderArray[$rec->rid] = $rec->created_at;
            }
        } else if($fieldSlug=='kora_meta_updated') {
            $updatedRecords = DB::table('records')
                ->select('rid','updated_at')
                ->whereIn('rid',$rids)
                ->orderBy('updated_at', $direction)
                ->get()->toArray();

            foreach($updatedRecords as $rec) {
                $newOrderArray[$rec->rid] = $rec->updated_at;
            }
        } else if($fieldSlug=='kora_meta_kid') {
            $kidRecords = DB::table('records')
                ->select('rid')
                ->whereIn('rid',$rids)
                ->orderBy('rid', $direction)
                ->get()->toArray();

            foreach($kidRecords as $rec) {
                $newOrderArray[$rec->rid] = $rec->rid;
            }
        } else {
            $field = FieldController::getField($fieldSlug);
            if(!$field->isSortable())
                return false;

            $typedField = $field->getTypedField();
            $chunks = array_chunk($rids, 500);

            //Get the values
            foreach($chunks as $chunk) {
                $dataResults = $typedField->getRidValuesForSort($chunk, $field->flid);
                //Filter results
                foreach($dataResults as $rec) {
                    $newOrderArray[$rec->rid] = $rec->value;
                }
            }

            //Sort that stuff
            if($direction=="ASC")
                uasort($newOrderArray, 'self::compareASCII');
            else if($direction=="DESC")
                uasort($newOrderArray, 'self::rCompareASCII');
        }

        //Deal with ties
        //Is there a tiebreaker rule?
        array_shift($sortFields); //remove field slug
        array_shift($sortFields); //remove direction
        if(!empty($sortFields)) {
            //Since we have a tiebreaker, foreach set of ties, call this function recursively
            $keysOnly = array_keys($newOrderArray);
            $finalResult = array();

            //Cycle through result keys (rids)
            for($i=0;$i<sizeof($keysOnly);$i++) {
                //This handles the case where we are on the last key, so nothing to compare
                //Add to results and bounce
                if(!isset($keysOnly[$i+1])) {
                    array_push($finalResult,$keysOnly[$i]);
                    continue;
                }

                //If the next key's value is the same, do stuff
                $thisKey = $keysOnly[$i];
                $nextKey = $keysOnly[$i+1];
                if($newOrderArray[$thisKey] == $newOrderArray[$nextKey]) {
                    //First step is to get all keys that match it
                    $tieKeys = array_keys($newOrderArray,$newOrderArray[$thisKey]);
                    //Run the tie breaker
                    $tieResult = $this->sort_rids($tieKeys,$sortFields);
                    //Add results to the final
                    $this->imitateMerge($finalResult,$tieResult);

                    //We need to take the size of the tied values, and then increment $i by that size - 1
                    //The minus one makes up for the fact that the for loop will also add to the index ($i++)
                    //This will land us on the next proper index to continue
                    $inc = sizeof($tieKeys)-1;
                    $i += $inc;
                } else {
                    //No tie to settle, so add this key to the finalResult
                    array_push($finalResult,$thisKey);
                }
            }
        } else {
            //convert to plain array of rids
            $finalResult = array_keys($newOrderArray);
        }

        //Add missing records
        $missing = array_diff($rids, $finalResult);
        $this->imitateMerge($finalResult,$missing);

        return $finalResult;
    }

    /**
     * Compares strings for sort, taking into account special characters to treat them as english. Second is reverse.
     *
     * @param  mixed $a
     * @param  mixed $b
     * @return int - The comparison result
     */
    private function compareASCII($a, $b) {
        $at = iconv('UTF-8', 'ASCII//TRANSLIT', $a);
        $bt = iconv('UTF-8', 'ASCII//TRANSLIT', $b);
        return strcmp($at, $bt);
    }
    private function rCompareASCII($a, $b) {
        $at = iconv('UTF-8', 'ASCII//TRANSLIT', $a);
        $bt = iconv('UTF-8', 'ASCII//TRANSLIT', $b);
        return strcmp($at, $bt)*(-1);
    }

    /**
     * Prepares list of rids and filters array for generating the record data.
     *
     * @param  array $rids - List of Record IDs
     * @param  array $filters - Filters from the search
     * @param  string $format - The return format for the results
     * @return string - Path to the results file
     */
    private function populateRecords($rids,$filters,$format = self::JSON) {
        //Filter options that need to be passed to the export in a normal api search
        if($format == self::JSON) {
            $options = [
                'fields' => $filters['fields'],
                'meta' => $filters['meta'],
                'data' => $filters['data'],
                'assoc' => $filters['assoc'],
                'realnames' => $filters['realnames']
            ];
        } else {
            //Old Kora 2 searches only need field filters
            $options = [
                'fields' => $filters['fields'],
                'under' => $filters['under']
            ];
        }

        //Slice up array of RIDs to get the correct subset
        //There are done down here to ensure sorting has already taken place
        if(!is_null($filters['index']))
            $rids = array_slice($rids,$filters['index']);

        if(!is_null($filters['count']))
            $rids = array_slice($rids,0,$filters['count']);

        if(empty($rids))
            return "{}";

        $expControl = new ExportController();
        $output = $expControl->exportWithRids($rids,$format,true,$options);

        return $output;
    }

    /**
     * Checks if provided format is a valid format for exporting.
     *
     * @param  string $format - The format
     * @return bool - Is valid
     */
    private function isValidFormat($format) {
        return in_array(($format), self::VALID_FORMATS);
    }

    /**
     * Makes sure provided token is valid and has the needed permission.
     *
     * @param  Int $pid - Project being searched/modified
     * @param  string $token - Provided token to check
     * @param  string $permission - Type of API action being taken
     * @return bool - Is valid and has permission
     */
    private function validateToken($pid,$token,$permission) {
        //Get all the projects tokens
        $project = ProjectController::getProject($pid);
        $tokens = $project->tokens()->get();
        //compare
        foreach($tokens as $t) {
            if($t->token == $token && $t->$permission)
                return true;
        }
        return false;
    }
}
