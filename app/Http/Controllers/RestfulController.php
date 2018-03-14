<?php namespace App\Http\Controllers;

use App\Field;
use App\Form;
use App\Record;
use App\Search;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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

    /**
     * @var array - Valid output formats
     */
    const VALID_FORMATS = [ self::JSON, self::KORA];

    /**
     * Gets the current version of Kora3.
     *
     * @return JsonResponse - Kora version
     */
    public function getKoraVersion() {
        $instInfo = DB::table("versions")->first();
        if(is_null($instInfo))
            return response()->json(["status"=>false,"error"=>"Failed to retrieve Kora installation version"],500);
        else
            return response()->json(["status"=>true,"result"=>$instInfo->version],200);
    }

    /**
     * Get a basic list of the forms in a project.
     *
     * @param  int $pid - Project ID
     * @return JsonResponse - The forms
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
        return response()->json(["status"=>true,"result"=>$forms],200);
    }

    /**
     * Get a basic list of the fields in a form.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @return JsonResponse - The fields
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
        return response()->json(["status"=>true,"result"=>$fields],200);
    }

    /**
     * Get the number of records in a form.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @return JsonResponse - Number of records
     */
    public function getFormRecordCount($pid, $fid) {
        if(!FormController::validProjForm($pid,$fid))
            return response()->json(["status"=>false,"error"=>"Invalid Project/Form Pair: ".$pid." ~ ".$fid],500);

        $form = FormController::getForm($fid);
        $count = $form->records()->count();
        return response()->json(["status"=>true,"result"=>$count],200);
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
        if(is_null($forms))
            return response()->json(["status"=>false,"error"=>"Unable to process forms array"],500);
        //get the format
        if(isset($request->format))
            $apiFormat = $request->format;
        else
            $apiFormat = self::JSON;
        //next, we authenticate each form
        foreach($forms as $f) {
            //next, we authenticate the form
            $form = FormController::getForm($f->form);
            if(is_null($form))
                return response()->json(["status"=>false,"error"=>"Invalid Form: ".$f->form],500);

            $validated = $this->validateToken($form,$f->token,"search");
            //Authentication failed
            if(!$validated)
                return response()->json(["status"=>false,"error"=>"Invalid search token provided"],500);
        }
        //now we actually do searches per form
        $resultsGlobal = [];
        $countArray = array();
        $countGlobal = 0;

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
                            $flids = null;
                            if(isset($query->fields)) {
                                $flids = array();
                                //takes care of converting slugs to flids
                                foreach($query->fields as $qfield) {
                                    $fieldMod = FieldController::getField($qfield);
                                    array_push($flids,$fieldMod->flid);
                                }
                            }
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
                            $search = new Search($form->pid, $form->fid, $keys, $method);
                            $rids = $search->formKeywordSearch($flids,true);
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
                                //Check permission to search externally
                                if(!$fieldModel->isExternalSearchable())
                                    continue;
                                $request->request->add([$fieldModel->flid.'_dropdown' => 'on']);
                                $request->request->add([$fieldModel->flid.'_valid' => 1]);
                                $request = $fieldModel->getTypedField()->setRestfulAdvSearch($data,$fieldModel->flid,$request);
                            }
                            $advSearch = new AdvancedSearchController();
                            $rids = $advSearch->apisearch($form->pid, $form->fid, $request);
                            if(is_null($rids))
                                $rids=[];
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
                                //if($record->fid != $form->fid)
                                    //return response()->json(["status"=>false,"error"=>"The following KID is not apart of the requested form: " . $kids[$i]],500);
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
                                //if($record->fid != $form->fid)
                                    //return response()->json(["status"=>false,"error"=>"The following legacy KID is not apart of the requested form: " . $kids[$i]],500);
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
                        $returnRIDS = array_merge($returnRIDS,$result);
                    }
                    $returnRIDS = array_unique($returnRIDS);
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
            'records' => $resultsGlobal
        ];
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
                asort($newOrderArray);
            else if($direction=="DESC")
                arsort($newOrderArray);
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
                    $finalResult = array_merge($finalResult,$tieResult);

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
        $finalResult = array_merge($finalResult, $missing);

        return $finalResult;
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
        if($operator=="AND") {
            $returnRIDS = array_intersect($firstRIDS,$secondRIDS);
        } else if($operator=="OR") {
            $returnRIDS = array_merge($firstRIDS,$secondRIDS);
        }
        return array_unique($returnRIDS);
    }

    /**
     * Creates a new record.
     *
     * @param  Request $request
     * @return JsonResponse - The new RID, if successful
     */
    public function create(Request $request) {
        //get the form
        $f = $request->form;
        //next, we authenticate the form
        $form = FormController::getForm($f);
        if(is_null($form))
            return response()->json(["status"=>false,"error"=>"Invalid Form: ".$form->fid],500);

        $validated = $this->validateToken($form,$request->token,"create");
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
        foreach($fields as $jsonField) {
            $fieldSlug = $jsonField->name;
            $field = Field::where('slug', '=', $fieldSlug)->get()->first();

            $recRequest = $field->getTypedField()->setRestfulRecordData($jsonField, $field->flid, $recRequest, $uToken);
        }
        $recRequest['api'] = true;
        $recCon = new RecordController();
        //TODO::do something with this
        $response = $recCon->store($form->pid,$form->fid,$recRequest);
        return response()->json(["status"=>true,"result"=>"Created Record: "],200);
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
     * @return JsonResponse - Status of record modification
     */
    public function edit(Request $request) {
        //get the form
        $f = $request->form;
        //next, we authenticate the form
        $form = FormController::getForm($f);
        if(is_null($form))
            return response()->json(["status"=>false,"error"=>"Invalid Form: ".$form->fid],500);

        $validated = $this->validateToken($form,$request->token,"edit");
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
        foreach($fields as $jsonField) {
            $fieldSlug = $jsonField->name;
            $field = Field::where('slug', '=', $fieldSlug)->get()->first();
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
        return response()->json(["status"=>true,"result"=>"Modified record: ".$request->kid],200);
    }

    /**
     * Delete a set of records from Kora3
     *
     * @param  Request $request
     * @return JsonResponse - Status of record deletion
     */
    public function delete(Request $request){
        //get the form
        $f = $request->form;
        //next, we authenticate the form
        $form = FormController::getForm($f);
        if(is_null($form))
            return response()->json(["status"=>false,"error"=>"Invalid Form: ".$form->fid],500);

        $validated = $this->validateToken($form,$request->token,"delete");
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
        return response()->json(["status"=>true,"result"=>"Deleted records"],200);
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
        $format = strtoupper($format);
        if( !self::isValidFormat($format))
            return 'Invalid format for export!';

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
     * @param  Form $form - Form being searched/modified
     * @param  string $token - Provided token to check
     * @param  string $permission - Type of API action being taken
     * @return bool - Is valid and has permission
     */
    private function validateToken($form,$token,$permission) {
        //Get all the projects tokens
        $project = ProjectController::getProject($form->pid);
        $tokens = $project->tokens()->get();
        //compare
        foreach($tokens as $t) {
            if($t->token == $token && $t->$permission)
                return true;
        }
        return false;
    }
}