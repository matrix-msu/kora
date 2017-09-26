<?php namespace App\Http\Controllers;

use App\DownloadTracker;
use App\Field;
use App\Form;
use Illuminate\Support\Facades\DB;
use App\Metadata;
use App\OptionPreset;
use App\RecordPreset;
use Carbon\Carbon;
use CsvParser\Parser;
use Illuminate\Support\Facades\Redirect;

class ExportController extends Controller {

    /*
    |--------------------------------------------------------------------------
    | Export Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the export process of Kora 3 structure and data
    |
    */

    /**
     * @var string - Valid formats for export
     */
    const JSON = "JSON";
    const XML = "XML";
    const META = "META";

    /**
     * @var array - Array of those formats
     */
    const VALID_FORMATS = [ self::JSON, self::XML, self::META ];

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
        if(!FormController::validProjForm($pid, $fid))
            return redirect('projects/'.$pid)->with('k3_global_error', 'form_invalid');

        $form = FormController::getForm($fid);

        if(!(\Auth::user()->isFormAdmin($form)))
            return redirect('projects/'.$pid)->with('k3_global_error', 'not_form_admin');

        $rids = DB::table("records")->where("fid", "=", $fid)->select("rid")->get();

        // The DB call returns an array of StdObj so we get the rids out of the objects.
        $rids = array_map( function($obj) {
            return $obj->rid;
        }, $rids);

        // Download tracker to stop the loading bar when finished.
        $tracker = new DownloadTracker();
        $tracker->fid = $form->fid;
        $tracker->save();

        $output = self::exportWithRids($rids, $type);

        if(file_exists($output)) { // File exists, so we download it.
            header("Content-Disposition: attachment; filename=\"" . basename($output) . "\"");
            header("Content-Type: application/octet-stream");
            header("Content-Length: " . filesize($output));

            readfile($output);

            $tracker->delete();
        } else { // File does not exist, so some kind of error occurred, and we redirect.
            $tracker->delete();

            return redirect("projects/" . $pid . "/forms/" . $fid . "/records")->with('k3_global_error', 'record_export_fail');
        }
    }

    /**
     * Returns status of record export to notify completion.
     *
     * @param  int $fid - Form ID of form that's being export
     * @return string - Json array of the status
     */
    public function checkRecordExport($fid) {
        return response()->json(["status"=>true,"message"=>"record_export_progress",
            "finished" => !! DB::table("download_trackers")->where("fid", "=", $fid)->count()],200);
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

        $path = env('BASE_PATH').'storage/app/files/p'.$pid.'/f'.$fid;
        $zipPath = env('BASE_PATH').'storage/app/tmpFiles/';

        // Initialize archive object
        $zip = new \ZipArchive();
        $time = Carbon::now();
        $zip->open($zipPath.$form->name.'_fileData_'.$time.'.zip', \ZipArchive::CREATE);

        //add files
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file) {
            // Skip directories (they would be added automatically)
            if(!$file->isDir()) {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($path) + 1);

                // Add current file to archive
                $zip->addFile($filePath, $relativePath);
            }
        }

        // Zip archive will be created only after closing object
        $zip->close();

        header("Content-Disposition: attachment; filename=".$form->name.'_fileData_'.$time.'.zip');
        header("Content-Type: application/zip; ");

        readfile($zipPath.$form->name.'_fileData_'.$time.'.zip');
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
        $formArray['slug'] = $form->slug;
        $formArray['desc'] = $form->description;
        $formArray['preset'] = $form->preset;
        $formArray['metadata'] = $form->public_metadata;

        //Page
        $pages = $form->pages()->get();
        $formArray['pages'] = array();
        foreach($pages as $page) {
            $p = array();
            $p['id'] = $page->id;
            $p['title'] = $page->title;
            $p['sequence'] = $page->sequence;

            array_push($formArray['pages'],$p);
        }

        //record presets
        $recPresets = RecordPreset::where('fid','=',$fid)->get();
        $formArray['recPresets'] = array();
        foreach($recPresets as $pre) {
            $rec = array();
            $rec['name'] = $pre->name;
            $rec['preset'] = $pre->preset;

            array_push($formArray['recPresets'],$rec);
        }

        $fields = Field::where('fid','=',$form->fid)->get();
        $formArray['fields'] = array();

        foreach($fields as $field) {
            $fieldArray = array();

            $fieldArray['flid'] = $field->flid;
            $fieldArray['page_id'] = $field->page_id;
            $fieldArray['sequence'] = $field->sequence;
            $fieldArray['type'] = $field->type;
            $fieldArray['name'] = $field->name;
            $fieldArray['slug'] = $field->slug;
            $fieldArray['desc'] = $field->desc;
            $fieldArray['required'] = $field->required;
            $fieldArray['searchable'] = $field->searchable;
            $fieldArray['advsearch'] = $field->advsearch;
            $fieldArray['extsearch'] = $field->extsearch;
            $fieldArray['viewable'] = $field->viewable;
            $fieldArray['viewresults'] = $field->viewresults;
            $fieldArray['extview'] = $field->extview;
            $fieldArray['default'] = $field->default;
            $fieldArray['options'] = $field->options;

            $meta = Metadata::where('flid','=',$field->flid)->get()->first();
            if(!is_null($meta))
                $fieldArray['metadata'] = $meta->name;
            else
                $fieldArray['metadata'] = '';

            array_push($formArray['fields'],$fieldArray);
        }

        if($download) {
            header("Content-Disposition: attachment; filename=" . $form->name . '_Layout_' . Carbon::now() . '.k3Form');
            header("Content-Type: application/octet-stream; ");

            echo json_encode($formArray);
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
        $projArray['slug'] = $proj->slug;
        $projArray['description'] = $proj->description;

        //preset stuff
        $optPresets = OptionPreset::where('pid','=',$pid)->get();
        $projArray['optPresets'] = array();
        foreach($optPresets as $pre) {
            $opt = array();
            $opt['type'] = $pre->type;
            $opt['name'] = $pre->name;
            $opt['preset'] = $pre->preset;
            $opt['shared'] = $pre->shared;

            array_push($projArray['optPresets'],$opt);
        }

        $forms = Form::where('pid','=',$pid)->get();
        $projArray['forms'] = array();
        foreach($forms as $form) {
            array_push($projArray['forms'],$this->exportForm($pid,$form->fid,false));
        }

        header("Content-Disposition: attachment; filename=" . $proj->name . '_Layout_' . Carbon::now() . '.k3Proj');
        header("Content-Type: application/octet-stream; ");

        echo json_encode($projArray);
    }

    /**
     * Passes the rids to python so we can use multi-threading to fetch all the record data.
     *
     * @param  array $rids - The RIDs to gather data for
     * @param  string $format - File format to export
     * @return string - The system path to the exported file
     */
    public static function exportWithRids(array $rids, $format = self::JSON) {
        $format = strtoupper($format);

        if(!self::isValidFormat($format))
            return null;

        $rids = json_encode($rids);

        $exec_string = env("BASE_PATH") . "python/export.py \"$rids\" \"$format\"";
        exec($exec_string, $output);

        return $output[0];
    }

    /**
     * Verifies the given format is an eligible format for exporting.
     *
     * @param  string $format - Format to compare
     * @return bool - Result of format being eligible
     */
    public static function isValidFormat($format) {
        return in_array(($format), self::VALID_FORMATS);
    }
}