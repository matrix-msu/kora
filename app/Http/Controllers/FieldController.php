<?php namespace App\Http\Controllers;

use App\Field;
use App\Http\Requests;
use App\Http\Requests\FieldRequest;
use App\Http\Controllers\Controller;
use App\FieldHelpers\FieldDefaults;

use Illuminate\Http\Request;

class FieldController extends Controller {


    /**
     * User must be logged in to access views in this controller.
     */
       public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('active');
    }


    /**
     * Show the form for creating a new resource.
     *
     * @param $pid
     * @param $fid
     * @return Response
     */
	public function create($pid, $fid)
	{
        if(!FieldController::checkPermissions($fid, 'create')) {
            return redirect('projects/'.$pid.'/forms/'.$fid.'/fields');
        }

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
        $layout = explode('</LAYOUT>',$form->layout);
        $form->layout = $layout[0].'<ID>'.$field->flid.'</ID></LAYOUT>';
        $form->save();

        RevisionController::wipeRollbacks($form->fid);

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
        if(!FieldController::checkPermissions($fid, 'edit')) {
            return redirect('projects/'.$pid.'/forms/'.$fid.'/fields');
        }

        if(!FieldController::validProjFormField($pid, $fid, $flid)){
            return redirect('projects');
        }

        $field = FieldController::getField($flid);
        $form = FormController::getForm($fid);
        $proj = ProjectController::getProject($pid);
        if($field->type=="Text") {
            return view('fields.options.text', compact('field', 'form', 'proj'));
        }else if($field->type=="Rich Text") {
            return view('fields.options.richtext', compact('field', 'form', 'proj'));
        }else if($field->type=="Number") {
            return view('fields.options.number', compact('field', 'form', 'proj'));
        }else if($field->type=="List") {
            return view('fields.options.list', compact('field', 'form', 'proj'));
        }else if($field->type=="Multi-Select List") {
            return view('fields.options.mslist', compact('field', 'form', 'proj'));
        }else if($field->type=="Generated List") {
            return view('fields.options.genlist', compact('field', 'form', 'proj'));
        }else if($field->type=="Date") {
            return view('fields.options.date', compact('field', 'form', 'proj'));
        }else if($field->type=="Schedule") {
            return view('fields.options.schedule', compact('field', 'form', 'proj'));
        }else if($field->type=="Geolocator") {
            return view('fields.options.geolocator', compact('field', 'form', 'proj'));
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
        if(!FieldController::checkPermissions($fid, 'edit')) {
            return redirect('projects/'.$pid.'/forms/'.$fid.'/fields');
        }

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
        if(!FieldController::checkPermissions($fid, 'edit')) {
            return redirect('projects/'.$pid.'/forms/'.$fid.'/fields');
        }

        if(!FieldController::validProjFormField($pid, $fid, $flid)){
            return redirect('projects');
        }

		$field = FieldController::getField($flid);

        $field->update($request->all());

        RevisionController::wipeRollbacks($fid);

        flash()->overlay('Your field has been successfully updated!', 'Good Job!');

        return redirect('projects/'.$pid.'/forms/'.$fid);
	}

    public function updateRequired($pid, $fid, $flid, FieldRequest $request)
    {
        if(!FieldController::checkPermissions($fid, 'edit')) {
            return redirect('projects/'.$pid.'/forms/'.$fid.'/fields');
        }

        if(!FieldController::validProjFormField($pid, $fid, $flid)){
            return redirect('projects');
        }

        $field = FieldController::getField($flid);

        $field->required = $request->required;
        $field->save();

        RevisionController::wipeRollbacks($fid);

        flash()->success('Option updated!');

        return redirect('projects/'.$pid.'/forms/'.$fid.'/fields/'.$flid.'/options');
    }

    public function updateDefault($pid, $fid, $flid, FieldRequest $request)
    {
        if(!FieldController::checkPermissions($fid, 'edit')) {
            return redirect('projects/'.$pid.'/forms/'.$fid.'/fields');
        }

        if(!FieldController::validProjFormField($pid, $fid, $flid)){
            return redirect('projects');
        }

        $field = FieldController::getField($flid);

        if(($field->type=='Multi-Select List' | $field->type=='Generated List') && !is_null($request->default)){
            $reqDefs = $request->default;
            $def = $reqDefs[0];
            for($i=1;$i<sizeof($reqDefs);$i++){
                $def .= '[!]'.$reqDefs[$i];
            }
            $field->default = $def;
        }else if ($field->type=='Date'){
            if(FieldController::validateDate($request->default_month,$request->default_day,$request->default_year))
                $field->default = '[M]'.$request->default_month.'[M][D]'.$request->default_day.'[D][Y]'.$request->default_year.'[Y]';
            else{
                flash()->error('Invalid date. Either day given w/ no month provided, or day and month are impossible.');

                return redirect('projects/'.$pid.'/forms/'.$fid.'/fields/'.$flid.'/options');
            }
        }else{
            $field->default = $request->default;
        }

        $field->save();

        RevisionController::wipeRollbacks($fid);

        flash()->success('Option updated!');

        return redirect('projects/'.$pid.'/forms/'.$fid.'/fields/'.$flid.'/options');
    }

    public function updateOptions($pid, $fid, $flid, FieldRequest $request)
    {
        if(!FieldController::checkPermissions($fid, 'edit')) {
            return redirect('projects/'.$pid.'/forms/'.$fid.'/fields');
        }

        if(!FieldController::validProjFormField($pid, $fid, $flid)){
            return redirect('projects');
        }

        $field = FieldController::getField($flid);

        FieldController::setFieldOptions($field, $request->option, $request->value);

        RevisionController::wipeRollbacks($fid);

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
        if(!FieldController::checkPermissions($fid, 'delete')) {
            return redirect('projects/'.$pid.'/forms/'.$fid.'/fields');
        }

        if(!FieldController::validProjFormField($pid, $fid, $flid)){
            return redirect('projects/'.$pid.'forms/');
        }

        $field = FieldController::getField($flid);
        $field->delete();

        $form = FormController::getForm($fid);
        $layout = explode('<ID>'.$field->flid.'</ID>',$form->layout);
        $form->layout = $layout[0].$layout[1];
        $form->save();

        RevisionController::wipeRollbacks($form->fid);

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

    private function checkPermissions($fid, $permission='')
    {
        switch($permission) {
            case 'create':
                if(!(\Auth::user()->canCreateFields(FormController::getForm($fid))))
                {
                    flash()->overlay('You do not have permission to create fields for that form.', 'Whoops.');
                    return false;
                }
                return true;
            case 'edit':
                if(!(\Auth::user()->canEditFields(FormController::getForm($fid))))
                {
                    flash()->overlay('You do not have permission to edit fields for that form.', 'Whoops.');
                    return false;
                }
                return true;
            case 'delete':
                if(!(\Auth::user()->canDeleteFields(FormController::getForm($fid))))
                {
                    flash()->overlay('You do not have permission to delete fields for that form.', 'Whoops.');
                    return false;
                }
                return true;
            default:
                if(!(\Auth::user()->inAFormGroup(FormController::getForm($fid))))
                {
                    flash()->overlay('You do not have permission to view that field.', 'Whoops.');
                    return false;
                }
                return true;
        }
    }


    //THIS SECTION IS RESERVED FOR FUNCTIONS DEALING WITH SPECIFIC LIST TYPES//////////////////////////////////////////

    public function saveList($pid, $fid, $flid){
        if ($_REQUEST['action']=='SaveList') {
            if(isset($_REQUEST['options']))
                $options = $_REQUEST['options'];
            else
                $options = array();

            $dbOpt = '';

            if (sizeof($options) == 1) {
                $dbOpt = $options[0];
            } else if (sizeof($options) == 2) {
                $dbOpt = $options[0] . '[!]' . $options[1];
            } else if (sizeof($options) > 2) {
                $dbOpt = $options[0];
                for ($i = 1; $i < sizeof($options); $i++) {
                    $dbOpt .= '[!]' . $options[$i];
                }
            }

            $field = FieldController::getField($flid);

            //This line removes the default if it no longer exists
            if(!in_array($field->default,$options)){
                $field->default = '';
                $field->save();
            }

            FieldController::setFieldOptions($field, 'Options', $dbOpt);
        }
    }

    public static function getList($field, $blankOpt=false)
    {
        $dbOpt = FieldController::getFieldOption($field, 'Options');
        $options = array();

        if ($dbOpt == '') {
            //skip
        } else if (!strstr($dbOpt, '[!]')) {
            $options = [$dbOpt => $dbOpt];
        } else {
            $opts = explode('[!]', $dbOpt);
            foreach ($opts as $opt) {
                $options[$opt] = $opt;
            }
        }

        if ($blankOpt) {
            $options = array('' => '') + $options;
        }

        return $options;
    }

    public static function msListArrayToString($array){
        if(is_array($array)){
            $list = $array[0];
            for($i=1;$i<sizeof($array);$i++){
                $list .= '[!]'.$array[$i];
            }
            return $list;
        }

        return '';
    }

    public static function validateDate($m,$d,$y){
        if($d!='' && !is_null($d)) {
            if ($m == '' | is_null($m)) {
                return false;
            } else {
                return checkdate($m, $d, $y);
            }
        }

        return true;
    }

    public static function getDateList($field)
    {
        $def = $field->default;
        $options = array();

        if ($def == '') {
            //skip
        } else if (!strstr($def, '[!]')) {
            $options = [$def => $def];
        } else {
            $opts = explode('[!]', $def);
            foreach ($opts as $opt) {
                $options[$opt] = $opt;
            }
        }

        return $options;
    }

    public static function saveDateList($pid, $fid, $flid){
        if ($_REQUEST['action']=='SaveDateList') {
            if(isset($_REQUEST['options']))
                $options = $_REQUEST['options'];
            else
                $options = array();

            $dbOpt = '';

            if (sizeof($options) == 1) {
                $dbOpt = $options[0];
            } else if (sizeof($options) == 2) {
                $dbOpt = $options[0] . '[!]' . $options[1];
            } else if (sizeof($options) > 2) {
                $dbOpt = $options[0];
                for ($i = 1; $i < sizeof($options); $i++) {
                    $dbOpt .= '[!]' . $options[$i];
                }
            }

            $field = FieldController::getField($flid);

            $field->default = $dbOpt;
            $field->save();
        }
    }
}
