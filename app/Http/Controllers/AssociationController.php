<?php namespace App\Http\Controllers;

use App\Association;
use App\AssociatorField;
use App\Form;
use App\Record;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AssociationController extends Controller {

    /*
    |--------------------------------------------------------------------------
    | Association Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles management of form associations for use in
    | associator fields
    |
    */

    /**
     * Constructs controller and makes sure user is authenticated.
     */
	public function __construct() {
		$this->middleware('auth');
		$this->middleware('active');
	}

    /**
     * Gets the view for the manage associations page, including any existing permissions.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @return View
     */
	public function index($pid, $fid) {
        if(!FormController::validProjForm($pid, $fid))
            return redirect('projects/'.$pid)->with('k3_global_error', 'form_invalid');

		$form = FormController::getForm($fid);
		$project = $form->project()->first();

		if(!(\Auth::user()->isFormAdmin($form)))
			return redirect('projects/'.$pid)->with('k3_global_error', 'not_form_admin');

		//Associations to this form
		$assocs = self::getAllowedAssociations($fid);
		//Create an array of fids of those associations
		$associds = array();
		foreach($assocs as $a) {
			array_push($associds,$a->assocForm);
		}

		return view('association.index', compact('form', 'assocs', 'associds', 'project'));
	}

    /**
     * Creates a new association permission.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  Request $request
     */
	public function create($pid, $fid, Request $request) {
		$assocFormID = $request->assocfid;

		$assoc = new Association();
		$assoc->dataForm = $fid;
		$assoc->assocForm = $assocFormID;
		$assoc->save();
	}

    /**
     * Delete an existing association permission.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  Request $request
     */
	public function destroy($pid, $fid, Request $request) {
		$assocFormID = $request->assocfid;

		$assoc = Association::where('dataForm','=',$fid)->where('assocForm','=',$assocFormID)->first();

		$assoc->delete();
	}

    /**
     * Gets all forms that a given form has given permission to.
     *
     * @param  int $fid - Form ID of form granting permission
     * @return Collection - The forms that this form has given permission
     */
    static function getAllowedAssociations($fid) {
		return Association::where('dataForm','=',$fid)->get()->all();
	}

    /**
     * Gets all forms that a given form can associate to.
     *
     * @param  int $fid - Form ID
     * @return Collection - The forms this form has access to
     */
    static function getAvailableAssociations($fid) {
		return Association::where('assocForm','=',$fid)->get()->all();
	}

    /**
     * Gets a list of forms that a given form doesn't have access to yet.
     *
     * @param  int $fid - Form ID
     * @return array - Forms that can be requested for access
     */
    public static function getRequestableAssociations($fid) {
        //get all forms
        $forms = Form::all();
        //get forms we already have permission to search
        $available = self::getAvailableAssociations($fid);
        //store things here
        $requestable = array();

        foreach($forms as $form) {
            //if it's not the current form continue
            if($form->fid==$fid)
                continue;
            //if it's in the available associations already, no worries, continue
            $noworries = false;
            foreach($available as $avail) {
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

    /**
     * Makes the request for permission to associate a form. Emails all admins of the requested form.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  Request $request
     * @return View
     */
    public function requestAccess($pid, $fid, Request $request) {
        $myForm = FormController::getForm($fid);
        $myProj = ProjectController::getProject($myForm->pid);
        $theirForm = FormController::getForm($request->rfid);
        $theirProj = ProjectController::getProject($theirForm->pid);

        //form admins only
        if(!(\Auth::user()->isFormAdmin($myForm)))
            return redirect('projects/'.$myProj->pid)->with('k3_global_error', 'not_form_admin');

        $group = $theirForm->adminGroup()->first();
        $users = $group->users()->get();

        foreach($users as $user) {
            Mail::send('emails.request.assoc', compact('myForm','myProj','theirForm', 'theirProj'), function ($message) use($user) {
                $message->from(env('MAIL_FROM_ADDRESS'));
                $message->to($user->email);
                $message->subject('Kora Form Association Request');
            });
        }

        ////////REDIRECT BACK TO INDEX WITH SUCCESS MESSAGE

        //Associations to this form
        $assocs = self::getAllowedAssociations($fid);
        //Create an array of fids of those associations
        $associds = array();
        foreach($assocs as $a) {
            array_push($associds,$a->assocForm);
        }
        //FIX THIS//
        flash()->overlay("Request for access successfully sent.", "Success!");
        ///////////
        $form=$myForm;
        $project=$myProj;
        return view('association.index', compact('form', 'assocs', 'associds', 'project'))->with('k3_global_success', 'assoc_access_requested');
    }

    /**
     * Gets a list of records that associate to a particular record
     *
     * @param  Record $record - The subject record
     * @return array - Records that associate it
     */
    public static function getAssociatedRecords($record) {
        $assoc = DB::table(AssociatorField::SUPPORT_NAME)
            ->select("rid")
            ->distinct()
            ->where('record','=',$record->rid)->get();
        $records = array();
        foreach($assoc as $af) {
            $rid = $af->rid;
            $rec = RecordController::getRecord($rid);
            array_push($records,$rec);
        }

        return $records;
    }
}
