<?php namespace App\Http\Controllers;

use App\Form;
use App\Record;
use App\Search;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RestfulController extends Controller {

    /*
    |--------------------------------------------------------------------------
    | Restful Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles API requests to kora.
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
     * @var array - Minor errors in api search. Since they happen in nested functions, it's easier to store globally.
     */
    public $minorErrors = array();

    /**
     * Gets the current version of kora.
     *
     * @return mixed - kora version
     */
    public function getKoraVersion() {
        $instInfo = DB::table("versions")->first();
        if(is_null($instInfo))
            return response()->json(["status"=>false,"error"=>"Failed to retrieve kora installation version","warnings"=>$this->minorErrors],500);
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
            return response()->json(["status"=>false,"error"=>"Invalid Project","warnings"=>$this->minorErrors],500);

        $project = ProjectController::getProject($pid);
        $formMods = $project->forms()->get();
        $forms = [];
        foreach($formMods as $form) {
            $fArray = array();
            $fArray['name'] = $form->name;
            $fArray['nickname'] = $form->internal_name;
            $fArray['description'] = $form->description;
            $forms[$form->id] = $fArray;
        }
        return $forms;
    }

    /**
     * Import form into project.
     *
     * @param  int $pid - Project ID
     * @return string - Success message
     */
    public function createForm($pid, Request $request) {
        if(!ProjectController::validProj($pid))
            return response()->json(["status"=>false,"error"=>"Invalid Project Provided","warnings"=>$this->minorErrors],500);

        $proj = ProjectController::getProject($pid);

        $validated = $this->validateToken($proj->id,$request->bearer_token,"create");
        //Authentication failed
        if(!$validated)
            return response()->json(["status"=>false,"error"=>"Invalid create token provided","warnings"=>$this->minorErrors],500);

        //Gather form data to insert
        if(!isset($request->form))
            return response()->json(["status"=>false,"error"=>"No form data supplied to insert into: ".$proj->name,"warnings"=>$this->minorErrors],500);

        $formData = json_decode($request->form);

        $ic = new ImportController();
        $ic->importFormNoFile($proj->id,$formData);

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
            return response()->json(["status"=>false,"error"=>"Invalid Project/Form Pair","warnings"=>$this->minorErrors],500);

        $form = FormController::getForm($fid);

        return $form->layout['fields'];
    }

    /**
     * Modify options on a field page.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @return mixed - Number of records
     */
    public function modifyFormFields($pid, $fid, Request $request) {
        if(!FormController::validProjForm($pid,$fid))
            return response()->json(["status"=>false,"error"=>"Invalid Project/Form Pair","warnings"=>$this->minorErrors],500);

        $validated = $this->validateToken($pid,$request->bearer_token,"edit");
        //Authentication failed
        if(!$validated)
            return response()->json(["status"=>false,"error"=>"Invalid edit token provided","warnings"=>$this->minorErrors],500);

        $form = FormController::getForm($fid);
        $layout = $form->layout;

        $toModify = json_decode($request->fields,true);
        if(!is_array($toModify))
            return response()->json(["status"=>false,"error"=>"Invalid Field Modification Array","warnings"=>$this->minorErrors],500);

        //For types that use enum
        $table = new \CreateRecordsTable();
        foreach($toModify as $fieldName => $options) {
            $flid = fieldMapper($fieldName,$pid,$fid);
            foreach($options as $opt => $value) {
                if(isset($layout['fields'][$flid]['options'][$opt]))
                    $layout['fields'][$flid]['options'][$opt] = $value;
                else
                    return response()->json(["status"=>false,"error"=>"Invalid Provided Option: ".$this->cleanseOutput($opt),"warnings"=>$this->minorErrors],500);
            }

            if(in_array($layout['fields'][$flid]['type'],Form::$enumFields)) {
                $table->updateEnum(
                    $fid,
                    $flid,
                    $layout['fields'][$flid]['options']['Options']
                );
            }
        }

        $form->layout = $layout;
        $form->save();

        return "Field Options Updated!";
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
            return response()->json(["status"=>false,"error"=>"Invalid Project/Form Pair","warnings"=>$this->minorErrors],500);

        $recTable = new Record(array(),$fid);
        return $recTable->newQuery()->count();
    }

    /**
     * Performs an API search on kora.
     *
     * @param  Request $request
     * @return mixed - The records
     */
    public function search(Request $request) {
        //get the forms
        $forms = json_decode($request->forms);
        if(is_null($forms) || !is_array($forms))
            return response()->json(["status"=>false,"error"=>"Unable to process forms array. Check the JSON structure of your request.","warnings"=>$this->minorErrors],500);

        //get the format
        if(isset($request->format))
            $apiFormat = $request->format;
        else
            $apiFormat = self::JSON;
        if(!self::isValidFormat($apiFormat))
            return response()->json(["status"=>false,"error"=>"Invalid format provided","warnings"=>$this->minorErrors],500);

        //check for global
        $globalRecords = array();
        $globalForms = array();
        //Merge will combine the results and let you maps field names together.
        if(isset($request->merge)) {
            $globalMergeArray = json_decode($request->merge);
            $globalMerge = true;
        } else {
            $globalMergeArray = null;
            $globalMerge = false;
        }
        if(isset($request->sort)) {
            $globalSortArray = json_decode($request->sort);
            $globalSort = true;
        } else {
            $globalSort = false;
        }

        //next, we authenticate each form
        foreach($forms as $f) {
            //next, we authenticate the form
            $form = FormController::getForm($f->form);
            if(is_null($form))
                return response()->json(["status"=>false,"error"=>"Invalid Form: ".$this->cleanseOutput($f->form),"warnings"=>$this->minorErrors],500);

            //Authentication failed
            if(!isset($f->bearer_token))
                return response()->json(["status"=>false,"error"=>"No search token provided for form: ".$this->cleanseOutput($f->form),"warnings"=>$this->minorErrors],500);
            if(!$this->validateToken($form->project_id,$f->bearer_token,"search"))
                return response()->json(["status"=>false,"error"=>"Invalid search token provided for form: ".$this->cleanseOutput($f->form),"warnings"=>$this->minorErrors],500);
        }

        //now we actually do searches per form
        $resultsGlobal = [];
        $filtersGlobal = [];
        $fidsGlobal = [];
        $countArray = array();
        $countGlobal = 0;

        foreach($forms as $f) {
            //initialize form
            $form = FormController::getForm($f->form);
            $recMod = new Record(array(),$form->id);
            if($globalSort)
                array_push($fidsGlobal, $form->id);

            //Configurations for what we will be returning
            $filters = array();
            $filters['data'] = isset($f->data) && is_bool($f->data) ? $f->data : true; //do we want data, or just info about the records theme selves
            $filters['meta'] = isset($f->meta) && is_bool($f->meta) ? $f->meta : true; //get meta data about record
            $filters['size'] = isset($f->size) && is_bool($f->size) ? $f->size : false; //do we want the number of records in the search result returned instead of data
            $filters['fields'] = isset($f->return_fields) && is_array($f->return_fields) ? $f->return_fields : 'ALL'; //which fields do we want data for
            $filters['altNames'] = isset($f->alt_names) && is_bool($f->alt_names) ? $f->alt_names : false; //Use alternative field names in returned results
            $filters['assoc'] = isset($f->assoc) && is_bool($f->assoc) ? $f->assoc : false; //do we want information back about associated records
            $filters['assocFlids'] = isset($f->assoc_fields) && is_array($f->assoc_fields) ? $f->assoc_fields : 'ALL'; //What fields should associated records return? Should be array
            $filters['revAssoc'] = isset($f->reverse_assoc) && is_bool($f->reverse_assoc) ? $f->reverse_assoc : true; //do we want information back about reverse associations for XML OUTPUT

            $filters['sort'] = isset($f->sort) && is_array($f->sort) ? $f->sort : null; //how should the data be sorted
            $filters['index'] = isset($f->index) && is_numeric($f->index) ? $f->index : null; //where the array of results should start [MUST USE 'count' FOR THIS TO WORK]
            $filters['count'] = isset($f->count) && is_numeric($f->count) ? $f->count : null; //how many records we should grab from that index

            //Note: Filters only captures values from certain fields, see Form::$validFilterFields to see which ones use it
            $filters['filters'] = isset($f->filters) && is_bool($f->filters) ? $f->filters : false; //do we want information back about result filters [i.e. Field 'First Name', has value 'Tom', '12' times]
            $filters['filterCount'] = isset($f->filter_count) && is_numeric($f->filter_count) ? $f->filter_count : 1; //What is the minimum threshold for a filter to return?
            $filters['filterFlids'] = isset($f->filter_fields) && is_array($f->filter_fields) ? $f->filter_fields : 'ALL'; //What fields should filters return for? Should be array

            //THIS SOLELY SERVES LEGACY. YOU PROBABLY WILL NEVER USE THIS. DON'T THINK ABOUT IT
            $filters['under'] = isset($f->under) && is_bool($f->under) ? $f->under : false; //Replace field spaces with underscores
            //If merge was provided, pass it along in the filters
            $filters['merge'] = $globalMerge ? $globalMergeArray : null;

            //Index and count become irrelevant to a single form in global sort, because we want to return count after all forms are sorted.
            if($globalSort) {
                if(!is_null($filters['index']))
                    return response()->json(["status"=>false,"error"=>"'index' is not allowed in a form search query when using the global sort variable. Use the global 'index'","warnings"=>$this->minorErrors],500);
                if(!is_null($filters['count']))
                    return response()->json(["status"=>false,"error"=>"'count' is not allowed in a form search query when using the global sort variable. Use the global 'count'","warnings"=>$this->minorErrors],500);
                if(!is_null($filters['sort']))
                    return response()->json(["status"=>false,"error"=>"'sort' is not allowed in a form search query when using the global sort variable.","warnings"=>$this->minorErrors],500);
            }

            //Check returned fields for illegal fields
            if(is_array($filters['fields'])) {
                foreach($filters['fields'] as $field) {
                    $flid = fieldMapper($field, $form->project_id, $form->id);
                    if(!isset($form->layout['fields'][$flid]))
                        return response()->json(["status"=>false,"error"=>"The following return field is not apart of the requested form: " . $this->cleanseOutput($flid),"warnings"=>$this->minorErrors],500);
                }
            }

            //parse the query
            if(!isset($f->queries)) {
                //return all records
                if($apiFormat==self::XML)
                    $records = $form->getRecordsForExportXML($filters);
                else if($apiFormat==self::KORA)
                    $records = $form->getRecordsForExportLegacy($filters);
                else
                    $records = $form->getRecordsForExport($filters);

                if($filters['size']) {
                    if($apiFormat==self::XML) //Since the return XML is a string. We'll just get the record count manually.
                        $cnt = $form->getRecordCount();
                    else
                        $cnt = sizeof($records);
                    $countGlobal += $cnt;
                    $countArray[$form->id] = $cnt;
                }

                $resultsGlobal[] = $records;

                if($filters['filters'])
                    $filtersGlobal[$form->id] = $form->getDataFilters($filters['filterCount'], $filters['filterFlids']);

                if($globalSort) {
                    $globalForms[] = $form;
                    $kids = array_keys($records);
                    $this->imitateMerge($globalRecords, $kids);
                }
            } else {
                $queries = $f->queries;
                if(!is_array($queries))
                    return response()->json(["status"=>false,"error"=>"Invalid queries array for form: ". $form->name,"warnings"=>$this->minorErrors],500);

                //perform all the and/or logic for search types
                if(!isset($f->logic)) {
                    $qCnt = sizeof($queries);
                    $logic = (object)['or' => range(0, $qCnt - 1)];
                } else {
                    $logic = $f->logic;
                }

                //go through the logic array
                $returnRIDS = $this->logicRecursive($logic,$queries,$form,$recMod);
                if($returnRIDS instanceof JsonResponse)
                    return $returnRIDS;

                if($apiFormat==self::XML)
                    $records = $form->getRecordsForExportXML($filters,$returnRIDS);
                else if($apiFormat==self::KORA)
                    $records = $form->getRecordsForExportLegacy($filters,$returnRIDS);
                else
                    $records = $form->getRecordsForExport($filters,$returnRIDS);

                if($filters['size']) {
                    $cnt = sizeof($returnRIDS);
                    $countGlobal += $cnt;
                    $countArray[$form->id] = $cnt;
                }

                $resultsGlobal[] = $records;

                if($filters['filters'])
                    $filtersGlobal[$form->id] = $form->getDataFilters($filters['filterCount'], $filters['filterFlids'], $returnRIDS);

                if($globalSort) {
                    $globalForms[] = $form;
                    $kids = array_keys($records);
                    $this->imitateMerge($globalRecords, $kids);
                }
            }
        }

        if($globalMerge) {
            $final = [];
            foreach($resultsGlobal as $result) {
                $final = array_merge($final,$result);
            }

            //Add to final result array
            $resultsGlobal = $final;
        }

        //Handle any global sorting
        if($globalSort) {
            $globalSortedResults = array();

            //Build and run the query to get the KIDs in proper order
            $globalSorted = Form::sortGlobalKids($globalForms, $globalRecords, $globalSortArray, $globalMergeArray);

            //Apply global sort flags if necessary
            if(isset($request->index) && !is_null($request->index) && is_numeric($request->index))
                $globalSorted = array_slice($globalSorted,$request->index);

            if(isset($request->count) && !is_null($request->count) && is_numeric($request->count))
                $globalSorted = array_slice($globalSorted,0,$request->count);

            //for each record in that new KID array
            foreach($globalSorted as $kid) {
                //If we merged results already, we can peak into the top level instead of looking at each form record set
                if($globalMerge) {
                    //Move said record to the new Results array
                    if(isset($resultsGlobal[$kid]))
                        $globalSortedResults[$kid] = $resultsGlobal[$kid];
                } else {
                    //Peak into the form results to find the record
                    foreach ($resultsGlobal as $formRecordSet) {
                        //Move said record to the new Results array
                        if(isset($formRecordSet[$kid]))
                            $globalSortedResults[$kid] = $formRecordSet[$kid];
                    }
                }
            }

            //Add to final result array
            $resultsGlobal = $globalSortedResults;
        }

        $countArray["global"] = $countGlobal;
        return [
            'counts' => $countArray,
            'filters' => $filtersGlobal,
            'records' => $resultsGlobal,
            'warnings' => $this->minorErrors
        ];
    }

    /**
     * Recursively goes through the search logic tree and does the and/or comparisons of each query.
     *
     * @param  array $logic - Query logic for the search
     * @param  array $queries - Array of search queries
     * @param  Form $form - Form model to search in
     * @param  Record $recMod - Record model for KID searches
     * @return array - A unique set of RIDs that fit the search query logic
     */
    private function logicRecursive($logic, $queries, $form, $recMod) {
        //check first to see that first index is an operator, and no other array elements exist
        if((isset($logic->or) || isset($logic->and))) {
            $operand = isset($logic->or) ? 'or': 'and';
            $ridSets = [];
            foreach($logic->{$operand} as $val) {
                if(is_numeric($val) and isset($queries[$val])) {
                    //run query and store
                    $queryRes = $this->processQuery($queries[$val], $form, $recMod);
                    //Check for error
                    if($queryRes instanceof JsonResponse)
                        return $queryRes;
                    $ridSets[] = $queryRes;
                } else if(is_object($val)) {
                    //New sub-operand, run recursive
                    $logicRes = $this->logicRecursive($val, $queries, $form, $recMod);
                    //Check for errorw
                    if($logicRes instanceof JsonResponse)
                        return $logicRes;
                    $ridSets[] = $logicRes;
                } else {
                    return response()->json(["status"=>false,"error"=>"Invalid logic array for form: ". $form->name,"warnings"=>$this->minorErrors],500);
                }
            }

            //Apply the operand
            $finalSet = array();
            switch($operand) {
                case 'or':
                    foreach($ridSets as $set) {
                        $this->imitateMerge($finalSet,$set);
                    }
                    break;
                case 'and':
                    $firstIntersect = true;
                    foreach($ridSets as $set) {
                        //First thing needs to be manually assigned
                        if($firstIntersect) {
                            $finalSet = $set;
                            $firstIntersect = false;
                        } else {
                            $finalSet = $this->imitateIntersect($finalSet, $set);
                        }
                    }
                    break;
                default:
                    return response()->json(["status"=>false,"error"=>"Invalid logic operand for form: ". $form->name,"warnings"=>$this->minorErrors],500);
                    break;
            }

            if($finalSet instanceof JsonResponse)
                return $finalSet;
            return array_flip(array_flip($finalSet));
        } else {
            return response()->json(["status"=>false,"error"=>"Invalid logic array for form: ". $form->name,"warnings"=>$this->minorErrors],500);
        }
    }

    /**
     * Decipher and execute the search query.
     *
     * @param  array $query - The search query
     * @param  Form $form - Form model to search in
     * @param  Record $recMod - Record model for KID searches
     * @return array - A unique set of RIDs that fit the search query
     */
    private function processQuery($query, $form, $recMod) {
        //determine our search type
        if(!isset($query->search) || !is_string($query->search))
            return response()->json(["status"=>false,"error"=>"No search query type supplied for form: ". $form->name,"warnings"=>$this->minorErrors],500);
        switch($query->search) {
            case 'advanced':
                //do an advanced search
                if(!isset($query->adv_fields) || !is_object($query->adv_fields))
                    return response()->json(["status"=>false,"error"=>"No fields supplied in an advanced search for form: ". $form->name,"warnings"=>$this->minorErrors],500);
                $fields = $query->adv_fields;
                $processed = [];
                foreach($fields as $advfield => $data) {
                    $flid = fieldMapper($advfield, $form->project_id, $form->id);
                    if(!isset($form->layout['fields'][$flid])) {
                        array_push($this->minorErrors, "The following field in keyword search is not apart of the requested form: " . $this->cleanseOutput($flid));
                        continue;
                    }
                    $fieldModel = $form->layout['fields'][$flid];

                    //Check permission to search externally
                    if(!$fieldModel['external_search']) {
                        array_push($this->minorErrors, "The following field in advanced search is not externally searchable: " . $fieldModel['name']);
                        continue;
                    }

                    $processed[$flid] = $form->getFieldModel($fieldModel['type'])->setRestfulAdvSearch($data);
                    if(isset($data->negative) && is_bool($data->negative))
                        $processed[$flid]['negative'] = true;
                    if(isset($data->empty) && is_bool($data->empty))
                        $processed[$flid]['empty'] = true;
                }
                $negative = isset($query->not) && is_bool($query->not) ? $query->not : false;
                $advSearch = new AdvancedSearchController();
                $rids = $advSearch->apisearch($form->project_id, $form->id, $processed, $negative);
                return $rids;
                break;
            case 'keyword':
                //do a keyword search
                if(!isset($query->key_words) || !is_array($query->key_words))
                    return response()->json(["status"=>false,"error"=>"No keywords supplied in a keyword search for form: ". $form->name,"warnings"=>$this->minorErrors],500);
                $keys = $query->key_words;

                //Check for limiting fields
                $searchFields = array();
                if(isset($query->key_fields)) {
                    if(!is_array($query->key_fields))
                        return response()->json(["status"=>false,"error"=>"Invalid fields array in keyword search for form: ". $form->name,"warnings"=>$this->minorErrors],500);

                    //takes care of converting slugs to flids
                    foreach($query->key_fields as $qfieldName) {
                        $qfield = fieldMapper($qfieldName,$form->project_id,$form->id);

                        if(!isset($form->layout['fields'][$qfield])) {
                            array_push($this->minorErrors, "The following field in keyword search is not apart of the requested form: " . $this->cleanseOutput($qfield));
                            continue;
                        }
                        $fieldMod = $form->layout['fields'][$qfield];

                        if(!$fieldMod['external_search']) {
                            array_push($this->minorErrors, "The following field in keyword search is not externally searchable: " . $fieldMod['name']);
                            continue;
                        }
                        $searchFields[$qfield] = $fieldMod;
                    }
                } else {
                    $searchFields = $form->layout['fields'];
                }
                if(empty($searchFields))
                    return response()->json(["status"=>false,"error"=>"Invalid fields provided for keyword search for form: ". $form->name,"warnings"=>$this->minorErrors],500);

                //Determine type of keyword search
                $method = isset($query->key_method) && is_string($query->key_method) ? $query->key_method : 'OR';
                switch($method) {
                    case 'OR':
                        $method = Search::SEARCH_OR;
                        break;
                    case 'AND':
                        $method = Search::SEARCH_AND;
                        break;
                    default:
                        return response()->json(["status"=>false,"error"=>"Invalid method, ".$this->cleanseOutput($method).", provided for keyword search for form: ". $form->name,"warnings"=>$this->minorErrors],500);
                        break;
                }

                //Determine if we need to add wildcards to search keywords, or if user will supply the wildcards
                $customWildcards = isset($query->custom_wildcards) && is_bool($query->custom_wildcards) ? $query->custom_wildcards : false;

                /// HERES WHERE THE NEW SEARCH WILL HAPPEN
                $negative = isset($query->not) && is_bool($query->not) ? $query->not : false;
                $search = new Search($form->project_id,$form->id,$keys,$method);
                $rids = $search->formKeywordSearch($searchFields, true, $negative, $customWildcards);

                return $rids;
                break;
            case 'kid':
                //do a kid search
                if(!isset($query->kids) || !is_array($query->kids))
                    return response()->json(["status"=>false,"error"=>"No KIDs supplied in a KID search for form: ". $form->name,"warnings"=>$this->minorErrors],500);
                $kids = $query->kids;
                for($i=0; $i < sizeof($kids); $i++) {
                    if(!Record::isKIDPattern($kids[$i])) {
                        array_push($this->minorErrors,"Illegal KID (".$this->cleanseOutput($kids[$i]).") in a KID search for form: ". $form->name);
                        continue;
                    }
                }
                $negative = isset($query->not) && is_bool($query->not) ? $query->not : false;
                if($negative)
                    $rids = $recMod->newQuery()->whereNotIn('kid',$kids)->pluck('id')->toArray();
                else
                    $rids = $recMod->newQuery()->whereIn('kid',$kids)->pluck('id')->toArray();
                return $rids;
                break;
            case 'legacy_kid':
                //do a kid search
                if(!isset($query->legacy_kids) || !is_array($query->legacy_kids))
                    return response()->json(["status"=>false,"error"=>"No KIDs supplied in a KID search for form: ". $form->name,"warnings"=>$this->minorErrors],500);
                $kids = $query->legacy_kids;

                $negative = isset($query->not) && is_bool($query->not) ? $query->not : false;
                if($negative)
                    $rids = $recMod->newQuery()->whereNotIn('legacy_kid',$kids)->pluck('id')->toArray();
                else
                    $rids = $recMod->newQuery()->whereIn('legacy_kid',$kids)->pluck('id')->toArray();
                return $rids;
                break;
            default:
                return response()->json(["status"=>false,"error"=>"Invalid search query type supplied for form: ". $form->name,"warnings"=>$this->minorErrors],500);
                break;
        }
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
            return response()->json(["status"=>false,"error"=>"Invalid Form","warnings"=>$this->minorErrors],500);

        $validated = $this->validateToken($form->project_id,$request->bearer_token,"create");
        //Authentication failed
        if(!$validated)
            return response()->json(["status"=>false,"error"=>"Invalid create token provided","warnings"=>$this->minorErrors],500);

        //Gather field data to insert
        if(!isset($request->fields))
            return response()->json(["status"=>false,"error"=>"No data supplied to insert into: ".$form->name,"warnings"=>$this->minorErrors],500);

        $fields = json_decode($request->fields,true);
        $recRequest = new Request();

        $uToken = uniqid(); //need a temp user id to interact, specifically for files
        $recRequest['userId'] = $uToken; //the new record will ultimately be owned by the root/sytem
        if(!is_null($request->file("zip_file")) ) {
            $file = $request->file("zip_file");
            $zipPath = $file->move(storage_path('app/tmpFiles/impU' . $uToken));
            $zip = new \ZipArchive();
            $res = $zip->open($zipPath);
            if($res === TRUE) {
                $zip->extractTo(storage_path('app/tmpFiles/impU' . $uToken));
                $zip->close();
            } else {
                return response()->json(["status"=>false,"error"=>"There was an error extracting the provided zip","warnings"=>$this->minorErrors],500);
            }
        }

        foreach($fields as $fieldName => $jsonField) {
            $flid = fieldMapper($fieldName, $form->project_id, $form->id);

            if(!isset($form->layout['fields'][$flid]))
                return response()->json(["status"=>false,"error"=>"The field, ".$this->cleanseOutput($fieldName).", does not exist","warnings"=>$this->minorErrors],500);

            $field = $form->layout['fields'][$flid];
            $typedField = $form->getFieldModel($field['type']);

            $recRequest = $typedField->processImportData($flid, $field, $jsonField, $recRequest);
        }

        $recRequest['api'] = true;
        $recRequest['assignRoot'] = true;
        $recCon = new RecordController();

        $response = $recCon->store($form->project_id,$form->id,$recRequest);
        return $response;
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
            return response()->json(["status"=>false,"error"=>"Invalid Form","warnings"=>$this->minorErrors],500);

        $validated = $this->validateToken($form->project_id,$request->bearer_token,"edit");
        //Authentication failed
        if(!$validated)
            return response()->json(["status"=>false,"error"=>"Invalid edit token provided","warnings"=>$this->minorErrors],500);

        //Gather field data to insert
        if(!isset($request->kid))
            return response()->json(["status"=>false,"error"=>"No record KID supplied to edit in: ".$form->name,"warnings"=>$this->minorErrors],500);

        //Gather field data to insert
        if(!isset($request->fields))
            return response()->json(["status"=>false,"error"=>"No data supplied to insert into: ".$form->name,"warnings"=>$this->minorErrors],500);

        $fields = json_decode($request->fields,true);
        $record = RecordController::getRecord($request->kid);
        if(is_null($record))
            return response()->json(["status"=>false,"error"=>"Invalid KID provided","warnings"=>$this->minorErrors],500);

        $recRequest = new Request();
        $uToken = uniqid(); //need a temp user id to interact, specifically for files

        $recRequest['userId'] = $uToken; //the new record will ultimately be owned by the root/sytem
        if( !is_null($request->file("zip_file")) ) {
            $file = $request->file("zip_file");
            $zipPath = $file->move(storage_path('app/tmpFiles/impU' . $uToken));
            $zip = new \ZipArchive();
            $res = $zip->open($zipPath);
            if($res === TRUE) {
                $zip->extractTo(storage_path('app/tmpFiles/impU' . $uToken));
                $zip->close();
            } else {
                return response()->json(["status"=>false,"error"=>"There was an issue extracting the provided file zip","warnings"=>$this->minorErrors],500);
            }
        }

        foreach($fields as $fieldName => $jsonField) {
            $flid = fieldMapper($fieldName, $form->project_id, $form->id);

            if(!isset($form->layout['fields'][$flid]))
                return response()->json(["status"=>false,"error"=>"The field, ".$this->cleanseOutput($fieldName).", does not exist","warnings"=>$this->minorErrors],500);

            $field = $form->layout['fields'][$flid];
            $typedField = $form->getFieldModel($field['type']);

            $recRequest = $typedField->processImportData($flid, $field, $jsonField, $recRequest);
        }

        $recRequest['api'] = true;
        $recCon = new RecordController();

        $response = $recCon->update($form->project_id,$form->id,$record->id,$recRequest);
        return $response;
    }

    /**
     * Delete a set of records from kora
     *
     * @param  Request $request
     * @return mixed - Status of record deletion
     */
    public function delete(Request $request) {
        //get the form
        $f = $request->form;
        //next, we authenticate the form
        $form = FormController::getForm($f);
        if(is_null($form))
            return response()->json(["status"=>false,"error"=>"Invalid Form","warnings"=>$this->minorErrors],500);

        $validated = $this->validateToken($form->project_id,$request->bearer_token,"delete");
        //Authentication failed
        if(!$validated)
            return response()->json(["status"=>false,"error"=>"Invalid delete token provided","warnings"=>$this->minorErrors],500);

        //Gather records to delete
        if(!isset($request->kids))
            return response()->json(["status"=>false,"error"=>"No KIDs supplied to delete in: ".$form->name,"warnings"=>$this->minorErrors],500);

        $kids = json_decode($request->kids);
        $recsToDelete = array();
        foreach($kids as $kid) {
            if(!Record::isKIDPattern($kid))
                return response()->json(["status"=>false,"error"=>"Illegal KID format for: ".$this->cleanseOutput($kid),"warnings"=>$this->minorErrors],500);

            $record = RecordController::getRecord($kid);

            if(is_null($record))
                return response()->json(["status"=>false,"error"=>"Supplied record does not exist: ".$kid,"warnings"=>$this->minorErrors],500);
            else
                array_push($recsToDelete,$record);
        }
        foreach($recsToDelete as $record) {
            $record->delete();
        }
        return response()->json(["status"=>true,"message"=>"record_deleted"],200);
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

    /**
     * Cleanse the output so we have better error reporting, but done safely.
     *
     * @param  string $input - String to be altered
     * @return string - Filtered string
     */
    private function cleanseOutput($input) {
        return preg_replace("/[^A-Za-z0-9_]/", '', $input);
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
}