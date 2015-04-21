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
	public function store($pid, FormRequest $request)
	{
        $project = ProjectController::getProject($pid);
        $fid = $project->nextForm;

        $form = new Form;
        $form->fid = $fid;
        $form->pid = $pid;
        $form->name = $request->name;
        $form->slug = $request->slug;
        $form->description = $request->description;
        $form->nextField = $request->nextField;
        $form->save();

        $project->increment('nextForm');

        flash()->overlay('Your form has been successfully created!','Good Job');

        return redirect('projects/'.$pid);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($pid, $fid)
	{
        $projName = ProjectController::getProject($pid)->name;
		$form = FormController::getForm($pid, $fid);

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
        $projName = ProjectController::getProject($pid)->name;
        $form = FormController::getForm($pid, $fid);

        return view('forms.edit', compact('form','projName'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($pid, FormRequest $request)
	{
        $form = FormController::getForm($pid, $request->fid);

        $form->name = $request->name;
        $form->slug = $request->slug;
        $form->description = $request->description;
        $form->nextField = $request->nextField;
        $form->save();

        flash()->overlay('Your form has been successfully updated!','Good Job');

        return redirect('projects/'.$pid);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($pid, $fid)
	{
        $form = FormController::getForm($pid, $fid);
        $form->delete();

        flash()->overlay('Your form has been successfully deleted!','Good Job');
	}

    public static function getForm($pid, $fid){
        $pid = ProjectController::getProject($pid)->pid;
        $form = Form::where('fid','=',$fid)->where('pid','=',$pid)->first();
        if(is_null($form)){
            $form = Form::where('slug','=',$fid)->where('pid','=',$pid)->first();
        }

        return $form;
    }

}
