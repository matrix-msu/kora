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

class ImportController extends Controller {

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
