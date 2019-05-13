<?php namespace App\Http\Controllers;

use App\Association;
use App\Commands\FormEmails;
use App\Form;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
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
        $associatedForms = array();
        foreach($assocs as $a) {
            array_push($associatedForms, FormController::getForm($a->assoc_form));
        }
        $associatable_forms = Form::all();
        $available_associations = self::getAvailableAssociations($fid);
        $requestable_associations = self::getRequestableAssociations($fid);

        $notification = array(
          'message' => '',
          'description' => '',
          'warning' => false,
          'static' => true /* the only notification to appear on this page will be static */
        );

		return view('association.index', compact('form', 'assocs', 'associatedForms', 'project', 'available_associations', 'requestable_associations', 'associatable_forms', 'notification'));
	}

    /**
     * Creates a new association permission.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  Request $request
     * @return JsonResponse
     */
	public function create($pid, $fid, Request $request) {
        if(!FormController::validProjForm($pid, $fid))
            return response()->json(['k3_global_error' => 'form_invalid']);

		$assocFormID = $request->assocfid;

		$assoc = new Association();
		$assoc->data_form = $fid;
		$assoc->assoc_form = $assocFormID;
        $assoc->save();

        $form = Form::where('id', $assocFormID)->get()->first();
        
        return response()->json(
            [
                'k3_global_success' => 'assoc_created',
                'form' => $form,
                'project_name' => $form->project()->get()->first()->name
            ]
        );
	}

    /**
     * Delete an existing association permission you've given.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  Request $request
     * @return JsonResponse
     */
	public function destroy($pid, $fid, Request $request) {
        if(!FormController::validProjForm($pid, $fid))
            return response()->json(['k3_global_error' => 'form_invalid']);

		$assocFormID = $request->assocfid;

		$assoc = Association::where('data_form','=',$fid)->where('assoc_form','=',$assocFormID)->first();

        $assoc->delete();

        $form = Form::where('id', '=', $assocFormID)->first();

        return response()->json(
            [
                'k3_global_success' => 'assoc_destroyed',
                'assocfid' => $assocFormID,
                'name' => $form->name
            ]
        );
	}

    /**
     * Delete an existing association permission you've received.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  Request $request
     * @return JsonResponse
     */
    public function destroyReverse($pid, $fid, Request $request) {
        if(!FormController::validProjForm($pid, $fid))
            return response()->json(['k3_global_error' => 'form_invalid']);

        $dataFormID = $request->assocfid;

        $assoc = Association::where('assoc_form','=',$fid)->where('data_form','=',$dataFormID)->first();

        $assoc->delete();

        $form = Form::where('id', '=', $dataFormID)->first();

        return response()->json(
            [
                'k3_global_success' => 'assoc_destroyed',
                'assocfid' => $dataFormID,
                'name' => $form->name
            ]
        );
    }

    /**
     * Gets all forms that a given form has given permission to.
     *
     * @param  int $fid - Form ID of form granting permission
     * @return Collection - The forms that this form has given permission
     */
    static function getAllowedAssociations($fid) {
		return Association::where('data_form','=',$fid)->get()->all();
	}

    /**
     * Gets all forms that a given form can associate to.
     *
     * @param  int $fid - Form ID
     * @return Collection - The forms this form has access to
     */
    static function getAvailableAssociations($fid) {
		return Association::where('assoc_form','=',$fid)->get()->all();
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
            if($form->id==$fid)
                continue;
            //if it's in the available associations already, no worries, continue
            $noWorries = false;
            foreach($available as $avail) {
                if($avail->data_form==$form->id)
                    $noWorries = true;
            }
            if($noWorries)
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
     * @return JsonResponse
     */
    public function requestAccess($pid, $fid, Request $request) {
        if(!FormController::validProjForm($pid, $fid))
            return response()->json(['k3_global_error' => 'form_invalid']);

        $myForm = FormController::getForm($fid);
        $myProj = ProjectController::getProject($myForm->project_id);
        $thierForm = FormController::getForm($request->rfid);
        $thierProj = ProjectController::getProject($thierForm->project_id);

        //form admins only
        if(!(\Auth::user()->isFormAdmin($myForm)))
            return response()->json(['k3_global_error' => 'not_form_admin']);

        $group = $thierForm->adminGroup()->first();
        $users = $group->users()->get();

        foreach($users as $user) {
            $job = new FormEmails('FormAssociationRequest', ['myForm' => $myForm, 'myProj' => $myProj,
                'thierForm' => $thierForm, 'thierProj' => $thierProj, 'user' => $user]);
            $job->handle();
        }

        return response()->json(['k3_global_success' => 'assoc_access_requested']);
    }
}
