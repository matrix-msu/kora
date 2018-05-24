<?php namespace App\Http\Controllers;

use App\DateField;
use App\Field;
use App\Form;
use App\ListField;
use App\NumberField;
use App\Record;
use App\Search;
use App\TextField;
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
        $filtersGlobal = [];
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
            $filters['filters'] = isset($f->filters) ? $f->filters : false; //do we want information back about result filters [i.e. Field 'First Name', has value 'Tom', '12' times]
            $filters['filterCount'] = isset($f->filterCount) ? $f->filterCount : 5; //What is the minimum threshold for a filter to return?
            $filters['filterFlids'] = isset($f->filterFlids) ? $f->filterFlids : 'ALL'; //What fields should filters return for? Should be array
                //Note: Filters only captures values from certain fields (mainly single value ones), see ExportController->exportWithRids() to see which ones use it
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

                if($filters['filters'])
                    $filtersGlobal[$form->slug] = $this->getDataFilters($form->fid, $returnRIDS, $filters['filterCount'], $filters['filterFlids']);

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

                $resultsGlobal[] = json_decode($this->populateRecords($returnRIDS, $filters, $apiFormat));
            }
        }

        $countArray["global"] = $countGlobal;
        return [
            'counts' => $countArray,
            'filters' => $filtersGlobal,
            'records' => $resultsGlobal,
            'warnings' => $minorErrors
        ];
    }

    private function apiKeywordSearch($searchFields, $keys, $method) {
	    //Laravel freaks out with the select statements, so we go right for the belly of the beast
	    $con = mysqli_connect(env('DB_HOST'), env('DB_USERNAME'), env('DB_PASSWORD'), env('DB_DATABASE'));
	    
        $results = array();
        foreach($keys as $k) {
            $selectFinal = [];
            $k = mysqli_real_escape_string($con,$k);

            foreach($searchFields as $field) {
                //TODO::modular?
                switch($field->type) {
                    case Field::_TEXT:
                    	if($method==Search::SEARCH_EXACT)
                    		$key = '"'.$k.'"*';
                    	else
                        	$key = $k.'*';
                        $where = "MATCH (`text`) AGAINST ('$key' IN BOOLEAN MODE)";
                        $select = "SELECT DISTINCT `rid` from ".env('DB_PREFIX')."text_fields where `flid`=".$field->flid." AND $where";
                        $selectFinal[] = $select;
                        break;
                    case Field::_RICH_TEXT:
                        if($method==Search::SEARCH_EXACT)
                    		$key = '"'.$k.'"*';
                    	else
                        	$key = $k.'*';
                        $where = "MATCH (`searchable_rawtext`) AGAINST ('$key' IN BOOLEAN MODE)";
                        $select = "SELECT DISTINCT `rid` from ".env('DB_PREFIX')."rich_text_fields where `flid`=".$field->flid." AND $where";
                        $selectFinal[] = $select;
                        break;
                    case Field::_NUMBER:
                        $bottom = $k - NumberField::EPSILON;
                        $top = $k + NumberField::EPSILON;
                        $where = "`number` BETWEEN $bottom AND $top";
                        $select = "SELECT DISTINCT `rid` from ".env('DB_PREFIX')."number_fields where `flid`=".$field->flid." AND $where";
                        $selectFinal[] = $select;
                        break;
                    case Field::_LIST:
                        if($method==Search::SEARCH_EXACT)
                    		$key = '"'.$k.'"*';
                    	else
                        	$key = $k.'*';
                        $where = "MATCH (`option`) AGAINST ('$key' IN BOOLEAN MODE)";
                        $select = "SELECT DISTINCT `rid` from ".env('DB_PREFIX')."list_fields where `flid`=".$field->flid." AND $where";
                        $selectFinal[] = $select;
                        break;
                    case Field::_MULTI_SELECT_LIST:
                        if($method==Search::SEARCH_EXACT)
                    		$key = '"'.$k.'"*';
                    	else
                        	$key = $k.'*';
                        $where = "MATCH (`options`) AGAINST ('$key' IN BOOLEAN MODE)";
                        $select = "SELECT DISTINCT `rid` from ".env('DB_PREFIX')."multi_select_list_fields where `flid`=".$field->flid." AND $where";
                        $selectFinal[] = $select;
                        break;
                    case Field::_GENERATED_LIST:
                        if($method==Search::SEARCH_EXACT)
                    		$key = '"'.$k.'"*';
                    	else
                        	$key = $k.'*';
                        $where = "MATCH (`options`) AGAINST ('$key' IN BOOLEAN MODE)";
                        $select = "SELECT DISTINCT `rid` from ".env('DB_PREFIX')."generated_list_fields where `flid`=".$field->flid." AND $where";
                        $selectFinal[] = $select;
                        break;
                    case Field::_COMBO_LIST:
                        $bottom = $k - NumberField::EPSILON;
                        $top = $k + NumberField::EPSILON;
                        if($method==Search::SEARCH_EXACT)
                    		$key = '"'.$k.'"*';
                    	else
                        	$key = $k.'*';
                        $where = "(MATCH (`data`) AGAINST ('$key' IN BOOLEAN MODE) OR `number` BETWEEN $bottom AND $top)";
                        $select = "SELECT DISTINCT `rid` from ".env('DB_PREFIX')."combo_support where `flid`=".$field->flid." AND $where";
                        $selectFinal[] = $select;
                        break;
                    case Field::_DATE:
                        // Boolean to decide if we should consider circa options.
                        $circa = explode("[!Circa!]", $field->options)[1] == "Yes";
                        // Boolean to decide if we should consider era.
                        $era = explode("[!Era!]", $field->options)[1] == "On";
                        //Checks to prevent false positives with default mysql values
                        $intVal = intval($k);
                        if($intVal == 0)
                            $intVal = 999999;
                        $intMonth = intval(DateField::monthToNumber($k));
                        if($intMonth == 0)
                            $intMonth = 999999;
                        $where = "`day`=$intVal OR `year`=$intVal";
                        if(DateField::isMonth($k))
                            $where .= " OR `month`=$intMonth";
                        if($era && self::isValidEra($k))
                            $where .= " OR `era`=".strtoupper($k);
                        if($circa && self::isCirca($k))
                            $where .= " OR `circa`=1";

                        $select = "SELECT DISTINCT `rid` from ".env('DB_PREFIX')."date_fields where `flid`=".$field->flid." AND ($where)";
                        $selectFinal[] = $select;
                        break;
                    case Field::_SCHEDULE:
                        if($method==Search::SEARCH_EXACT)
                    		$key = '"'.$k.'"*';
                    	else
                        	$key = $k.'*';
                        $where = "MATCH (`desc`) AGAINST ('$key' IN BOOLEAN MODE)";
                        $select = "SELECT DISTINCT `rid` from ".env('DB_PREFIX')."schedule_support where `flid`=".$field->flid." AND $where";
                        $selectFinal[] = $select;
                        break;
                    case Field::_DOCUMENTS:
                        if($method==Search::SEARCH_EXACT)
                    		$key = '"'.$k.'"*';
                    	else
                        	$key = $k.'*';
                        $where = "MATCH (`documents`) AGAINST ('$key' IN BOOLEAN MODE)";
                        $select = "SELECT DISTINCT `rid` from ".env('DB_PREFIX')."documents_fields where `flid`=".$field->flid." AND $where";
                        $selectFinal[] = $select;
                        break;
                    case Field::_GALLERY:
                        if($method==Search::SEARCH_EXACT)
                    		$key = '"'.$k.'"*';
                    	else
                        	$key = $k.'*';
                        $where = "MATCH (`images`) AGAINST ('$key' IN BOOLEAN MODE)";
                        $select = "SELECT DISTINCT `rid` from ".env('DB_PREFIX')."gallery_fields where `flid`=".$field->flid." AND $where";
                        $selectFinal[] = $select;
                        break;
                    case Field::_PLAYLIST:
                        if($method==Search::SEARCH_EXACT)
                    		$key = '"'.$k.'"*';
                    	else
                        	$key = $k.'*';
                        $where = "MATCH (`audio`) AGAINST ('$key' IN BOOLEAN MODE)";
                        $select = "SELECT DISTINCT `rid` from ".env('DB_PREFIX')."playlist_fields where `flid`=".$field->flid." AND $where";
                        $selectFinal[] = $select;
                        break;
                    case Field::_VIDEO:
                        if($method==Search::SEARCH_EXACT)
                    		$key = '"'.$k.'"*';
                    	else
                        	$key = $k.'*';
                        $where = "MATCH (`video`) AGAINST ('$key' IN BOOLEAN MODE)";
                        $select = "SELECT DISTINCT `rid` from ".env('DB_PREFIX')."video_fields where `flid`=".$field->flid." AND $where";
                        $selectFinal[] = $select;
                        break;
                    case Field::_3D_MODEL:
                        if($method==Search::SEARCH_EXACT)
                    		$key = '"'.$k.'"*';
                    	else
                        	$key = $k.'*';
                        $where = "MATCH (`model`) AGAINST ('$key' IN BOOLEAN MODE)";
                        $select = "SELECT DISTINCT `rid` from ".env('DB_PREFIX')."model_fields where `flid`=".$field->flid." AND $where";
                        $selectFinal[] = $select;
                        break;
                    case Field::_GEOLOCATOR:
                        if($method==Search::SEARCH_EXACT)
                    		$key = '"'.$k.'"*';
                    	else
                        	$key = $k.'*';
                        $where = "MATCH (`desc`) AGAINST ('$key' IN BOOLEAN MODE) OR MATCH (`address`) AGAINST ($key IN BOOLEAN MODE)";
                        $select = "SELECT DISTINCT `rid` from ".env('DB_PREFIX')."geolocator_support where `flid`=".$field->flid." AND ($where)";
                        $selectFinal[] = $select;
                        break;
                    case Field::_ASSOCIATOR:
                        $key = explode('-',$k);
                        $rid = end($key);
                        $where = "MATCH (`record`) AGAINST (\"$rid\" IN BOOLEAN MODE)";
                        $select = "SELECT DISTINCT `rid` from ".env('DB_PREFIX')."associator_support where `flid`=".$field->flid." AND $where";
                        $selectFinal[] = $select;
                        break;
                    default:
                        break;
                }
            }

            //Union statements together and run SQL statement
            $selectString = implode(' UNION ', $selectFinal);
            $ridsUnclean = $con->query($selectString);
            $rids = [];
            
            //Transform objects to array
            while($rid = $ridsUnclean->fetch_assoc()) {
                if(!is_null($rid['rid']))
                    $rids[]=$rid['rid'];
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
     * Based on set of RIDs from a search result, return all RIDs that do not fit that search.
     *
     * @param  Form $form - Form being searched
     * @param  array $rids - Record IDs we don't want
     * @return Collection - The RIDs not in the given set
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
        $cnt = 0;
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
        }
        
        //Doing this for pretty much the same reason as keyword search above
	    $con = mysqli_connect(env('DB_HOST'), env('DB_USERNAME'), env('DB_PASSWORD'), env('DB_DATABASE'));
	    
	    //We want to make sure we are doing things in utf8 for special characters
		if(!mysqli_set_charset($con, "utf8")) {
		    printf("Error loading character set utf8: %s\n", mysqli_error($con));
		    exit();
		}

        $textOccurrences = DB::raw("select `text`, flid, COUNT(*) as count from ".env('DB_PREFIX')."text_fields where `fid`=$fid and `rid` in ($ridString)$flidSQL group by `flid`,`text` order by count ASC");
        $listOccurrences = DB::raw("select `option`, flid, COUNT(*) as count from ".env('DB_PREFIX')."list_fields where `fid`=$fid and `rid` in ($ridString)$flidSQL group by `flid`,`option` order by count ASC");
        $msListOccurrences = DB::raw("select `options`, flid, COUNT(*) as count from ".env('DB_PREFIX')."multi_select_list_fields where `fid`=$fid and `rid` in ($ridString)$flidSQL group by `flid`,`options` order by count ASC");
        $genListOccurrences = DB::raw("select `options`, flid, COUNT(*) as count from ".env('DB_PREFIX')."generated_list_fields where `fid`=$fid and `rid` in ($ridString)$flidSQL group by `flid`,`options` order by count ASC");
        $numberOccurrences = DB::raw("select `number`, flid, COUNT(*) as count from ".env('DB_PREFIX')."number_fields where `fid`=$fid and `rid` in ($ridString)$flidSQL group by `flid`,`number` order by count ASC");
        $assocOccurrences = DB::raw("select `record`, flid, COUNT(*) as count from ".env('DB_PREFIX')."associator_support where `fid`=$fid and `rid` in ($ridString)$flidSQL group by `flid`,`record` order by count ASC");
        $rAssocOccurrences = DB::raw("select `rid`, flid, COUNT(*) as count from ".env('DB_PREFIX')."associator_support where `fid`=$fid and `record` in ($ridString)$flidSQL group by `flid`,`rid` order by count ASC");

		//Because of the complex data in MS List, we break stuff up and then format
		$msListUnclean = $con->query($msListOccurrences);
		$msArray = [];
        while($occur = $msListUnclean->fetch_assoc()) {
	        $msFlid = $occur['flid'];
	        $msOpt = $occur['options'];
	        $msCnt = $occur['count'];
	        
            if(!isset($msArray[$msFlid]))
	            $msArray[$msFlid] = [];
            
	        if(strpos($msOpt, '[!]') !== false) {
		        $opts = explode('[!]', $msOpt);
		        
		        foreach($opts as $opt) {
			        if(isset($msArray[$msFlid][$opt]))
			        	$msArray[$msFlid][$opt] += $msCnt;
			        else
			        	$msArray[$msFlid][$opt] = $msCnt;
		        }
	        } else {
		        if(isset($msArray[$msFlid][$msOpt]))
		        	$msArray[$msFlid][$msOpt] += $msCnt;
		        else
		        	$msArray[$msFlid][$msOpt] = $msCnt;
	        }
        }
        foreach($msArray as $flid => $msCounts) {
	        foreach($msCounts as $msFilter => $msCount) {
		        if($msCount >= $count) {
	        		$filters[$flid][] = ['value'=>$msFilter,'type'=>'Multi-Select List','count'=>$msCount];
	        		$cnt++;
	        	}
	    	}
    	}
		//repeat
		$genListUnclean = $con->query($genListOccurrences);
		$genArray = [];
        while($occur = $genListUnclean->fetch_assoc()) {
	        $genFlid = $occur['flid'];
	        $genOpt = $occur['options'];
	        $genCnt = $occur['count'];
	        
            if(!isset($genArray[$genFlid]))
	            $genArray[$genFlid] = [];
            
	        if(strpos($genOpt, '[!]') !== false) {
		        $opts = explode('[!]', $genOpt);
		        
		        foreach($opts as $opt) {
			        if(isset($genArray[$genFlid][$opt]))
			        	$genArray[$genFlid][$opt] += $genCnt;
			        else
			        	$genArray[$genFlid][$opt] = $genCnt;
		        }
	        } else {
		        if(isset($genArray[$genFlid][$genOpt]))
		        	$genArray[$genFlid][$genOpt] += $genCnt;
		        else
		        	$genArray[$genFlid][$genOpt] = $genCnt;
	        }
        }
        foreach($genArray as $flid => $genCounts) {
	        foreach($genCounts as $genFilter => $genCount) {
		        if($genCount >= $count) {
	        		$filters[$flid][] = ['value'=>$genFilter,'type'=>'Generated List','count'=>$genCount];
	        		$cnt++;
	        	}
	    	}
    	}
        //End GenList/MS-List Madness
        
        $textUnclean = $con->query($textOccurrences);
        while($occur = $textUnclean->fetch_assoc()) {
            if($occur['count'] >= $count) {
                $filters[$occur['flid']][] = ['value'=>$occur['text'],'type'=>'Text','count'=>$occur['count']];
                $cnt++;
            }
        }
        $listUnclean = $con->query($listOccurrences);
        while($occur = $listUnclean->fetch_assoc()) {
            if($occur['count'] >= $count) {
                $filters[$occur['flid']][] = ['value'=>$occur['option'],'type'=>'List','count'=>$occur['count']];
                $cnt++;
            }
        }
        $numberUnclean = $con->query($numberOccurrences);
        while($occur = $numberUnclean->fetch_assoc()) {
            if($occur['count'] >= $count) {
                $value = (float)$occur['number'];
                $filters[$occur['flid']][] = ['value'=>$value,'type'=>'Number','count'=>$occur['count']];
                $cnt++;
            }
        }
        $assocUnclean = $con->query($assocOccurrences);
        while($occur = $assocUnclean->fetch_assoc()) {
            if($occur['count'] >= $count) {
                $value = Record::where('rid','=',$occur['record'])->first()->kid;
                $filters[$occur['flid']][] = ['value'=>$value,'type'=>'Associator','count'=>$occur['count']];
                $cnt++;
            }
        }
        $rAssocUnclean = $con->query($rAssocOccurrences);
        while($occur = $rAssocUnclean->fetch_assoc()) {
            if($occur['count'] >= $count) {
                $value = Record::where('rid', '=', $occur['rid'])->first()->kid;
                $filters[$occur['flid']][] = ['value'=>$value,'type'=>'Reverse Associator','count'=>$occur['count']];
                $cnt++;
            }
        }
        
        //We want to swap out the flids to slugs so it's more predictable/ readable
        foreach($filters as $flid => $results) {
	        $slug = FieldController::getField($flid)->slug;
	        $filters[$slug] = $results;
	        unset($filters[$flid]);
        }
        
        $filters['total'] = $cnt;
        
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
        foreach($fields as $jsonField) {
            $fieldSlug = $jsonField->name;
            $field = Field::where('slug', '=', $fieldSlug)->get()->first();

            $recRequest = $field->getTypedField()->setRestfulRecordData($jsonField, $field->flid, $recRequest, $uToken);
        }
        $recRequest['api'] = true;
        $recCon = new RecordController();
        //TODO::do something with this
        $response = $recCon->store($form->pid,$form->fid,$recRequest);
        return "Created Record: ";
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