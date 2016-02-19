<?php namespace App\Http\Controllers;

use App\Field;
use App\Form;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Metadata;
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
        $form->adminGID = 1;
        $form->name = $fileArray->name;
        $form->slug = $fileArray->slug.rand(10000,99999); //fix some time
        $form->description = $fileArray->desc;
        $form->layout = $fileArray->layout;
        $form->preset = $fileArray->preset;
        $form->public_metadata = $fileArray->metadata;

        $form->save();

        $fields = $fileArray->fields;

        foreach($fields as $fieldArray){
            $field = new Field();

            $field->pid = $project->pid;
            $field->fid = $form->fid;
            $field->type = $fieldArray->type;
            $field->name = $fieldArray->name;
            $field->slug = $fieldArray->slug.rand(10000,99999);
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

}
