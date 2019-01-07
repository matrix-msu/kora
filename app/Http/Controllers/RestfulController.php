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
            if(Form::where('fid','=',$f->form)->count()==1)
                $piece = 'fid';
            else if(Form::where('slug','=',$f->form)->count()==1)
                $piece = 'slug';
            else
                return response()->json(["status"=>false,"error"=>"Invalid Form: ".$f->form],500);

            $validated = $this->validateToken(Form::where($piece,'=',$f->form)->value('pid'),$f->token,"search");
            //Authentication failed
            if(!$validated)
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
            if($globalSort)
                array_push($fidsGlobal, $form->fid);

            //things we will be returning
            //NOTE: Items marked ***, will be overwritten when using globalSort
            $filters = array();
            $filters['data'] = isset($f->data) ? $f->data : true; //do we want data, or just info about the records theme selves***
            $filters['meta'] = isset($f->meta) ? $f->meta : false; //get meta data about record***
            $filters['size'] = isset($f->size) ? $f->size : false; //do we want the number of records in the search result returned instead of data
            $filters['assoc'] = isset($f->assoc) ? $f->assoc : false; //do we want information back about associated records***
            $filters['revAssoc'] = isset($f->revAssoc) ? $f->revAssoc : true; //do we want information back about reverse associations for XML OUTPUT
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
                //It's apparently quicker to use our negative results function to all forms RIDs so, here we go
                $returnRIDS = $this->negative_results($form,array());

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
                        $resultsGlobal[] = $this->populateRecords($returnRIDS, $filters, $apiFormat, $form->fid);
                    else {
                        $resultsGlobal[] = json_decode($this->populateRecords($returnRIDS, $filters, $apiFormat, $form->fid));
                    }
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
                                $request->request->add([$fieldModel->flid => 1]);
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
                                if(!Record::isKIDPattern($kids[$i])) {
                                    array_push($minorErrors,"Illegal KID ($kids[$i]) in a KID search for form: ". $form->name);
                                    continue;
                                }
                                $rid = explode("-", $kids[$i])[2];
                                $record = Record::where('rid',$rid)->get()->first();
                                if(is_null($record) || $record->fid != $form->fid)
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
                            if(!isset($query->kids))
                                return response()->json(["status"=>false,"error"=>"You must provide KIDs in a Legacy KID search for form: " . $form->name],500);
                            $kids = $query->kids;
                            $rids = array();
                            for($i = 0; $i < sizeof($kids); $i++) {
                                $legacy_kid = $kids[$i];
                                $record = Record::where('legacy_kid','=',$legacy_kid)->get()->first();
                                if(is_null($record) || $record->fid != $form->fid)
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
                        $resultsGlobal[] = $this->populateRecords($returnRIDS, $filters, $apiFormat, $form->fid);
                    else
                        $resultsGlobal[] = json_decode($this->populateRecords($returnRIDS, $filters, $apiFormat, $form->fid));
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
            $resultsGlobal = json_decode($this->populateRecords($globalSorted, $filters, $apiFormat, $fidsGlobal));
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
	    $con = mysqli_connect(
	        config('database.connections.mysql.host'),
            config('database.connections.mysql.username'),
            config('database.connections.mysql.password'),
            config('database.connections.mysql.database')
        );

	    //We want to make sure we are doing things in utf8 for special characters
		if(!mysqli_set_charset($con, "utf8")) {
		    printf("Error loading character set utf8: %s\n", mysqli_error($con));
		    exit();
		}

		if($ridString!="")
			$select = "SELECT `rid` from ".config('database.connections.mysql.prefix')."records WHERE `fid`=".$form->fid." AND `rid` NOT IN ($ridString)";
		else
			$select = "SELECT `rid` from ".config('database.connections.mysql.prefix')."records WHERE `fid`=".$form->fid;

		$negUnclean = $con->query($select);

		while($row = $negUnclean->fetch_assoc()) {
			array_push($returnRIDS, $row['rid']);
		}
        mysqli_free_result($negUnclean);

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

            if($fieldSlug=='kora_meta_owner') {
                $selectJoins .= "LEFT JOIN ".$prefix."_users as us ON us.id=rec.owner ";
                array_push($selectOrdArr, "`username` $direction");
            } else if($fieldSlug=='kora_meta_created') {
                array_push($selectOrdArr, "`created_at` $direction");
            } else if($fieldSlug=='kora_meta_updated') {
                array_push($selectOrdArr, "`updated_at` $direction");
            } else if($fieldSlug=='kora_meta_kid') {
                array_push($selectOrdArr, "`rid` $direction");
            } else {
                $field = FieldController::getField($fieldSlug);
                if(is_null($field) || !$field->isSortable())
                    return false;
                $typedField = $field->getTypedField();

                $flid = $field->flid;
                $type = $typedField->getSortColumn();
                $table = $prefix.$typedField->getTable();

                if(!is_null($type)) {
                    $selectJoins .= "LEFT JOIN ".$table." as field".$flid." ON field".$flid.".rid=rec.rid and field".$flid.".`flid`=".$flid." ";
                    array_push($selectOrdArr, "field".$flid.".`$type` IS NULL, field".$flid.".`$type` $direction");
                }
            }
        }
        $selectOrders = implode(', ',$selectOrdArr);

        $select = "SELECT rec.`rid` from kora3_records as rec $selectJoins";
        $select .= "WHERE rec.`rid` IN ($ridString) ORDER BY $selectOrders";

        $sort = $con->query($select);

        while($row = $sort->fetch_assoc()) {
            array_push($newOrderArray,$row['rid']);
        }
        mysqli_free_result($sort);

        return $newOrderArray;
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
            array_push($newOrderArray,$row['rid']);
        }
        mysqli_free_result($sort);

        return $newOrderArray;
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
     * @param  int $count - Minimum occurances required for a filter to return (Maybe reimplement later?)
     * @param  array $flids - Specifies the fields we need filters from
     * @return array - The array of filters
     */
    private function getDataFilters($fid, $rids, $count, $flids) {
        if(empty($rids))
            return ['total' => 0];

        $filters = [];
        $ridIndex = [];
        foreach($rids as $r) {
            $ridIndex[$r] = '';
        }
        $flidSQL = '';
        $convert = [];

        if($flids != 'ALL') {
            //In case slugs are provided, we need flids
            $convertedFlids = array();
            foreach($flids as $fl) {
                $thisField = FieldController::getField($fl);
                array_push($convertedFlids, $thisField->flid); //error bad fields, not 100% sure how we'll get it up a level
                $convert[$thisField->flid] = $thisField->slug;
            }

            $flidString = implode(',',$convertedFlids);
            $flidSQL = " and `flid` in ($flidString)";
        } else {
            $flids = Form::find($fid)->fields()->pluck('flid')->toArray();
            $flidString = implode(',',$flids);
            $flidSQL = " and `flid` in ($flidString)";

            foreach($flids as $id){
                $convert[$id] = FieldController::getField($id)->slug;
            }
        }

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

        if(sizeof($rids)<=500) {
            $ridString = implode(',',$rids);
            $wherePiece = "`rid` IN ($ridString)";
        } else
            $wherePiece = "`fid`=$fid";

        $textOccurrences = "select `text`, `flid`, `rid` from ".$prefix."text_fields where $wherePiece $flidSQL";
        $listOccurrences = "select `option`, `flid`, `rid` from ".$prefix."list_fields where $wherePiece $flidSQL";
        $msListOccurrences = "select `options`, `flid`, `rid` from ".$prefix."multi_select_list_fields where $wherePiece $flidSQL";
        $genListOccurrences = "select `options`, `flid` from ".$prefix."generated_list_fields where $wherePiece $flidSQL";
        $numberOccurrences = "select `number`, `flid`, `rid` from ".$prefix."number_fields where $wherePiece $flidSQL";
        $dateOccurrences = "select `month`, `day`, `year`, `flid`, `rid` from ".$prefix."date_fields where $wherePiece $flidSQL";
        $assocOccurrences = "select s.`flid`, r.`kid`, r.`rid` from ".$prefix."associator_support as s left join kora3_records as r on s.`record`=r.`rid` where s.$wherePiece and s.`flid` in ($flidString)";
        $rAssocOccurrences = "select s.`flid`, r.`kid`, r.`rid` from ".$prefix."associator_support as s left join kora3_records as r on s.`rid`=r.`rid` where s.$wherePiece and s.`flid` in ($flidString)";

        //Because of the complex data in MS List, we break stuff up and then format
        $msListUnclean = $con->query($msListOccurrences);
        while($occur = $msListUnclean->fetch_assoc()) {
            $flid = $occur['flid'];
            $rid = $occur['rid'];
            $msOpt = $occur['options'];

            if(!array_key_exists($rid,$ridIndex))
                continue;

            $opts = explode('[!]', $msOpt);

            foreach($opts as $opt) {
                if(!isset($filters[$convert[$flid]][$opt]))
                    $filters[$convert[$flid]][$opt] = 1;
                else
                    $filters[$convert[$flid]][$opt] += 1;
            }
        }
        mysqli_free_result($msListUnclean);

        //repeat for gen list
        $genListUnclean = $con->query($genListOccurrences);
        while($occur = $genListUnclean->fetch_assoc()) {
            $flid = $occur['flid'];
            $rid = $occur['rid'];
            $gsOpt = $occur['options'];

            if(!array_key_exists($rid,$ridIndex))
                continue;

            $opts = explode('[!]', $gsOpt);

            foreach($opts as $opt) {
                if(!isset($filters[$convert[$flid]][$opt]))
                    $filters[$convert[$flid]][$opt] = 1;
                else
                    $filters[$convert[$flid]][$opt] += 1;
            }
        }
        mysqli_free_result($genListUnclean);

        $dateUnclean = $con->query($dateOccurrences);
        while($occur = $dateUnclean->fetch_assoc()) {
            $flid = $occur['flid'];
            $rid = $occur['rid'];

            if(!array_key_exists($rid,$ridIndex))
                continue;

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

            if(!isset($filters[$convert[$flid]][$value]))
                $filters[$convert[$flid]][$value] = 1;
            else
                $filters[$convert[$flid]][$value] += 1;
        }
        mysqli_free_result($dateUnclean);

        $textUnclean = $con->query($textOccurrences);
        while($occur = $textUnclean->fetch_assoc()) {
            $flid = $occur['flid'];
            $rid = $occur['rid'];
            $value = $occur['text'];

            if(!array_key_exists($rid,$ridIndex))
                continue;

            if(!isset($filters[$convert[$flid]][$value]))
                $filters[$convert[$flid]][$value] = 1;
            else
                $filters[$convert[$flid]][$value] += 1;
        }
        mysqli_free_result($textUnclean);

        $listUnclean = $con->query($listOccurrences);
        while($occur = $listUnclean->fetch_assoc()) {
            $flid = $occur['flid'];
            $rid = $occur['rid'];
            $value = $occur['option'];

            if(!array_key_exists($rid,$ridIndex))
                continue;

            if(!isset($filters[$convert[$flid]][$value]))
                $filters[$convert[$flid]][$value] = 1;
            else
                $filters[$convert[$flid]][$value] += 1;
        }
        mysqli_free_result($listUnclean);

        $numberUnclean = $con->query($numberOccurrences);
        while($occur = $numberUnclean->fetch_assoc()) {
            $flid = $occur['flid'];
            $rid = $occur['rid'];
            $value = (float)$occur['number'];

            if(!array_key_exists($rid,$ridIndex))
                continue;

            if(!isset($filters[$convert[$flid]][$value]))
                $filters[$convert[$flid]][$value] = 1;
            else
                $filters[$convert[$flid]][$value] += 1;
        }
        mysqli_free_result($numberUnclean);

        $assocUnclean = $con->query($assocOccurrences);
        while($occur = $assocUnclean->fetch_assoc()) {
            $flid = $occur['flid'];
            $rid = $occur['rid'];
            $value = $occur['kid'];

            if(!array_key_exists($rid,$ridIndex))
                continue;

            if(!isset($filters[$convert[$flid]][$value]))
                $filters[$convert[$flid]][$value] = 1;
            else
                $filters[$convert[$flid]][$value] += 1;
        }
        mysqli_free_result($assocUnclean);

        $rAssocUnclean = $con->query($rAssocOccurrences);
        while($occur = $rAssocUnclean->fetch_assoc()) {
            $flid = $occur['flid'];
            $rid = $occur['rid'];
            $value = $occur['kid'];

            if(!array_key_exists($rid,$ridIndex))
                continue;

            if(!isset($filters[$convert[$flid]][$value]))
                $filters[$convert[$flid]][$value] = 1;
            else
                $filters[$convert[$flid]][$value] += 1;
        }
        mysqli_free_result($rAssocUnclean);

        if($count != 1) {
            $newFilters = [];
            foreach($filters as $flid => $valCnt) {
                foreach($valCnt as $val => $cnt) {
                    if($cnt >= $count)
                        $newFilters[$flid][$val] = $cnt;
                }
            }
            $filters = $newFilters;
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
     * @param  int $fid - Form ID
     * @return string - Path to the results file
     */
    private function populateRecords($rids,$filters,$format = self::JSON,$fid) {
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
                'fields' => $filters['fields']
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
        $output = $expControl->exportFormRecordData($fid,$rids,$format,true,$options);

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