<?php namespace App\Http\Controllers;

use App\Form;
use Illuminate\Support\Facades\DB;
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
    | This controller handles the export process of Kora 3 structure and data
    |
    */

    /**
     * @var string - Valid formats for export
     */
    const JSON = "JSON";
    const XML = "XML";
    const KORA = "KORA_OLD";

    /**
     * @var array - Array of those formats
     */
    const VALID_FORMATS = [ self::JSON, self::XML, self::KORA];

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
    public function exportRecords($pid, $fid, $type) { //TODO::CASTLE
        if(!FormController::validProjForm($pid,$fid))
            return redirect('projects/'.$pid);

        $form = FormController::getForm($fid);

        if(!\Auth::user()->isFormAdmin($form))
            return redirect('projects/'.$pid.'/forms/'.$fid);

        $rids = DB::table("records")->where("fid", "=", $fid)->select("rid")->get()->toArray();

        // The DB call returns an array of StdObj so we get the rids out of the objects.
        $rids = array_map( function($obj) {
            return $obj->rid;
        }, $rids);

        //most of these are included to not break JSON, revAssoc is the only one that matters to us for this so we can get
        // the reverse associations. The others are only relevant to the API
        $options = ["revAssoc" => true, "meta" => false, "fields" => 'ALL', "realnames" => false, "assoc" => false];
        $output = $this->exportFormRecordData($fid, $rids, $type, false, $options);

        if(file_exists($output)) { // File exists, so we download it.
            header("Content-Disposition: attachment; filename=\"" . basename($output) . "\"");
            header("Content-Type: application/octet-stream");
            header("Content-Length: " . filesize($output));

            readfile($output);
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
    public function exportSelectedRecords($pid, $fid, $type, Request $request) { //TODO::CASTLE
      if(!FormController::validProjForm($pid,$fid))
        return redirect('projects/'.$pid.'/forms/'.$fid.'/records');

      $form = FormController::getForm($fid);

      if(!\Auth::user()->isFormAdmin($form))
        return redirect('projects/'.$pid.'/forms/'.$fid.'/records');

      $rids = $request->rid;
      $rids = array_map('intval', explode(',', $rids));

      $options = ["revAssoc" => true, "meta" => false, "fields" => 'ALL', "realnames" => false, "assoc" => false];
      $output = $this->exportFormRecordData($fid, $rids, $type, false, $options);

      if(file_exists($output)) { // File exists, so we download it.
          header("Content-Disposition: attachment; filename=\"" . basename($output) . "\"");
          header("Content-Type: application/octet-stream");
          header("Content-Length: " . filesize($output));

          readfile($output);
          exit;
      } else { // File does not exist, so some kind of error occurred, and we redirect.
          flash()->overlay(trans("records_index.exporterror"), trans("controller_admin.whoops"));
          return redirect("projects/" . $pid . "/forms/" . $fid . "/records");
      }
    }

    /**
     * To speed things up, this function preps record data files into a zip beforehand.
     *
     * @param  int $pid - Project ID
     * @param  int $fid - Form ID
     */
    public function prepRecordFiles($pid, $fid) { //TODO::CASTLE
        if(!FormController::validProjForm($pid, $fid))
            return redirect('projects/'.$pid)->with('k3_global_error', 'form_invalid');

        $form = FormController::getForm($fid);

        if(!(\Auth::user()->isFormAdmin($form)))
            return redirect('projects/'.$pid)->with('k3_global_error', 'not_form_admin');

        $path = storage_path('app/files/p'.$pid.'/f'.$fid);
        $zipPath = storage_path('app/tmpFiles/'.$form->name.'_preppedZIP_user'.\Auth::user()->id.'.zip');

        $fileSizeCount = 0.0;

        // Initialize archive object
        $zip = new \ZipArchive();
        $zip->open($zipPath, (\ZipArchive::CREATE | \ZipArchive::OVERWRITE));

        if(file_exists($path)) {
            ini_set('max_execution_time',0);
            ini_set('memory_limit', "6G");

            //add files
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $name => $file) {
                if($fileSizeCount > 5)
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
    public function exportRecordFiles($pid, $fid) { //TODO::CASTLE
        if(!FormController::validProjForm($pid, $fid))
            return redirect('projects/'.$pid)->with('k3_global_error', 'form_invalid');

        $form = FormController::getForm($fid);

        if(!(\Auth::user()->isFormAdmin($form)))
            return redirect('projects/'.$pid)->with('k3_global_error', 'not_form_admin');

        $path = storage_path('app/files/p'.$pid.'/f'.$fid);
        $zipPath = storage_path('app/tmpFiles/');

        ini_set('max_execution_time',0);
        ini_set('memory_limit', "6G");

        $fileSizeCount = 0.0;

        if(file_exists($zipPath.$form->name.'_preppedZIP_user'.\Auth::user()->id.'.zip')) {
            $subPath = $form->name.'_preppedZIP_user'.\Auth::user()->id.'.zip';
        } else {
            $time = Carbon::now();
            $subPath = $form->name . '_fileData_' . $time . '.zip';

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
    public function exportForm($pid, $fid, $download=true) { //TODO::CASTLE
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
            header('Content-Disposition: attachment; filename="' . $form->name . '_Layout_' . Carbon::now() . '.k3Form"');
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
    public function exportProject($pid) { //TODO::CASTLE
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

        header('Content-Disposition: attachment; filename="' . $proj->name . '_Layout_' . Carbon::now() . '.k3Proj"');
        header('Content-Type: application/octet-stream; ');

        echo json_encode($projArray);
        exit;
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