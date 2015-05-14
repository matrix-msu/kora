<?php namespace App\Http\Controllers;

use App\Form;
use App\Http\Requests;
use App\Http\Requests\FormRequest;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class FormController extends Controller {

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create($pid)
	{
        $project = ProjectController::getProject($pid);
        return view('forms.create', compact('project')); //pass in
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(FormRequest $request)
	{
        $form = Form::create($request->all());

        flash()->overlay('Your form has been successfully created!','Good Job');

        return redirect('projects/'.$form->pid);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($pid, $fid)
	{
        if(!FormController::validProjForm($pid,$fid)){
            return redirect('projects');
        }

        $form = FormController::getForm($fid);
        $proj = ProjectController::getProject($pid);
        $projName = $proj->name;

        return view('forms.show', compact('form','projName'));
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($pid, $fid)
    {
        if(!FormController::validProjForm($pid,$fid)){
            return redirect('projects');
        }

        $form = FormController::getForm($fid);
        $proj = ProjectController::getProject($pid);
        $projName = $proj->name;

        return view('forms.edit', compact('form','projName'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id, FormRequest $request)
	{
        $form = FormController::getForm($id);

        $form->update($request->all());

        flash()->overlay('Your form has been successfully updated!','Good Job');

        return redirect('projects/'.$form->pid);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($pid, $fid)
	{
        if(!FormController::validProjForm($pid,$fid)){
            return redirect('projects');
        }
        
        $form = FormController::getForm($fid);
        $form->delete();

        flash()->overlay('Your form has been successfully deleted!','Good Job');
	}

    /**
     * Get form object for use in controller.
     *
     * @param $fid
     * @return mixed
     */
    public static function getForm($fid)
    {
        $form = Form::where('fid','=',$fid)->first();
        if(is_null($form)){
            $form = Form::where('slug','=',$fid)->first();
        }

        return $form;
    }

    /**
     * Validate that a form belongs to the project in use.
     *
     * @param $pid
     * @param $fid
     * @return bool
     */
    public static function validProjForm($pid, $fid)
    {
        $form = FormController::getForm($fid);
        $proj = ProjectController::getProject($pid);

        if(is_null($form) || is_null($proj))
            return false;
        else if($proj->pid==$form->pid)
            return true;
        else
            return false;
    }

}
