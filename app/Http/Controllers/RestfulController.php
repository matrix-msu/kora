<?php namespace App\Http\Controllers;

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

        return $form->layout['fields'];
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

            //Authentication failed
            if(!$this->validateToken($form->project_id,$f->token,"search"))
                return response()->json(["status"=>false,"error"=>"Invalid search token provided for form: ".$f->form],500);
        }

        //now we actually do searches per form
        $resultsGlobal = [];
        $filtersGlobal = [];
        $fidsGlobal = [];
        $countArray = array();
        $countGlobal = 0;
        $minorErrors = array(); //Some errors we may not want to error out on

        foreach($forms as $f) {
            //initialize form
            $form = FormController::getForm($f->form);
            $recMod = new Record(array(),$form->id);
            if($globalSort)
                array_push($fidsGlobal, $form->id);

            //Configurations for what we will be returning
            //NOTE: Items marked ***, will be overwritten when using globalSort
            $filters = array();
            $filters['data'] = isset($f->data) ? $f->data : true; //do we want data, or just info about the records theme selves
            $filters['meta'] = isset($f->meta) ? $f->meta : false; //get meta data about record
            $filters['size'] = isset($f->size) ? $f->size : false; //do we want the number of records in the search result returned instead of data
            $filters['fields'] = isset($f->fields) ? $f->fields : 'ALL'; //which fields do we want data for
            $filters['sort'] = isset($f->sort) ? $f->sort : null; //how should the data be sorted
            $filters['count'] = isset($f->count) ? $f->count : null; //how many records we should grab from that index
            $filters['index'] = isset($f->index) ? $f->index : null; //where the array of results should start [MUST USE 'count' FOR THIS TO WORK]
            $filters['assoc'] = isset($f->assoc) ? $f->assoc : false; //do we want information back about associated records //TODO::CASTLE
            $filters['revAssoc'] = isset($f->revAssoc) ? $f->revAssoc : true; //do we want information back about reverse associations for XML OUTPUT //TODO::CASTLE

            //Note: Filters only captures values from certain fields (mainly single value ones), see Form::$validFilterFields to see which ones use it
            $filters['filters'] = isset($f->filters) ? $f->filters : false; //do we want information back about result filters [i.e. Field 'First Name', has value 'Tom', '12' times]
            $filters['filterCount'] = isset($f->filterCount) ? $f->filterCount : 1; //What is the minimum threshold for a filter to return?
            $filters['filterFlids'] = isset($f->filterFlids) ? $f->filterFlids : 'ALL'; //What fields should filters return for? Should be array

            //Bonus filters
            //WARNING::IF FIELD NAMES SHARE A TITLE WITHIN THE SAME FIELD, THIS WOULD IN THEORY BREAK
            $filters['realnames'] = isset($f->realnames) ? $f->realnames : false; //do we want records indexed by titles rather than slugs
            //THIS SOLELY SERVES LEGACY. YOU PROBABLY WILL NEVER USE THIS. DON'T THINK ABOUT IT
            $filters['under'] = isset($f->under) ? $f->under : false; //Replace field spaces with underscores //TODO::CASTLE

            //parse the query
            if(!isset($f->query)) {
                //return all records
                $records = $form->getRecordsForExport($filters);

                if($filters['size']) {
                    $cnt = sizeof($records);
                    $countGlobal += $cnt;
                    $countArray[$form->id] = $cnt;
                }

                $resultsGlobal[] = $records;

                if($filters['filters'])
                    $filtersGlobal[$form->internal_name] = $form->getDataFilters($filters['filterCount'], $filters['filterFlids']);

//                if($globalSort) //TODO::CASTLE
//                    $this->imitateMerge($globalRecords,$returnRIDS);
//                else { //TODO::CASTLE
//                    if($apiFormat==self::XML)
//                        $resultsGlobal[] = $this->populateRecords($returnRIDS, $filters, $apiFormat, $form->id);
//                    else {
//                        $resultsGlobal[] = json_decode($this->populateRecords($returnRIDS, $filters, $apiFormat, $form->id));
//                    }
//                }
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
                                    if(!isset($form->layout['fields'][$qfield])) {
                                        array_push($minorErrors, "The following field in keyword search is not apart of the requested form: " . $qfield);
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
                            $negative = isset($query->not) ? $query->not : false;
                            $search = new Search($form->project_id,$form->id,$keys,$method);
                            $rids = $search->formKeywordSearch($searchFields, true, $negative);

                            $resultSets[] = $rids;
                            break;
                        case 'advanced':
                            //do an advanced search
                            if(!isset($query->fields))
                                return response()->json(["status"=>false,"error"=>"No fields supplied in an advanced search for form: ". $form->name],500);
                            $fields = $query->fields;
                            foreach($fields as $flid => $data) {
                                if(!isset($form->layout['fields'][$flid])) {
                                    array_push($minorErrors, "The following field in keyword search is not apart of the requested form: " . $flid);
                                    continue;
                                }
                                $fieldModel = $form->layout['fields'][$flid];

                                //Check permission to search externally
                                if(!$fieldModel['external_search']) {
                                    array_push($minorErrors, "The following field in advanced search is not externally searchable: " . $fieldModel['name']);
                                    continue;
                                }
                                $request->request->add([$flid.'_dropdown' => 'on']);
                                $request->request->add([$flid.'_valid' => 1]);
                                $request->request->add([$flid => 1]);
                                $request = $form->getFieldModel($fieldModel['type'])->setRestfulAdvSearch($data,$flid,$request);
                            }
                            $negative = isset($query->not) ? $query->not : false;
                            $advSearch = new AdvancedSearchController();
                            $rids = $advSearch->apisearch($form->project_id, $form->id, $request, $negative);
                            $resultSets[] = $rids;
                            break;
                        case 'kid':
                            //do a kid search
                            if(!isset($query->kids))
                                return response()->json(["status"=>false,"error"=>"No KIDs supplied in a KID search for form: ". $form->name],500);
                            $kids = $query->kids;
                            for($i=0; $i < sizeof($kids); $i++) {
                                if(!Record::isKIDPattern($kids[$i])) {
                                    array_push($minorErrors,"Illegal KID ($kids[$i]) in a KID search for form: ". $form->name);
                                    continue;
                                }
                            }
                            $negative = isset($query->not) ? $query->not : false;
                            if($negative)
                                $rids = $recMod->newQuery()->whereNotIn('kid',$kids)->pluck('id');
                            else
                                $rids = $recMod->newQuery()->whereIn('kid',$kids)->pluck('id');
                            $resultSets[] = $rids;
                            break;
                        case 'legacy_kid':
                            //do a kid search
                            if(!isset($query->kids))
                                return response()->json(["status"=>false,"error"=>"No KIDs supplied in a KID search for form: ". $form->name],500);
                            $kids = $query->kids;

                            $negative = isset($query->not) ? $query->not : false;
                            if($negative)
                                $rids = $recMod->newQuery()->whereNotIn('legacy_kid',$kids)->pluck('id');
                            else
                                $rids = $recMod->newQuery()->whereIn('legacy_kid',$kids)->pluck('id');
                            $resultSets[] = $rids;
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

                $records = $form->getRecordsForExport($filters,$returnRIDS);

                if($filters['size']) {
                    $cnt = sizeof($records);
                    $countGlobal += $cnt;
                    $countArray[$form->id] = $cnt;
                }

                $resultsGlobal[] = $records;

                if($filters['filters'])
                    $filtersGlobal[$form->internal_name] = $form->getDataFilters($filters['filterCount'], $filters['filterFlids'], $returnRIDS);

//                if($globalSort) //TODO::CASTLE
//                    $this->imitateMerge($globalRecords,$returnRIDS);
//                else {
//                    if($apiFormat==self::XML)
//                        $resultsGlobal[] = $this->populateRecords($returnRIDS, $filters, $apiFormat, $form->fid);
//                    else
//                        $resultsGlobal[] = json_decode($this->populateRecords($returnRIDS, $filters, $apiFormat, $form->fid));
//                }
            }
        }

        if($globalSort) { //TODO::CASTLE
//            $filters = array();
//
//            if(isset($request->globalFilters))
//                $f = json_decode($request->globalFilters);
//            else
//                $f = array();
//
//            $filters['data'] = isset($f->data) ? $f->data : true; //do we want data, or just info about the records theme selves***
//            $filters['meta'] = isset($f->meta) ? $f->meta : false; //get meta data about record***
//            $filters['assoc'] = isset($f->assoc) ? $f->assoc : false; //do we want information back about associated records***
//            $filters['fields'] = isset($f->fields) ? $f->fields : 'ALL'; //which fields do we want data for***
//            $filters['index'] = isset($f->index) ? $f->index : null; //where the array of results should start***
//            $filters['count'] = isset($f->count) ? $f->count : null; //how many records we should grab from that index***
//            //WARNING::IF FIELD NAMES SHARE A TITLE WITHIN THE SAME FIELD, THIS WOULD IN THEORY BREAK
//            $filters['realnames'] = isset($f->realnames) ? $f->realnames : false; //do we want records indexed by titles rather than slugs***
//            //THIS SOLELY SERVES LEGACY. YOU PROBABLY WILL NEVER USE THIS. DON'T THINK ABOUT IT
//            $filters['under'] = isset($f->under) ? $f->under : false; //Replace field spaces with underscores***
//
//            $globalSorted = $this->sortGlobalRids($globalRecords, $globalSortArray);
//            $resultsGlobal = json_decode($this->populateRecords($globalSorted, $filters, $apiFormat, $fidsGlobal));
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
     * Sorts RIDs by fields.
     *
     * @param  array $rids - The RIDs to sort
     * @param  array $sortFields - The field arrays to sort by
     * @return array - The new array with sorted RIDs
     */
    private function sortGlobalRids($rids, $sortFields) { //TODO::CASTLE
        //get field
        $newOrderArray = array();
        $ridString = implode(',',$rids);

        //Doing this for pretty much the same reason as keyword search above
        $con = mysqli_connect(
            config('database.connections.mysql.host'),
            config('database.connections.mysql.username'),
            config('database.connections.mysql.password'),
            config('database.connections.mysql.database')
        );
        $prefix = config('database.connections.mysql.prefix');

        //We want to make sure we are doing things in utf8 for special characters
        if(!mysqli_set_charset($con, "utf8")) {
            printf("Error loading character set utf8: %s\n", mysqli_error($con));
            exit();
        }

        //report errors, not 100% sure how we'll get it up a level

        $selectJoins = "";
        $selectOrdArr = array();

        for($s=0;$s<sizeof($sortFields);$s=$s+2) {
            $fieldSlug = $sortFields[$s];
            $direction = $sortFields[$s+1];

            if(!is_array($fieldSlug) && $fieldSlug=='kora_meta_owner') {
                $selectJoins .= "LEFT JOIN ".$prefix."_users as us ON us.id=rec.owner ";
                array_push($selectOrdArr, "`username` $direction");
            } else if(!is_array($fieldSlug) && $fieldSlug=='kora_meta_created') {
                array_push($selectOrdArr, "`created_at` $direction");
            } else if(!is_array($fieldSlug) && $fieldSlug=='kora_meta_updated') {
                array_push($selectOrdArr, "`updated_at` $direction");
            } else if(!is_array($fieldSlug) && $fieldSlug=='kora_meta_kid') {
                array_push($selectOrdArr, "`rid` $direction");
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

                $flidColumn = implode('_',$flids);
                $flidString = implode(',',$flids);
                $type = $typedField->getSortColumn();
                $table = $prefix.$typedField->getTable();

                if(!is_null($type)) {
                    $selectJoins .= "LEFT JOIN ".$table." as field".$flidColumn." ON field".$flidColumn.".rid=rec.rid and field".$flidColumn.".`flid` IN (".$flidString.") ";
                    array_push($selectOrdArr, "field".$flidColumn.".`$type` IS NULL, field".$flidColumn.".`$type` $direction");
                }
            }
        }
        $selectOrders = implode(', ',$selectOrdArr);

        $select = "SELECT rec.`rid` from kora3_records as rec $selectJoins";
        $select .= "WHERE rec.`rid` IN ($ridString) ORDER BY $selectOrders";

        $sort = $con->query($select);

        while($row = $sort->fetch_assoc()) {
            $newOrderArray[] = $row['rid'];
        }
        mysqli_free_result($sort);

        return $newOrderArray;
    }



    /**
     * Import form into project.
     *
     * @param  int $pid - Project ID
     * @return string - Success message
     */
    public function createForm($pid, Request $request) { //TODO::CASTLE
        if(!ProjectController::validProj($pid))
            return response()->json(["status"=>false,"error"=>"Invalid Project: ".$pid],500);

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