<?php

namespace App\Http\Controllers;

use App\ComboListField;
use App\DateField;
use App\Field;
use App\ListField;
use App\NumberField;
use App\Record;
use App\Search;
use App\TextField;
use App\Token;
use App\User;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RestfulController extends Controller
{
    /**
     * Standard output formats.
     * @var string.
     */
    const JSON = "JSON";
    const XML = "XML";

    /**
     * @var array
     */
    const VALID_FORMATS = [ self::JSON, self::XML ];
    const VALID_SORT = ['Text','List','Number','Date'];

    public function getKoraVersion(){
        $instInfo = DB::table("versions")->first();

        if(is_null($instInfo)){
            return "Failed to retrieve Kora version";
        }else{
            return $instInfo->version;
        }
    }

    public function getProjectForms($pid){
        if(!ProjectController::validProj($pid)){
            return 'Invalid Project: '.$pid;
        }

        $project = ProjectController::getProject($pid);

        $formMods = $project->forms()->get();
        $forms = array();

        foreach($formMods as $form){
            $fArray = array();

            $fArray['name'] = $form->name;
            $fArray['nickname'] = $form->slug;
            $fArray['description'] = $form->description;

            $forms[$form->fid] = $fArray;
        }

        return json_encode($forms);
    }

    public function getFormFields($pid, $fid){
        if(!FormController::validProjForm($pid,$fid)){
            return "Invalid Project/Form Pair: ".$pid." ~ ".$fid;
        }

        $form = FormController::getForm($fid);

        $fieldMods = $form->fields()->get();
        $fields = array();

        foreach($fieldMods as $field){
            $fArray = array();

            $fArray['name'] = $field->name;
            $fArray['type'] = $field->type;
            $fArray['nickname'] = $field->slug;
            $fArray['description'] = $field->desc;

            $fields[$field->flid] = $fArray;
        }

        return json_encode($fields);
    }

    public function getFormRecordCount($pid, $fid){
        if(!FormController::validProjForm($pid,$fid)){
            return "Invalid Project/Form Pair: ".$pid." ~ ".$fid;
        }

        $form = FormController::getForm($fid);

        $count = $form->records()->count();

        return $count;
    }

    public function search(Request $request){

        //get the forms
        $forms = json_decode($request->forms);

        //next, we authenticate each form
        foreach($forms as $f){
            //next, we authenticate the form
            $form = FormController::getForm($f->form);
            if(is_null($form)){
                return 'Illegal form provided: '.$f->form;
            }
            $validated = $this->validateToken($form,$f->token,"search");

            //Authentication failed
            if(!$validated){
                return "The provided token is invalid for form: ".$form->name;
            }
        }

        //now we actually do searches per form
        foreach($forms as $f){
            //initialize form
            $form = FormController::getForm($f->form);
            //things we will be returning
            $filters = array();
            $filters['data'] = isset($f->data) ? $f->data : true; //do we want data, or just info about the records theme selves
            $filters['meta'] = isset($f->meta) ? $f->meta : false; //get meta data about record
            $filters['size'] = isset($f->size) ? $f->size : false; //do we want the number of records in the search result returned instead of data
            $filters['assoc'] = isset($f->assoc) ? $f->assoc : false; //TODO: do we want information back about associated records
            $filters['fields'] = isset($f->fields) ? $f->fields : 'ALL'; //which fields do we want data for
            $filters['sort'] = isset($f->sort) ? $f->sort : null; //how should the data be sorted

            //parse the query
            if(!isset($f->query)){
                //return all records
                $returnRIDS = Record::where("fid","=",$form->fid)->lists('rid')->all();

                if(!is_null($filters['sort'])){
                    $sortArray = explode(',',$filters['sort']);
                    $returnRIDS = $this->sort_rids($returnRIDS,$sortArray);

                    if(!$returnRIDS)
                        return "Illegal field type or invalid field provided for sort in form: " . $form->name;
                }

                //see if we are returning the
                if ($filters['size'])
                    return sizeof($returnRIDS);
                else
                    return $this->populateRecords($returnRIDS, $filters);
            }else {
                $queries = $f->query;
                $resultSets = array();

                foreach ($queries as $query) {
                    //determine our search type
                    switch ($query->search) {
                        case 'keyword':
                            //do a keyword search
                            if (!isset($query->keys)) {
                                return "You must provide keywords in a keyword search for form: " . $form->name;
                            }

                            $keys = $query->keys;
                            $method = isset($query->method) ? $query->method : 'OR';

                            $search = new Search($form->pid, $form->fid, $keys, $method);

                            $rids = $search->formKeywordSearch();

                            $negative = isset($query->not) ? $query->not : false;
                            if($negative){
                                $rids = $this->negative_results($form,$rids);
                            }

                            array_push($resultSets,$rids);
                            break;
                        case 'advanced':
                            //do an advanced search
                            if (!isset($query->fields)) {
                                return "You must provide fields in an advanced search for form: " . $form->name;
                            }

                            $fields = $query->fields;

                            foreach($fields as $flid => $data) {
                                $field = FieldController::getField($flid);
                                $id = $field->flid;

                                $request->request->add([$id.'_dropdown' => 'on']);
                                $request->request->add([$id.'_valid' => 1]);

                                switch($field->type){
                                    case 'Text':
                                        $request->request->add([$id.'_input' => $data->input]);
                                        break;
                                    case 'Rich Text':
                                        $request->request->add([$id.'_input' => $data->input]);
                                        break;
                                    case 'Number':
                                        if(isset($data->left))
                                            $leftNum = $data->left;
                                        else
                                            $leftNum = '';
                                        $request->request->add([$id.'_left' => $leftNum]);
                                        if(isset($data->right))
                                            $rightNum = $data->right;
                                        else
                                            $rightNum = '';
                                        $request->request->add([$id.'_right' => $rightNum]);
                                        if(isset($data->invert))
                                            $invert = $data->invert;
                                        else
                                            $invert = 0;
                                        $request->request->add([$id.'_' => $invert]);
                                        break;
                                    case 'List':
                                        $request->request->add([$id.'_input' => $data->input]);
                                        break;
                                    case 'Multi-Select List':
                                        $request->request->add([$id.'_input' => $data->input]);
                                        break;
                                    case 'Generated List':
                                        $request->request->add([$id.'_input' => $data->input]);
                                        break;
                                    case 'Combo List':
                                        //TODO
                                        break;
                                    case 'Date':
                                        if(isset($data->begin_month))
                                            $beginMonth = $data->begin_month;
                                        else
                                            $beginMonth = '';
                                        if(isset($data->begin_day))
                                            $beginDay = $data->begin_day;
                                        else
                                            $beginDay = '';
                                        if(isset($data->begin_year))
                                            $beginYear = $data->begin_year;
                                        else
                                            $beginYear = '';
                                        $request->request->add([$id.'_begin_month' => $beginMonth]);
                                        $request->request->add([$id.'_begin_day' => $beginDay]);
                                        $request->request->add([$id.'_begin_year' => $beginYear]);

                                        if(isset($data->end_month))
                                            $endMonth = $data->end_month;
                                        else
                                            $endMonth = '';
                                        if(isset($data->end_day))
                                            $endDay = $data->end_day;
                                        else
                                            $endDay = '';
                                        if(isset($data->end_year))
                                            $endYear = $data->end_year;
                                        else
                                            $endYear = '';
                                        $request->request->add([$id.'_end_month' => $endMonth]);
                                        $request->request->add([$id.'_end_day' => $endDay]);
                                        $request->request->add([$id.'_end_year' => $endYear]);

                                        break;
                                    case 'Schedule':
                                        if(isset($data->begin_month))
                                            $beginMonth = $data->begin_month;
                                        else
                                            $beginMonth = '';
                                        if(isset($data->begin_day))
                                            $beginDay = $data->begin_day;
                                        else
                                            $beginDay = '';
                                        if(isset($data->begin_year))
                                            $beginYear = $data->begin_year;
                                        else
                                            $beginYear = '';
                                        $request->request->add([$id.'_begin_month' => $beginMonth]);
                                        $request->request->add([$id.'_begin_day' => $beginDay]);
                                        $request->request->add([$id.'_begin_year' => $beginYear]);

                                        if(isset($data->end_month))
                                            $endMonth = $data->end_month;
                                        else
                                            $endMonth = '';
                                        if(isset($data->end_day))
                                            $endDay = $data->end_day;
                                        else
                                            $endDay = '';
                                        if(isset($data->end_year))
                                            $endYear = $data->end_year;
                                        else
                                            $endYear = '';
                                        $request->request->add([$id.'_end_month' => $endMonth]);
                                        $request->request->add([$id.'_end_day' => $endDay]);
                                        $request->request->add([$id.'_end_year' => $endYear]);

                                        break;
                                    case 'Documents' | 'Gallery'  | 'Playlist' | 'Video' | '3D-Model':
                                        $request->request->add([$id.'_input' => $data->input]);
                                        break;
                                    case 'Geolocator':
                                        $request->request->add([$id.'_type' => $data->type]);

                                        if(isset($data->lat))
                                            $lat = $data->lat;
                                        else
                                            $lat = '';
                                        $request->request->add([$id.'_lat' => $lat]);
                                        if(isset($data->lon))
                                            $lon = $data->lon;
                                        else
                                            $lon = '';
                                        $request->request->add([$id.'_lon' => $lon]);
                                        if(isset($data->zone))
                                            $zone = $data->zone;
                                        else
                                            $zone = '';
                                        $request->request->add([$id.'_zone' => $zone]);
                                        if(isset($data->east))
                                            $east = $data->east;
                                        else
                                            $east = '';
                                        $request->request->add([$id.'_east' => $east]);
                                        if(isset($data->north))
                                            $north = $data->north;
                                        else
                                            $north = '';
                                        $request->request->add([$id.'_north' => $north]);
                                        if(isset($data->address))
                                            $address = $data->address;
                                        else
                                            $address = '';
                                        $request->request->add([$id.'_address' => $address]);

                                        $request->request->add([$id.'_range' => $data->range]);
                                        break;
                                    case 'Associator':
                                        //TODO
                                        break;
                                    default:
                                        break;
                                }

                                $advSearch = new AdvancedSearchController();

                                $rids = $advSearch->search($form->pid, $form->fid, $request);

                                $negative = isset($query->not) ? $query->not : false;
                                if($negative){
                                    $rids = $this->negative_results($form,$rids);
                                }

                                array_push($resultSets,$rids);
                            }
                            break;
                        case 'kid':
                            //do a kid search
                            if (!isset($query->kids)) {
                                return "You must provide KIDs in a KID search for form: " . $form->name;
                            }

                            $kids = explode(",", $query->kids);
                            $rids = array();
                            for ($i = 0; $i < sizeof($kids); $i++) {
                                $rids[$i] = explode("-", $kids[$i])[2];
                            }

                            $negative = isset($query->not) ? $query->not : false;
                            if($negative){
                                $rids = $this->negative_results($form,$rids);
                            }

                            array_push($resultSets,$rids);
                            break;
                        default:
                            return "You must provide a search query type for form: " . $form->name;
                            break;
                    }
                }

                //perform all the and/or logic for search types
                $returnRIDS = array();

                if(!isset($f->logic)){
                    //OR IT ALL TOGETHER
                    foreach($resultSets as $result){
                        $returnRIDS = array_merge($returnRIDS,$result);
                    }
                    $returnRIDS = array_unique($returnRIDS);
                }else {
                    //do the work!!!!
                    $logic = $f->logic;

                    $returnRIDS = $this->logic_recursive($logic,$resultSets);
                }

                //sort
                if(!is_null($filters['sort'])){
                    $sortArray = explode(',',$filters['sort']);
                    $returnRIDS = $this->sort_rids($returnRIDS,$sortArray);

                    if(!$returnRIDS)
                        return "Illegal field type or invalid field provided for sort in form: " . $form->name;
                }

                //see if we are returning the
                if ($filters['size'])
                    return sizeof($returnRIDS);
                else
                    return $this->populateRecords($returnRIDS, $filters);
            }
        }

        return 'Successful search!';
    }

    private function negative_results($form, $rids){
        $negatives = Record::where('fid','=',$form->fid)->whereNotIn('rid',$rids)->lists('rid')->all();
        return $negatives;
    }

    private function sort_rids($rids, $sortFields){
        //get field
        $fieldSlug = $sortFields[0];
        $direction = $sortFields[1];

        $newOrderArray = array();
        $noSortValue = array();

        if($fieldSlug=="kora_meta_owner"){
            foreach ($rids as $rid) {
                $record = RecordController::getRecord($rid);
                $owner = User::where('id','=',$record->owner)->first();

                $newOrderArray[$rid] = $owner->username;
            }
        }else if($fieldSlug=="kora_meta_created"){
            foreach ($rids as $rid) {
                $record = RecordController::getRecord($rid);
                $created = $record->created_at;

                $newOrderArray[$rid] = $created;
            }
        }else if($fieldSlug=="kora_meta_updated"){
            foreach ($rids as $rid) {
                $record = RecordController::getRecord($rid);
                $updated = $record->updated_at;

                $newOrderArray[$rid] = $updated;
            }
        }else if($fieldSlug=="kora_meta_kid"){
            foreach ($rids as $rid) {
                $newOrderArray[$rid] = $rid;
            }
        }else {
            $field = FieldController::getField($fieldSlug);
            if (!in_array(($field->type), self::VALID_SORT)) {
                return false;
            }

            //for each rid
            foreach ($rids as $rid) {
                //based on type
                switch ($field->type) {
                    case 'Text':
                        $tf = TextField::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first();
                        if (is_null($tf))
                            array_push($noSortValue, $rid);
                        else
                            $newOrderArray[$rid] = $tf->text;
                        break;
                    case 'List':
                        $lf = ListField::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first();
                        if (is_null($lf))
                            array_push($noSortValue, $rid);
                        else
                            $newOrderArray[$rid] = $lf->option;
                        break;
                    case 'Number':
                        $nf = NumberField::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first();
                        if (is_null($nf))
                            array_push($noSortValue, $rid);
                        else
                            $newOrderArray[$rid] = $nf->number;
                        break;
                    case 'Date':
                        $df = DateField::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first();
                        if (is_null($df))
                            array_push($noSortValue, $rid);
                        else
                            $newOrderArray[$rid] = \DateTime::createFromFormat("Y-m-d", $df->year . "-" . $df->month . "-" . $df->day);;
                        break;
                    default:
                        return false;
                        break;
                }
            }
        }

        //sort new array
        $extraData = array($sortFields,$direction,$newOrderArray);
        uksort($newOrderArray, function($a_key,$b_key) use ($extraData){
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
            if(is_a($a,'DateTime') | (is_numeric($a) && is_numeric($b))){
                if($a==$b){
                    if(!empty($copySort)){
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
                    }
                    else
                        return 0;
                } else if($a>$b){
                    return 1*$dir;
                } else if($a<$b){
                    return -1*$dir;
                }
            }else{
                $answer = strcmp($a, $b)*$dir;
                if($answer==0 && !empty($copySort)){
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
                }
                else
                    return $answer;
            }
        });

        //convert to plain array of rids
        $finalResult = array_keys($newOrderArray);

        return $finalResult;
    }

    private function logic_recursive($logicArray, $ridSets){
        $returnRIDS = array();
        $firstRIDS = array();
        $secondRIDS = array();

        //get first array of rids, or recurse till it becomes array
        if(is_array($logicArray[0]))
            $firstRIDS = $this->logic_recursive($logicArray[0],$ridSets);
        else{
            $firstRIDS = $ridSets[$logicArray[0]];
        }

        //get second array of rids, or recurse till it becomes array
        if(is_array($logicArray[2]))
            $secondRIDS = $this->logic_recursive($logicArray[2],$ridSets);
        else{
            $secondRIDS = $ridSets[$logicArray[2]];
        }

        $operator = $logicArray[1];

        if($operator=="AND"){
            $returnRIDS = array_intersect($firstRIDS,$secondRIDS);
        }else if($operator=="OR"){
            $returnRIDS = array_merge($firstRIDS,$secondRIDS);
        }

        return array_unique($returnRIDS);
    }

    public function create(Request $request){
        //get the form
        $f = $request->form;

        //next, we authenticate the form
        $form = FormController::getForm($f);
        if(is_null($form)){
            return 'Illegal form provided: '.$f->form;
        }
        $validated = $this->validateToken($form,$request->token,"create");

        //Authentication failed
        if(!$validated){
            return "The provided token is invalid for form: ".$form->name;
        }

        //Gather field data to insert
        if(!isset($request->fields)){
            return "You must provide data to insert into: ".$form->name;
        }

        $fields = json_decode($request->fields);

        $recRequest = new Request();
        $recRequest['userId'] = 1;

        foreach ($fields as $field) {
            $fieldSlug = $field->name;
            $flid = Field::where('slug', '=', $fieldSlug)->get()->first()->flid;
            $type = $field->type;

            if ($type == 'Text'){
                $recRequest[$flid] = $field->text;
            } else if ($type == 'Rich Text'){
                $recRequest[$flid] = $field->richtext;
            } else if ($type == 'Number'){
                $recRequest[$flid] = $field->number;
            } else if ($type == 'List') {
                $recRequest[$flid] = $field->option;
            } else if ($type == 'Multi-Select List') {
                $recRequest[$flid] = $field->options;
            } else if ($type == 'Generated List') {
                $recRequest[$flid] = $field->options;
            } else if ($type == 'Combo List') {
                $values = array();
                $nameone = ComboListField::getComboFieldName(FieldController::getField($flid), 'one');
                $nametwo = ComboListField::getComboFieldName(FieldController::getField($flid), 'two');
                foreach ($field->values as $val) {
                    if (!is_array($val[$nameone]))
                        $fone = '[!f1!]' . $val[$nameone] . '[!f1!]';
                    else
                        $fone = '[!f1!]' . FieldController::listArrayToString($val[$nameone]) . '[!f1!]';


                    if (!is_array($val[$nametwo]))
                        $ftwo = '[!f2!]' . $val[$nametwo] . '[!f2!]';
                    else
                        $ftwo = '[!f2!]' . FieldController::listArrayToString($val[$nametwo]) . '[!f2!]';

                    array_push($values, $fone . $ftwo);
                }
                $recRequest[$flid] = '';
                $recRequest[$flid . '_val'] = $values;
            } else if ($type == 'Date') {
                $recRequest['circa_' . $flid] = $field->circa;
                $recRequest['month_' . $flid] = $field->month;
                $recRequest['day_' . $flid] = $field->day;
                $recRequest['year_' . $flid] = $field->year;
                $recRequest['era_' . $flid] = $field->era;
                $recRequest[$flid] = '';
            } else if ($type == 'Schedule') {
                $events = array();
                foreach ($field->events as $event) {
                    $string = $event['title'] . ': ' . $event['start'] . ' - ' . $event['end'];
                    array_push($events, $string);
                }
                $recRequest[$flid] = $events;
            } else if ($type == 'Geolocator') {
                $geo = array();
                foreach ($field->locations as $loc) {
                    $string = '[Desc]' . $loc['desc'] . '[Desc]';
                    $string .= '[LatLon]' . $loc['lat'] . ',' . $loc['lon'] . '[LatLon]';
                    $string .= '[UTM]' . $loc['zone'] . ':' . $loc['east'] . ',' . $loc['north'] . '[UTM]';
                    $string .= '[Address]' . $loc['address'] . '[Address]';
                    array_push($geo, $string);
                }
                $recRequest[$flid] = $geo;
            }/* else if ($type == 'Documents' | $type == 'Playlist' | $type == 'Video' | $type == '3D-Model') {
                $files = array();
                $currDir = env('BASE_PATH') . 'storage/app/tmpFiles/impU' . \Auth::user()->id . '/r' . $originRid . '/fl' . $flid;
                $newDir = env('BASE_PATH') . 'storage/app/tmpFiles/f' . $flid . 'u' . \Auth::user()->id;
                if (file_exists($newDir)) {
                    foreach (new \DirectoryIterator($newDir) as $file) {
                        if ($file->isFile()) {
                            unlink($newDir . '/' . $file->getFilename());
                        }
                    }
                } else {
                    mkdir($newDir, 0775, true);
                }
                foreach ($field['files'] as $file) {
                    $name = $file['name'];
                    //move file from imp temp to tmp files
                    copy($currDir . '/' . $name, $newDir . '/' . $name);
                    //add input for this file
                    array_push($files, $name);
                }
                $recRequest['file' . $flid] = $files;
                $recRequest[$flid] = 'f' . $flid . 'u' . \Auth::user()->id;
            } else if ($type == 'Gallery') {
                $files = array();
                $currDir = env('BASE_PATH') . 'storage/app/tmpFiles/impU' . \Auth::user()->id . '/r' . $originRid . '/fl' . $flid;
                $newDir = env('BASE_PATH') . 'storage/app/tmpFiles/f' . $flid . 'u' . \Auth::user()->id;
                if (file_exists($newDir)) {
                    foreach (new \DirectoryIterator($newDir) as $file) {
                        if ($file->isFile()) {
                            unlink($newDir . '/' . $file->getFilename());
                        }
                    }
                    if (file_exists($newDir . '/thumbnail')) {
                        foreach (new \DirectoryIterator($newDir . '/thumbnail') as $file) {
                            if ($file->isFile()) {
                                unlink($newDir . '/thumbnail/' . $file->getFilename());
                            }
                        }
                    }
                    if (file_exists($newDir . '/medium')) {
                        foreach (new \DirectoryIterator($newDir . '/medium') as $file) {
                            if ($file->isFile()) {
                                unlink($newDir . '/medium/' . $file->getFilename());
                            }
                        }
                    }
                } else {
                    mkdir($newDir, 0775, true);
                    mkdir($newDir . '/thumbnail', 0775, true);
                    mkdir($newDir . '/medium', 0775, true);
                }
                foreach ($field['files'] as $file) {
                    $name = $file['name'];
                    //move file from imp temp to tmp files
                    copy($currDir . '/' . $name, $newDir . '/' . $name);
<<<<<<< HEAD
                    copy($currDir . '/thumbnail/' . $name, $newDir . '/thumbnail/' . $name);
                    copy($currDir . '/medium/' . $name, $newDir . '/medium/' . $name);
=======
                    $smallParts = explode('x',FieldController::getFieldOption($field,'ThumbSmall'));
                    $tImage = new \Imagick($newDir . '/' . $name);
                    $tImage->thumbnailImage($smallParts[0],$smallParts[1],true);
                    $tImage->writeImage($newDir . '/thumbnail/' . $name);
                    $largeParts = explode('x',FieldController::getFieldOption($field,'ThumbLarge'));
                    $mImage = new \Imagick($newDir . '/' . $name);
                    $mImage->thumbnailImage($largeParts[0],$largeParts[1],true);
                    $mImage->writeImage($newDir . '/medium/' . $name);
>>>>>>> 48230995e5ed2496d95699ba3c490b87c383c26e
                    //add input for this file
                    array_push($files, $name);
                }
                $recRequest['file' . $flid] = $files;
                $recRequest[$flid] = 'f' . $flid . 'u' . \Auth::user()->id;
            }*/
        }

        //dd($recRequest);
        $recRequest['api'] = true;
        $recCon = new RecordController();
        $response = $recCon->store($form->pid,$form->fid,$recRequest);

        return 'Created record: '.$response;
    }

    public function edit(Request $request){
        //get the form
        $f = $request->form;

        //next, we authenticate the form
        $form = FormController::getForm($f);
        if(is_null($form)){
            return 'Illegal form provided: '.$f->form;
        }
        $validated = $this->validateToken($form,$request->token,"edit");

        //Authentication failed
        if(!$validated){
            return "The provided token is invalid for form: ".$form->name;
        }

        //Gather field data to insert
        if(!isset($request->kid)){
            return "You must provide a record kid for: ".$form->name;
        }

        //Gather field data to insert
        if(!isset($request->fields)){
            return "You must provide data to insert into: ".$form->name;
        }

        $fields = json_decode($request->fields);
        $record = RecordController::getRecordByKID($request->kid);

        if(is_null($record)){
            return 'Illegal record provided: '.$request->kid;
        }

        $recRequest = new Request();
        $recRequest['userId'] = 1;

        foreach ($fields as $field) {
            $fieldSlug = $field->name;
            $flid = Field::where('slug', '=', $fieldSlug)->get()->first()->flid;
            $type = $field->type;

            if ($type == 'Text'){
                $recRequest[$flid] = $field->text;
            } else if ($type == 'Rich Text'){
                $recRequest[$flid] = $field->richtext;
            } else if ($type == 'Number'){
                $recRequest[$flid] = $field->number;
            } else if ($type == 'List') {
                $recRequest[$flid] = $field->option;
            } else if ($type == 'Multi-Select List') {
                $recRequest[$flid] = $field->options;
            } else if ($type == 'Generated List') {
                $recRequest[$flid] = $field->options;
            } else if ($type == 'Combo List') {
                $values = array();
                $nameone = ComboListField::getComboFieldName(FieldController::getField($flid), 'one');
                $nametwo = ComboListField::getComboFieldName(FieldController::getField($flid), 'two');
                foreach ($field->values as $val) {
                    if (!is_array($val[$nameone]))
                        $fone = '[!f1!]' . $val[$nameone] . '[!f1!]';
                    else
                        $fone = '[!f1!]' . FieldController::listArrayToString($val[$nameone]) . '[!f1!]';


                    if (!is_array($val[$nametwo]))
                        $ftwo = '[!f2!]' . $val[$nametwo] . '[!f2!]';
                    else
                        $ftwo = '[!f2!]' . FieldController::listArrayToString($val[$nametwo]) . '[!f2!]';

                    array_push($values, $fone . $ftwo);
                }
                $recRequest[$flid] = '';
                $recRequest[$flid . '_val'] = $values;
            } else if ($type == 'Date') {
                $recRequest['circa_' . $flid] = $field->circa;
                $recRequest['month_' . $flid] = $field->month;
                $recRequest['day_' . $flid] = $field->day;
                $recRequest['year_' . $flid] = $field->year;
                $recRequest['era_' . $flid] = $field->era;
                $recRequest[$flid] = '';
            } else if ($type == 'Schedule') {
                $events = array();
                foreach ($field->events as $event) {
                    $string = $event['title'] . ': ' . $event['start'] . ' - ' . $event['end'];
                    array_push($events, $string);
                }
                $recRequest[$flid] = $events;
            } else if ($type == 'Geolocator') {
                $geo = array();
                foreach ($field->locations as $loc) {
                    $string = '[Desc]' . $loc['desc'] . '[Desc]';
                    $string .= '[LatLon]' . $loc['lat'] . ',' . $loc['lon'] . '[LatLon]';
                    $string .= '[UTM]' . $loc['zone'] . ':' . $loc['east'] . ',' . $loc['north'] . '[UTM]';
                    $string .= '[Address]' . $loc['address'] . '[Address]';
                    array_push($geo, $string);
                }
                $recRequest[$flid] = $geo;
            }/* else if ($type == 'Documents' | $type == 'Playlist' | $type == 'Video' | $type == '3D-Model') {
                $files = array();
                $currDir = env('BASE_PATH') . 'storage/app/tmpFiles/impU' . \Auth::user()->id . '/r' . $originRid . '/fl' . $flid;
                $newDir = env('BASE_PATH') . 'storage/app/tmpFiles/f' . $flid . 'u' . \Auth::user()->id;
                if (file_exists($newDir)) {
                    foreach (new \DirectoryIterator($newDir) as $file) {
                        if ($file->isFile()) {
                            unlink($newDir . '/' . $file->getFilename());
                        }
                    }
                } else {
                    mkdir($newDir, 0775, true);
                }
                foreach ($field['files'] as $file) {
                    $name = $file['name'];
                    //move file from imp temp to tmp files
                    copy($currDir . '/' . $name, $newDir . '/' . $name);
                    //add input for this file
                    array_push($files, $name);
                }
                $recRequest['file' . $flid] = $files;
                $recRequest[$flid] = 'f' . $flid . 'u' . \Auth::user()->id;
            } else if ($type == 'Gallery') {
                $files = array();
                $currDir = env('BASE_PATH') . 'storage/app/tmpFiles/impU' . \Auth::user()->id . '/r' . $originRid . '/fl' . $flid;
                $newDir = env('BASE_PATH') . 'storage/app/tmpFiles/f' . $flid . 'u' . \Auth::user()->id;
                if (file_exists($newDir)) {
                    foreach (new \DirectoryIterator($newDir) as $file) {
                        if ($file->isFile()) {
                            unlink($newDir . '/' . $file->getFilename());
                        }
                    }
                    if (file_exists($newDir . '/thumbnail')) {
                        foreach (new \DirectoryIterator($newDir . '/thumbnail') as $file) {
                            if ($file->isFile()) {
                                unlink($newDir . '/thumbnail/' . $file->getFilename());
                            }
                        }
                    }
                    if (file_exists($newDir . '/medium')) {
                        foreach (new \DirectoryIterator($newDir . '/medium') as $file) {
                            if ($file->isFile()) {
                                unlink($newDir . '/medium/' . $file->getFilename());
                            }
                        }
                    }
                } else {
                    mkdir($newDir, 0775, true);
                    mkdir($newDir . '/thumbnail', 0775, true);
                    mkdir($newDir . '/medium', 0775, true);
                }
                foreach ($field['files'] as $file) {
                    $name = $file['name'];
                    //move file from imp temp to tmp files
                    copy($currDir . '/' . $name, $newDir . '/' . $name);
                    copy($currDir . '/thumbnail/' . $name, $newDir . '/thumbnail/' . $name);
                    copy($currDir . '/medium/' . $name, $newDir . '/medium/' . $name);
                    $smallParts = explode('x',FieldController::getFieldOption($field,'ThumbSmall'));
                    $tImage = new \Imagick($newDir . '/' . $name);
                    $tImage->thumbnailImage($smallParts[0],$smallParts[1],true);
                    $tImage->writeImage($newDir . '/thumbnail/' . $name);
                    $largeParts = explode('x',FieldController::getFieldOption($field,'ThumbLarge'));
                    $mImage = new \Imagick($newDir . '/' . $name);
                    $mImage->thumbnailImage($largeParts[0],$largeParts[1],true);
                    $mImage->writeImage($newDir . '/medium/' . $name);
                    //add input for this file
                    array_push($files, $name);
                }
                $recRequest['file' . $flid] = $files;
                $recRequest[$flid] = 'f' . $flid . 'u' . \Auth::user()->id;
            }*/
        }

        //dd($recRequest);
        $recRequest['api'] = true;
        $recCon = new RecordController();
        $recCon->update($form->pid,$form->fid,$record->rid,$recRequest);

        return 'Modified record: '.$request->kid;
    }

    public function delete(Request $request){
        //get the form
        $f = $request->form;

        //next, we authenticate the form
        $form = FormController::getForm($f);
        if(is_null($form)){
            return 'Illegal form provided: '.$f->form;
        }
        $validated = $this->validateToken($form,$request->token,"delete");

        //Authentication failed
        if(!$validated){
            return "The provided token is invalid for form: ".$form->name;
        }

        //Gather records to delete
        if(!isset($request->kids)){
            return "You must provide KIDs to delete from: ".$form->name;
        }

        $kids = explode(",",$request->kids);
        $recsToDelete = array();
        for($i=0;$i<sizeof($kids);$i++){
            $rid = explode("-",$kids[$i])[2];
            $record = RecordController::getRecord($rid);

            if(is_null($record)){
                return 'Non-existent record provided: '.$kids[$i];
            }else{
                array_push($recsToDelete,$record);
            }
        }

        foreach($recsToDelete as $record){
            $record->delete();
        }

        return 'DELETE SUCCESS!';
    }

    //mimics the export python functionality to populate records
    private function populateRecords($rids,$filters,$format = self::JSON){
        $format = strtoupper($format);

        if ( ! self::isValidFormat($format)) {
            return null;
        }

        if($filters['fields'] == "ALL"){
            $fields = json_encode(array());
        }else{
            $fields = json_encode($filters['fields']);
        }
        if($filters['meta'])
            $meta = 'True';
        else
            $meta = 'False';

        if($filters['data'])
            $data = 'True';
        else
            $data = 'False';
        $rids = json_encode($rids);

        $exec_string = env("BASE_PATH") . "python/api.py \"$rids\" \"$format\" '$fields' \"$meta\" \"$data\"";
        exec($exec_string, $output);

        return $output[0];
    }

    /**
     * @param string $format
     * @return bool, true if valid.
     */
    private function isValidFormat($format) {
        return in_array(($format), self::VALID_FORMATS);
    }

    private function validateToken($form,$token,$permission){
        //Get all the projects tokens
        $project = ProjectController::getProject($form->pid);
        $tokens = $project->tokens()->get();

        //compare
        foreach($tokens as $t){
            if($t->token == $token && $t->$permission){
                return true;
            }
        }

        return false;
    }
}
