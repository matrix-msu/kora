<?php
namespace App\Http\Controllers;

use App\Field;
use App\Form;
use App\Record;
use App\Search;
use App\User;
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
    const XML = "XML";

    /**
     * @var array - Valid output formats
     */
    const VALID_FORMATS = [ self::JSON, self::XML ];

    /**
     * @var array - Fields that are valid for sort
     */
    const VALID_SORT = Field::VALID_SORT;

    /**
     * Gets the current version of Kora3.
     *
     * @return string - Kora version
     */
    public function getKoraVersion() {
        $instInfo = DB::table("versions")->first();
        if(is_null($instInfo)) {
            return "Failed to retrieve Kora version";
        } else {
            return $instInfo->version;
        }
    }

    /**
     * Get a basic list of the forms in a project.
     *
     * @param  int $pid - Project ID
     * @return string - Json array of the forms
     */
    public function getProjectForms($pid) {
        if(!ProjectController::validProj($pid)) {
            return 'Invalid Project: '.$pid;
        }
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
        return json_encode($forms);
    }

    /**
     * Get a basic list of the fields in a form.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @return string - Json array of the fields
     */
    public function getFormFields($pid, $fid) {
        if(!FormController::validProjForm($pid,$fid)) {
            return "Invalid Project/Form Pair: ".$pid." ~ ".$fid;
        }
        $form = FormController::getForm($fid);
        $fieldMods = $form->fields()->get();
        $fields = array();
        foreach($fieldMods as $field) {
            $fArray = array();
            $fArray['name'] = $field->name;
            $fArray['type'] = $field->type;
            $fArray['nickname'] = $field->slug;
            $fArray['description'] = $field->desc;
            $fields[$field->flid] = $fArray;
        }
        return json_encode($fields);
    }

    /**
     * Get the number of records in a form.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @return int - Number of records
     */
    public function getFormRecordCount($pid, $fid) {
        if(!FormController::validProjForm($pid,$fid)) {
            return "Invalid Project/Form Pair: ".$pid." ~ ".$fid;
        }
        $form = FormController::getForm($fid);
        $count = $form->records()->count();
        return $count;
    }

    /**
     * Performs an API search on Kora3.
     *
     * @param  Request $request
     * @return string - Json response of the records
     */
    public function search(Request $request) {
        //get the forms
        $forms = json_decode($request->forms);
        //next, we authenticate each form
        foreach($forms as $f) {
            //next, we authenticate the form
            $form = FormController::getForm($f->form);
            if(is_null($form)) {
                return 'Illegal form provided: '.$f->form;
            }
            $validated = $this->validateToken($form,$f->token,"search");
            //Authentication failed
            if(!$validated) {
                return "The provided token is invalid for form: ".$form->name;
            }
        }
        //now we actually do searches per form
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
            //parse the query
            if(!isset($f->query)) {
                //return all records
                $returnRIDS = Record::where("fid","=",$form->fid)->lists('rid')->all();
                if(!is_null($filters['sort'])) {
                    $returnRIDS = $this->sort_rids($returnRIDS,$filters['sort']);
                    if(!$returnRIDS)
                        return "Illegal field type or invalid field provided for sort in form: " . $form->name;
                }
                //see if we are returning the
                if ($filters['size'])
                    return sizeof($returnRIDS);
                else
                    return $this->populateRecords($returnRIDS, $filters);
            } else {
                $queries = $f->query;
                $resultSets = array();
                foreach($queries as $query) {
                    //determine our search type
                    switch($query->search) {
                        case 'keyword':
                            //do a keyword search
                            if (!isset($query->keys))
                                return "You must provide keywords in a keyword search for form: " . $form->name;
                            $keys = $query->keys;
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
                                    return "Illegal method, ".$method.", provided for keyword search for form: " . $form->name;
                                    break;
                            }
                            $search = new Search($form->pid, $form->fid, $keys, $method);
                            $rids = $search->formKeywordSearch(null,true);
                            $negative = isset($query->not) ? $query->not : false;
                            if($negative)
                                $rids = $this->negative_results($form,$rids);
                            array_push($resultSets,$rids);
                            break;
                        case 'advanced':
                            //do an advanced search
                            if (!isset($query->fields))
                                return "You must provide fields in an advanced search for form: " . $form->name;
                            $fields = $query->fields;
                            foreach($fields as $flid => $data) {
                                $field = FieldController::getField($flid);
                                //Check permission to search externally
                                if(!$field->isExternalSearchable())
                                    continue;
                                $id = $field->flid;
                                $request->request->add([$id.'_dropdown' => 'on']);
                                $request->request->add([$id.'_valid' => 1]);
                                $request = Field::setRestfulAdvSearch($data,$field,$request);
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
                            if (!isset($query->kids))
                                return "You must provide KIDs in a KID search for form: " . $form->name;
                            $kids = $query->kids;
                            $rids = array();
                            for($i = 0; $i < sizeof($kids); $i++) {
                                $rids[$i] = explode("-", $kids[$i])[2];
                            }
                            $negative = isset($query->not) ? $query->not : false;
                            if($negative)
                                $rids = $this->negative_results($form,$rids);
                            array_push($resultSets,$rids);
                            break;
                        default:
                            return "You must provide a search query type for form: " . $form->name;
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
                if(!is_null($filters['sort'])) {
                    $returnRIDS = $this->sort_rids($returnRIDS,$filters['sort']);
                    if(!$returnRIDS)
                        return "Illegal field type or invalid field provided for sort in form: " . $form->name;
                }
                //see if we are returning the
                if($filters['size'])
                    return sizeof($returnRIDS);
                else
                    return $this->populateRecords($returnRIDS, $filters);
            }
        }
        return 'Successful search!';
    }

    /**
     * Based on set of RIDs from a search result, return all RIDs that do not fit that search.
     *
     * @param  Form $form - Form being searched
     * @param  array $rids - Record IDs we don't want
     * @return Collection - The RIDs not in the given set
     */
    private function negative_results($form, $rids) {
        $negatives = Record::where('fid','=',$form->fid)->whereNotIn('rid',$rids)->lists('rid')->all();
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
        $noSortValue = array();
        if($fieldSlug=="kora_meta_owner") {
            foreach($rids as $rid) {
                $record = RecordController::getRecord($rid);
                $owner = User::where('id','=',$record->owner)->first();
                $newOrderArray[$rid] = $owner->username;
            }
        } else if($fieldSlug=="kora_meta_created") {
            foreach($rids as $rid) {
                $record = RecordController::getRecord($rid);
                $created = $record->created_at;
                $newOrderArray[$rid] = $created;
            }
        } else if($fieldSlug=="kora_meta_updated") {
            foreach($rids as $rid) {
                $record = RecordController::getRecord($rid);
                $updated = $record->updated_at;
                $newOrderArray[$rid] = $updated;
            }
        } else if($fieldSlug=="kora_meta_kid") {
            foreach($rids as $rid) {
                $newOrderArray[$rid] = $rid;
            }
        } else {
            $field = FieldController::getField($fieldSlug);
            if(!in_array(($field->type), self::VALID_SORT))
                return false;
            //for each rid
            foreach($rids as $rid) {
                //based on type
                $hasSort = Field::hasValueToSort($field, $rid, $newOrderArray, $noSortValue);
                $newOrderArray = $hasSort[0];
                $noSortValue = $hasSort[1];
            }
        }
        //sort new array
        $extraData = array($sortFields,$direction,$newOrderArray);
        uksort($newOrderArray, function($a_key,$b_key) use ($extraData) {
            $sortArray = $extraData[0]; //we need to remove the original sort term and direction to determine if we have tiebreakers defined
            $copySort = $sortArray;
            array_shift($copySort); //this removes the first sort field
            array_shift($copySort); //this removes the first direction
            //determine direction to know if we should flip the result
            $dir = $extraData[1];
            if($dir=="ASC")
                $dir = 1;
            else if("DESC")
                $dir = -1;
            else
                $dir = 1;
            //using a key sort, but we really want compare the values (like a uasort)
            //this is the only way we can have both the keys and the values in the compare function
            $copyArray = $extraData[2]; //a copy of the newOrderArray
            $a = $copyArray[$a_key];
            $b = $copyArray[$b_key];
            if(is_a($a,'DateTime') | (is_numeric($a) && is_numeric($b))) {
                if($a==$b) {
                    if(!empty($copySort)) {
                        //do things to tiebreak
                        //get the rids were working with
                        $recurRids = array($a_key,$b_key);
                        //run through sort again passing rids and new sort array
                        $tiebreaker = $this->sort_rids($recurRids,$copySort);
                        //we know the answer will be an array of a and b's rid
                        //if a is first
                        if($tiebreaker[0]==$a_key)
                            return -1*$dir;
                        else
                            return 1*$dir;
                    } else {
                        return 0;
                    }
                } else if($a>$b) {
                    return 1*$dir;
                } else if($a<$b) {
                    return -1*$dir;
                }
            } else {
                $answer = strcmp($a, $b)*$dir;
                if($answer==0 && !empty($copySort)) {
                    //do things to tiebreak
                    //get the rids were working with
                    $recurRids = array($a_key,$b_key);
                    //run through sort again passing rids and new sort array
                    $tiebreaker = $this->sort_rids($recurRids,$copySort);
                    //we know the answer will be an array of a and b's rid
                    //if a is first
                    if($tiebreaker[0]==$a_key)
                        return -1*$dir;
                    else
                        return 1*$dir;
                } else {
                    return $answer;
                }
            }
        });
        //convert to plain array of rids
        $finalResult = array_keys($newOrderArray);
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
     * @return string - Json response with the new RID, if successful
     */
    public function create(Request $request) {
        //get the form
        $f = $request->form;
        //next, we authenticate the form
        $form = FormController::getForm($f);
        if(is_null($form)) {
            return 'Illegal form provided: '.$f->form;
        }
        $validated = $this->validateToken($form,$request->token,"create");
        //Authentication failed
        if(!$validated) {
            return "The provided token is invalid for form: ".$form->name;
        }
        //Gather field data to insert
        if(!isset($request->fields)) {
            return "You must provide data to insert into: ".$form->name;
        }
        $fields = json_decode($request->fields);
        $recRequest = new Request();
        $uToken = $this->fileToken(); //need a temp user id to interact, specifically for files
        $recRequest['userId'] = $uToken; //the new record will ultimately be owned by the root/sytem
        if( !is_null($request->file("zipFile")) ) {
            $file = $request->file("zipFile");
            $zipPath = $file->move(env('BASE_PATH') . 'storage/app/tmpFiles/impU' . $uToken);
            $zip = new \ZipArchive();
            $res = $zip->open($zipPath);
            if($res === TRUE) {
                $zip->extractTo(env('BASE_PATH') . 'storage/app/tmpFiles/impU' . $uToken);
                $zip->close();
            } else {
                return "There was an error extracting the provided zip";
            }
        }
        foreach($fields as $field) {
            $fieldSlug = $field->name;
            $flid = Field::where('slug', '=', $fieldSlug)->get()->first()->flid;

            $recRequest = Field::setRestfulRecordData($field, $flid, $recRequest, $uToken);
        }
        $recRequest['api'] = true;
        $recCon = new RecordController();
        $response = $recCon->store($form->pid,$form->fid,$recRequest);
        return 'Created record: '.$response;
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
     * Edit an existing record
     *
     * @param  type $name - DESCRIPTION
     * @return string - DESCRIPTION
     */
    public function edit(Request $request) {
        //get the form
        $f = $request->form;
        //next, we authenticate the form
        $form = FormController::getForm($f);
        if(is_null($form)) {
            return 'Illegal form provided: '.$f->form;
        }
        $validated = $this->validateToken($form,$request->token,"edit");
        //Authentication failed
        if(!$validated) {
            return "The provided token is invalid for form: ".$form->name;
        }
        //Gather field data to insert
        if(!isset($request->kid)) {
            return "You must provide a record kid for: ".$form->name;
        }
        //Gather field data to insert
        if(!isset($request->fields)) {
            return "You must provide data to insert into: ".$form->name;
        }
        $fields = json_decode($request->fields);
        $record = RecordController::getRecordByKID($request->kid);
        if(is_null($record)) {
            return 'Illegal record provided: '.$request->kid;
        }
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
            $zipPath = $file->move(env('BASE_PATH') . 'storage/app/tmpFiles/impU' . $uToken);
            $zip = new \ZipArchive();
            $res = $zip->open($zipPath);
            if($res === TRUE) {
                $zip->extractTo(env('BASE_PATH') . 'storage/app/tmpFiles/impU' . $uToken);
                $zip->close();
            } else {
                return "There was an error extracting the provided zip";
            }
        }
        foreach($fields as $field) {
            $fieldSlug = $field->name;
            $flid = Field::where('slug', '=', $fieldSlug)->get()->first()->flid;
            //if keepfields scenario, keep track of this field that will be edited
            if($keepFields=="true")
                array_push($fieldsToEditArray,$flid);

            $recRequest = Field::setRestfulRecordData($field, $flid, $recRequest, $uToken);
        }
        $recRequest['api'] = true;
        $recRequest['keepFields'] = $keepFields; //whether we keep unmentioned fields
        $recRequest['fieldsToEdit'] = $fieldsToEditArray; //what fields can be modified if keepfields
        $recCon = new RecordController();
        $recCon->update($form->pid,$form->fid,$record->rid,$recRequest);
        return 'Modified record: '.$request->kid;
    }

    /**
     * Delete a set of records from Kora3
     *
     * @param  Request $request
     * @return string - Success/error message
     */
    public function delete(Request $request){
        //get the form
        $f = $request->form;
        //next, we authenticate the form
        $form = FormController::getForm($f);
        if(is_null($form)) {
            return 'Illegal form provided: '.$f->form;
        }
        $validated = $this->validateToken($form,$request->token,"delete");
        //Authentication failed
        if(!$validated) {
            return "The provided token is invalid for form: ".$form->name;
        }
        //Gather records to delete
        if(!isset($request->kids)) {
            return "You must provide KIDs to delete from: ".$form->name;
        }
        $kids = explode(",",$request->kids);
        $recsToDelete = array();
        for($i=0;$i<sizeof($kids);$i++) {
            $rid = explode("-",$kids[$i])[2];
            $record = RecordController::getRecord($rid);
            if(is_null($record)) {
                return 'Non-existent record provided: '.$kids[$i];
            } else {
                array_push($recsToDelete,$record);
            }
        }
        foreach($recsToDelete as $record) {
            $record->delete();
        }
        return 'DELETE SUCCESS!';
    }

    /**
     * Sends list of RIDs to python to generate the record data.
     *
     * @param  array $rids - List of Record IDs
     * @param  array $filters - Filters from the search
     * @param  string $format - The return format for the results
     * @return string - Path to the results file
     */
    //mimics the export python functionality to populate records
    private function populateRecords($rids,$filters,$format = self::JSON) {
        $format = strtoupper($format);
        if( ! self::isValidFormat($format)) {
            return null;
        }
        if($filters['fields'] == "ALL")
            $fields = json_encode(array());
        else
            $fields = json_encode($filters['fields']);

        if($filters['meta'])
            $meta = 'True';
        else
            $meta = 'False';
        if($filters['data'])
            $data = 'True';
        else
            $data = 'False';
        if($filters['assoc'])
            $assoc = 'True';
        else
            $assoc = 'False';

        //Slice up array of RIDs to get the correct subset
        //There are done down here to ensure sorting has already taken place

        if(!is_null($filters['index']))
            $rids = array_slice($rids,$filters['index']);

        if(!is_null($filters['count']))
            $rids = array_slice($rids,0,$filters['count']);

        $rids = json_encode($rids);
        $exec_string = env("BASE_PATH") . "python/api.py \"$rids\" \"$format\" '$fields' \"$meta\" \"$data\" \"$assoc\"";
        exec($exec_string, $output);
        return $output[0];
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