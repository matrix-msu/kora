<?php namespace App\Http\Controllers;

use App\FieldHelpers\FieldValidation;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Record;
use App\TextField;
use Illuminate\Http\Request;

class RecordController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index($pid, $fid)
	{
        if(!FormController::validProjForm($pid,$fid)){
            return redirect('projects');
        }

        $form = FormController::getForm($fid);

        return view('records.index', compact('form'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create($pid, $fid)
	{
        if(!FormController::validProjForm($pid,$fid)){
            return redirect('projects');
        }

        $form = FormController::getForm($fid);

        return view('records.create', compact('form'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store($pid, $fid, Request $request)
	{
        if(!FormController::validProjForm($pid,$fid)){
            return redirect('projects');
        }

        foreach($request->all() as $key => $value){
            if($key=='_token'){
                continue;
            }
            $message = FieldValidation::validateField($key, $value);
            if($message != ''){
                flash()->error($message);

                return redirect()->back()->withInput();
            }
        }

        $record = new Record();
        $record->pid = $pid;
        $record->fid = $fid;
        $record->owner = 'koraadmin';
        $record->save(); //need to save to create rid needed to make kid
        $record->kid = $pid.'-'.$fid.'-'.$record->rid;
        $record->save();

        foreach($request->all() as $key => $value){
            if($key=='_token'){
                continue;
            }
            $field = FieldController::getField($key);
            if($field->type=='Text'){
                $tf = new TextField();
                $tf->flid = $field->flid;
                $tf->rid = $record->rid;
                $tf->text = $value;
                $tf->save();
            }
        }

        flash()->overlay('Your record has been successfully created!', 'Good Job!');

        return redirect('projects/'.$pid.'/forms/'.$fid.'/records');
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
