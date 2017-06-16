<?php namespace App\Http\Controllers;

use App\ComboListField;
use App\DateField;
use App\DocumentsField;
use App\DownloadTracker;
use App\Field;
use App\Form;
use App\GalleryField;
use App\GeneratedListField;
use App\GeolocatorField;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use App\ListField;
use App\Metadata;
use App\ModelField;
use App\MultiSelectListField;
use App\NumberField;
use App\OptionPreset;
use App\PlaylistField;
use App\Record;
use App\RecordPreset;
use App\RichTextField;
use App\ScheduleField;
use App\TextField;
use App\VideoField;
use Carbon\Carbon;
use CsvParser\Parser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ExportController extends Controller {
    /**
     * Standard output formats.
     * @var string.
     */
    const JSON = "JSON";
    const XML = "XML";
    const META = "META";

    /**
     * @var array
     */
    const VALID_FORMATS = [ self::JSON, self::XML, self::META ];

    /**
     * Export records of a particular form.
     *
     * @param int $pid, project id.
     * @param int $fid, form id.
     * @param string $type, type of the output.
     * @return mixed
     */
    public function exportRecords($pid, $fid, $type){
        if(!FormController::validProjForm($pid,$fid)){
            return redirect('projects/'.$pid);
        }

        $form = FormController::getForm($fid);

        if(!\Auth::user()->isFormAdmin($form)){
            return redirect('projects/'.$pid.'/forms/'.$fid);
        }

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

        if (file_exists($output)) { // File exists, so we download it.
            header("Content-Disposition: attachment; filename=\"" . basename($output) . "\"");
            header("Content-Type: application/octet-stream");
            header("Content-Length: " . filesize($output));

            readfile($output);

            $tracker->delete();
        }
        else { // File does not exist, so some kind of error occurred, and we redirect.
            $tracker->delete();

            flash()->overlay(trans("records_index.exporterror"), trans("controller_admin.whoops"));
            return redirect("projects/" . $pid . "/forms/" . $fid . "/records");
        }
    }

    /**
     * Checks if there is an active export for the form.
     * @param $fid, int
     * @return string, json object.
     */
    public function checkRecordExport($fid) {
        return json_encode(["finished" => !! DB::table("download_trackers")->where("fid", "=", $fid)->count()]);
    }

    private function xmlTagClear($value){
        $value = htmlentities($value);
        $value = str_replace(' ','_',$value);

        return $value;
    }

    public function exportRecordFiles($pid, $fid){
        if(!FormController::validProjForm($pid,$fid)){
            return redirect('projects/'.$pid);
        }

        $form = FormController::getForm($fid);

        if(!\Auth::user()->isFormAdmin($form)){
            return redirect('projects/'.$pid.'/forms/'.$fid);
        }

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

        foreach ($files as $name => $file)
        {
            // Skip directories (they would be added automatically)
            if (!$file->isDir())
            {
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

    public function exportForm($pid, $fid, $download=true)
    {
        if (!FormController::validProjForm($pid, $fid)) {
            return redirect('projects/'.$pid);
        }

        $form = FormController::getForm($fid);

        if (!\Auth::user()->isFormAdmin($form)) {
            return redirect('projects/' . $pid . '/forms/' . $fid);
        }

        $formArray = array();

        $formArray['name'] = $form->name;
        $formArray['slug'] = $form->slug;
        $formArray['desc'] = $form->description;
        $formArray['layout'] = $form->layout; //TODO::layout
        $formArray['preset'] = $form->preset;
        $formArray['metadata'] = $form->public_metadata;

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

        foreach($fields as $field){
            $fieldArray = array();

            $fieldArray['flid'] = $field->flid;
            $fieldArray['type'] = $field->type;
            $fieldArray['name'] = $field->name;
            $fieldArray['slug'] = $field->slug;
            $fieldArray['desc'] = $field->desc;
            $fieldArray['required'] = $field->required;
            $fieldArray['default'] = $field->default;
            $fieldArray['options'] = $field->options;

            $meta = Metadata::where('flid','=',$field->flid)->get()->first();
            if(!is_null($meta))
                $fieldArray['metadata'] = $meta->name;
            else
                $fieldArray['metadata'] = '';

            array_push($formArray['fields'],$fieldArray);

            //swap layout flid with slug for import
            //TODO::layout
            $formArray['layout'] = str_replace('<ID>'.$field->flid.'</ID>','<ID>'.$field->slug.'</ID>',$formArray['layout']);
        }

        if($download) {
            header("Content-Disposition: attachment; filename=" . $form->name . '_Layout_' . Carbon::now() . '.k3Form');
            header("Content-Type: application/octet-stream; ");

            echo json_encode($formArray);
        }else{
            return $formArray;
        }
    }

    public function exportProject($pid){
        if (!ProjectController::validProj($pid)) {
            return redirect('projects');
        }

        $proj = ProjectController::getProject($pid);

        if (!\Auth::user()->isProjectAdmin($proj)) {
            return redirect('projects/' . $pid);
        }

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
     * Simplifies export to work with an array of rids.
     * Makes an external call to the python exporter to speed things up (see: app/python).
     *
     * Note: unit tests are not possible for this function as the python exporter does not know about the test database.
     *
     * @param array $rids, array of rids to export.
     * @param string $format, the desired output format, defaults to JSON.
     * @return string | null, if the format is valid, will return the absolute path of the file the rids were exported to.
     */
    public static function exportWithRids(array $rids, $format = self::JSON) {
        $format = strtoupper($format);

        if ( ! self::isValidFormat($format)) {
            return null;
        }

        $rids = json_encode($rids);

        $exec_string = env("BASE_PATH") . "python/export.py \"$rids\" \"$format\"";
        exec($exec_string, $output);

        return $output[0];
    }

    /**
     * @param string $format
     * @return bool, true if valid.
     */
    public static function isValidFormat($format) {
        return in_array(($format), self::VALID_FORMATS);
    }
}