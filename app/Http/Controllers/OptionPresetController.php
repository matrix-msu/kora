<?php namespace App\Http\Controllers;

use App\DateField;
use App\Field;
use App\Form;
use App\Project;
use App\GeneratedListField;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\ListField;
use App\MultiSelectListField;
use App\NumberField;
use App\Record;
use App\RecordPreset;
use App\RichTextField;
use App\ScheduleField;
use App\TextField;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use App\OptionPreset;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProjectController;

/**
 * Class OptionPresetController
 * @package App\Http\Controllers
 */
class OptionPresetController extends Controller
{

    /**
     * User must be logged in to access views in this controller.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('active');
    }

    public function index($pid)
    {
        $all_presets = $this->getPresetsIndex($pid);
        $project = Project::find($pid);
        return view('optionPresets/index', compact('project', 'all_presets'));
    }

    /**
     * @param $pid
     * @return \Illuminate\View\View
     */
    public function newPreset($pid){
        $project = Project::find($pid);
        return view('optionPresets.create',compact('project','pid'));
    }

    /**
     * @param $pid
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create($pid, Request $request){
        $this->validate($request, [
            'preset' => 'required',
            'type' => 'required|in:Text,List,Schedule,Geolocator',
            'name' => 'required',
            'shared' => 'required',
        ]);
        if(Project::find($pid) != null){
            $presets_project = Project::find($pid);
            $user = Auth::user();
            $pc = new ProjectController;
            if(!$pc->isProjectAdmin($user,$presets_project)){
                flash()->overlay('You cannot create presets unless you are an admin of the project it belongs to.','Whoops.');
                return response()->json("You cannot create presets unless you are an admin of the project it belongs to",500);
            }
        }
        else{
            flash()->overlay("You cannot create presets outside of a project.","Whoops.");
            return response()->json("You cannot create presets outside of a project.",500);
        }
        $type = $request->input("type");
        $name = $request->input("name");
        $value = $request->input("preset");

        if ($type == "List" || $type == "Schedule" || $type == "Geolocator") {
            $value = implode("[!]", $value);
        }

        $preset = OptionPreset::create(['pid' => $pid, 'type' => $type, 'name' => $name, 'preset' => $value]);
        $preset->save();
        if ($request->input("shared") == "true") {
            $preset->shared = 1;
        } else {
            $preset->shared = 0;
        }
        $preset->save();
        flash()->success("The preset was created!");
        return response()->json( ['status'=>true,'url'=>(action("OptionPresetController@index",compact('pid')))],200);
    }

    /**
     * @param $pid
     * @param $id
     * @return \Illuminate\View\View
     */
    public function edit($pid, $id)
    {
        $preset = OptionPreset::find($id);
        $project = Project::find($pid);

        if(!is_null($preset) && !is_null($preset)) {
            return view('optionPresets.edit', compact('preset', 'project', 'pid', 'id'));
        }
        else{
            flash()->overlay("The preset or project you're trying to edit doesn't exist","Whoops.");
            return redirect()->back();
        }
    }

    /**
     * @param $pid
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update($pid,$id,Request $request){

        $preset = OptionPreset::where('id', '=', $id)->first();

        if(($preset->pid === null)){
            flash()->overlay("You can't edit a stock preset",'Whoops.');
            return response()->json(["status"=>false,"message"=>"Can't edit a stock preset"],500);
        }
        if($preset->pid !== null){
            $presets_project = $preset->project->first();
            $user = Auth::user();
            $pc = new ProjectController;
            if(!$pc->isProjectAdmin($user,$presets_project)){
                flash()->overlay('You cannot modify presets unless you are an admin of the project it belongs to.','Whoops.');
                return response()->json(["status"=>false,"message"=>"You cannot modify a preset unless you are an admin of the project it belongs to"],500);
            }
        }

        $this->validate($request,[
           'action' => 'required|in:changeName,changeSharing,changeRegex',
            'preset_name' => 'required_if:action,changeName',
            'preset_shared' => 'required_if:action,changeSharing',
            'preset_regex' => 'required_if:action,changeRegex'
        ]);

        if($request->input("action") == 'changeName'){
            $op = OptionPreset::find($id);
            $op->name = $request->input("preset_name");
            $op->save();
            return response()->json(["status"=>true,"message"=>"The name has been updated"],200);
        }

        elseif($request->input("action") == 'changeSharing'){
            $op = OptionPreset::find($id);
            if($request->input("preset_shared") == 'true'){
                $op->shared = true;
            }
            else{
                $op->shared = false;
            }
            $op->save();
            return response()->json(["status"=>true,"message"=>"The sharing preference has been updated"],200);
        }

        elseif($request->input("action") == "changeRegex"){
            $op = OptionPreset::find($id);
            $op->preset = $request->input("preset_regex");
            $op->save();

            return response()->json(["status"=>true,"message"=>"Updated the regex"],200);
        }

        return response()->json(["status"=>false,"message"=>"The preset or action requested isn't valid"]);

    }

    public function delete(Request $request)
    {
        $id = $request->input("presetId");
        $preset = OptionPreset::where('id', '=', $id)->first();
        if($preset->pid == null){
            if(!Auth::user()->admin){
                flash()->overlay("You do not have permission to modify a stock preset");
                return response()->json(["status"=>false,"message"=>"Cannot modify stock preset"],500);
            }
            else{
                $preset->delete();
                flash()->overlay('The option preset was deleted.', 'Success!');
                return response()->json(["status"=>true,"message"=>"Preset deleted"],200);
            }
        }
        else{
            $presets_project = $preset->project->first();
            $user = Auth::user();
            $pc = new ProjectController;
            if(!$pc->isProjectAdmin($user,$presets_project)){
                flash()->overlay('You cannot modify presets unless you are an admin of the project it belongs to.','Whoops.');
                return response()->json("You cannot modify presets unless you are an admimn of the project it belongs to.",500);
            }
            else{
                $preset->delete();
                flash()->overlay('The option preset was deleted.', 'Success!');
                return response()->json(["status"=>true,"message"=>"Preset deleted"],200);
            }
        }
        flash()->overlay('You do not have permission to delete the preset', 'Whoops');
        return response()->json(["status"=>false,"message"=>"You do not have permission to delete the preset"],500);
    }

    public static function getPresetsIndex($pid){
        $project_presets = OptionPreset::where('pid', '=', $pid)->get();
        $stock_presets = OptionPreset::where('pid', '=', null)->get();
        $shared_presets = OptionPreset::where('shared', '=', 1)->get();


        foreach ($shared_presets as $key => $sp) {
            if ($sp->pid == $pid || $sp->pid == null) {
                $shared_presets->forget($key);
            }
        }

        $all_presets = ["Stock" => $stock_presets, "Project" => $project_presets, "Shared" => $shared_presets];

        return $all_presets;
    }

    public static function getPresetsSupported($pid,$field){

        $preset_field_compatibility = collect(['Text'=>'Text','List'=>'List','Multi-Select List'=>'List','Generated List'=>'List','Geolocator'=>'Geolocator','Schedule'=>'Schedule']);

        $all_presets = OptionPresetController::getPresetsIndex($pid);
        foreach($all_presets as $subset){
            foreach($subset as $key => $preset){
                if($preset->type != $preset_field_compatibility->get($field->type)){
                    $subset->forget($key);
                }
            }
        }

        return $all_presets;
    }

    public function applyPreset(Request $request,$pid,$fid,$flid){
        $id = $request->input("id");
        $preset = OptionPreset::findOrFail($id);
        $project = Project::findOrFail($pid);
        $field = Field::findOrFail($flid);
        $user = Auth::user();
        $arr = [$preset,$project,$field];

        if(!is_null($preset)  && !is_null($project)   && !is_null($field)){ //check if all of these exist
            if(($preset->pid == $project->pid) || $preset->shared || is_null($preset->pid)){
                //Make sure preset is for this project or is shared
                if(!$user->canEditFields(FormController::getForm($fid))){
                    flash()->overlay("You do not have permission to edit this field");
                    return response()->json(["status"=>false,"message"=>"You do not have permission to edit this field"],500);
                }
                else{
                    if($preset->type=="Text") {
                        FieldController::setFieldOptions($field, "Regex", $preset->preset);
                        flash()->overlay("The preset was applied to the regex",'Good job!');
                        return response()->json(["status"=>true,"presetval"=>$preset->preset],200);
                    }
                    else if($preset->type =="List"){
                        FieldController::setFieldOptions($field,"Options",$preset->preset);
                        flash()->overlay("The preset was applied to the options for the field",'Good job!');
                        return response()->json(["status"=>true,"presetval"=>$preset->preset],200);
                    }
                    else if($preset->type == "Schedule" || $preset->type == "Geolocator"){
                        $field->default = $preset->preset;
                        $field->save();
                        flash()->overlay("The preset was applied to the defaults for the field","Good job!");
                        return response()->json(["status"=>true,"presetval"=>$preset->preset],200);
                    }
                }
            }
            else{
                flash()->overlay("Make sure you selected a preset that belongs to this project, or is shared from another project, or is a stock preset","Whoops.");
                return response()->json(['status'=>false,"message"=>"preset is not valid for this project","preset_project_field_objects"=>$arr,"preset_pid"=>$preset->pid, "project_pid"=>$project->pid],500);
            }
        }
        else{
            flash()->overlay("Make sure you have permission to edit this field then try again","Whoops.");
            return response()->json(["status"=>false,"message"=>"Make sure you have at least edit permission for this field","values"=>$arr],500);
        }
    }


    //These are some modified methods taken from FieldController to enable editing of lists///////////////////

    public static function getList($id, $blankOpt = false)
    {
        // $dbOpt = FieldController::getFieldOption($field, 'Options');
        $dbOpt = OptionPreset::find($id)->preset;
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

    public function saveList($pid, $id)
    {
        $preset = OptionPreset::where('id', '=', $id)->first();

        if(($preset->pid === null)){
            flash()->overlay("You can't edit a stock preset",'Whoops.');
            return response()->json("Can't edit a stock preset",500);
        }
        if($preset->pid !== null){
            $presets_project = $preset->project->first();
            $user = Auth::user();
            $pc = new ProjectController;
            if(!$pc->isProjectAdmin($user,$presets_project)){
                flash()->overlay('You cannot modify presets unless you are an admin of the project it belongs to.','Whoops.');
                return response()->json("You cannot modify presets unless you are an admimn of the project it belongs to.",500);
            }
        }

        if ($_REQUEST['action'] == 'SaveList') {
            if (isset($_REQUEST['options']))
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
            //$field = FieldController::getField($flid);
            $preset = OptionPreset::find($id);
            $preset->preset = $dbOpt;
            $preset->save();

        }
    }
}
