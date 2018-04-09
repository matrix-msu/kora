<?php namespace App\Http\Controllers;

use App\Field;
use App\Project;
Use App\ComboListField;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
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
        $project = ProjectController::getProject($pid);

        if(!\Auth::user()->isProjectAdmin($project))
            return redirect('projects')->with('k3_global_error', 'not_project_admin');

        $all_presets = $this->getPresetsIndex($pid);
        return view('optionPresets/index', compact('project', 'all_presets'));
    }

    /**
     * Gets the view for creating a new preset in a project.
     *
     * @param  int $pid - Project ID
     * @return View
     */
    public function newPreset($pid) {
        $project = ProjectController::getProject($pid);

        if(!\Auth::user()->isProjectAdmin($project))
            return redirect('projects')->with('k3_global_error', 'not_project_admin');

        return view('optionPresets.create',compact('project'));
    }

    /**
     * Creates and stores a new preset.
     *
     * @param  int $pid - Project ID
     * @param  Request $request
     * @return JsonResponse
     */
    public function create($pid, Request $request) {
        $this->validate($request, [
            'preset' => 'required',
            'type' => 'required|in:Text,List,Schedule,Geolocator',
            'name' => 'required'
        ]);
        if(Project::find($pid) != null) {
            $presets_project = Project::find($pid);
            $user = Auth::user();
            $pc = new ProjectController;
            if(!$pc->isProjectAdmin($user,$presets_project))
                return response()->json(["status"=>false,"message"=>"not_project_admin"],500);
        } else {
            return response()->json(["status"=>false,"message"=>"project_required"],500);
        }
        $type = $request->type;
        $name = $request->name;
        $value = $request->preset;

        if($type == "List" || $type == "Schedule" || $type == "Geolocator")
            $value = implode("[!]", $value);

        $preset = OptionPreset::create(['pid' => $pid, 'type' => $type, 'name' => $name, 'preset' => $value]);
        $preset->save();
        if(isset($request->shared))
            $preset->shared = 1;
        else
            $preset->shared = 0;

        $preset->save();

        return redirect('projects/'.$pid.'/presets')->with('k3_global_success', 'field_preset_created');
    }

    /**
     * Get the view for editing a preset.
     *
     * @param  int $pid - Project ID
     * @param  int $id - ID of the preset
     * @return View
     */
    public function edit($pid, $id) {
        $project = ProjectController::getProject($pid);

        if(!\Auth::user()->isProjectAdmin($project))
            return redirect('projects')->with('k3_global_error', 'not_project_admin');

        $preset = OptionPreset::find($id);

        if(!is_null($preset) && !is_null($project))
            return view('optionPresets.edit', compact('preset', 'project'));
        else
            return redirect()->back()->with('k3_global_error', 'cant_edit_preset');
    }

    /**
     * Update an existing preset.
     *
     * @param  int $pid - Project ID
     * @param  int $id - ID of the preset
     * @param  Request $request
     * @return JsonResponse
     */
    public function update($pid, $id, Request $request) {
        $this->validate($request, [
            'preset' => 'required',
            'name' => 'required'
        ]);
        if(Project::find($pid) != null) {
            $presets_project = Project::find($pid);
            $user = Auth::user();
            $pc = new ProjectController;
            if(!$pc->isProjectAdmin($user,$presets_project))
                return response()->json(["status"=>false,"message"=>"not_project_admin"],500);
        } else {
            return response()->json(["status"=>false,"message"=>"project_required"],500);
        }

        $preset = OptionPreset::find($id);

        $preset->name = $request->name;

        if($preset->type == "List" || $preset->type == "Schedule" || $preset->type == "Geolocator")
            $preset->preset = implode("[!]", $request->preset);
        else
            $preset->preset = $request->preset;

        if(isset($request->shared))
            $preset->shared = 1;
        else
            $preset->shared = 0;

        $preset->save();

        return redirect('projects/'.$pid.'/presets')->with('k3_global_success', 'field_preset_edited');
    }

    /**
     * Delete a specified preset.
     *
     * @param  Request $request
     * @return JsonResponse
     */
    public function delete(Request $request) {
        $id = $request->presetId;
        $preset = OptionPreset::find($id);

        //If Stock preset
        if($preset->pid == null) {
            if(!Auth::user()->admin) {
                return response()->json(["status"=>false,"message"=>"preset_not_admin"],500);
            } else {
                $preset->delete();
                return response()->json(["status"=>true,"message"=>"preset_deleted"],200);
            }
        } else {
            $presets_project = $preset->project->first();
            $pc = new ProjectController;
            if(!$pc->isProjectAdmin(Auth::user(),$presets_project)) {
                return response()->json(["status"=>false,"message"=>"preset_not_admin"],500);
            } else {
                $preset->delete();
                return response()->json(["status"=>true,"message"=>"preset_deleted"],200);
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
        $project_presets = OptionPreset::where('pid', '=', $pid)->orderBy('id','asc')->get();
        $stock_presets = OptionPreset::where('pid', '=', null)->orderBy('id','asc')->get();
        $shared_presets = OptionPreset::where('shared', '=', 1)->orderBy('id','asc')->get();

        foreach($shared_presets as $key => $sp) {
            if($sp->pid == $pid || $sp->pid == null)
                $shared_presets->forget($key);
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
    public static function supportsPresets($type) { //TODO::Evaluate Usage
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
    public static function getPresetsSupported($pid,$field) { //TODO::Evaluate Usage
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
                    if($preset->type != $preset_field_compatibility->get($oneType))
                        $subset->forget($key);
                }
            }
            $comboPresets->put("one",$onePresets);
            //ComboList field two
            $twoPresets = self::getPresetsIndex($pid);
            foreach($twoPresets as $subset) {
                foreach($subset as $key => $preset) {
                    if($preset->type != $preset_field_compatibility->get($twoType))
                        $subset->forget($key);
                }
            }
            $comboPresets->put("two",$twoPresets);

            return $comboPresets;
        } else {
            $all_presets = self::getPresetsIndex($pid);
            foreach($all_presets as $subset) {
                foreach($subset as $key => $preset) {
                    if($preset->type != $preset_field_compatibility->get($field->type))
                        $subset->forget($key);
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
     * @return JsonResponse
     */
    public function applyPreset($pid,$fid,$flid,Request $request) { //TODO::Evaluate Usage
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
                    return response()->json(["status"=>false,"message"=>"cant_edit_field"],500);
                } else {
                    //TODO::modular?
                    if($field->type == "Text" && $preset->type == "Text") {
                        $typedField = $field->getTypedField();
                        $req = new Request();
                        $req->options = $preset->preset;
                        $req->default = $typedField->default;
                        $req->required = $typedField->required;
                        $typedField->updateOptions($field, $req);
                    } else if(in_array($field->type,["List","Generated List","Multi-Select List"]) && $preset->type=="List") {
                        $typedField = $field->getTypedField();
                        $req = new Request();
                        $req->options = explode('[!]',$preset->preset);
                        $req->default = $typedField->default;
                        $req->required = $typedField->required;
                        $typedField->updateOptions($field, $req);
                    } else if(in_array($field->type,["Schedule","Geolocator"]) && in_array($preset->type,["Schedule","Geolocator"])) {
                        $field->default = $preset->preset;
                        $field->save();
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
                        return response()->json(["status"=>false,"message"=>"preset_invalid_field"],500);
                    }
                    return response()->json(["status"=>true,"message"=>"preset_applied"],200);
                }
            } else {
                return response()->json(["status"=>false,"message"=>"invalid_preset_selected"],500);
            }
        } else {
            return response()->json(["status"=>false,"message"=>"apply_preset_failed"],500);
        }
    }

    /**
     * Saves list items to a preset
     *
     * @param  int $pid - Project ID
     * @param  int $id - ID of the preset
     * @param  Request $request
     * @return JsonResponse
     */
    public function saveList($pid, $id, Request $request) { //TODO::Evaluate Usage
        $preset = OptionPreset::where('id', '=', $id)->first();

        if(($preset->pid === null))
            return response()->json(["status"=>false,"message"=>"cant_edit_stock"],500);

        if($preset->pid !== null) {
            $presets_project = $preset->project->first();
            $user = Auth::user();
            $pc = new ProjectController;
            if(!$pc->isProjectAdmin($user,$presets_project))
                return response()->json(["status"=>false,"message"=>"not_project_admin"],500);
        }

        if($request->action == 'SaveList') {
            if(isset($request->options))
                $options = $request->options;
            else
                $options = array();
            $dbOpt = '';
            if(isset($options[0]))
                $dbOpt = implode("[!]",$options);

            $preset = OptionPreset::find($id);
            $preset->preset = $dbOpt;
            $preset->save();
        }

        return response()->json(["status"=>true,"message"=>"preset_list_saved"],200);
    }
}
