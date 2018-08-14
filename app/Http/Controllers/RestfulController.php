<?php namespace App\Http\Controllers;

use App\Field;
use App\Form;
use App\Record;
use App\Search;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RestfulController extends Controller {

    /*
    |--------------------------------------------------------------------------
    | Restful Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles API requests to Kora3.
    |
    */

    /**
     * @var string - Standard output formats
     */
    const JSON = "JSON";
    const KORA = "KORA_OLD";
    const XML = "XML";

    /**
     * @var array - Valid output formats
     */
    const VALID_FORMATS = [ self::JSON, self::KORA, self::XML];

    /**
     * Gets the current version of Kora3.
     *
     * @return mixed - Kora version
     */
    public function getKoraVersion() {
        $instInfo = DB::table("versions")->first();
        if(is_null($instInfo))
            return response()->json(["status"=>false,"error"=>"Failed to retrieve Kora installation version"],500);
        else
            return $instInfo->version;
    }

    /**
     * Get a basic list of the forms in a project.
     *
     * @param  int $pid - Project ID
     * @return mixed - The forms
     */
    public function getProjectForms($pid) {
        if(!ProjectController::validProj($pid))
            return response()->json(["status"=>false,"error"=>"Invalid Project: ".$pid],500);

        $project = ProjectController::getProject($pid);
        $formMods = $project->forms()->get();
        $forms = array();
        foreach($formMods as $form) {
            $fArray = array();
            $fArray['name'] = $form->name;
            $fArray['nickname'] = $form->slug;
            $fArray['description'] = $form->description;
            $forms[$form->fid] = $fArray;
        }
        return $forms;
    }

    /**
     * Get a basic list of the forms in a project.
     *
     * @param  int $pid - Project ID
     * @return mixed - The forms
     */
    public function createForm($pid, Request $request) {
        if(!ProjectController::validProj($pid))
            return response()->json(["status"=>false,"error"=>"Invalid Project: ".$pid],500);

        $proj = ProjectController::getProject($pid);

        $validated = $this->validateToken($proj->pid,$request->token,"create");
        //Authentication failed
        if(!$validated)
            return response()->json(["status"=>false,"error"=>"Invalid create token provided"],500);

        //Gather form data to insert
        if(!isset($request->k3Form))
            return response()->json(["status"=>false,"error"=>"No form data supplied to insert into: ".$proj->name],500);

        $formData = json_decode($request->k3Form);

        $ic = new ImportController();
        $ic->importFormNoFile($proj->pid,$formData);

        return "Form Created!";
    }

    /**
     * Get a basic list of the fields in a form.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @return mixed - The fields
     */
    public function getFormFields($pid, $fid) {
        if(!FormController::validProjForm($pid,$fid))
            return response()->json(["status"=>false,"error"=>"Invalid Project/Form Pair: ".$pid." ~ ".$fid],500);

        $form = FormController::getForm($fid);
        $fieldMods = $form->fields()->get();
        $fields = array();
        foreach($fieldMods as $field) {
            $fArray = array();
            $fArray['name'] = $field->name;
            $fArray['type'] = $field->type;
            $fArray['nickname'] = $field->slug;
            $fArray['description'] = $field->desc;
            $fArray['options'] = Field::getTypedFieldStatic($field->type)->getOptionsArray($field);
            $fArray['required'] = $field->required;
            $fArray['searchable'] = $field->searchable;
            $fArray['extsearch'] = $field->extsearch;
            $fArray['viewable'] = $field->viewable;
            $fArray['viewresults'] = $field->viewresults;
            $fArray['extview'] = $field->extview;

            $fields[$field->flid] = $fArray;
        }
        return $fields;
    }

    /**
     * Get the number of records in a form.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @return mixed - Number of records
     */
    public function getFormRecordCount($pid, $fid) {
        if(!FormController::validProjForm($pid,$fid))
            return response()->json(["status"=>false,"error"=>"Invalid Project/Form Pair: ".$pid." ~ ".$fid],500);

        $form = FormController::getForm($fid);
        $count = $form->records()->count();
        return $count;
    }

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
            return response()->json(["status"=>false,"error"=>"Invalid format provided: $apiFormat"],500);

        //check for global
        $globalRecords = array();
        if(isset($request->globalSort)) {
            $globalSortArray = json_decode($request->globalSort);
            $globalSort = true;
        } else {
            $globalSort = false;
        }

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
        $filtersGlobal = [];
        $countArray = array();
        $countGlobal = 0;
        $minorErrors = array(); //Some errors we may not want to error out on

        foreach($forms as $f) {
            //initialize form
            $form = FormController::getForm($f->form);

            //things we will be returning
            //NOTE: Items marked ***, will be overwritten when using globalSort
            $filters = array();
            $filters['data'] = isset($f->data) ? $f->data : true; //do we want data, or just info about the records theme selves***
            $filters['meta'] = isset($f->meta) ? $f->meta : false; //get meta data about record***
            $filters['size'] = isset($f->size) ? $f->size : false; //do we want the number of records in the search result returned instead of data
            $filters['assoc'] = isset($f->assoc) ? $f->assoc : false; //do we want information back about associated records***
            $filters['revAssoc'] = isset($f->revAssoc) ? $f->revAssoc : true; //do we want information back about reverse associations for XML OUPTUT
            $filters['filters'] = isset($f->filters) ? $f->filters : false; //do we want information back about result filters [i.e. Field 'First Name', has value 'Tom', '12' times]
            $filters['filterCount'] = isset($f->filterCount) ? $f->filterCount : 5; //What is the minimum threshold for a filter to return?
            $filters['filterFlids'] = isset($f->filterFlids) ? $f->filterFlids : 'ALL'; //What fields should filters return for? Should be array
                //Note: Filters only captures values from certain fields (mainly single value ones), see ExportController->exportWithRids() to see which ones use it
            $filters['fields'] = isset($f->fields) ? $f->fields : 'ALL'; //which fields do we want data for***
            $filters['sort'] = isset($f->sort) ? $f->sort : null; //how should the data be sorted
            $filters['index'] = isset($f->index) ? $f->index : null; //where the array of results should start***
            $filters['count'] = isset($f->count) ? $f->count : null; //how many records we should grab from that index***
            //WARNING::IF FIELD NAMES SHARE A TITLE WITHIN THE SAME FIELD, THIS WOULD IN THEORY BREAK
            $filters['realnames'] = isset($f->realnames) ? $f->realnames : false; //do we want records indexed by titles rather than slugs***
            //THIS SOLELY SERVES LEGACY. YOU PROBABLY WILL NEVER USE THIS. DON'T THINK ABOUT IT
            $filters['under'] = isset($f->under) ? $f->under : false; //Replace field spaces with underscores***

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

                if($filters['filters'])
                    $filtersGlobal[$form->slug] = $this->getDataFilters($form->fid, $returnRIDS, $filters['filterCount'], $filters['filterFlids']);

                if($globalSort)
                    $this->imitateMerge($globalRecords,$returnRIDS);
                else {
                    if($apiFormat==self::XML)
                        $resultsGlobal[] = $this->populateRecords($returnRIDS, $filters, $apiFormat);
                    else
                        $resultsGlobal[] = json_decode($this->populateRecords($returnRIDS, $filters, $apiFormat));
                }
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
                                    if(!$fieldMod->isExternalSearchable()) {
                                        array_push($minorErrors, "The following field in keyword search is not externally searchable: " . $fieldMod->name);
                                        continue;
                                    }
                                    array_push($searchFields,$fieldMod);
                                }
                            } else {
                                $searchFields = $form->fields()->get();
                            }
							if(empty($searchFields))
								return response()->json(["status"=>false,"error"=>"Invalid fields provided for keyword search for form: ". $form->name],500);
                            //Determine type of keyword search
                            $method = isset($query->method) ? $query->method : 'OR';
                            switch($method) {
                                case 'OR':
                                    $method = Search::SEARCH_OR;
                                    break;
                                case 'AND':
                                    $method = Search::SEARCH_AND;
                                    break;
                                case 'EXACT':
                                    $method = Search::SEARCH_EXACT;
                                    break;
                                default:
                                    return response()->json(["status"=>false,"error"=>"Invalid method, ".$method.", provided for keyword search for form: ". $form->name],500);
                                    break;
                            }
                            /// HERES WHERE THE NEW SEARCH WILL HAPPEN
                            $search = new Search($form->pid,$form->fid,$keys,$method);
                            $rids = $search->formKeywordSearch($searchFields, true);

                            $negative = isset($query->not) ? $query->not : false;
                            if($negative)
                                $rids = $this->negative_results($form,$rids);
                            array_push($resultSets,$rids);
                            break;
                        case 'advanced':
                            //do an advanced search
                            if(!isset($query->fields))
                                return response()->json(["status"=>false,"error"=>"No fields supplied in an advanced search for form: ". $form->name],500);
                            $fields = $query->fields;
                            foreach($fields as $flid => $data) {
                                $fieldModel = FieldController::getField($flid);
                                //Check if it's in this form
                                if($fieldModel->fid != $form->fid) {
                                    array_push($minorErrors, "The following field in advanced search is not apart of the requested form: " . $fieldModel->name);
                                    continue;
                                }
                                //Check permission to search externally
                                if(!$fieldModel->isExternalSearchable()) {
                                    array_push($minorErrors, "The following field in advanced search is not externally searchable: " . $fieldModel->name);
                                    continue;
                                }
                                $request->request->add([$fieldModel->flid.'_dropdown' => 'on']);
                                $request->request->add([$fieldModel->flid.'_valid' => 1]);
                                $request = $fieldModel->getTypedField()->setRestfulAdvSearch($data,$fieldModel->flid,$request);
                            }
                            $advSearch = new AdvancedSearchController();
                            $rids = $advSearch->apisearch($form->pid, $form->fid, $request);

                            $negative = isset($query->not) ? $query->not : false;
                            if($negative)
                                $rids = $this->negative_results($form,$rids);
                            array_push($resultSets,$rids);
                            break;
                        case 'kid':
                            //do a kid search
                            if(!isset($query->kids))
                                return response()->json(["status"=>false,"error"=>"No KIDs supplied in a KID search for form: ". $form->name],500);
                            $kids = $query->kids;
                            $rids = array();
                            for($i = 0; $i < sizeof($kids); $i++) {
                                $rid = explode("-", $kids[$i])[2];
                                $record = Record::where('rid',$rid)->get()->first();
                                if($record->fid != $form->fid)
                                    array_push($minorErrors,"The following KID is not apart of the requested form: " . $kids[$i]);
                                else
                                    $rids[$i] = $record->rid;
                            }
                            $negative = isset($query->not) ? $query->not : false;
                            if($negative)
                                $rids = $this->negative_results($form,$rids);
                            array_push($resultSets,$rids);
                            break;
                        case 'legacy_kid':
                            //do a kid search
                            if (!isset($query->kids))
                                return response()->json(["status"=>false,"error"=>"You must provide KIDs in a Legacy KID search for form: " . $form->name],500);
                            $kids = $query->kids;
                            $rids = array();
                            for($i = 0; $i < sizeof($kids); $i++) {
                                $legacy_kid = $kids[$i];
                                $record = Record::where('legacy_kid','=',$legacy_kid)->get()->first();
                                if($record->fid != $form->fid)
                                    array_push($minorErrors,"The following legacy KID is not apart of the requested form: " . $kids[$i]);
                                else
                                    array_push($rids,$record->rid);
                            }
                            $negative = isset($query->not) ? $query->not : false;
                            if($negative)
                                $rids = $this->negative_results($form,$rids);
                            array_push($resultSets,$rids);
                            break;
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

                if($filters['filters'])
                    $filtersGlobal[$form->slug] = $this->getDataFilters($form->fid, $returnRIDS, $filters['filterCount'], $filters['filterFlids']);

                if($globalSort)
                    $this->imitateMerge($globalRecords,$returnRIDS);
                else {
                    if($apiFormat==self::XML)
                        $resultsGlobal[] = $this->populateRecords($returnRIDS, $filters, $apiFormat);
                    else
                        $resultsGlobal[] = json_decode($this->populateRecords($returnRIDS, $filters, $apiFormat));
                }
            }
        }

        if($globalSort) {
            $filters = array();

            if(isset($request->globalFilters))
                $f = json_decode($request->globalFilters);
            else
                $f = array();

            $filters['data'] = isset($f->data) ? $f->data : true; //do we want data, or just info about the records theme selves***
            $filters['meta'] = isset($f->meta) ? $f->meta : false; //get meta data about record***
            $filters['assoc'] = isset($f->assoc) ? $f->assoc : false; //do we want information back about associated records***
            $filters['fields'] = isset($f->fields) ? $f->fields : 'ALL'; //which fields do we want data for***
            $filters['index'] = isset($f->index) ? $f->index : null; //where the array of results should start***
            $filters['count'] = isset($f->count) ? $f->count : null; //how many records we should grab from that index***
            //WARNING::IF FIELD NAMES SHARE A TITLE WITHIN THE SAME FIELD, THIS WOULD IN THEORY BREAK
            $filters['realnames'] = isset($f->realnames) ? $f->realnames : false; //do we want records indexed by titles rather than slugs***
            //THIS SOLELY SERVES LEGACY. YOU PROBABLY WILL NEVER USE THIS. DON'T THINK ABOUT IT
            $filters['under'] = isset($f->under) ? $f->under : false; //Replace field spaces with underscores***

            $globalSorted = $this->sortGlobalRids($globalRecords, $globalSortArray);
            $resultsGlobal = json_decode($this->populateRecords($globalSorted, $filters, $apiFormat));
        }

        $countArray["global"] = $countGlobal;
        return [
            'counts' => $countArray,
            'filters' => $filtersGlobal,
            'records' => $resultsGlobal,
            'warnings' => $minorErrors
        ];
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
     * Based on set of RIDs from a search result, return all RIDs that do not fit that search.
     *
     * @param  Form $form - Form being searched
     * @param  array $rids - Record IDs we don't want
     * @return array - The RIDs not in the given set
     */
    private function negative_results($form, $rids) {
	    $returnRIDS = array();
	    $ridString = implode(',',$rids);
	    
	    //Doing this for pretty much the same reason as keyword search above
	    $con = mysqli_connect(env('DB_HOST'), env('DB_USERNAME'), env('DB_PASSWORD'), env('DB_DATABASE'));
	    
	    //We want to make sure we are doing things in utf8 for special characters
		if(!mysqli_set_charset($con, "utf8")) {
		    printf("Error loading character set utf8: %s\n", mysqli_error($con));
		    exit();
		}
		
		if($ridString!="")
			$select = "SELECT `rid` from ".env('DB_PREFIX')."records WHERE `fid`=".$form->fid." AND `rid` NOT IN ($ridString)";
		else
			$select = "SELECT `rid` from ".env('DB_PREFIX')."records WHERE `fid`=".$form->fid;
			
		$negUnclean = $con->query($select);
		
		while($row = $negUnclean->fetch_assoc()) {
			array_push($returnRIDS, $row['rid']);
		}

        mysqli_close($con);
		
        return $returnRIDS;
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
            if(is_null($field) || !$field->isSortable())
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
     * Sorts RIDs by fields.
     *
     * @param  array $rids - The RIDs to sort
     * @param  array $sortFields - The field arrays to sort by
     * @return array - The new array with sorted RIDs
     */
    private function sortGlobalRids($rids, $sortFields) {
        //get field
        $fieldSlug = $sortFields[0];
        $direction = $sortFields[1];
        $newOrderArray = array();

        if(!is_array($fieldSlug) && $fieldSlug=='kora_meta_owner') {
            $userRecords = DB::table('records')->join('users','users.id','=','records.owner')
                ->select('records.rid','users.username')
                ->whereIn('records.rid',$rids)
                ->orderBy('users.username', $direction)
                ->get()->toArray();

            foreach($userRecords as $rec) {
                $newOrderArray[$rec->rid] = $rec->username;
            }
        } else if(!is_array($fieldSlug) && $fieldSlug=='kora_meta_created') {
            $createdRecords = DB::table('records')
                ->select('rid','created_at')
                ->whereIn('rid',$rids)
                ->orderBy('created_at', $direction)
                ->get()->toArray();

            foreach($createdRecords as $rec) {
                $newOrderArray[$rec->rid] = $rec->created_at;
            }
        } else if(!is_array($fieldSlug) && $fieldSlug=='kora_meta_updated') {
            $updatedRecords = DB::table('records')
                ->select('rid','updated_at')
                ->whereIn('rid',$rids)
                ->orderBy('updated_at', $direction)
                ->get()->toArray();

            foreach($updatedRecords as $rec) {
                $newOrderArray[$rec->rid] = $rec->updated_at;
            }
        } else if(!is_array($fieldSlug) && $fieldSlug=='kora_meta_kid') {
            $kidRecords = DB::table('records')
                ->select('rid')
                ->whereIn('rid',$rids)
                ->orderBy('rid', $direction)
                ->get()->toArray();

            foreach($kidRecords as $rec) {
                $newOrderArray[$rec->rid] = $rec->rid;
            }
        } else {
            $flids = array();
            $type = '';
            if(!is_array($fieldSlug))
                return false;

            foreach($fieldSlug as $slug) {
                $field = FieldController::getField($slug);
                if(is_null($field) || !$field->isSortable())
                    return false;
                array_push($flids,$field->flid);
                if($type=='')
                    $type = $field->type;
                else if($type != $field->type)
                    return false;
            }

            $typedField = Field::getTypedFieldStatic($type);
            $chunks = array_chunk($rids, 500);

            //Get the values
            foreach($chunks as $chunk) {
                $dataResults = $typedField->getRidValuesForGlobalSort($chunk, $flids);
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
        $a = Search::convertCloseChars($a);
        $b = Search::convertCloseChars($b);
        if(is_numeric($a) && is_numeric($b))
            return $a>$b;
        else
            return strcasecmp($a, $b);
    }
    private function rCompareASCII($a, $b) {
        $a = Search::convertCloseChars($a);
        $b = Search::convertCloseChars($b);
        if(is_numeric($a) && is_numeric($b))
            return $a<$b;
        else
            return strcasecmp($a, $b)*(-1);
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
     * Scan tables to build out filters list
     *
     * @param  int $fid - Form ID
     * @param  array $rids - Record IDs to search for
     * @param  int $count - Minimum occurances required for a filter to return
     * @param  array $flids - Specifies the fields we need filters from
     * @return array - The array of filters
     */
    private function getDataFilters($fid, $rids, $count, $flids) {
        if(empty($rids))
            return ['total' => 0];

        $filters = [];
        $ridString = implode(',',$rids);
        $flidSQL = '';

        if($flids != 'ALL') {
            //In case slugs are provided, we need flids
            $convertedFlids = array();
            foreach($flids as $fl) {
                array_push($convertedFlids, FieldController::getField($fl)->flid); //TODO::error bad fields, not 100% sure how we'll get it up a level
            }

            $flidString = implode(',',$convertedFlids);
            $flidSQL = " and `flid` in ($flidString)";
        } else {
            $flids = Form::find($fid)->fields()->pluck('flid')->toArray();
            $flidString = implode(',',$flids);
            $flidSQL = " and `flid` in ($flidString)";
        }

        //Doing this for pretty much the same reason as keyword search above
        $con = mysqli_connect(env('DB_HOST'), env('DB_USERNAME'), env('DB_PASSWORD'), env('DB_DATABASE'));

        //We want to make sure we are doing things in utf8 for special characters
        if(!mysqli_set_charset($con, "utf8")) {
            printf("Error loading character set utf8: %s\n", mysqli_error($con));
            exit();
        }

        $textOccurrences = DB::raw("select `text`, `flid` from ".env('DB_PREFIX')."text_fields where `fid`=$fid and `rid` in ($ridString)$flidSQL");
        $listOccurrences = DB::raw("select `option`, `flid` from ".env('DB_PREFIX')."list_fields where `fid`=$fid and `rid` in ($ridString)$flidSQL");
        $msListOccurrences = DB::raw("select `options`, `flid` from ".env('DB_PREFIX')."multi_select_list_fields where `fid`=$fid and `rid` in ($ridString)$flidSQL");
        $genListOccurrences = DB::raw("select `options`, `flid` from ".env('DB_PREFIX')."generated_list_fields where `fid`=$fid and `rid` in ($ridString)$flidSQL");
        $numberOccurrences = DB::raw("select `number`, `flid` from ".env('DB_PREFIX')."number_fields where `fid`=$fid and `rid` in ($ridString)$flidSQL");
        $dateOccurrences = DB::raw("select `month`, `day`, `year`, `flid` from ".env('DB_PREFIX')."date_fields where `fid`=$fid and `rid` in ($ridString)$flidSQL");
        $assocOccurrences = DB::raw("select s.`flid`, r.`kid` from ".env('DB_PREFIX')."associator_support as s left join kora3_records as r on s.`record`=r.`rid` where s.`fid`=$fid and s.`rid` in ($ridString) and s.`flid` in ($flidString)");
        $rAssocOccurrences = DB::raw("select s.`flid`, r.`kid` from ".env('DB_PREFIX')."associator_support as s left join kora3_records as r on s.`rid`=r.`rid` where s.`fid`=$fid and s.`rid` in ($ridString) and s.`flid` in ($flidString)");

        //Because of the complex data in MS List, we break stuff up and then format
        $msListUnclean = $con->query($msListOccurrences);
        while($occur = $msListUnclean->fetch_assoc()) {
            $msFlid = $occur['flid'];
            $msOpt = $occur['options'];

            $opts = explode('[!]', $msOpt);

            foreach($opts as $opt) {
                if(!isset($filters[$msFlid][$opt]))
                    $filters[$msFlid][$opt] = 1;
                else
                    $filters[$msFlid][$opt] += 1;
            }
        }

        //repeat for gen list
        $genListUnclean = $con->query($genListOccurrences);
        while($occur = $genListUnclean->fetch_assoc()) {
            $gsFlid = $occur['flid'];
            $gsOpt = $occur['options'];

            $opts = explode('[!]', $gsOpt);

            foreach($opts as $opt) {
                if(!isset($filters[$gsFlid][$opt]))
                    $filters[$gsFlid][$opt] = 1;
                else
                    $filters[$gsFlid][$opt] += 1;
            }
        }

        $dateUnclean = $con->query($dateOccurrences);
        while($occur = $dateUnclean->fetch_assoc()) {
            $flid = $occur['flid'];

            if($occur['month']==0 && $occur['day']==0)
                $value = $occur['year'];
            else if($occur['day']==0 && $occur['year']==0)
                $value = \DateTime::createFromFormat('m', $occur['month'])->format('F');
            else if($occur['day']==0)
                $value = \DateTime::createFromFormat('m', $occur['month'])->format('F').', '.$occur['year'];
            else if($occur['year']==0)
                $value = \DateTime::createFromFormat('m', $occur['month'])->format('F').' '.$occur['day'];
            else
                $value = $occur['month'].'-'.$occur['day'].'-'.$occur['year'];

            if(!isset($filters[$flid][$value]))
                $filters[$flid][$value] = 1;
            else
                $filters[$flid][$value] += 1;
        }

        $textUnclean = $con->query($textOccurrences);
        while($occur = $textUnclean->fetch_assoc()) {
            $flid = $occur['flid'];
            $value = $occur['text'];

            if(!isset($filters[$flid][$value]))
                $filters[$flid][$value] = 1;
            else
                $filters[$flid][$value] += 1;
        }

        $listUnclean = $con->query($listOccurrences);
        while($occur = $listUnclean->fetch_assoc()) {
            $flid = $occur['flid'];
            $value = $occur['option'];

            if(!isset($filters[$flid][$value]))
                $filters[$flid][$value] = 1;
            else
                $filters[$flid][$value] += 1;
        }

        $numberUnclean = $con->query($numberOccurrences);
        while($occur = $numberUnclean->fetch_assoc()) {
            $flid = $occur['flid'];
            $value = (float)$occur['number'];

            if(!isset($filters[$flid][$value]))
                $filters[$flid][$value] = 1;
            else
                $filters[$flid][$value] += 1;
        }

        $assocUnclean = $con->query($assocOccurrences);
        while($occur = $assocUnclean->fetch_assoc()) {
            $flid = $occur['flid'];
            $value = $occur['kid'];

            if(!isset($filters[$flid][$value]))
                $filters[$flid][$value] = 1;
            else
                $filters[$flid][$value] += 1;
        }

        $rAssocUnclean = $con->query($rAssocOccurrences);
        while($occur = $rAssocUnclean->fetch_assoc()) {
            $flid = $occur['flid'];
            $value = $occur['kid'];

            if(!isset($filters[$flid][$value]))
                $filters[$flid][$value] = 1;
            else
                $filters[$flid][$value] += 1;
        }

        //We want to swap out the flids to slugs so it's more predictable/ readable
        foreach($filters as $flid => $results) {
            $slug = FieldController::getField($flid)->slug;
            $filters[$slug] = $results;
            unset($filters[$flid]);
        }

        mysqli_close($con);

        return $filters;
    }

    /**
     * Creates a new record.
     *
     * @param  Request $request
     * @return mixed - The new RID, if successful
     */
    public function create(Request $request) {
        //get the form
        $f = $request->form;
        //next, we authenticate the form
        $form = FormController::getForm($f);
        if(is_null($form))
            return response()->json(["status"=>false,"error"=>"Invalid Form: ".$form->fid],500);

        $validated = $this->validateToken($form->pid,$request->token,"create");
        //Authentication failed
        if(!$validated)
            return response()->json(["status"=>false,"error"=>"Invalid create token provided"],500);

        //Gather field data to insert
        if(!isset($request->fields))
            return response()->json(["status"=>false,"error"=>"No data supplied to insert into: ".$form->name],500);

        $fields = json_decode($request->fields);
        $recRequest = new Request();
        $uToken = $this->fileToken(); //need a temp user id to interact, specifically for files
        $recRequest['userId'] = $uToken; //the new record will ultimately be owned by the root/sytem
        if( !is_null($request->file("zipFile")) ) {
            $file = $request->file("zipFile");
            $zipPath = $file->move(config('app.base_path') . 'storage/app/tmpFiles/impU' . $uToken);
            $zip = new \ZipArchive();
            $res = $zip->open($zipPath);
            if($res === TRUE) {
                $zip->extractTo(config('app.base_path') . 'storage/app/tmpFiles/impU' . $uToken);
                $zip->close();
            } else {
                return response()->json(["status"=>false,"error"=>"There was an error extracting the provided zip"],500);
            }
        }
        foreach($fields as $fieldName => $jsonField) {
            $fieldSlug = $fieldName;
            $field = Field::where('slug', '=', $fieldSlug)->get()->first();
            if(is_null($field))
                return response()->json(["status"=>false,"error"=>"The field, $fieldSlug, does not exist"],500);

            $recRequest = $field->getTypedField()->setRestfulRecordData($jsonField, $field->flid, $recRequest, $uToken);
        }
        $recRequest['api'] = true;
        $recRequest['assignRoot'] = true;
        $recCon = new RecordController();

        $response = $recCon->store($form->pid,$form->fid,$recRequest);
        return $response;
    }

    /**
     * Creates a fake user id to exist within the temp file structure of Kora3.
     *
     * @return string - The id
     */
    private function fileToken() {
        $valid = 'abcdefghijklmnopqrstuvwxyz';
        $valid .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $valid .= '0123456789';
        $token = '';
        for($i = 0; $i < 12; $i++) {
            $token .= $valid[( rand() % 62 )];
        }
        return $token;
    }

    /**
     * Edit an existing record.
     *
     * @param  Request $request
     * @return mixed - Status of record modification
     */
    public function edit(Request $request) {
        //get the form
        $f = $request->form;
        //next, we authenticate the form
        $form = FormController::getForm($f);
        if(is_null($form))
            return response()->json(["status"=>false,"error"=>"Invalid Form: ".$form->fid],500);

        $validated = $this->validateToken($form->pid,$request->token,"edit");
        //Authentication failed
        if(!$validated)
            return response()->json(["status"=>false,"error"=>"Invalid create token provided"],500);

        //Gather field data to insert
        if(!isset($request->kid))
            return response()->json(["status"=>false,"error"=>"No record KID supplied to edit in: ".$form->name],500);

        //Gather field data to insert
        if(!isset($request->fields))
            return response()->json(["status"=>false,"error"=>"No data supplied to insert into: ".$form->name],500);

        $fields = json_decode($request->fields);
        $record = RecordController::getRecordByKID($request->kid);
        if(is_null($record))
            return response()->json(["status"=>false,"error"=>"Invalid Record: ".$request->kid],500);

        $recRequest = new Request();
        $uToken = $this->fileToken(); //need a temp user id to interact, specifically for files
        $recRequest['userId'] = $uToken; //the new record will ultimately be owned by the root/sytem
        //Basically this determines if we keep data for fields we don't mention in the request
        //if true, we keep the data
        //by default, we delete data from unmentioned fields
        $keepFields = isset($request->keepFields) ? $request->keepFields : "false";
        $fieldsToEditArray = array(); //These are the fields that are allowed to be editted if we are doing keepfields
        if( !is_null($request->file("zipFile")) ) {
            $file = $request->file("zipFile");
            $zipPath = $file->move(config('app.base_path') . 'storage/app/tmpFiles/impU' . $uToken);
            $zip = new \ZipArchive();
            $res = $zip->open($zipPath);
            if($res === TRUE) {
                $zip->extractTo(config('app.base_path') . 'storage/app/tmpFiles/impU' . $uToken);
                $zip->close();
            } else {
                return response()->json(["status"=>false,"error"=>"There was an issue extracting the provided file zip"],500);
            }
        }
        foreach($fields as $fieldName => $jsonField) {
            $fieldSlug = $fieldName;
            $field = Field::where('slug', '=', $fieldSlug)->get()->first();
            if(is_null($field))
                return response()->json(["status"=>false,"error"=>"The field, $fieldSlug, does not exist"],500);
            //if keepfields scenario, keep track of this field that will be edited
            if($keepFields=="true")
                array_push($fieldsToEditArray,$field->flid);

            $recRequest = $field->getTypedField()->setRestfulRecordData($jsonField, $field->flid, $recRequest, $uToken);
        }
        $recRequest['api'] = true;
        $recRequest['keepFields'] = $keepFields; //whether we keep unmentioned fields
        $recRequest['fieldsToEdit'] = $fieldsToEditArray; //what fields can be modified if keepfields
        $recCon = new RecordController();
        $recCon->update($form->pid,$form->fid,$record->rid,$recRequest);

        return "Modified record: ".$request->kid;
    }

    /**
     * Delete a set of records from Kora3
     *
     * @param  Request $request
     * @return mixed - Status of record deletion
     */
    public function delete(Request $request){
        //get the form
        $f = $request->form;
        //next, we authenticate the form
        $form = FormController::getForm($f);
        if(is_null($form))
            return response()->json(["status"=>false,"error"=>"Invalid Form: ".$form->fid],500);

        $validated = $this->validateToken($form->pid,$request->token,"delete");
        //Authentication failed
        if(!$validated)
            return response()->json(["status"=>false,"error"=>"Invalid create token provided"],500);

        //Gather records to delete
        if(!isset($request->kids))
            return response()->json(["status"=>false,"error"=>"No record KIDs supplied to delete in: ".$form->name],500);

        $kids = explode(",",$request->kids);
        $recsToDelete = array();
        for($i=0;$i<sizeof($kids);$i++) {
            $rid = explode("-",$kids[$i])[2];
            $record = RecordController::getRecord($rid);
            if(is_null($record))
                return response()->json(["status"=>false,"error"=>"Supplied record does not exist: ".$kids[$i]],500);
            else
                array_push($recsToDelete,$record);
        }
        foreach($recsToDelete as $record) {
            $record->delete();
        }
        return "Deleted records";
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
        } else if($format == self::KORA) {
            //Old Kora 2 searches only need field filters
            $options = [
                'fields' => $filters['fields'],
                'under' => $filters['under']
            ];
        } else if($format == self::XML) {
            $options = [
                "revAssoc" => $filters['revAssoc']
            ];
        } else {
            return "{}";
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