<?php namespace App\Http\Controllers;

use App\Field;
use App\Http\Requests;
use App\Http\Requests\FieldRequest;
use App\Http\Controllers\Controller;
use App\FieldHelpers\FieldDefaults;

use Illuminate\Http\Request;

class FieldController extends Controller {

    /**
     * Show the form for creating a new resource.
     *
     * @param $pid
     * @param $fid
     * @return Response
     */
	public function create($pid, $fid)
	{
        if(!FormController::validProjForm($pid, $fid)){
            return redirect('projects');
        }

		$form = FormController::getForm($fid);
        return view('fields.create', compact('form'));
	}

    /**
     * Store a newly created resource in storage.
     *
     * @param FieldRequest $request
     * @return Response
     */
	public function store(FieldRequest $request)
    {
        $field = Field::Create($request->all());
        $field->options = FieldDefaults::getOptions($field->type);
        $field->default = FieldDefaults::getDefault($field->type);
        $field->save();

        //need to add field to layout xml
        $form = FormController::getForm($field->fid);
        $layout = explode('</layout>',$form->layout);
        $form->layout = $layout[0].'<id>'.$field->flid.'</id></layout>';
        $form->save();

        flash()->overlay('Your field has been successfully created!', 'Good Job');

        return redirect('projects/'.$field->pid.'/forms/'.$field->fid);
	}

    /**
     * Display the specified resource.
     *
     * @param $pid
     * @param $fid
     * @param $flid
     * @return Response
     * @internal param int $id
     */
	public function show($pid, $fid, $flid)
	{
        if(!FieldController::validProjFormField($pid, $fid, $flid)){
            return redirect('projects');
        }

        $field = FieldController::getField($flid);
        $form = FormController::getForm($fid);
        $proj = ProjectController::getProject($pid);

        if($field->type=="Text") {
            return view('fields.options.text', compact('field', 'form', 'proj'));
        }
	}

    /**
     * Show the form for editing the specified resource.
     *
     * @param $pid
     * @param $fid
     * @param $flid
     * @return Response
     * @internal param int $id
     */
	public function edit($pid, $fid, $flid)
	{
        if(!FieldController::validProjFormField($pid, $fid, $flid)){
            return redirect('projects');
        }

        $field = FieldController::getField($flid);

        return view('fields.edit', compact('field', 'fid', 'pid'));
	}

    /**
     * Update the specified resource in storage.
     *
     * @param $flid
     * @param FieldRequest $request
     * @return Response
     * @internal param int $id
     */
	public function update($pid, $fid, $flid, FieldRequest $request)
	{
        if(!FieldController::validProjFormField($pid, $fid, $flid)){
            return redirect('projects');
        }

		$field = FieldController::getField($flid);

        $field->update($request->all());

        flash()->overlay('Your field has been successfully updated!', 'Good Job!');

        return redirect('projects/'.$pid.'/forms/'.$fid);
	}

    public function updateRequired($pid, $fid, $flid, FieldRequest $request)
    {
        if(!FieldController::validProjFormField($pid, $fid, $flid)){
            return redirect('projects');
        }

        $field = FieldController::getField($flid);

        $field->required = $request->required;
        $field->save();

        flash()->success('Option updated!');

        return redirect('projects/'.$pid.'/forms/'.$fid.'/fields/'.$flid.'/options');
    }

    public function updateDefault($pid, $fid, $flid, FieldRequest $request)
    {
        if(!FieldController::validProjFormField($pid, $fid, $flid)){
            return redirect('projects');
        }

        $field = FieldController::getField($flid);

        $field->default = $request->default;
        $field->save();

        flash()->success('Option updated!');

        return redirect('projects/'.$pid.'/forms/'.$fid.'/fields/'.$flid.'/options');
    }

    public function updateOptions($pid, $fid, $flid, FieldRequest $request)
    {
        if(!FieldController::validProjFormField($pid, $fid, $flid)){
            return redirect('projects');
        }

        $field = FieldController::getField($flid);

        FieldController::setFieldOptions($field, $request->option, $request->value);

        flash()->success('Option updated!');

        return redirect('projects/'.$pid.'/forms/'.$fid.'/fields/'.$flid.'/options');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $pid
     * @param $fid
     * @param $flid
     * @return Response
     * @internal param int $id
     */
	public function destroy($pid, $fid, $flid)
	{
        if(!FieldController::validProjFormField($pid, $fid, $flid)){
            return redirect('projects/'.$pid.'forms/');
        }

        $field = FieldController::getField($flid);
        $field->delete();

        $form = FormController::getForm($fid);
        $layout = explode('<id>'.$field->flid.'</id>',$form->layout);
        $form->layout = $layout[0].$layout[1];
        $form->save();

        flash()->overlay('Your field has been successfully deleted!', 'Good Job!');
	}

    /**
     * Get field object for use in controller.
     *
     * @param $flid
     * @return mixed
     */
    public static function getField($flid)
    {
        $field = Field::where('flid', '=', $flid)->first();
        if(is_null($field)){
            $field = Field::where('slug','=',$flid)->first();
        }

        return $field;
    }

    /**
     * Validate that a field belongs to a form and project.
     *
     * @param $pid
     * @param $fid
     * @param $flid
     * @return bool
     */
    public static function validProjFormField($pid, $fid, $flid)
    {
        $field = FieldController::getField($flid);
        $form = FormController::getForm($fid);
        $proj = ProjectController::getProject($pid);

        if (!FormController::validProjForm($pid, $fid))
            return false;

        if (is_null($field) || is_null($form) || is_null($proj))
            return false;
        else if ($field->fid == $form->fid)
            return true;
        else
            return false;
    }

    public static function getFieldOption($field, $key){
        $options = $field->options;
        $tag = '[!'.$key.'!]';
        $value = explode($tag,$options)[1];

        return $value;
    }

    public static function setFieldOptions($field, $key, $value){
        $options = $field->options;
        $tag = '[!'.$key.'!]';
        $array = explode($tag,$options);

        $field->options = $array[0].$tag.$value.$tag.$array[2];
        $field->save();
    }
}
