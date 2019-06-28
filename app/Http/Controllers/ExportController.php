<?php namespace App\Http\Controllers;

use App\FieldValuePreset;
use App\Form;
use App\RecordPreset;
use Carbon\Carbon;
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
        $rids = array_map('intval', explode(',', $request->rid));
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
     * To speed things up, this function preps record data files into a zip beforehand.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     */
    public function prepRecordFiles($pid, $fid) {
        if(!FormController::validProjForm($pid, $fid))
            return redirect('projects/'.$pid)->with('k3_global_error', 'form_invalid');

        $form = FormController::getForm($fid);

        if(!(\Auth::user()->isFormAdmin($form)))
            return redirect('projects/'.$pid)->with('k3_global_error', 'not_form_admin');

        $path = storage_path('app/files/'.$pid.'/'.$fid);
        $zipPath = storage_path('app/tmpFiles/'.$form->internal_name.'preppedZIP_user'.\Auth::user()->id.'.zip');

        $fileSizeCount = 0.0;

        // Initialize archive object
        $zip = new \ZipArchive();
        $zip->open($zipPath, (\ZipArchive::CREATE | \ZipArchive::OVERWRITE));

        if(file_exists($path)) {
            ini_set('max_execution_time',0);
            ini_set('memory_limit', "2G");

            //add files
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $name => $file) {
                if($fileSizeCount > 1)
                    return response()->json(["status"=>false,"message"=>"zip_too_big"],500);

                // Skip directories (they would be added automatically)
                if(!$file->isDir()) {
                    // Get real and relative path for current file
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($path) + 1);

                    // Add current file to archive
                    $zip->addFile($filePath, $relativePath);

                    $fileSizeCount += number_format(filesize($filePath) / 1073741824, 2);
                }
            }
        } else {
            return response()->json(["status"=>false,"message"=>"no_record_files"],500);
        }

        // Zip archive will be created only after closing object
        $zip->close();

        return 'Success';
    }

    /**
     * Exports the files associated with the form records being exported.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     * @return string - The html to download the file
     */
    public function exportRecordFiles($pid, $fid) {
        if(!FormController::validProjForm($pid, $fid))
            return redirect('projects/'.$pid)->with('k3_global_error', 'form_invalid');

        $form = FormController::getForm($fid);

        if(!(\Auth::user()->isFormAdmin($form)))
            return redirect('projects/'.$pid)->with('k3_global_error', 'not_form_admin');

        $path = storage_path('app/files/'.$pid.'/'.$fid);
        $zipPath = storage_path('app/tmpFiles/');

        ini_set('max_execution_time',0);
        ini_set('memory_limit', "6G");

        $fileSizeCount = 0.0;

        if(file_exists($zipPath.$form->internal_name.'preppedZIP_user'.\Auth::user()->id.'.zip')) {
            $subPath = $form->internal_name.'preppedZIP_user'.\Auth::user()->id.'.zip';
        } else {
            $time = Carbon::now();
            $subPath = $form->internal_name . 'fileData_' . $time . '.zip';

            // Initialize archive object
            $zip = new \ZipArchive();
            $zip->open($zipPath . $subPath, \ZipArchive::CREATE);

            if(file_exists($path)) {
                //add files
                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($path),
                    \RecursiveIteratorIterator::LEAVES_ONLY
                );

                foreach($files as $name => $file) {
                    if($fileSizeCount > 5)
                        return redirect('projects/' . $pid . '/forms/' . $fid)->with('k3_global_error', 'zip_too_big');

                    // Skip directories (they would be added automatically)
                    if(!$file->isDir()) {
                        // Get real and relative path for current file
                        $filePath = $file->getRealPath();
                        $relativePath = substr($filePath, strlen($path) + 1);

                        // Add current file to archive
                        $zip->addFile($filePath, $relativePath);

                        $fileSizeCount += number_format(filesize($filePath) / 1073741824, 2);
                    }
                }
            } else {
                return redirect('projects/' . $pid . '/forms/' . $fid)->with('k3_global_error', 'no_record_files');
            }

            // Zip archive will be created only after closing object
            $zip->close();
        }

        header('Content-Disposition: attachment; filename="'.$subPath.'"');
        header('Content-Type: application/zip; ');

        readfile($zipPath.$subPath);
        exit;
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