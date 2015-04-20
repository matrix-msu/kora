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
	public function show($id)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}

}
