<?php namespace App\Http\Controllers;

use App\Association;
use App\AssociatorField;
use App\Form;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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
			flash()->overlay(trans('controller_association.admin'), trans('controller_association.whoops'));
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

    //These are the forms that we have given permission to search us
	static function getAllowedAssociations($fid){
		return Association::where('dataForm','=',$fid)->get()->all();
	}

    //These are the forms that have given us permission to search
	static function getAvailableAssociations($fid){
		return Association::where('assocForm','=',$fid)->get()->all();
	}

    static function getRequestableAssociations($fid){
        //get all forms
        $forms = Form::all();
        //get forms we already have permission to search
        $available = AssociationController::getAvailableAssociations($fid);
        //store things here
        $requestable = array();

        foreach($forms as $form){
            //if it's not the current form continue
            if($form->fid==$fid)
                continue;
            //if it's in the available associations already, no worries, continue
            $noworries = false;
            foreach($available as $avail){
                if($avail->dataForm==$form->fid)
                    $noworries = true;
            }
            if($noworries)
                continue;
            //if we get here, add to array
            array_push($requestable,$form);
        }

        return $requestable;
    }

    public function requestAccess($pid, $fid, Request $request){
        $myForm = FormController::getForm($fid);
        $myProj = ProjectController::getProject($myForm->pid);
        $theirForm = FormController::getForm($request->rfid);
        $theirProj = ProjectController::getProject($theirForm->pid);

        //form admins only
        if(!(\Auth::user()->isFormAdmin($myForm))) {
            flash()->overlay(trans('controller_association.admin'), trans('controller_association.whoops'));
            return redirect('projects'.$myProj->pid);
        }

        $group = $theirForm->adminGroup()->first();
        $users = $group->users()->get();

        foreach($users as $user){
            Mail::send('emails.request.assoc', compact('myForm','myProj','theirForm', 'theirProj'), function ($message) use($user) {
                $message->from(env('MAIL_FROM_ADDRESS'));
                $message->to($user->email);
                $message->subject('Kora Form Association Request');
            });
        }

        ////////REDIRECT BACK TO INDEX WITH SUCCESS MESSAGE

        //Associations to this form
        $assocs = AssociationController::getAllowedAssociations($fid);
        //Create an array of fids of those associations
        $associds = array();
        foreach($assocs as $a){
            array_push($associds,$a->assocForm);
        }
        //FIX THIS//
        flash()->overlay(trans('controller_association.requestsent'), trans('controller_association.success'));
        ///////////
        $form=$myForm;
        $project=$myProj;
        return view('association.index', compact('form', 'assocs', 'associds', 'project'));
    }

    public static function getAssociatedRecords($record){
        $assoc = AssociatorField::where('records','LIKE','%'.$record->kid.'%')->get();
        $records = array();
        foreach($assoc as $af){
            $rid = $af->rid;
            $rec = RecordController::getRecord($rid);
            array_push($records,$rec);
        }

        return $records;
    }
}
