<?php namespace App\Http\Controllers;

use App\Field;
use App\Form;
use App\FormGroup;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Metadata;
use App\OptionPreset;
use App\Project;
use App\ProjectGroup;
use App\RecordPreset;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ImportController extends Controller {

    public function matchupFields($pid, $fid, Request $request){
        $form = FormController::getForm($fid);

        if(!\Auth::user()->admin && !\Auth::user()->isFormAdmin($form)){
            return 'Error: ';
        }

        //if zip file
        if(!is_null($request->file('files'))) {
            $zip = new \ZipArchive();
            $res = $zip->open($request->file('files'));
            if($res){
                $dir = env('BASE_PATH').'storage/app/tmpFiles/impU'.\Auth::user()->id;
                //clear import directory
                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($dir),
                    \RecursiveIteratorIterator::LEAVES_ONLY
                );
                foreach ($files as $file)
                {
                    // Skip directories (they would be added automatically)
                    if (!$file->isDir())
                    {
                        unlink($file);
                    }
                }
                $zip->extractTo($dir.'/');
                $zip->close();
            }
        }

        $xml = simplexml_load_file($request->file('records'));

        $xmlNames = array();
        $recordXMLobjs = array();

        foreach ($xml->children() as $record) {
            array_push($recordXMLobjs, $record->asXML());
            foreach ($record->children() as $fields){
                array_push($xmlNames,$fields->getName());
            }
        }

        $xmlNames = array_unique($xmlNames);

        $fields = $form->fields()->get();

        $table = '<div id="matchup_table" style="overflow: auto">';

        $table .= '<div>';
        $table .= '<span style="float:left;width:50%;margin-bottom:10px"><b>'.trans('controller_input.slug').'</b></span>';
        $table .= '<span style="float:left;width:50%;margin-bottom:10px"><b>'.trans('controller_input.xml').'</b></span>';
        $table .= '</div>';

        foreach ($fields as $field){
            $table .= '<div>';
            $table .= '<span style="float:left;width:50%;margin-bottom:10px">';
            $table .= $field->name.' ('.$field->slug.')';
            $table .= '</span>';
            $table .= '<input type="hidden" class="slugs" value="'.$field->slug.'">';
            $table .= '<span style="float:left;width:50%;margin-bottom:10px">';
            $table .= '<select class="tags">';
            $table .= '<option></option>';
            foreach($xmlNames as $name){
                if($field->slug==$name) {
                    $table .= '<option selected>' . $name . '</option>';
                }
                else
                    $table .= '<option>'.$name.'</option>';
            }
            $table .= '</select>';
            $table .= '</span>';
            $table .= '</div>';
        }

        $table .= '</div>';

        $table .= '<div class="form-group">';
           $table .= '<button type="button" class="form-control btn btn-primary" id="submit_records">'.trans('controller_input.records').'</button>';
        $table .= '</div>';

        $result = array();
        $result['records'] = $recordXMLobjs;
        $result['matchup'] = $table;

        return $result;
    }

    public function importRecord($pid, $fid, Request $request){
        $matchup = $request->table;

        $record = $request->record;

        $record =  simplexml_load_string($record);

        $recRequest = new Request();
        $recRequest['userId'] = \Auth::user()->id;

        $originKid = $record->attributes()->kid;
        $originRid = explode('-',$originKid)[2];

        foreach($record->children() as $key => $field){
            $fieldSlug = $matchup[$key];
            $flid = Field::where('slug', '=', $fieldSlug)->get()->first()->flid;
            $type = $field->attributes()->type;

            if($type=='Text' | $type=='Rich Text' | $type=='Number' | $type=='List')
                $recRequest[$flid] = (string)$field;
            else if($type=='Multi-Select List'){
                $recRequest[$flid] = (array)$field->value;
            } else if($type=='Generated List'){
                $recRequest[$flid] = (array)$field->value;
            } else if($type=='Combo List'){
                $values = array();
                foreach($field->Value as $val) {
                    if((string)$val->Field_One!='')
                        $fone = '[!f1!]'.(string)$val->Field_One.'[!f1!]';
                    else if(sizeof($val->Field_One->value)==1)
                        $fone = '[!f1!]'.(string)$val->Field_One->value.'[!f1!]';
                    else
                        $fone = '[!f1!]'.FieldController::listArrayToString((array)$val->Field_One->value).'[!f1!]';


                    if((string)$val->Field_Two!='')
                        $ftwo = '[!f2!]'.(string)$val->Field_Two.'[!f2!]';
                    else if(sizeof($val->Field_Two->value)==1)
                        $ftwo = '[!f2!]'.(string)$val->Field_Two->value.'[!f2!]';
                    else
                        $ftwo = '[!f2!]'.FieldController::listArrayToString((array)$val->Field_Two->value).'[!f2!]';

                    array_push($values,$fone.$ftwo);
                }
                $recRequest[$flid] = '';
                $recRequest[$flid.'_val'] = $values;
            } else if($type=='Date'){
                $recRequest['circa_'.$flid] = (string)$field->Circa;
                $recRequest['month_'.$flid] = (string)$field->Month;
                $recRequest['day_'.$flid] = (string)$field->Day;
                $recRequest['year_'.$flid] = (string)$field->Year;
                $recRequest['era_'.$flid] = (string)$field->Era;
                $recRequest[$flid] = '';
            } else if($type=='Schedule'){
                $events = array();
                foreach($field->Event as $event){
                    $string = $event->Title.': '.$event->Start.' - '.$event->End;
                    array_push($events,$string);
                }
                $recRequest[$flid] = $events;
            } else if($type=='Geolocator'){
                $geo = array();
                foreach($field->Location as $loc){
                    $string = '[Desc]'.$loc->Desc.'[Desc]';
                    $string .= '[LatLon]'.$loc->Lat.','.$loc->Lon.'[LatLon]';
                    $string .= '[UTM]'.$loc->Zone.':'.$loc->East.','.$loc->North.'[UTM]';
                    $string .= '[Address]'.$loc->Address.'[Address]';
                    array_push($geo,$string);
                }
                $recRequest[$flid] = $geo;
            } else if($type=='Documents' | $type=='Playlist' | $type=='Video' | $type=='3D-Model'){
                $files = array();
                $currDir = env('BASE_PATH').'storage/app/tmpFiles/impU'.\Auth::user()->id.'/r'.$originRid.'/fl'.$flid;
                $newDir = env('BASE_PATH').'storage/app/tmpFiles/f'.$flid.'u'.\Auth::user()->id;
                if(file_exists($newDir)) {
                    foreach (new \DirectoryIterator($newDir) as $file) {
                        if ($file->isFile()) {
                            unlink($newDir.'/'.$file->getFilename());
                        }
                    }
                }else{
                    mkdir($newDir, 0775, true);
                }
                foreach($field->File as $file){
                    $name = (string)$file->Name;
                    //move file from imp temp to tmp files
                    copy($currDir.'/'.$name,$newDir.'/'.$name);
                    //add input for this file
                    array_push($files, $name);
                }
                var_dump($files);
                $recRequest['file'.$flid] = $files;
                $recRequest[$flid] = 'f'.$flid.'u'.\Auth::user()->id;
            } else if($type=='Gallery'){
                $files = array();
                $currDir = env('BASE_PATH').'storage/app/tmpFiles/impU'.\Auth::user()->id.'/r'.$originRid.'/fl'.$flid;
                $newDir = env('BASE_PATH').'storage/app/tmpFiles/f'.$flid.'u'.\Auth::user()->id;
                if(file_exists($newDir)) {
                    foreach (new \DirectoryIterator($newDir) as $file) {
                        if ($file->isFile()) {
                            unlink($newDir.'/'.$file->getFilename());
                        }
                    }
                    if(file_exists($newDir.'/thumbnail')) {
                        foreach (new \DirectoryIterator($newDir.'/thumbnail') as $file) {
                            if ($file->isFile()) {
                                unlink($newDir.'/thumbnail/'.$file->getFilename());
                            }
                        }
                    }
                    if(file_exists($newDir.'/medium')) {
                        foreach (new \DirectoryIterator($newDir.'/medium') as $file) {
                            if ($file->isFile()) {
                                unlink($newDir.'/medium/'.$file->getFilename());
                            }
                        }
                    }
                }else{
                    mkdir($newDir, 0775, true);
                    mkdir($newDir . '/thumbnail', 0775, true);
                    mkdir($newDir . '/medium', 0775, true);
                }
                foreach($field->File as $file){
                    $name = (string)$file->Name;
                    //move file from imp temp to tmp files
                    copy($currDir.'/'.$name,$newDir.'/'.$name);
                    copy($currDir.'/thumbnail/'.$name,$newDir.'/thumbnail/'.$name);
                    copy($currDir.'/medium/'.$name,$newDir.'/medium/'.$name);
                    //add input for this file
                    array_push($files, $name);
                }
                var_dump($files);
                $recRequest['file'.$flid] = $files;
                $recRequest[$flid] = 'f'.$flid.'u'.\Auth::user()->id;
            }
        }

        $recCon = new RecordController();
        $recCon->store($pid,$fid,$recRequest);

        return '';
    }

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function importForm($pid, Request $request){
        $project = ProjectController::getProject($pid);

        if(!\Auth::user()->admin && !\Auth::user()->isProjectAdmin($project)){
            return redirect('projects/'.$pid);
        }

        $file = $request->file('form');

        $fileArray = json_decode(file_get_contents($file));

        //dd($fileArray);

        $form = new Form();

        $form->pid = $project->pid;
        $form->name = $fileArray->name;
        if (Form::where('slug', '=', $fileArray->slug)->exists()) {
            $unique = false;
            $i=1;
            while(!$unique){
                if(Form::where('slug', '=', $fileArray->slug.$i)->exists()){
                    $i++;
                }else{
                    $form->slug = $fileArray->slug.$i;
                    $unique = true;
                }
            }
        }else{
            $form->slug = $fileArray->slug;
        }
        $form->description = $fileArray->desc;
        $form->layout = $fileArray->layout;
        $form->preset = $fileArray->preset;
        $form->public_metadata = $fileArray->metadata;

        $form->save();

        //make admin group
        $admin = $this->makeFormAdminGroup($form);
        $form->adminGID = $admin->id;
        $form->save();

        //record presets
        $recPresets = $fileArray->recPresets;

        foreach($recPresets as $pre) {
            $rec = new RecordPreset();

            $rec->fid = $form->fid;
            $rec->name = $pre->name;
            $rec->preset = $pre->preset;

            $rec->save();
        }

        $fields = $fileArray->fields;

        foreach($fields as $fieldArray){
            $field = new Field();

            $field->pid = $project->pid;
            $field->fid = $form->fid;
            $field->type = $fieldArray->type;
            $field->name = $fieldArray->name;
            if (Field::where('slug', '=', $fieldArray->slug)->exists()) {
                $unique = false;
                $i=1;
                while(!$unique){
                    if(Field::where('slug', '=', $fieldArray->slug.$i)->exists()){
                        $i++;
                    }else{
                        $field->slug = $fieldArray->slug.$i;
                        $unique = true;
                    }
                }
            }else{
                $field->slug = $fieldArray->slug;
            }
            $field->desc = $fieldArray->desc;
            $field->required = $fieldArray->required;
            $field->default = $fieldArray->default;
            $field->options = $fieldArray->options;

            $field->save();

            //fix layout
            $form->layout = str_replace('<ID>'.$fieldArray->slug.'</ID>','<ID>'.$field->flid.'</ID>',$form->layout);
            $form->save();

            //metadata
            if($fieldArray->metadata!=""){
                $meta = new Metadata();
                $meta->flid = $field->flid;
                $meta->pid = $project->pid;
                $meta->fid = $form->fid;
                $meta->name = $fieldArray->metadata;
                $meta->save();
            }
        }

        flash()->overlay(trans('controller_form.create'),trans('controller_form.goodjob'));

        return redirect('projects/'.$form->pid);
    }

    private function importFormNoFile($pid, $fileArray){
        $project = ProjectController::getProject($pid);

        //dd($fileArray);

        $form = new Form();

        $form->pid = $project->pid;
        $form->name = $fileArray->name;
        if (Form::where('slug', '=', $fileArray->slug)->exists()) {
            $unique = false;
            $i=1;
            while(!$unique){
                if(Form::where('slug', '=', $fileArray->slug.$i)->exists()){
                    $i++;
                }else{
                    $form->slug = $fileArray->slug.$i;
                    $unique = true;
                }
            }
        }else{
            $form->slug = $fileArray->slug;
        }
        $form->description = $fileArray->desc;
        $form->layout = $fileArray->layout;
        $form->preset = $fileArray->preset;
        $form->public_metadata = $fileArray->metadata;

        $form->save();

        //make admin group
        $admin = $this->makeFormAdminGroup($form);
        $form->adminGID = $admin->id;
        $form->save();

        //record presets
        $recPresets = $fileArray->recPresets;

        foreach($recPresets as $pre) {
            $rec = new RecordPreset();

            $rec->fid = $form->fid;
            $rec->name = $pre->name;
            $rec->preset = $pre->preset;

            $rec->save();
        }

        $fields = $fileArray->fields;

        foreach($fields as $fieldArray){
            $field = new Field();

            $field->pid = $project->pid;
            $field->fid = $form->fid;
            $field->type = $fieldArray->type;
            $field->name = $fieldArray->name;
            if (Field::where('slug', '=', $fieldArray->slug)->exists()) {
                $unique = false;
                $i=1;
                while(!$unique){
                    if(Field::where('slug', '=', $fieldArray->slug.$i)->exists()){
                        $i++;
                    }else{
                        $field->slug = $fieldArray->slug.$i;
                        $unique = true;
                    }
                }
            }else{
                $field->slug = $fieldArray->slug;
            }
            $field->desc = $fieldArray->desc;
            $field->required = $fieldArray->required;
            $field->default = $fieldArray->default;
            $field->options = $fieldArray->options;

            $field->save();

            //fix layout
            $form->layout = str_replace('<ID>'.$fieldArray->slug.'</ID>','<ID>'.$field->flid.'</ID>',$form->layout);
            $form->save();

            //metadata
            if($fieldArray->metadata!=""){
                $meta = new Metadata();
                $meta->flid = $field->flid;
                $meta->pid = $project->pid;
                $meta->fid = $form->fid;
                $meta->name = $fieldArray->metadata;
                $meta->save();
            }
        }
    }

    private function makeFormAdminGroup(Form $form)
    {
        $groupName = $form->name;
        $groupName .= ' Admin Group';

        $adminGroup = new FormGroup();
        $adminGroup->name = $groupName;
        $adminGroup->fid = $form->fid;
        $adminGroup->save();

        $formProject = $form->project()->first();
        $projectAdminGroup = $formProject->adminGroup()->first();

        $projectAdmins = $projectAdminGroup->users()->get();
        $idArray = [];

        //Add all current project admins to the form's admin group.
        foreach($projectAdmins as $projectAdmin)
            $idArray[] .= $projectAdmin->id;


        $idArray = array_unique(array_merge(array(\Auth::user()->id), $idArray));

        if (!empty($idArray))
            $adminGroup->users()->attach($idArray);

        $adminGroup->create = 1;
        $adminGroup->edit = 1;
        $adminGroup->delete = 1;
        $adminGroup->ingest = 1;
        $adminGroup->modify = 1;
        $adminGroup->destroy = 1;

        $adminGroup->save();

        return $adminGroup;
    }

    public function importProject(Request $request){
        if(!\Auth::user()->admin){
            return redirect('projects/');
        }

        $file = $request->file('project');

        $fileArray = json_decode(file_get_contents($file));

        //dd($fileArray);

        $proj = new Project();

        $proj->name = $fileArray->name;
        if (Project::where('slug', '=', $fileArray->slug)->exists()) {
            $unique = false;
            $i=1;
            while(!$unique){
                if(Project::where('slug', '=', $fileArray->slug.$i)->exists()){
                    $i++;
                }else{
                    $proj->slug = $fileArray->slug.$i;
                    $unique = true;
                }
            }
        }else{
            $proj->slug = $fileArray->slug;
        }
        $proj->description = $fileArray->description;
        $proj->active = 1;

        $proj->save();

        //make admin group
        $admin = $this->makeProjAdminGroup($proj);
        $proj->adminGID = $admin->id;
        $proj->save();

        $optPresets = $fileArray->optPresets;

        foreach($optPresets as $opt) {
            $pre = new OptionPreset();

            $pre->pid = $proj->pid;
            $pre->type = $opt->type;
            $pre->name = $opt->name;
            $pre->preset = $opt->preset;
            $pre->shared = $opt->shared;

            $pre->save();
        }

        $forms = $fileArray->forms;

        foreach($forms as $form) {
            $this->importFormNoFile($proj->pid,$form);
        }

        flash()->overlay(trans('controller_project.create'),trans('controller_project.goodjob'));

        return redirect('projects');
    }

    private function makeProjAdminGroup($project)
    {
        $groupName = $project->name;
        $groupName .= ' Admin Group';

        $adminGroup = new ProjectGroup();
        $adminGroup->name = $groupName;
        $adminGroup->pid = $project->pid;
        $adminGroup->save();

        $adminGroup->users()->attach(array(\Auth::user()->id));

        $adminGroup->create = 1;
        $adminGroup->edit = 1;
        $adminGroup->delete = 1;

        $adminGroup->save();

        return $adminGroup;
    }

}
