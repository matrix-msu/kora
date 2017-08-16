<?php namespace App\Http\Controllers;

use App\Field;
use App\Project;
Use App\ComboListField;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use App\OptionPreset;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OptionPresetController extends Controller {

    /*
    |--------------------------------------------------------------------------
    | Option Preset Controller
    |--------------------------------------------------------------------------
    |
    | This controller handle preset values that can be used in various field options
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
     * Gets the view for the preset management page.
     *
     * @param  int $pid - Project ID
     * @return View
     */
    public function index($pid) {
        $all_presets = $this->getPresetsIndex($pid);
        $project = Project::find($pid);
        return view('optionPresets/index', compact('project', 'all_presets'));
    }

    /**
     * Gets the view for creating a new preset in a project.
     *
     * @param  int $pid - Project ID
     * @return View
     */
    public function newPreset($pid) {
        $project = Project::find($pid);
        return view('optionPresets.create',compact('project','pid'));
    }

    /**
     * Creates and stores a new preset.
     *
     * @param  int $pid - Project ID
     * @param  Request $request
     * @return string - Json array response
     */
    public function create($pid, Request $request) {
        $this->validate($request, [
            'preset' => 'required',
            'type' => 'required|in:Text,List,Schedule,Geolocator',
            'name' => 'required',
            'shared' => 'required',
        ]);
        if(Project::find($pid) != null) {
            $presets_project = Project::find($pid);
            $user = Auth::user();
            $pc = new ProjectController;
            if(!$pc->isProjectAdmin($user,$presets_project)) {
                flash()->overlay("You cannot create presets unless you are an admin of the project it belongs to.","Whoops");
                return response()->json("You cannot create presets unless you are an admin of the project it belongs to.",500);
            }
        } else {
            flash()->overlay("You cannot create presets outside of a project.","Whoops");
            return response()->json("You cannot create presets outside of a project.",500);
        }
        $type = $request->input("type");
        $name = $request->input("name");
        $value = $request->input("preset");

        if($type == "List" || $type == "Schedule" || $type == "Geolocator") {
            $value = implode("[!]", $value);
        }

        $preset = OptionPreset::create(['pid' => $pid, 'type' => $type, 'name' => $name, 'preset' => $value]);
        $preset->save();
        if($request->input("shared") == "true") {
            $preset->shared = 1;
        } else {
            $preset->shared = 0;
        }
        $preset->save();
        flash()->success("The preset was created!");
        return response()->json( ['status'=>true,'url'=>(action("OptionPresetController@index",compact('pid')))],200);
    }

    /**
     * Get the view for editing a preset.
     *
     * @param  int $pid - Project ID
     * @param  int $id - ID of the preset
     * @return View
     */
    public function edit($pid, $id) {
        $preset = OptionPreset::find($id);
        $project = Project::find($pid);

        if(!is_null($preset) && !is_null($project)) {
            return view('optionPresets.edit', compact('preset', 'project', 'pid', 'id'));
        } else {
            flash()->overlay("The preset or project you're trying to edit doesn't exist","Whoops");
            return redirect()->back();
        }
    }

    /**
     * Update an existing preset.
     *
     * @param  int $pid - Project ID
     * @param  int $id - ID of the preset
     * @param  Request $request
     * @return string - Json array response
     */
    public function update($pid,$id,Request $request) {

        $preset = OptionPreset::where('id', '=', $id)->first();

        if(($preset->pid === null)) {
            flash()->overlay("You can't edit a stock preset","Whoops");
            return response()->json(["status"=>false,"message"=>"Can't edit a stock preset"],500);
        }
        if($preset->pid !== null) {
            $presets_project = $preset->project->first();
            $user = Auth::user();
            $pc = new ProjectController;
            if(!$pc->isProjectAdmin($user,$presets_project)) {
                flash()->overlay("You cannot create presets unless you are an admin of the project it belongs to.","Whoops");
                return response()->json(["status"=>false,"message"=>"You cannot create presets unless you are an admin of the project it belongs to."],500);
            }
        }

        $this->validate($request,[
           'action' => 'required|in:changeName,changeSharing,changeRegex',
            'preset_name' => 'required_if:action,changeName',
            'preset_shared' => 'required_if:action,changeSharing',
            'preset_regex' => 'required_if:action,changeRegex'
        ]);

        if($request->input("action") == 'changeName') {
            $op = OptionPreset::find($id);
            $op->name = $request->input("preset_name");
            $op->save();
            return response()->json(["status"=>true,"message"=>"The name has been updated"],200);
        } else if($request->input("action") == 'changeSharing') {
            $op = OptionPreset::find($id);
            if($request->input("preset_shared") == 'true') {
                $op->shared = true;
            } else {
                $op->shared = false;
            }
            $op->save();
            return response()->json(["status"=>true,"message"=>"The sharing preference has been updated"],200);
        } else if($request->input("action") == "changeRegex") {
            $op = OptionPreset::find($id);
            $op->preset = $request->input("preset_regex");
            $op->save();

            return response()->json(["status"=>true,"message"=>"Updated the regex"],200);
        }

        return response()->json(["status"=>false,"message"=>"The preset or action requested isn't valid"]);
    }

    /**
     * Delete a specified preset.
     *
     * @param  Request $request
     * @return string - Json array response
     */
    public function delete(Request $request) {
        $id = $request->input("presetId");
        $preset = OptionPreset::where('id', '=', $id)->first();
        if($preset->pid == null) {
            if(!Auth::user()->admin) {
                flash()->overlay("You do not have permission to modify a stock preset");
                return response()->json(["status"=>false,"message"=>"Cannot modify stock preset"],500);
            } else {
                $preset->delete();
                flash()->overlay("The option preset was deleted.", "Success!");
                return response()->json(["status"=>true,"message"=>"Preset deleted"],200);
            }
        } else {
            $presets_project = $preset->project->first();
            $user = Auth::user();
            $pc = new ProjectController;
            if(!$pc->isProjectAdmin($user,$presets_project)) {
                flash()->overlay("You cannot create presets unless you are an admin of the project it belongs to.","Whoops");
                return response()->json("You cannot create presets unless you are an admin of the project it belongs to.",500);
            } else {
                $preset->delete();
                flash()->overlay("The option preset was deleted.", "Success!");
                return response()->json(["status"=>true,"message"=>"Preset deleted"],200);
            }
        }
    }

    /**
     * Gets a list of all presets for a project.
     *
     * @param  int $pid - Project ID
     * @return array - The presets
     */
    public static function getPresetsIndex($pid) {
        $project_presets = OptionPreset::where('pid', '=', $pid)->get();
        $stock_presets = OptionPreset::where('pid', '=', null)->get();
        $shared_presets = OptionPreset::where('shared', '=', 1)->get();

        foreach($shared_presets as $key => $sp) {
            if($sp->pid == $pid || $sp->pid == null) {
                $shared_presets->forget($key);
            }
        }

        $all_presets = ["Stock" => $stock_presets, "Project" => $project_presets, "Shared" => $shared_presets];

        return $all_presets;
    }

    /**
     * Used by combo list to see if one of it's sub field types supports presets.
     *
     * @param  string $type - Field type to compare
     * @return bool - Result of the comparison
     */
    public static function supportsPresets($type) {
        $preset_field_compatibility = collect(['Text'=>'Text','List'=>'List','Multi-Select List'=>'List',
            'Generated List'=>'List','Geolocator'=>'Geolocator','Schedule'=>'Schedule']);
        return $preset_field_compatibility->has($type);
    }

    /**
     * Gets list of supported presets for a particular field.
     *
     * @param  int $pid - Project ID
     * @param  Field $field - Field to check
     * @return mixed - List of compatible presets for field
     */
    public static function getPresetsSupported($pid,$field) {
        //You need to update this if a new field with presets gets added!
        //Note that combolist is different
        $comboPresets = new Collection();
        $preset_field_compatibility = collect(['Text'=>'Text','List'=>'List','Multi-Select List'=>'List',
            'Generated List'=>'List','Geolocator'=>'Geolocator','Schedule'=>'Schedule']);
        if($field->type == "Combo List") {
            $oneType = ComboListField::getComboFieldType($field,'one');
            $twoType = ComboListField::getComboFieldType($field,'two');
            //ComboList field one
            $onePresets = self::getPresetsIndex($pid);
            foreach($onePresets as $subset) {
                foreach($subset as $key => $preset) {
                    if($preset->type != $preset_field_compatibility->get($oneType)) {
                        $subset->forget($key);
                    }
                }
            }
            $comboPresets->put("one",$onePresets);
            //ComboList field two
            $twoPresets = self::getPresetsIndex($pid);
            foreach($twoPresets as $subset) {
                foreach($subset as $key => $preset) {
                    if($preset->type != $preset_field_compatibility->get($twoType)) {
                        $subset->forget($key);
                    }
                }
            }
            $comboPresets->put("two",$twoPresets);

            return $comboPresets;
        } else {
            $all_presets = self::getPresetsIndex($pid);
            foreach($all_presets as $subset) {
                foreach($subset as $key => $preset) {
                    if($preset->type != $preset_field_compatibility->get($field->type)) {
                        $subset->forget($key);
                    }
                }
            }
            return $all_presets;
        }
    }

    /**
     * Apply preset to the field.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  int $flid - Field ID
     * @param  Request $request
     * @return string - Json array response
     */
    public function applyPreset($pid,$fid,$flid,Request $request) {
        $id = $request->input("id");
        $preset = OptionPreset::findOrFail($id);
        $project = Project::findOrFail($pid);
        $field = FieldController::getField($flid);
        $user = Auth::user();
        $arr = [$preset,$project,$field];

        //Since alot of the field stuff is preset specific, we will leave the logic here
        if(!is_null($preset) && !is_null($project) && !is_null($field)) { //check if all of these exist
            if(($preset->pid == $project->pid) || $preset->shared || is_null($preset->pid)) {
                //Make sure preset is for this project or is shared
                if(!$user->canEditFields(FormController::getForm($fid))) {
                    flash()->overlay("You do not have permission to edit this field");
                    return response()->json(["status"=>false,"message"=>"You do not have permission to edit this field"],500);
                } else {
                    //TODO::modular?
                    if($field->type == "Text" && $preset->type == "Text") {
                        $field->getTypedField()->updateOptions("Regex",$preset->preset);
                        flash()->overlay("The preset was applied to the regex","Good job!");
                        return response()->json(["status"=>true,"presetval"=>$preset->preset],200);
                    } else if(in_array($field->type,["List","Generated List","Multi-Select List"]) && $preset->type=="List") {
                        $field->getTypedField()->updateOptions("Options",$preset->preset);
                        flash()->overlay("The preset was applied to the options for the field","Good job!");
                        return response()->json(["status"=>true,"presetval"=>$preset->preset],200);
                    } else if(in_array($field->type,["Schedule","Geolocator"]) && in_array($preset->type,["Schedule","Geolocator"])) {
                        $field->default = $preset->preset;
                        $field->save();
                        flash()->overlay("The preset was applied to the defaults for the field","Good job!");
                        return response()->json(["status"=>true,"presetval"=>$preset->preset],200);
                    } else if($field->type == "Combo List" &&  $preset->type == "Text") {
                        if($request->input("combo_subfield") == "one") {
                            $subfield = "[!Field1!]";
                            $options = explode($subfield,$field->options);
                            $subfield_options = $options[1];
                            $regex_options = explode('[!Regex!]',$subfield_options);
                            $new_subfield_options = $regex_options[0] . "[!Regex!]".$preset->preset . "[!Regex!]" . $regex_options[2];
                            $new_options = $subfield.$new_subfield_options.$subfield.$options[2];
                            $field->options = $new_options;
                            $field->save();
                        } else {
                            $subfield = "[!Field2!]";
                            $options = explode($subfield,$field->options);
                            $subfield_options = $options[1];
                            $regex_options = explode('[!Regex!]',$subfield_options);
                            $new_subfield_options = $regex_options[0] . "[!Regex!]".$preset->preset . "[!Regex!]" . $regex_options[2];
                            $new_options = $options[0].$subfield.$new_subfield_options.$subfield;
                            $field->options = $new_options;
                            $field->save();
                        }

                    } else if($field->type == "Combo List" && $preset->type == "List") {
                        if($request->input('combo_subfield') == "one") {

                            $subfield = "[!Field1!]";
                            $options = explode($subfield,$field->options);
                            $subfield_options = $options[1];
                            $subfield_type = explode("[Type]",$subfield_options)[1];
                            if($subfield_type == "Generated List") {
                                $list_options = explode('[Options]',$subfield_options);
                                $list_options = explode('[!Options!]',$list_options[1]);
                                $new_subfield_options = explode('[Options]',$subfield_options)[0]."[Options]".$list_options[0] . "[!Options!]".$preset->preset . "[!Options!]"."[Options]";
                                $new_options = $subfield.$new_subfield_options.$subfield.$options[2];
                                $field->options = $new_options;
                                $field->save();
                            } else {
                                $list_options = explode('[!Options!]',$subfield_options);
                                $new_subfield_options = $list_options[0] . "[!Options!]".$preset->preset . "[!Options!]";
                                $new_options = $subfield.$new_subfield_options.$subfield.$options[2];
                                $field->options = $new_options;
                                $field->save();
                            }
                        } else {
                            $subfield = "[!Field2!]";
                            $options = explode($subfield,$field->options);
                            $subfield_options = $options[1];
                            $subfield_type = explode("[Type]",$subfield_options)[1];
                            if($subfield_type == "Generated List") {
                                $list_options = explode('[Options]',$subfield_options);
                                $list_options = explode('[!Options!]',$list_options[1]);
                                $new_subfield_options = explode('[Options]',$subfield_options)[0]."[Options]".$list_options[0] . "[!Options!]".$preset->preset . "[!Options!]"."[Options]";
                                $new_options = explode($subfield,$field->options)[0].$subfield.$new_subfield_options.$subfield.$options[2];
                                $field->options = $new_options;
                                $field->save();
                            } else {
                                $list_options = explode('[!Options!]',$subfield_options);
                                $new_subfield_options = $list_options[0] . "[!Options!]".$preset->preset . "[!Options!]";
                                $new_options = explode($subfield,$field->options)[0].$subfield.$new_subfield_options.$subfield.$options[2];
                                $field->options = $new_options;
                                $field->save();
                            }
                        }
                    } else {
                        dd($request);
                    }
                }
            } else {
                flash()->overlay("Make sure you selected a preset that belongs to this project, or is shared from another project, or is a stock preset","Whoops");
                return response()->json(['status'=>false,"message"=>"preset is not valid for this project","preset_project_field_objects"=>$arr,"preset_pid"=>$preset->pid, "project_pid"=>$project->pid],500);
            }
        } else {
            flash()->overlay("Make sure you have permission to edit this field then try again","Whoops");
            return response()->json(["status"=>false,"message"=>"Make sure you have at least edit permission for this field","values"=>$arr],500);
        }
    }


    /**
     * Gets list items from a preset.
     *
     * @param  int $id - ID of the preset
     * @param  bool $blankOpt - Inserts a blank option as the first value
     * @return array - The list items
     */
    public static function getList($id, $blankOpt = false) {
        $dbOpt = OptionPreset::find($id)->preset;
        $options = array();

        if($dbOpt == '') {
            //skip
        } else if(!strstr($dbOpt, '[!]')) {
            $options = [$dbOpt => $dbOpt];
        } else {
            $opts = explode('[!]', $dbOpt);
            foreach($opts as $opt) {
                $options[$opt] = $opt;
            }
        }

        if($blankOpt)
            $options = array('' => '') + $options;

        return $options;
    }

    /**
     * Saves list items to a preset
     *
     * @param  int $pid - Project ID
     * @param  int $id - ID of the preset
     * @param  Request $request
     * @return string - Json array response on error
     */
    public function saveList($pid, $id, Request $request) {
        $preset = OptionPreset::where('id', '=', $id)->first();

        if(($preset->pid === null)) {
            flash()->overlay("You can't edit a stock preset","Whoops");
            return response()->json("Can't edit a stock preset",500);
        }
        if($preset->pid !== null) {
            $presets_project = $preset->project->first();
            $user = Auth::user();
            $pc = new ProjectController;
            if(!$pc->isProjectAdmin($user,$presets_project)) {
                flash()->overlay("You cannot create presets unless you are an admin of the project it belongs to.","Whoops");
                return response()->json("You cannot create presets unless you are an admin of the project it belongs to.",500);
            }
        }

        if($request->action == 'SaveList') {
            if(isset($request->options))
                $options = $request->options;
            else
                $options = array();
            $dbOpt = '';
            if(isset($options[0]))
                $dbOpt = implode("[!]",$options);
            //$field = FieldController::getField($flid);
            $preset = OptionPreset::find($id);
            $preset->preset = $dbOpt;
            $preset->save();
        }
    }
}
