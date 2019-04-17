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
            return response()->json(["status"=>false,"error"=>"Invalid Project"],500);

        $project = ProjectController::getProject($pid);
        $formMods = $project->forms()->get();
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
            return response()->json(["status"=>false,"error"=>"Invalid Project Provided"],500);

        $proj = ProjectController::getProject($pid);

        $validated = $this->validateToken($proj->id,$request->token,"create");
        //Authentication failed
        if(!$validated)
            return response()->json(["status"=>false,"error"=>"Invalid create token provided"],500);

        //Gather form data to insert
        if(!isset($request->k3Form))
            return response()->json(["status"=>false,"error"=>"No form data supplied to insert into: ".$proj->name],500);

        $formData = json_decode($request->k3Form);

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
            return response()->json(["status"=>false,"error"=>"Invalid Project/Form Pair"],500);

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
            return response()->json(["status"=>false,"error"=>"Invalid Project/Form Pair"],500);

        $validated = $this->validateToken($pid,$request->token,"create");
        //Authentication failed
        if(!$validated)
            return response()->json(["status"=>false,"error"=>"Invalid create token provided"],500);

        $form = FormController::getForm($fid);
        $layout = $form->layout;

        $toModify = json_decode($request->fields,true);
        if(!is_array($toModify))
            return response()->json(["status"=>false,"error"=>"Invalid Field Modification Array"],500);

        //For types that use enum
        $table = new \CreateRecordsTable();
        foreach($toModify as $flid => $options) {
            foreach($options as $opt => $value) {
                if(isset($layout['fields'][$flid]['options'][$opt]))
                    $layout['fields'][$flid]['options'][$opt] = $value;
                else
                    return response()->json(["status"=>false,"error"=>"Invalid Provided Option: ".$this->cleanseOutput($opt)],500);
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
            return response()->json(["status"=>false,"error"=>"Invalid Project/Form Pair"],500);

        $recTable = new Record(array(),$fid);
        return $recTable->newQuery()->count();
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
            return response()->json(["status"=>false,"error"=>"Unable to process forms array. Check the JSON structure of your request."],500);

        //get the format
        if(isset($request->format))
            $apiFormat = $request->format;
        else
            $apiFormat = self::JSON;
        if(!self::isValidFormat($apiFormat))
            return response()->json(["status"=>false,"error"=>"Invalid format provided"],500);

        //check for global
        $globalRecords = array();
        $globalForms = array();
        if(isset($request->global_sort)) {
            $globalSortArray = json_decode($request->global_sort);
            $globalSort = true;
        } else {
            $globalSort = false;
        }

        //next, we authenticate each form
        foreach($forms as $f) {
            //next, we authenticate the form
            $form = FormController::getForm($f->form);
            if(is_null($form))
                return response()->json(["status"=>false,"error"=>"Invalid Form: ".$this->cleanseOutput($f->form)],500);

            //Authentication failed
            if(!$this->validateToken($form->project_id,$f->bearer_token,"search"))
                return response()->json(["status"=>false,"error"=>"Invalid search token provided for form: ".$this->cleanseOutput($f->form)],500);
        }

        //now we actually do searches per form
        $resultsGlobal = [];
        $filtersGlobal = [];
        $fidsGlobal = [];
        $countArray = array();
        $countGlobal = 0;
        $minorErrors = array(); //Some errors we may not want to error out on //TODO::CASTLE especially with new private functions

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

            //Note: Filters only captures values from certain fields, see Form::$validFilterFields to see which ones use it
            $filters['filters'] = isset($f->filters) && is_bool($f->filters) ? $f->filters : false; //do we want information back about result filters [i.e. Field 'First Name', has value 'Tom', '12' times]
            $filters['filterCount'] = isset($f->filter_count) && is_numeric($f->filter_count) ? $f->filter_count : 1; //What is the minimum threshold for a filter to return?
            $filters['filterFlids'] = isset($f->filter_fields) && is_array($f->filter_fields) ? $f->filter_fields : 'ALL'; //What fields should filters return for? Should be array

            $filters['assoc'] = isset($f->assoc) && is_bool($f->assoc) ? $f->assoc : false; //do we want information back about associated records
            $filters['assocFlids'] = isset($f->assoc_fields) && is_array($f->assoc_fields) ? $f->assoc_fields : 'ALL'; //What fields should associated records return? Should be array
            $filters['revAssoc'] = isset($f->reverse_assoc) && is_bool($f->reverse_assoc) ? $f->reverse_assoc : true; //do we want information back about reverse associations for XML OUTPUT

            //WARNING::IF FIELD NAMES SHARE A TITLE WITHIN THE SAME FIELD, THIS WOULD IN THEORY BREAK
            $filters['realnames'] = isset($f->real_names) && is_bool($f->real_names) ? $f->real_names : false; //do we want records indexed by titles rather than slugs
            //THIS SOLELY SERVES LEGACY. YOU PROBABLY WILL NEVER USE THIS. DON'T THINK ABOUT IT
            $filters['under'] = isset($f->under) && is_bool($f->under) ? $f->under : false; //Replace field spaces with underscores

            $filters['fields'] = isset($f->return_fields) && is_array($f->return_fields) ? $f->return_fields : 'ALL'; //which fields do we want data for
            $filters['sort'] = isset($f->sort) && is_array($f->sort) ? $f->sort : null; //how should the data be sorted

            $filters['index'] = isset($f->index) && is_numeric($f->index) ? $f->index : null; //where the array of results should start [MUST USE 'count' FOR THIS TO WORK]
            $filters['count'] = isset($f->count) && is_numeric($f->count) ? $f->count : null; //how many records we should grab from that index

            //Index and count become irrelevant to a single form in global sort, because we want to return count after all forms are sorted.
            if($globalSort) {
                $filters['index'] = null;
                $filters['count'] = null;
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
                    $filtersGlobal[$form->internal_name] = $form->getDataFilters($filters['filterCount'], $filters['filterFlids']);

                if($globalSort) {
                    $globalForms[] = $form->id;
                    $kids = array_keys($records);
                    $this->imitateMerge($globalRecords, $kids);
                }
            } else {
                $queries = $f->queries;
                if(!is_array($queries))
                    return response()->json(["status"=>false,"error"=>"Invalid queries array for form: ". $form->name],500);

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
                    $filtersGlobal[$form->internal_name] = $form->getDataFilters($filters['filterCount'], $filters['filterFlids'], $returnRIDS);

                if($globalSort) {
                    $globalForms[] = $form->id;
                    $kids = array_keys($records);
                    $this->imitateMerge($globalRecords, $kids);
                }
            }
        }

        //Handle any global sorting
        if($globalSort) {
            $globalSortedResults = array();

            //Build and run the query to get the KIDs in proper order
            $globalSorted = Form::sortGlobalKids($globalForms, $globalRecords, $globalSortArray);

            //Apply $flags if necessary
            if(isset($request->global_flags))
                $flags = json_decode($request->global_flags);
            else
                $flags = array();

            if(isset($flags->index) && !is_null($flags->index) && is_numeric($flags->index))
                $globalSorted = array_slice($globalSorted,$flags->index);

            if(isset($flags->count) && !is_null($flags->count) && is_numeric($flags->count))
                $globalSorted = array_slice($globalSorted,0,$flags->count);

            //for each record in that new KID array
            foreach($globalSorted as $kid) {
                //Peak into the form results to find the record
                foreach($resultsGlobal as $formRecordSet) {
                    //Move said record to the new Results array
                    if(isset($formRecordSet[$kid]))
                        $globalSortedResults[$kid] = $formRecordSet[$kid];
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
            'warnings' => $minorErrors
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
                    $ridSets[] = $this->processQuery($queries[$val], $form, $recMod);
                } else if(is_object($val)) {
                    //New sub-operand, run recursive
                    $ridSets[] = $this->logicRecursive($val, $queries, $form, $recMod);
                } else {
                    return response()->json(["status"=>false,"error"=>"Invalid logic array for form: ". $form->name],500);
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
                    return response()->json(["status"=>false,"error"=>"Invalid logic operand for form: ". $form->name],500);
                    break;
            }

            if($finalSet instanceof JsonResponse)
                return $finalSet;
            return array_flip(array_flip($finalSet));
        } else {
            return response()->json(["status"=>false,"error"=>"Invalid logic array for form: ". $form->name],500);
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
            return response()->json(["status"=>false,"error"=>"No search query type supplied for form: ". $form->name],500);
        switch($query->search) {
            case 'advanced':
                //do an advanced search
                if(!isset($query->adv_fields) || !is_object($query->adv_fields))
                    return response()->json(["status"=>false,"error"=>"No fields supplied in an advanced search for form: ". $form->name],500);
                $fields = $query->adv_fields;
                $processed = [];
                foreach($fields as $flid => $data) {
                    if(!isset($form->layout['fields'][$flid])) {
                        array_push($minorErrors, "The following field in keyword search is not apart of the requested form: " . $this->cleanseOutput($flid));
                        continue;
                    }
                    $fieldModel = $form->layout['fields'][$flid];

                    //Check permission to search externally
                    if(!$fieldModel['external_search']) {
                        array_push($minorErrors, "The following field in advanced search is not externally searchable: " . $fieldModel['name']);
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
                    return response()->json(["status"=>false,"error"=>"No keywords supplied in a keyword search for form: ". $form->name],500);
                $keys = $query->key_words;

                //Check for limiting fields
                $searchFields = array();
                if(isset($query->key_fields)) {
                    if(!is_array($query->key_fields))
                        return response()->json(["status"=>false,"error"=>"Invalid fields array in keyword search for form: ". $form->name],500);

                    //takes care of converting slugs to flids
                    foreach($query->key_fields as $qfield) {
                        if(!isset($form->layout['fields'][$qfield])) {
                            array_push($minorErrors, "The following field in keyword search is not apart of the requested form: " . $this->cleanseOutput($qfield));
                            continue;
                        }
                        $fieldMod = $form->layout['fields'][$qfield];

                        if(!$fieldMod['external_search']) {
                            array_push($minorErrors, "The following field in keyword search is not externally searchable: " . $fieldMod['name']);
                            continue;
                        }
                        $searchFields[$qfield] = $fieldMod;
                    }
                } else {
                    $searchFields = $form->layout['fields'];
                }
                if(empty($searchFields))
                    return response()->json(["status"=>false,"error"=>"Invalid fields provided for keyword search for form: ". $form->name],500);

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
                        return response()->json(["status"=>false,"error"=>"Invalid method, ".$this->cleanseOutput($method).", provided for keyword search for form: ". $form->name],500);
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
                    return response()->json(["status"=>false,"error"=>"No KIDs supplied in a KID search for form: ". $form->name],500);
                $kids = $query->kids;
                for($i=0; $i < sizeof($kids); $i++) {
                    if(!Record::isKIDPattern($kids[$i])) {
                        array_push($minorErrors,"Illegal KID (".$this->cleanseOutput($kids[$i]).") in a KID search for form: ". $form->name);
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
                    return response()->json(["status"=>false,"error"=>"No KIDs supplied in a KID search for form: ". $form->name],500);
                $kids = $query->legacy_kids;

                $negative = isset($query->not) && is_bool($query->not) ? $query->not : false;
                if($negative)
                    $rids = $recMod->newQuery()->whereNotIn('legacy_kid',$kids)->pluck('id')->toArray();
                else
                    $rids = $recMod->newQuery()->whereIn('legacy_kid',$kids)->pluck('id')->toArray();
                return $rids;
                break;
            default:
                return response()->json(["status"=>false,"error"=>"Invalid search query type supplied for form: ". $form->name],500);
                break;
        }
    }

    /**
     * Creates a new record.
     *
     * @param  Request $request
     * @return mixed - The new RID, if successful
     */
    public function create(Request $request) { //TODO::CASTLE
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
            $zipPath = $file->move(storage_path('app/tmpFiles/impU' . $uToken));
            $zip = new \ZipArchive();
            $res = $zip->open($zipPath);
            if($res === TRUE) {
                $zip->extractTo(storage_path('app/tmpFiles/impU' . $uToken));
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
    private function fileToken() { //TODO::CASTLE
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
    public function edit(Request $request) { //TODO::CASTLE
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
            $zipPath = $file->move(storage_path('app/tmpFiles/impU' . $uToken));
            $zip = new \ZipArchive();
            $res = $zip->open($zipPath);
            if($res === TRUE) {
                $zip->extractTo(storage_path('app/tmpFiles/impU' . $uToken));
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
                $fieldsToEditArray[] = $field->flid;

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
    public function delete(Request $request) { //TODO::CASTLE
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