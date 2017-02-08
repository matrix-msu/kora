<?php

namespace App\Http\Controllers;

use App\ComboListField;
use App\Field;
use App\Search;
use App\Token;
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
            $validated = false;

            //Get all the projects tokens
            $form = FormController::getForm($f->form);
            if(is_null($form)){
                return 'Illegal form provided: '.$f->form;
            }
            $project = ProjectController::getProject($form->pid);
            $tokens = $project->tokens()->get();

            //user provided token
            $token = $f->token;

            //compare
            foreach($tokens as $t){
                if($t->token == $token && $t->type == "search"){
                    $validated = true;
                }
            }

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
            $filters['data'] = isset($f->data) ? $f->data : true; //TODO: do we want data, or just info about the records themeselves
            $filters['meta'] = isset($f->meta) ? $f->meta : false; //TODO: do we want the field info sorted by it's metadata tag
            $filters['size'] = isset($f->size) ? $f->size : false; //do we want the number of records in the search result returned instead of data
            $filters['assoc'] = isset($f->assoc) ? $f->assoc : false; //TODO: do we want information back about associated records
            $filters['fields'] = isset($f->fields) ? $f->fields : 'ALL'; //which fields do we want data for
            $filters['sort'] = isset($f->sort) ? $f->sort : false; //TODO: how should the data be sorted

            //parse the query
            $query = $f->query;
            if(!isset($query)){
                //return all records...
            }else{
                //determine our search type
                switch($query->search){
                    case 'keyword':
                        //do a keyword search
                        if(!isset($query->keys)){
                            return "You must provide keywords in a keyword search for form: ".$form->name;
                        }

                        $keys = $query->keys;
                        $method = isset($query->method) ? $query->method : 'OR';

                        $search = new Search($form->pid, $form->fid, $keys, $method);

                        $rids = $search->formKeywordSearch2();

                        if($filters['size'])
                            return sizeof($rids);
                        else
                            return $this->populateRecords($rids,$filters);
                        break;
                    case 'advanced':
                        //do an advanced search
                        break;
                    case 'kid':
                        //do a kid search
                        if(!isset($query->kids)){
                            return "You must provide KIDs in a KID search for form: ".$form->name;
                        }

                        $kids = explode(",",$query->kids);
                        $rids = array();
                        for($i=0;$i<sizeof($kids);$i++){
                            $rids[$i] = explode("-",$kids[$i])[2];
                        }

                        return $this->populateRecords($rids,$filters);
                        break;
                    default:
                        return "You must provide a search query type for form: ".$form->name;
                        break;
                }
            }
        }

        return 'Successful search!';
    }

    public function create(Request $request){
        //get the form
        $f = $request->form;

        //next, we authenticate the form
        $validated = false;

        //Get all the projects tokens
        $form = FormController::getForm($f);
        if(is_null($form)){
            return 'Illegal form provided: '.$f;
        }
        $project = ProjectController::getProject($form->pid);
        $tokens = $project->tokens()->get();

        //user provided token
        $token = $request->token;

        //compare
        foreach($tokens as $t){
            if($t->token == $token && $t->type == "create"){
                $validated = true;
            }
        }

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
                    copy($currDir . '/thumbnail/' . $name, $newDir . '/thumbnail/' . $name);
                    copy($currDir . '/medium/' . $name, $newDir . '/medium/' . $name);
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
        $validated = false;

        //Get all the projects tokens
        $form = FormController::getForm($f);
        if(is_null($form)){
            return 'Illegal form provided: '.$f;
        }
        $project = ProjectController::getProject($form->pid);
        $tokens = $project->tokens()->get();

        //user provided token
        $token = $request->token;

        //compare
        foreach($tokens as $t){
            if($t->token == $token && $t->type == "edit"){
                $validated = true;
            }
        }

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
        $validated = false;

        //Get all the projects tokens
        $form = FormController::getForm($f);
        if(is_null($form)){
            return 'Illegal form provided: '.$f;
        }
        $project = ProjectController::getProject($form->pid);
        $tokens = $project->tokens()->get();

        //user provided token
        $token = $request->token;

        //compare
        foreach($tokens as $t){
            if($t->token == $token && $t->type == "delete"){
                $validated = true;
            }
        }

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
        $rids = json_encode($rids);

        $exec_string = env("BASE_PATH") . "python/api.py \"$rids\" \"$format\" '$fields'";
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
}
