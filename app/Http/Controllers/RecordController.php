<?php namespace App\Http\Controllers;

use App\GeneratedListField;
use App\User;
use App\Record;
use App\TextField;
use App\NumberField;
use App\Http\Requests;
use App\RichTextField;
use App\ListField;
use App\MultiSelectListField;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\FieldHelpers\FieldValidation;


class RecordController extends Controller {

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
        if(!RecordController::checkPermissions($fid)) {
            return redirect('projects/'.$pid.'/forms/'.$fid);
        }

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
        if(!RecordController::checkPermissions($fid, 'ingest')) {
            return redirect('projects/'.$pid.'/forms/'.$fid);
        }

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
            if($key=='_token' | $key=='userId'){
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
        $record->owner = $request->userId;
        $record->save(); //need to save to create rid needed to make kid
        $record->kid = $pid.'-'.$fid.'-'.$record->rid;
        $record->save();

        foreach($request->all() as $key => $value){
            if($key=='_token' | $key=='userId'){
                continue;
            }
            $field = FieldController::getField($key);
            if($field->type=='Text'){
                $tf = new TextField();
                $tf->flid = $field->flid;
                $tf->rid = $record->rid;
                $tf->text = $value;
                $tf->save();
            } else if($field->type=='Rich Text'){
                $rtf = new RichTextField();
                $rtf->flid = $field->flid;
                $rtf->rid = $record->rid;
                $rtf->rawtext = $value;
                $rtf->save();
            } else if($field->type=='Number'){
                $nf = new NumberField();
                $nf->flid = $field->flid;
                $nf->rid = $record->rid;
                $nf->number = $value;
                $nf->save();
            } else if($field->type=='List'){
                $lf = new ListField();
                $lf->flid = $field->flid;
                $lf->rid = $record->rid;
                $lf->option = $value;
                $lf->save();
            } else if($field->type=='Multi-Select List'){
                $mslf = new MultiSelectListField();
                $mslf->flid = $field->flid;
                $mslf->rid = $record->rid;
                $mslf->options = FieldController::msListArrayToString($value);
                $mslf->save();
            } else if($field->type=='Generated List'){
                $glf = new GeneratedListField();
                $glf->flid = $field->flid;
                $glf->rid = $record->rid;
                $glf->options = FieldController::msListArrayToString($value);
                $glf->save();
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
	public function show($pid, $fid, $rid)
	{
        if(!RecordController::checkPermissions($fid)) {
            return redirect('projects/'.$pid.'/forms/'.$fid);
        }

        if(!RecordController::validProjFormRecord($pid, $fid, $rid)){
            return redirect('projects');
        }

        $form = FormController::getForm($fid);
        $record = RecordController::getRecord($rid);
        $owner = User::where('id', '=', $record->owner)->first();

        return view('records.show', compact('record', 'form', 'pid', 'owner'));
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($pid, $fid, $rid)
	{
        if(!RecordController::checkPermissions($fid, 'modify') && !\Auth::user()->isOwner(RecordController::getRecord($rid))) {
            return redirect('projects/'.$pid.'/forms/'.$fid);
        }

        if(!RecordController::validProjFormRecord($pid, $fid, $rid)){
            return redirect('projects');
        }

        $form = FormController::getForm($fid);
        $record = RecordController::getRecord($rid);

        return view('records.edit', compact('record', 'form'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($pid, $fid, $rid, Request $request)
	{
        if(!FormController::validProjForm($pid,$fid)){
            return redirect('projects');
        }

        foreach($request->all() as $key => $value){
            if($key=='_token' | $key=='_method'){
                continue;
            }
            $message = FieldValidation::validateField($key, $value);
            if($message != ''){
                flash()->error($message);

                return redirect()->back()->withInput();
            }
        }

        $record = Record::where('rid', '=', $rid)->first();
        $record->save();

        foreach($request->all() as $key => $value){
            if($key=='_token' | $key=='_method'){
                continue;
            }
            $field = FieldController::getField($key);
            if($field->type=='Text'){
                //we need to check if the field exist first
                if(TextField::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first() != null){
                    $tf = TextField::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first();
                    $tf->text = $value;
                    $tf->save();
                }else {
                    $tf = new TextField();
                    $tf->flid = $field->flid;
                    $tf->rid = $record->rid;
                    $tf->text = $value;
                    $tf->save();
                }
            } else if($field->type=='Rich Text'){
                //we need to check if the field exist first
                if(RichTextField::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first() != null){
                    $rtf = RichTextField::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first();
                    $rtf->rawtext = $value;
                    $rtf->save();
                }else {
                    $rtf = new RichTextField();
                    $rtf->flid = $field->flid;
                    $rtf->rid = $record->rid;
                    $rtf->rawtext = $value;
                    $rtf->save();
                }
            } else if($field->type=='Number'){
                //we need to check if the field exist first
                if(NumberField::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first() != null){
                    $nf = NumberField::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first();
                    $nf->number = $value;
                    $nf->save();
                }else {
                    $nf = new NumberField();
                    $nf->flid = $field->flid;
                    $nf->rid = $record->rid;
                    $nf->number = $value;
                    $nf->save();
                }
            } else if($field->type=='List'){
                //we need to check if the field exist first
                if(ListField::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first() != null){
                    $lf = ListField::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first();
                    $lf->option = $value;
                    $lf->save();
                }else {
                    $lf = new ListField();
                    $lf->flid = $field->flid;
                    $lf->rid = $record->rid;
                    $lf->option = $value;
                    $lf->save();
                }
            } else if($field->type=='Multi-Select List'){
                //we need to check if the field exist first
                if(MultiSelectListField::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first() != null){
                    $mslf = MultiSelectListField::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first();
                    $mslf->options = FieldController::msListArrayToString($value);
                    $mslf->save();
                }else {
                    $mslf = new MultiSelectListField();
                    $mslf->flid = $field->flid;
                    $mslf->rid = $record->rid;
                    $mslf->options = FieldController::msListArrayToString($value);
                    $mslf->save();
                }
            } else if($field->type=='Generated List'){
                //we need to check if the field exist first
                if(GeneratedListField::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first() != null){
                    $glf = GeneratedListField::where('rid', '=', $rid)->where('flid', '=', $field->flid)->first();
                    $glf->options = FieldController::msListArrayToString($value);
                    $glf->save();
                }else {
                    $glf = new GeneratedListField();
                    $glf->flid = $field->flid;
                    $glf->rid = $record->rid;
                    $glf->options = FieldController::msListArrayToString($value);
                    $glf->save();
                }
            }
        }

        flash()->overlay('Your record has been successfully updated!', 'Good Job!');

        return redirect('projects/'.$pid.'/forms/'.$fid.'/records/'.$rid);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($pid, $fid, $rid)
	{
        if(!RecordController::checkPermissions($fid, 'destroy') && !\Auth::user()->isOwner(RecordController::getRecord($rid))) {
            return redirect('projects/'.$pid.'/forms/'.$fid);
        }

        if(!RecordController::validProjFormRecord($pid, $fid, $rid)){
            return redirect('projects/'.$pid.'forms/');
        }

        $record = RecordController::getRecord($rid);
        $record->delete();

        flash()->overlay('Your record has been successfully deleted!', 'Good Job!');
	}

    public static function getRecord($rid)
    {
        $record = Record::where('rid', '=', $rid)->first();

        return $record;
    }

    public static function validProjFormRecord($pid, $fid, $rid)
    {
        $record = RecordController::getRecord($rid);
        $form = FormController::getForm($fid);
        $proj = ProjectController::getProject($pid);

        if (!FormController::validProjForm($pid, $fid))
            return false;

        if (is_null($record) || is_null($form) || is_null($proj))
            return false;
        else if ($record->fid == $form->fid)
            return true;
        else
            return false;
    }

    private function checkPermissions($fid, $permission='')
    {
        switch($permission){
            case 'ingest':
                if(!(\Auth::user()->canIngestRecords(FormController::getForm($fid))))
                {
                    flash()->overlay('You do not have permission to create records for that form.', 'Whoops.');
                    return false;
                }
                return true;
            case 'modify':
                if(!(\Auth::user()->canModifyRecords(FormController::getForm($fid))))
                {
                    flash()->overlay('You do not have permission to edit records for that form.', 'Whoops.');
                    return false;
                }
                return true;
            case 'destroy':
                if(!(\Auth::user()->canDestroyRecords(FormController::getForm($fid))))
                {
                    flash()->overlay('You do not have permission to delete records for that form.', 'Whoops.');
                    return false;
                }
                return true;
            default:
                if(!(\Auth::user()->inAFormGroup(FormController::getForm($fid))))
                {
                    flash()->overlay('You do not have permission to view records for that form.', 'Whoops.');
                    return false;
                }
                return true;
        }
    }

}
