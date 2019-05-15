<?php namespace App\Http\Controllers;

use App\FieldValuePreset;
use App\Project;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FieldValuePresetController extends Controller {

    /*
    |--------------------------------------------------------------------------
    | Field Value Preset Controller
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
    public function index($pid, Request $request) {
        $project = ProjectController::getProject($pid);

        if(!\Auth::user()->isProjectAdmin($project))
            return redirect('projects')->with('k3_global_error', 'not_project_admin');

        $all_presets = $project->fieldValuePresets();

        $notification = array(
          'message' => '',
          'description' => '',
          'warning' => false,
          'static' => false
        );

        $prevUrlArray = $request->session()->get('_previous');
        $prevUrl = reset($prevUrlArray);
        if($prevUrl !== url()->current()) {
          $session = $request->session()->get('k3_global_success');

          if($session == 'field_preset_created')
            $notification['message'] = 'Field Value Preset Created!';
          else if($session == 'field_preset_edited')
            $notification['message'] = 'Field Value Preset Updated!';
        }

        return view('fieldValuePresets/index', compact('project', 'all_presets', 'notification'));
    }

    /**
     * Gets the view for creating a new preset in a project.
     *
     * @param  int $pid - Project ID
     * @return View
     */
    public function newPreset($pid) { //TODO::CASTLE
        $project = ProjectController::getProject($pid);

        if(!\Auth::user()->isProjectAdmin($project))
            return redirect('projects')->with('k3_global_error', 'not_project_admin');

        return view('fieldValuePresets.create',compact('project'));
    }

    /**
     * Creates and stores a new preset.
     *
     * @param  int $pid - Project ID
     * @param  Request $request
     * @return JsonResponse
     */
    public function create($pid, Request $request) { //TODO::CASTLE
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
                return redirect()->back()->with('k3_global_error', 'not_project_admin');
        } else {
            return redirect()->back()->with('k3_global_error', 'project_required');
        }
        $type = $request->type;
        $name = $request->name;
        $value = $request->preset;

        if($type == "List" || $type == "Schedule" || $type == "Geolocator")
            $value = implode("[!]", $value);

        $preset = OptionPreset::create(['pid' => $pid, 'type' => $type, 'name' => $name, 'preset' => $value]);

        if(isset($request->shared))
            $preset->shared = 1;
        else
            $preset->shared = 0;

        $preset->save();

        return redirect('projects/'.$pid.'/presets')->with('k3_global_success', 'field_preset_created');
    }

    /**
     * Creates and stores a new preset from api call.
     *
     * @param  int $pid - Project ID
     * @param  Request $request
     * @return JsonResponse
     */
    public function createApi($pid, Request $request) { //TODO::CASTLE
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

        $preset->shared = $request->shared;

        $preset->save();

        response()->json(["status"=>true,"message"=>"field_preset_created"],200);
    }

    /**
     * Get the view for editing a preset.
     *
     * @param  int $pid - Project ID
     * @param  int $id - ID of the preset
     * @return View
     */
    public function edit($pid, $id) { //TODO::CASTLE
        $project = ProjectController::getProject($pid);

        if(!\Auth::user()->isProjectAdmin($project))
            return redirect('projects')->with('k3_global_error', 'not_project_admin');

        $preset = OptionPreset::find($id);

        if(!is_null($preset) && !is_null($project))
            return view('fieldValuePresets.edit', compact('preset', 'project'));
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
    public function update($pid, $id, Request $request) { //TODO::CASTLE
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
    public function delete(Request $request) { //TODO::CASTLE
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

    public function validatePresetFormFields($pid, Request $request) { //TODO::CASTLE
        $this->validate($request, [
            'preset' => 'required',
            'name' => 'required'
        ]);
        return response()->json(["status"=>true, "message"=>"Form Valid", 200]);
    }

    /**
     * Gets list of supported presets for a particular field.
     *
     * @param  int $pid - Project ID
     * @param  Field $field - Field to check
     * @return mixed - List of compatible presets for field
     */
    public static function getPresetsSupported($pid,$field) { //TODO::CASTLE
        //You need to update this if a new field with presets gets added!
        //Note that combolist is different
        $comboPresets = new Collection();
        $project = ProjectController::getProject($pid);

        $preset_field_compatibility = collect(['Text'=>'Text','List'=>'List','Multi-Select List'=>'List',
            'Generated List'=>'List','Geolocator'=>'Geolocator']);

        if($field->type == "Combo List") {
            $oneType = $field['one']['type'];
            $twoType = $field['two']['type'];
            //ComboList field one
            $onePresets = $project->fieldValuePresets();
            foreach($onePresets as $subset) {
                foreach($subset as $key => $preset) {
                    if($preset->type != $preset_field_compatibility->get($oneType))
                        $subset->forget($key);
                }
            }
            $comboPresets->put("one",$onePresets);
            //ComboList field two
            $twoPresets = $project->fieldValuePresets();
            foreach($twoPresets as $subset) {
                foreach($subset as $key => $preset) {
                    if($preset->type != $preset_field_compatibility->get($twoType))
                        $subset->forget($key);
                }
            }
            $comboPresets->put("two",$twoPresets);

            return $comboPresets;
        } else {
            $all_presets = $project->fieldValuePresets();
            foreach($all_presets as $subset) {
                foreach($subset as $key => $preset) {
                    if($preset->type != $preset_field_compatibility->get($field->type))
                        $subset->forget($key);
                }
            }
            return $all_presets;
        }
    }
}
