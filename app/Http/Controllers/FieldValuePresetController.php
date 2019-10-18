<?php namespace App\Http\Controllers;

use App\FieldValuePreset;
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
        if(!is_null($prevUrlArray) && reset($prevUrlArray) !== url()->current()) {
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
    public function newPreset($pid) {
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
    public function create($pid, Request $request) {
        $this->validate($request, [
            'preset' => 'required',
            'type' => 'required|in:Regex,List',
            'name' => 'required'
        ]);

        $project = ProjectController::getProject($pid);

        if(!\Auth::user()->isProjectAdmin($project))
            return redirect('projects')->with('k3_global_error', 'not_project_admin');

        $this->saveFVPreset($pid, $request);

        return redirect('projects/'.$pid.'/presets')->with('k3_global_success', 'field_preset_created');
    }

    /**
     * Creates and stores a new preset from api call.
     *
     * @param  int $pid - Project ID
     * @param  Request $request
     * @return JsonResponse
     */
    public function createApi($pid, Request $request) {
        $project = ProjectController::getProject($pid);

        if(!\Auth::user()->isProjectAdmin($project))
            return redirect('projects')->with('k3_global_error', 'not_project_admin');

        $this->saveFVPreset($pid, $request);

        response()->json(["status"=>true,"message"=>"field_preset_created"],200);
    }

    /**
     * Does the actual store
     *
     * @param  int $pid - Project ID
     * @param  Request $request
     * @return JsonResponse
     */
    private function saveFVPreset($pid, Request $request) {
        $type = $request->type;
        $name = $request->name;
        $value = $request->preset;
        if(isset($request->shared) && $request->shared == 1)
            $shared = 1;
        else
            $shared = 0;

        $preset = ["name" => $name,"type"=>$type,"preset"=>$value];

        FieldValuePreset::create(['project_id' => $pid, 'preset' => $preset, 'shared' => $shared]);
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

        $preset = FieldValuePreset::find($id);

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
    public function update($pid, $id, Request $request) {
        $this->validate($request, [
            'preset' => 'required',
            'name' => 'required'
        ]);
        $project = ProjectController::getProject($pid);

        if(!\Auth::user()->isProjectAdmin($project))
            return redirect('projects')->with('k3_global_error', 'not_project_admin');

        $preset = FieldValuePreset::find($id);
        $type = $preset->preset['type'];

        if(isset($request->shared))
            $preset->shared = 1;
        else
            $preset->shared = 0;

        $presetVal = ["name" => $request->name,"type"=>$type,"preset"=>$request->preset];

        $preset->preset = $presetVal;
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
        $preset = FieldValuePreset::find($id);

        //If Stock preset
        if($preset->pid == null) {
            if(Auth::user()->id != 1) {
                return response()->json(["status"=>false,"message"=>"preset_not_admin"],500);
            } else {
                $preset->delete();
                return response()->json(["status"=>true,"message"=>"preset_deleted"],200);
            }
        } else {
            $project = $preset->project->first();
            if(!\Auth::user()->isProjectAdmin($project)) {
                return response()->json(["status"=>false,"message"=>"preset_not_admin"],500);
            } else {
                $preset->delete();
                return response()->json(["status"=>true,"message"=>"preset_deleted"],200);
            }
        }
    }

    public function validatePresetFormFields($pid, Request $request) {
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
     * @param  array $field - Field to check
     * @return mixed - List of compatible presets for field
     */
    public static function getPresetsSupported($pid,$field) {
        //You need to update this if a new field with presets gets added!
        //Note that combolist is different
        $project = ProjectController::getProject($pid);
        $all_presets = $project->fieldValuePresets();
        $compatibility = FieldValuePreset::$compatability;

        //Is the field supported?
        if(!array_key_exists($field['type'], $compatibility))
            return null;

        //Filter out by type
        foreach($all_presets as $subset) {
            foreach($subset as $key => $preset) {
                if(!in_array($preset->preset['type'], $compatibility[$field['type']]))
                    $subset->forget($key);
            }
        }

        return $all_presets;

        //TODO::CASTLE Combo list and field value presets
//        if($field->type == "Combo List") {
//            $oneType = $field['one']['type'];
//            $twoType = $field['two']['type'];
//            //ComboList field one
//            $onePresets = $project->fieldValuePresets();
//            foreach($onePresets as $subset) {
//                foreach($subset as $key => $preset) {
//                    if($preset->type != $preset_field_compatibility->get($oneType))
//                        $subset->forget($key);
//                }
//            }
//            $comboPresets->put("one",$onePresets);
//            //ComboList field two
//            $twoPresets = $project->fieldValuePresets();
//            foreach($twoPresets as $subset) {
//                foreach($subset as $key => $preset) {
//                    if($preset->type != $preset_field_compatibility->get($twoType))
//                        $subset->forget($key);
//                }
//            }
//            $comboPresets->put("two",$twoPresets);
//
//            return $comboPresets;
//        } else {
//            $all_presets = $project->fieldValuePresets();
//            foreach($all_presets as $subset) {
//                foreach($subset as $key => $preset) {
//                    if($preset->type != $preset_field_compatibility->get($field->type))
//                        $subset->forget($key);
//                }
//            }
//            return $all_presets;
//        }
    }
}
