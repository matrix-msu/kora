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
    public function index($pid, Request $request) {
        $project = ProjectController::getProject($pid);

        if(!\Auth::user()->isProjectAdmin($project))
            return redirect('projects')->with('k3_global_error', 'not_project_admin');

        $all_presets = $this->getPresetsIndex($pid);

        $notification = '';
        $prevUrlArray = $request->session()->get('_previous');
        $prevUrl = reset($prevUrlArray);
        if ($prevUrl !== url()->current()) {
          $session = $request->session()->get('k3_global_success');

          if ($session == 'field_preset_created') $notification = 'Field Value Preset Created!';
          else if ($session == 'field_preset_edited') $notification = 'Field Value Preset Updated!';
        }

        return view('optionPresets/index', compact('project', 'all_presets', 'notification'));
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
    public function createApi($pid, Request $request) {
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

    public function validatePresetFormFields($pid, Request $request) {
        $this->validate($request, [
            'preset' => 'required',
            'name' => 'required'
        ]);
        return response()->json(["status"=>true, "message"=>"Form Valid", 200]);
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

        $all_presets = ["Project" => $project_presets, "Shared" => $shared_presets, "Stock" => $stock_presets];

        return $all_presets;
    }

    /**
     * Used by combo list to see if one of it's sub field types supports presets.
     *
     * @param  string $type - Field type to compare
     * @return bool - Result of the comparison
     */
    public static function supportsPresets($type) { //TODO::Evaluate Usage
        $preset_field_compatibility = collect([
            'Text'=>'Text',
            'List'=>'List',
            'Multi-Select List'=>'List',
            'Generated List'=>'List',
            'Geolocator'=>'Geolocator',
            'Schedule'=>'Schedule'
        ]);

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
}
