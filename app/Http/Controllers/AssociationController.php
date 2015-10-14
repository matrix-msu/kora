<?php namespace App\Http\Controllers;

use App\Association;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class AssociationController extends Controller {

	/**
	 * User must be logged in to access views in this controller.
	 */
	public function __construct()
	{
		$this->middleware('auth');
		$this->middleware('active');
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index($pid, $fid)
	{
		$form = FormController::getForm($fid);
		$project = $form->project()->first();

		if(!(\Auth::user()->isFormAdmin($form))) {
			flash()->overlay('You are not an admin for that form.', 'Whoops.');
			return redirect('projects'.$project->pid);
		}

		//Associations to this form
		$assocs = AssociationController::getAllowedAssociations($fid);
		//Create an array of fids of those associations
		$associds = array();
		foreach($assocs as $a){
			array_push($associds,$a->assocForm);
		}
		//dd($associds);
		return view('association.index', compact('form', 'assocs', 'associds', 'project'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create($pid, $fid, Request $request)
	{
		$assocFormID = $request->assocfid;

		$assoc = new Association();
		$assoc->dataForm = $fid;
		$assoc->assocForm = $assocFormID;
		$assoc->save();
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		//
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
	public function destroy($pid, $fid, Request $request)
	{
		$assocFormID = $request->assocfid;

		$assoc = Association::where('dataForm','=',$fid)->where('assocForm','=',$assocFormID)->first();

		$assoc->delete();
	}

	static function getAllowedAssociations($fid){
		return Association::where('dataForm','=',$fid)->get()->all();
	}

	static function getAvailableAssociations($fid){
		return Association::where('assocForm','=',$fid)->get()->all();
	}
}
