<?php namespace App\Http\Controllers;

use App\Commands\PrepRecordFileZip;
use App\FieldValuePreset;
use App\Form;
use App\RecordPreset;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;

class ExportController extends Controller {

    /*
    |--------------------------------------------------------------------------
    | Export Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the export process of kora structure and data
    |
    */

    /**
     * @var string - Valid formats for export
     */
    const JSON = "JSON";
    const XML = "XML";

    /**
     * @var array - Array of those formats
     */
    const VALID_FORMATS = [ self::JSON, self::XML];

    /**
     * Constructs controller and makes sure user is authenticated.
     */
    public function __construct() {
        $this->middleware('auth');
        $this->middleware('active');
    }

    /**
     * Gathers and exports a forms records.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  string $type - Type of export format
     * @return Redirect
     */
    public function exportRecords($pid, $fid, $type) {
        if(!FormController::validProjForm($pid,$fid))
            return redirect('projects/'.$pid);

        $form = FormController::getForm($fid);

        if(!\Auth::user()->isFormAdmin($form))
            return redirect('projects/'.$pid.'/forms/'.$fid);

        //Get the data
        $filters = ["revAssoc" => true, "meta" => false, "fields" => 'ALL', "altNames" => false, "assoc" => false,
            "data" => true, "sort" => null, "count" => null, "index" => null];
        if($type==self::JSON) {
            $data = json_encode($form->getRecordsForExport($filters));
            $ext = 'json';
        } else if($type==self::XML) {
            $data = $form->getRecordsForExportXML($filters);
            $ext = 'xml';
        }

        $dt = new \DateTime();
        $format = $dt->format('Y_m_d_H_i_s');
        $path = storage_path("app/exports/record_export_$format.$ext");

        file_put_contents($path, $data);

        if(file_exists($path)) { // File exists, so we download it.
            header("Content-Disposition: attachment; filename=\"" . basename($path) . "\"");
            header("Content-Type: application/octet-stream");
            header("Content-Length: " . filesize($path));

            readfile($path);
            exit;
        } else { // File does not exist, so some kind of error occurred, and we redirect.
            return redirect("projects/" . $pid . "/forms/" . $fid . "/records");
        }
    }

    /**
     * Export a subset of records.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  string $type - Type of export format
     * @param  Request $request
     * @return Redirect
     */
    public function exportSelectedRecords($pid, $fid, $type, Request $request) {
        if(!FormController::validProjForm($pid,$fid))
            return redirect('projects/'.$pid);

        $form = FormController::getForm($fid);

        if(!\Auth::user()->isFormAdmin($form))
            return redirect('projects/'.$pid.'/forms/'.$fid);

        //Get the data
        $kids = explode(',', $request->rids);
        $rids = [];
        foreach($kids as $kid) {
            $parts = explode('-',$kid);
            $rids[] = $parts[2];
        }
        $filters = ["revAssoc" => true, "meta" => false, "fields" => 'ALL', "altNames" => false, "assoc" => false,
            "data" => true, "sort" => null, "count" => null, "index" => null];
        if($type==self::JSON) {
            $data = json_encode($form->getRecordsForExport($filters,$rids));
            $ext = 'json';
        } else if($type==self::XML) {
            $data = $form->getRecordsForExportXML($filters,$rids);
            $ext = 'xml';
        }

        $dt = new \DateTime();
        $format = $dt->format('Y_m_d_H_i_s');
        $path = storage_path("app/exports/record_export_$format.$ext");

        file_put_contents($path, $data);

        if(file_exists($path)) { // File exists, so we download it.
            header("Content-Disposition: attachment; filename=\"" . basename($path) . "\"");
            header("Content-Type: application/octet-stream");
            header("Content-Length: " . filesize($path));

            readfile($path);
            exit;
        } else { // File does not exist, so some kind of error occurred, and we redirect.
            return redirect("projects/" . $pid . "/forms/" . $fid . "/records");
        }
    }

    /**
     * To speed things up, this function preps record data files into a zip in the background.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  Request $request
     * @return JsonResponse - Status report on success of zip kick off
     */
    public function prepRecordFiles($pid, $fid, Request $request) {
        if(!FormController::validProjForm($pid, $fid))
            return redirect('projects/'.$pid)->with('k3_global_error', 'form_invalid');

        $form = FormController::getForm($fid);
        if(!(\Auth::user()->isFormAdmin($form)))
            return redirect('projects/'.$pid)->with('k3_global_error', 'not_form_admin');

        if(isset($request->rids))
            $kids = explode(',', $request->rids);
        else
            $kids = 'ALL';

        $filename = $form->internal_name.uniqid().'.zip';
        $dbid = DB::table('zip_progress')->insertGetId(['filename' => $filename]);

        PrepRecordFileZip::dispatch($dbid, $filename, $form, $kids)->onQueue('zip_file');

        return response()->json(["status" => true, "message" => "success", "dbid" => $dbid], 200);
    }

    /**
     * This function checks on the status of the zip creation background process
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  Request $request
     * @return JsonResponse - Status report of the process
     */
    public function checkRecordFiles($pid, $fid, Request $request) {
        if(!FormController::validProjForm($pid, $fid))
            return redirect('projects/'.$pid)->with('k3_global_error', 'form_invalid');

        $form = FormController::getForm($fid);
        if(!(\Auth::user()->isFormAdmin($form)))
            return redirect('projects/'.$pid)->with('k3_global_error', 'not_form_admin');

        $status = DB::table('zip_progress')->where('id','=',$request->dbid)->first();

        if($status->finished)
            return response()->json(["status" => true, "message" => "finished", "filename" => $status->filename], 200);
        else if($status->failed)
            return response()->json(["status" => false, "message" => $status->message], 500);
        else
            return response()->json(["status" => true, "message" => "inprogress"], 200);
    }

    /**
     * Exports the files associated with the form records being exported.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @return string - The html to download the file
     */
    public function exportRecordFiles($pid, $fid, $name) {
        if(!FormController::validProjForm($pid, $fid))
            return redirect('projects/'.$pid)->with('k3_global_error', 'form_invalid');

        $form = FormController::getForm($fid);
        if(!(\Auth::user()->isFormAdmin($form)))
            return redirect('projects/'.$pid)->with('k3_global_error', 'not_form_admin');

        ini_set('max_execution_time',0);
        ini_set('memory_limit', "6G");

        if(file_exists(storage_path('app/tmpFiles/').$name)) {
            header('Content-Disposition: attachment; filename="' . $name . '"');
            header('Content-Type: application/zip; ');

            readfile(storage_path('app/tmpFiles/').$name);
            exit;
        } else {
            return response()->json(["status" => false, "message" => "zip_not_found"], 500);
        }
    }

    /**
     * Exports a form and its structure.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @param  bool $download - Download as a file or as an array for the project export
     * @return mixed - Export file or data array
     */
    public function exportForm($pid, $fid, $download=true) {
        if(!FormController::validProjForm($pid, $fid))
            return redirect('projects/'.$pid)->with('k3_global_error', 'form_invalid');

        $form = FormController::getForm($fid);

        if(!(\Auth::user()->isFormAdmin($form)))
            return redirect('projects/'.$pid)->with('k3_global_error', 'not_form_admin');


        $formArray = array();

        $formArray['name'] = $form->name;
        $formArray['internal_name'] = $form->internal_name;
        $formArray['description'] = $form->description;
        $formArray['preset'] = $form->preset;
        $formArray['layout'] = $form->layout;

        //record presets
        $recPresets = RecordPreset::where('form_id','=',$fid)->get();
        $formArray['recPresets'] = array();
        foreach($recPresets as $pre) {
            $rec = array();
            $rec['preset'] = $pre->preset;

            array_push($formArray['recPresets'],$rec);
        }

        if($download) {
            header('Content-Disposition: attachment; filename="' . $form->name . '_Layout_' . Carbon::now() . '.kForm"');
            header('Content-Type: application/octet-stream; ');

            echo json_encode($formArray);
            exit;
        } else {
            return $formArray;
        }
    }

    /**
     * Exports a project and its structure.
     *
     * @param  int $pid - Project ID
     * @return string - html for the file
     */
    public function exportProject($pid) {
        if(!ProjectController::validProj($pid))
            return redirect('projects')->with('k3_global_error', 'project_invalid');

        $proj = ProjectController::getProject($pid);

        if(!\Auth::user()->isProjectAdmin($proj))
            return redirect('projects')->with('k3_global_error', 'not_project_admin');

        $projArray = array();

        $projArray['name'] = $proj->name;
        $projArray['internal_name'] = $proj->internal_name;
        $projArray['description'] = $proj->description;

        //preset stuff
        $optPresets = FieldValuePreset::where('project_id','=',$pid)->get();
        $projArray['fieldValuePresets'] = array();
        foreach($optPresets as $pre) {
            $data = $pre->preset;
            $opt = array();
            $opt['type'] = $data['type'];
            $opt['name'] = $data['name'];
            $opt['preset'] = $data['preset'];
            $opt['shared'] = $pre->shared;

            array_push($projArray['fieldValuePresets'],$opt);
        }

        $forms = Form::where('project_id','=',$pid)->get();
        $projArray['forms'] = array();
        foreach($forms as $form) {
            array_push($projArray['forms'],$this->exportForm($pid,$form->id,false));
        }

        header('Content-Disposition: attachment; filename="' . $proj->name . '_Layout_' . Carbon::now() . '.kProj"');
        header('Content-Type: application/octet-stream; ');

        echo json_encode($projArray);
        exit;
    }
}