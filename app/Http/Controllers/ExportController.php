<?php namespace App\Http\Controllers;

use App\Field;
use App\Form;
use App\Record;
use Illuminate\Support\Facades\DB;
use App\Metadata;
use App\OptionPreset;
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
    const META = "META";

    /**
     * @var array - Array of those formats
     */
    const VALID_FORMATS = [ self::JSON, self::XML, self::KORA, self::META ];

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
    public function exportSelectedRecords($pid, $fid, $type, Request $request) {
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
    public function prepRecordFiles($pid, $fid) {
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
    public function exportRecordFiles($pid, $fid) {
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

        header('Content-Disposition: attachment; filename="' . $proj->name . '_Layout_' . Carbon::now() . '.k3Proj"');
        header('Content-Type: application/octet-stream; ');

        echo json_encode($projArray);
        exit;
    }

    private function imitateKeyMerge(&$array1, &$array2) {
        foreach($array2 as $k => $i) {
            $array1[$k] = $i;
        }
    }

    /**
     * Builds out the record data for the given RIDs. TODO::modular?
     *
     * @param  int $fid - Form ID (NOTE: Can be an array if records from multiple forms)
     * @param  array $rids - Record IDs
     * @param  string $format - Format of exported data
     * @param  bool $dataOnly - Do we want just the data, or the created file info
     * @param  array $options - Options for certain configurations of data
     * @return mixed - The export results. Array of records, or file download info
     */
    public function exportFormRecordData($fid, $rids, $format = self::JSON, $dataOnly = false, $options = null) {
        //If less than 500 records, no need to process everything. But beyond that, form based population seems to be faster
        $ridMode = false;
        if(sizeof($rids)<=500)
            $ridMode = true;

        $records = [];

        //Check to see if we should bother with options
        $useOpts = !is_null($options);

        //Make sure requested format exists
        $format = strtoupper($format);
        if(!self::isValidFormat($format))
            return null;

        //Set up the DB
        $con = mysqli_connect(
            config('database.connections.mysql.host'),
            config('database.connections.mysql.username'),
            config('database.connections.mysql.password'),
            config('database.connections.mysql.database')
        );
        $prefix = config('database.connections.mysql.prefix');

        //We want to make sure we are doing things in utf8 for special characters
        if(!mysqli_set_charset($con, "utf8")) {
            printf("Error loading character set utf8: %s\n", mysqli_error($con));
            exit();
        }

        //Temporary fix for large combolist statements
        $groupCat = $con->query("SET SESSION group_concat_max_len = 12345");

        //Grab information about the form's fields
        $fields = [];
        if(is_array($fid)) {
            //Global sort from API results case
            foreach($fid as $formID) {
                $form = FormController::getForm($formID);
                $fieldMods = $form->fields()->get();
                foreach($fieldMods as $field) {
                    $fArray['flid'] = $field->flid;
                    $fArray['name'] = $field->name;
                    $fArray['type'] = $field->type;
                    $fArray['nickname'] = $field->slug;
                    $fArray['options'] = $field->options;

                    //We want both so we can get field regardless of having id or slug
                    $fields[$field->flid] = $fArray;
                    $fields[$field->slug] = $fArray;
                }
            }
        } else {
            $form = FormController::getForm($fid);
            $fieldMods = $form->fields()->get();
            foreach($fieldMods as $field) {
                $fArray['flid'] = $field->flid;
                $fArray['name'] = $field->name;
                $fArray['type'] = $field->type;
                $fArray['nickname'] = $field->slug;
                $fArray['options'] = $field->options;

                //We want both so we can get field regardless of having id or slug
                $fields[$field->flid] = $fArray;
                $fields[$field->slug] = $fArray;
            }
        }

        //First option to check is the fields we want back, so lets pull out the slugs from options
        $slugQL = '';
        if($useOpts && isset($options['fields']) && $options['fields'] != 'ALL' && $options['fields'] != 'KID') { //The KID is for KORA exports
            $slugOpts = $options['fields'];

            if(!is_null($slugOpts)) {
                foreach ($slugOpts as $slug) {
                    $id = $fields[$slug]['flid'];
                    $slugQL .= "'$id',";
                }
                $slugQL = ' and flid in (' . substr($slugQL, 0, -1) . ')';
            }
        }

        //Gather the kid/rid pairs for the form
        if($ridMode) {
            $ridString = implode(',',$rids);
            $wherePiece = "`rid` IN ($ridString)";
        } else if(is_array($fid)) {
            //Global sort from API results case
            $fidString = implode(',',$fid);
            $wherePiece = "`fid` in ($fidString)";
        } else
            $wherePiece = "`fid`=$fid";

        $ridsToKids = array_fill_keys($rids, null);

        $select = "SELECT r.`rid`, r.`kid`, r.`legacy_kid`, r.`created_at`, r.`updated_at`, u.`username` FROM ".$prefix."records as r
                      LEFT JOIN ".$prefix."users as u on r.owner=u.id where r.$wherePiece";
        $kids = $con->query($select);
        $ridMeta = ($useOpts && isset($options['meta']) && $options['meta']);
        while($row = $kids->fetch_assoc()) {
            if(!array_key_exists($row['rid'],$ridsToKids))
                continue;
            $ridsToKids[$row['rid']] = $row['kid'];

            if($ridMeta && $format == self::JSON) {
                $records[$row['kid']] = [
                    'created_at' => $row['created_at'],
                    'updated_at' => $row['updated_at'],
                    'username' => $row['username'],
                ];
            } else if($format == self::KORA) {
                $records[$row['kid']] = [
                    'kid' => $row['kid'],
                    'legacy_kid' => $row['legacy_kid'],
                    'systimestamp' => $row['updated_at'],
                    'recordowner' => $row['username'],
                ];
            } else
                $records[$row['kid']] = [];
        }
        $kids->free();

        //Prep the table statements
        if($ridMode) {
            $ridString = implode(',',$rids);
            $wherePiece = "`rid` IN ($ridString)";
        } else if(is_array($fid)) {
            //Global sort from API results case
            $fidString = implode(',',$fid);
            $wherePiece = "`fid` in ($fidString)";
        } else
            $wherePiece = "`fid`=$fid";

        //NOTE: ORDER MATTERS WITH HOW TABLES ARE ACCESSED BELOW
        if($format == self::KORA) {
            //We only get a select set of field types that are kora 2 supported
            $dataselects = array(
                "SELECT `rid`, `flid`, `text` FROM " . $prefix . "text_fields where $wherePiece$slugQL",
                "SELECT `rid`, `flid`, `number` FROM " . $prefix . "number_fields where $wherePiece$slugQL",
                "SELECT `rid`, `flid`, `rawtext` FROM " . $prefix . "rich_text_fields where $wherePiece$slugQL",
                "SELECT `rid`, `flid`, `option` FROM " . $prefix . "list_fields where $wherePiece$slugQL",
                "SELECT `rid`, `flid`, `options` FROM " . $prefix . "multi_select_list_fields where $wherePiece$slugQL",
                "SELECT `rid`, `flid`, `options` FROM " . $prefix . "generated_list_fields where $wherePiece$slugQL",
                "SELECT `rid`, `flid`, `circa`, `month`, `day`, `year`, `era` FROM " . $prefix . "date_fields where $wherePiece$slugQL",
                "SELECT `rid`, `flid`, GROUP_CONCAT(`begin` SEPARATOR '[!]') as `value`, 
                  GROUP_CONCAT(`end` SEPARATOR '[!]') as `val2`, 
                  GROUP_CONCAT(`allday` SEPARATOR '[!]') as `val3`,
                  GROUP_CONCAT(`desc` SEPARATOR '[!]') as `val4` 
                  FROM " . $prefix . "schedule_support where $wherePiece$slugQL group by `rid`, `flid`",
                "SELECT `rid`, `flid`, `documents` FROM " . $prefix . "documents_fields where $wherePiece$slugQL",
                "SELECT `rid`, `flid`, `images`, `captions` FROM " . $prefix . "gallery_fields where $wherePiece$slugQL",
                "SELECT af.rid as `rid`, af.flid as `flid`, GROUP_CONCAT(aRec.kid SEPARATOR ',') as `value` 
                  FROM " . $prefix . "associator_support as af left join " . $prefix . "records as aRec on af.record=aRec.rid 
                  where af.$wherePiece$slugQL group by `rid`, `flid`"
            );
        } else {
            $dataselects = array(
                "SELECT `rid`, `flid`, `text` FROM " . $prefix . "text_fields where $wherePiece$slugQL",
                "SELECT `rid`, `flid`, `number` FROM " . $prefix . "number_fields where $wherePiece$slugQL",
                "SELECT `rid`, `flid`, `rawtext` FROM " . $prefix . "rich_text_fields where $wherePiece$slugQL",
                "SELECT `rid`, `flid`, `option` FROM " . $prefix . "list_fields where $wherePiece$slugQL",
                "SELECT `rid`, `flid`, `options` FROM " . $prefix . "multi_select_list_fields where $wherePiece$slugQL",
                "SELECT `rid`, `flid`, `options` FROM " . $prefix . "generated_list_fields where $wherePiece$slugQL",
                "SELECT `rid`, `flid`, GROUP_CONCAT(if(`field_num`=1, `data`, null) ORDER BY `list_index` ASC SEPARATOR '[!data!]' ) as `value`,
                  GROUP_CONCAT(if(`field_num`=2, `data`, null) ORDER BY `list_index` ASC SEPARATOR '[!data!]' ) as `val2`,
                  GROUP_CONCAT(if(`field_num`=1, `number`, null) ORDER BY `list_index` ASC SEPARATOR '[!data!]' ) as `val3`,
                  GROUP_CONCAT(if(`field_num`=2, `number`, null) ORDER BY `list_index` ASC SEPARATOR '[!data!]' ) as `val4` 
                  FROM " . $prefix . "combo_support where $wherePiece$slugQL group by `rid`, `flid`",
                "SELECT `rid`, `flid`, `circa`, `month`, `day`, `year`, `era` FROM " . $prefix . "date_fields where $wherePiece$slugQL",
                "SELECT `rid`, `flid`, GROUP_CONCAT(`begin` SEPARATOR '[!]') as `value`, 
                  GROUP_CONCAT(`end` SEPARATOR '[!]') as `val2`, 
                  GROUP_CONCAT(`allday` SEPARATOR '[!]') as `val3`,
                  GROUP_CONCAT(`desc` SEPARATOR '[!]') as `val4` 
                  FROM " . $prefix . "schedule_support where $wherePiece$slugQL group by `rid`, `flid`",
                "SELECT `rid`, `flid`, `documents` FROM " . $prefix . "documents_fields where $wherePiece$slugQL",
                "SELECT `rid`, `flid`, `images`, `captions` FROM " . $prefix . "gallery_fields where $wherePiece$slugQL",
                "SELECT `rid`, `flid`, `audio` FROM " . $prefix . "playlist_fields where $wherePiece$slugQL",
                "SELECT `rid`, `flid`, `video` FROM " . $prefix . "video_fields where $wherePiece$slugQL",
                "SELECT `rid`, `flid`, `model` FROM " . $prefix . "model_fields where $wherePiece$slugQL",
                "SELECT `rid`, `flid`, GROUP_CONCAT(`desc` SEPARATOR '[!]') as `value`, 
                  GROUP_CONCAT(`address` SEPARATOR '[!]') as `val2`, 
                  GROUP_CONCAT(CONCAT_WS('[!]', `lat`, `lon`) SEPARATOR '[!latlon!]') as `val3`, 
                  GROUP_CONCAT(CONCAT_WS('[!]', `zone`, `easting`, `northing`) SEPARATOR '[!utm!]') as `val4` 
                  FROM " . $prefix . "geolocator_support where $wherePiece$slugQL group by `rid`, `flid`",
                "SELECT af.rid as `rid`, af.flid as `flid`, GROUP_CONCAT(aRec.kid SEPARATOR ',') as `value` 
                  FROM " . $prefix . "associator_support as af left join " . $prefix . "records as aRec on af.record=aRec.rid 
                  where af.$wherePiece$slugQL group by `rid`, `flid`"
            );
        }
        $finalDataSelect = implode('; ',$dataselects);

        switch($format) {
            case self::JSON:
                //Next we see if metadata is requested for reverse associations
                if($useOpts && isset($options['meta']) && $options['meta']) {
                    $reverse = "SELECT aSupp.record as main, recs.kid as linker FROM ".$prefix."associator_support as aSupp
                      LEFT JOIN ".$prefix."records as recs on aSupp.rid=recs.rid WHERE aSupp.record in (".implode(', ',$rids).")";

                    $datafields = $con->query($reverse);
                    while($row = $datafields->fetch_assoc()) {
                        $kid = $ridsToKids[$row['main']];
                        if(!array_key_exists($kid,$records))
                            continue;

                        $records[$kid]["reverseAssociations"][] = $row['linker'];
                    }
                    $datafields->free();
                }

                //specifically for file exports, NOT API. API use the function above
                //It's a little different than META above in the sense we organize reverse associations by field ID
                if($useOpts && isset($options['revAssoc']) && $options['revAssoc']) {
                    $revAssoc = "SELECT aSupp.record as main, aSupp.flid as flid, recs.kid as linker FROM ".$prefix."associator_support as aSupp 
                      LEFT JOIN ".$prefix."records as recs on aSupp.rid=recs.rid WHERE aSupp.record in (".implode(', ',$rids).")";

                    $datafields = $con->query($revAssoc);
                    while($row = $datafields->fetch_assoc()) {
                        $kid = $ridsToKids[$row['main']];
                        if(!array_key_exists($kid,$records))
                            continue;

                        $records[$kid]["reverseAssociations"][$row['flid']][] = $row['linker'];
                    }
                    $datafields->free();
                }

                //Get the data
                $gatherRecordData = true;
                if($useOpts && isset($options['data']) && isset($options['data']))
                    $gatherRecordData = $options['data'];

                if($useOpts && $options['realnames'])
                    $fIndex = 'name';
                else
                    $fIndex = 'nickname';

                if($gatherRecordData) {
                    $con->multi_query($finalDataSelect);

                    //Text Field
                    $datafields = $con->store_result();
                    while($row = $datafields->fetch_assoc()) {
                        if(!array_key_exists($row['rid'], $ridsToKids))
                            continue;
                        $kid = $ridsToKids[$row['rid']];

                        $fieldIndex = $fields[$row['flid']][$fIndex];

                        $records[$kid][$fieldIndex]['value'] = $row['text'];
                    }
                    $datafields->free();
                    $con->next_result();

                    //Number Field
                    $datafields = $con->store_result();
                    while ($row = $datafields->fetch_assoc()) {
                        if(!array_key_exists($row['rid'], $ridsToKids))
                            continue;
                        $kid = $ridsToKids[$row['rid']];

                        $fieldIndex = $fields[$row['flid']][$fIndex];

                        $records[$kid][$fieldIndex]['value'] = $row['number'];
                    }
                    $datafields->free();
                    $con->next_result();

                    //Rich Text Field
                    $datafields = $con->store_result();
                    while ($row = $datafields->fetch_assoc()) {
                        if(!array_key_exists($row['rid'], $ridsToKids))
                            continue;
                        $kid = $ridsToKids[$row['rid']];

                        $fieldIndex = $fields[$row['flid']][$fIndex];

                        $records[$kid][$fieldIndex]['value'] = $row['rawtext'];
                    }
                    $datafields->free();
                    $con->next_result();

                    //List Field
                    $datafields = $con->store_result();
                    while ($row = $datafields->fetch_assoc()) {
                        if(!array_key_exists($row['rid'], $ridsToKids))
                            continue;
                        $kid = $ridsToKids[$row['rid']];

                        $fieldIndex = $fields[$row['flid']][$fIndex];

                        $records[$kid][$fieldIndex]['value'] = $row['option'];
                    }
                    $datafields->free();
                    $con->next_result();

                    //Multi-Select List Field
                    $datafields = $con->store_result();
                    while ($row = $datafields->fetch_assoc()) {
                        if(!array_key_exists($row['rid'], $ridsToKids))
                            continue;
                        $kid = $ridsToKids[$row['rid']];

                        $fieldIndex = $fields[$row['flid']][$fIndex];

                        $records[$kid][$fieldIndex]['value'] = explode('[!]', $row['options']);
                    }
                    $datafields->free();
                    $con->next_result();

                    //Generated List Field
                    $datafields = $con->store_result();
                    while ($row = $datafields->fetch_assoc()) {
                        if(!array_key_exists($row['rid'], $ridsToKids))
                            continue;
                        $kid = $ridsToKids[$row['rid']];

                        $fieldIndex = $fields[$row['flid']][$fIndex];

                        $records[$kid][$fieldIndex]['value'] = explode('[!]', $row['options']);
                    }
                    $datafields->free();
                    $con->next_result();

                    //Combo List Field
                    $datafields = $con->store_result();
                    while ($row = $datafields->fetch_assoc()) {
                        if(!array_key_exists($row['rid'], $ridsToKids))
                            continue;
                        $kid = $ridsToKids[$row['rid']];

                        $fieldIndex = $fields[$row['flid']][$fIndex];

                        $value = array();
                        $dataone = explode('[!data!]', $row['value']);
                        $datatwo = explode('[!data!]', $row['val2']);
                        $numberone = explode('[!data!]', $row['val3']);
                        $numbertwo = explode('[!data!]', $row['val4']);
                        $typeone = explode('[Type]', explode('[!Field1!][Type]', $fields[$row['flid']]['options'])[1])[0];
                        $typetwo = explode('[Type]', explode('[!Field2!][Type]', $fields[$row['flid']]['options'])[1])[0];
                        if ($typeone == 'Number')
                            $cnt = sizeof($numberone);
                        else
                            $cnt = sizeof($dataone);
                        $nameone = explode('[Name]', explode('[Type][Name]', $fields[$row['flid']]['options'])[1])[0];
                        $nametwo = explode('[Name]', explode('[Type][Name]', $fields[$row['flid']]['options'])[2])[0];

                        for ($c = 0; $c < $cnt; $c++) {
                            $val = [];

                            switch ($typeone) {
                                case Field::_MULTI_SELECT_LIST:
                                case Field::_GENERATED_LIST:
                                    $valone = explode('[!]', $dataone[$c]);
                                    break;
                                case Field::_NUMBER:
                                    $valone = $numberone[$c];
                                    break;
                                default:
                                    $valone = $dataone[$c];
                                    break;
                            }

                            switch ($typetwo) {
                                case Field::_MULTI_SELECT_LIST:
                                case Field::_GENERATED_LIST:
                                    $valtwo = explode('[!]', $datatwo[$c]);
                                    break;
                                case Field::_NUMBER:
                                    $valtwo = $numbertwo[$c];
                                    break;
                                default:
                                    $valtwo = $datatwo[$c];
                                    break;
                            }

                            $val[$nameone] = $valone;
                            $val[$nametwo] = $valtwo;

                            $value[] = $val;
                        }

                        $records[$kid][$fieldIndex]['value'] = $value;
                    }
                    $datafields->free();
                    $con->next_result();

                    //Date Field
                    $datafields = $con->store_result();
                    while ($row = $datafields->fetch_assoc()) {
                        if(!array_key_exists($row['rid'], $ridsToKids))
                            continue;
                        $kid = $ridsToKids[$row['rid']];

                        $fieldIndex = $fields[$row['flid']][$fIndex];

                        $records[$kid][$fieldIndex]['value'] = [
                            'circa' => $row['circa'],
                            'month' => $row['month'],
                            'day' => $row['day'],
                            'year' => $row['year'],
                            'era' => $row['era']
                        ];
                    }
                    $datafields->free();
                    $con->next_result();

                    //Schedule Field
                    $datafields = $con->store_result();
                    while ($row = $datafields->fetch_assoc()) {
                        if(!array_key_exists($row['rid'], $ridsToKids))
                            continue;
                        $kid = $ridsToKids[$row['rid']];

                        $fieldIndex = $fields[$row['flid']][$fIndex];

                        $value = array();
                        $begin = explode('[!]', $row['value']);
                        $cnt = sizeof($begin);
                        $end = explode('[!]', $row['val2']);
                        $allday = explode('[!]', $row['val3']);
                        $desc = explode('[!]', $row['val4']);
                        for ($i = 0; $i < $cnt; $i++) {
                            if ($allday[$i] == 1) {
                                $formatBegin = date("m/d/Y", strtotime($begin[$i]));
                                $formatEnd = date("m/d/Y", strtotime($end[$i]));
                            } else {
                                $formatBegin = date("m/d/Y h:i A", strtotime($begin[$i]));
                                $formatEnd = date("m/d/Y h:i A", strtotime($end[$i]));
                            }
                            $info = [
                                'begin' => $formatBegin,
                                'end' => $formatEnd,
                                'allday' => $allday[$i],
                                'desc' => $desc[$i]
                            ];
                            $value[] = $info;
                        }

                        $records[$kid][$fieldIndex]['value'] = $value;
                    }
                    $datafields->free();
                    $con->next_result();

                    //Documents Field
                    $datafields = $con->store_result();
                    while ($row = $datafields->fetch_assoc()) {
                        if(!array_key_exists($row['rid'], $ridsToKids))
                            continue;
                        $kid = $ridsToKids[$row['rid']];

                        $fieldIndex = $fields[$row['flid']][$fIndex];

                        $url = url('app/files/p' . $form->pid . '/f' . $form->fid . '/r' . $row['rid'] . '/fl' . $row['flid']) . '/';
                        $value = array();
                        $files = explode('[!]', $row['documents']);
                        foreach ($files as $file) {
                            $info = [
                                'name' => explode('[Name]', $file)[1],
                                'size' => floatval(explode('[Size]', $file)[1]) / 1000 . " mb",
                                'type' => explode('[Type]', $file)[1],
                                'url' => $url . explode('[Name]', $file)[1]
                            ];
                            $value[] = $info;
                        }
                        $records[$kid][$fieldIndex]['value'] = $value;
                    }
                    $datafields->free();
                    $con->next_result();

                    //Gallery Field
                    $datafields = $con->store_result();
                    while ($row = $datafields->fetch_assoc()) {
                        if(!array_key_exists($row['rid'], $ridsToKids))
                            continue;
                        $kid = $ridsToKids[$row['rid']];

                        $fieldIndex = $fields[$row['flid']][$fIndex];

                        $url = url('app/files/p' . $form->pid . '/f' . $form->fid . '/r' . $row['rid'] . '/fl' . $row['flid']) . '/';
                        $value = array();
                        $files = explode('[!]', $row['images']);
                        $captions = (!is_null($row['captions']) && $row['captions'] != '') ? explode('[!]', $row['captions']) : null;
                        for ($gi = 0; $gi < sizeof($files); $gi++) {
                            $info = [
                                'name' => explode('[Name]', $files[$gi])[1],
                                'size' => floatval(explode('[Size]', $files[$gi])[1]) / 1000 . " mb",
                                'type' => explode('[Type]', $files[$gi])[1],
                                'url' => $url . explode('[Name]', $files[$gi])[1]
                            ];
                            if (!is_null($captions))
                                $info['caption'] = $captions[$gi];
                            else
                                $info['caption'] = '';
                            $value[] = $info;
                        }
                        $records[$kid][$fieldIndex]['value'] = $value;
                    }
                    $datafields->free();
                    $con->next_result();

                    //Playlist Field
                    $datafields = $con->store_result();
                    while ($row = $datafields->fetch_assoc()) {
                        if(!array_key_exists($row['rid'], $ridsToKids))
                            continue;
                        $kid = $ridsToKids[$row['rid']];

                        $fieldIndex = $fields[$row['flid']][$fIndex];

                        $url = url('app/files/p' . $form->pid . '/f' . $form->fid . '/r' . $row['rid'] . '/fl' . $row['flid']) . '/';
                        $value = array();
                        $files = explode('[!]', $row['audio']);
                        foreach ($files as $file) {
                            $info = [
                                'name' => explode('[Name]', $file)[1],
                                'size' => floatval(explode('[Size]', $file)[1]) / 1000 . " mb",
                                'type' => explode('[Type]', $file)[1],
                                'url' => $url . explode('[Name]', $file)[1]
                            ];
                            $value[] = $info;
                        }
                        $records[$kid][$fieldIndex]['value'] = $value;
                    }
                    $datafields->free();
                    $con->next_result();

                    //Video Field
                    $datafields = $con->store_result();
                    while ($row = $datafields->fetch_assoc()) {
                        if(!array_key_exists($row['rid'], $ridsToKids))
                            continue;
                        $kid = $ridsToKids[$row['rid']];

                        $fieldIndex = $fields[$row['flid']][$fIndex];

                        $url = url('app/files/p' . $form->pid . '/f' . $form->fid . '/r' . $row['rid'] . '/fl' . $row['flid']) . '/';
                        $value = array();
                        $files = explode('[!]', $row['video']);
                        foreach ($files as $file) {
                            $info = [
                                'name' => explode('[Name]', $file)[1],
                                'size' => floatval(explode('[Size]', $file)[1]) / 1000 . " mb",
                                'type' => explode('[Type]', $file)[1],
                                'url' => $url . explode('[Name]', $file)[1]
                            ];
                            $value[] = $info;
                        }
                        $records[$kid][$fieldIndex]['value'] = $value;
                    }
                    $datafields->free();
                    $con->next_result();

                    //Model Field
                    $datafields = $con->store_result();
                    while ($row = $datafields->fetch_assoc()) {
                        if(!array_key_exists($row['rid'], $ridsToKids))
                            continue;
                        $kid = $ridsToKids[$row['rid']];

                        $fieldIndex = $fields[$row['flid']][$fIndex];

                        $url = url('app/files/p' . $form->pid . '/f' . $form->fid . '/r' . $row['rid'] . '/fl' . $row['flid']) . '/';
                        $value = array();
                        $files = explode('[!]', $row['model']);
                        foreach ($files as $file) {
                            $info = [
                                'name' => explode('[Name]', $file)[1],
                                'size' => floatval(explode('[Size]', $file)[1]) / 1000 . " mb",
                                'type' => explode('[Type]', $file)[1],
                                'url' => $url . explode('[Name]', $file)[1]
                            ];
                            $value[] = $info;
                        }
                        $records[$kid][$fieldIndex]['value'] = $value;
                    }
                    $datafields->free();
                    $con->next_result();

                    //Geolocator Field
                    $datafields = $con->store_result();
                    while ($row = $datafields->fetch_assoc()) {
                        if(!array_key_exists($row['rid'], $ridsToKids))
                            continue;
                        $kid = $ridsToKids[$row['rid']];

                        $fieldIndex = $fields[$row['flid']][$fIndex];

                        $value = array();
                        $desc = explode('[!]', $row['value']);
                        $cnt = sizeof($desc);
                        $address = explode('[!]', $row['val2']);
                        $latlon = explode('[!latlon!]', $row['val3']);
                        $utm = explode('[!utm!]', $row['val4']);
                        for ($i = 0; $i < $cnt; $i++) {
                            $ll = explode('[!]', $latlon[$i]);
                            $u = explode('[!]', $utm[$i]);
                            $info = [
                                'desc' => $desc[$i],
                                'lat' => $ll[0],
                                'lon' => $ll[1],
                                'zone' => $u[0],
                                'east' => $u[1],
                                'north' => $u[2],
                                'address' => $address[$i],
                            ];

                            $value[] = $info;
                        }

                        $records[$kid][$fieldIndex]['value'] = $value;
                    }
                    $datafields->free();
                    $con->next_result();

                    //Associator Field
                    $datafields = $con->store_result();
                    while ($row = $datafields->fetch_assoc()) {
                        if(!array_key_exists($row['rid'], $ridsToKids))
                            continue;
                        $kid = $ridsToKids[$row['rid']];

                        $fieldIndex = $fields[$row['flid']][$fIndex];

                        if($useOpts && isset($options['assoc']) && $options['assoc']) {
                            //First we need to format these kids as rids
                            $vals = explode(',', $row['value']);
                            foreach ($vals as $akid) {
                                if(Record::isKIDPattern($akid)) {
                                    $arid = explode('-',$akid)[2];
                                    $afid = explode('-',$akid)[1];
                                    $records[$kid][$fieldIndex][$akid] = $this->getSingleRecordForAssoc($arid, $con, $afid, $fIndex);
                                }
                            }
                        } else {
                            $records[$kid][$fieldIndex]['value'] = explode(',', $row['value']);
                        }
                    }
                    $datafields->free();
                }

                $records = json_encode($records);

                if($dataOnly) {
                    mysqli_close($con);
                    return $records;
                } else {
                    $dt = new \DateTime();
                    $format = $dt->format('Y_m_d_H_i_s');
                    $path = storage_path("app/exports/record_export_$format.json");

                    file_put_contents($path, $records);

                    mysqli_close($con);
                    return $path;
                }
                break;
            case self::KORA:
                if($useOpts && isset($options['fields']) && $options['fields'] == 'KID')
                    return json_encode(array_keys($records));

                //Add those blank values
                $fieldKeys = [];
                foreach($fieldMods as $field) {
                    $fieldKeys[$field['name']] = '';
                }
                foreach($records as $kid => $data) {
                    $this->imitateKeyMerge($records[$kid],$fieldKeys);
                }

                //Meta data function but for old Kora format
                $reverse = "SELECT aSupp.record as main, recs.kid as linker FROM ".$prefix."associator_support as aSupp 
                      LEFT JOIN ".$prefix."records as recs on aSupp.rid=recs.rid WHERE aSupp.record in (".implode(', ',$rids).")";

                $datafields = $con->query($reverse);
                while($row = $datafields->fetch_assoc()) {
                    $records[$row['main']]["linkers"][] = $row['linker'];
                }
                mysqli_free_result($datafields);

                //Back to regular data
                $con->multi_query($finalDataSelect);

                //Text Field
                $datafields = $con->store_result();
                while($row = $datafields->fetch_assoc()) {
                    if(!array_key_exists($row['rid'], $ridsToKids))
                        continue;
                    $kid = $ridsToKids[$row['rid']];

                    $records[$kid][$fields[$row['flid']]['name']] = $row['text'];
                }
                $datafields->free();
                $con->next_result();

                //Number Field
                $datafields = $con->store_result();
                while($row = $datafields->fetch_assoc()) {
                    if(!array_key_exists($row['rid'], $ridsToKids))
                        continue;
                    $kid = $ridsToKids[$row['rid']];

                    $records[$kid][$fields[$row['flid']]['name']] = $row['number'];
                }
                $datafields->free();
                $con->next_result();

                //Raw Text Field
                $datafields = $con->store_result();
                while($row = $datafields->fetch_assoc()) {
                    if(!array_key_exists($row['rid'], $ridsToKids))
                        continue;
                    $kid = $ridsToKids[$row['rid']];

                    $records[$kid][$fields[$row['flid']]['name']] = $row['rawtext'];
                }
                $datafields->free();
                $con->next_result();

                //List Field
                $datafields = $con->store_result();
                while($row = $datafields->fetch_assoc()) {
                    if(!array_key_exists($row['rid'], $ridsToKids))
                        continue;
                    $kid = $ridsToKids[$row['rid']];

                    $records[$kid][$fields[$row['flid']]['name']] = $row['option'];
                }
                $datafields->free();
                $con->next_result();

                //Multi-Select List Field
                $datafields = $con->store_result();
                while($row = $datafields->fetch_assoc()) {
                    if(!array_key_exists($row['rid'], $ridsToKids))
                        continue;
                    $kid = $ridsToKids[$row['rid']];

                    $records[$kid][$fields[$row['flid']]['name']] = explode('[!]',$row['options']);
                }
                $datafields->free();
                $con->next_result();

                //Generated List Field
                $datafields = $con->store_result();
                while($row = $datafields->fetch_assoc()) {
                    if(!array_key_exists($row['rid'], $ridsToKids))
                        continue;
                    $kid = $ridsToKids[$row['rid']];

                    $records[$kid][$fields[$row['flid']]['name']] = explode('[!]',$row['options']);
                }
                $datafields->free();
                $con->next_result();

                //Date Field
                $datafields = $con->store_result();
                while($row = $datafields->fetch_assoc()) {
                    if(!array_key_exists($row['rid'], $ridsToKids))
                        continue;
                    $kid = $ridsToKids[$row['rid']];

                    $records[$kid][$fields[$row['flid']]['name']] = [
                        'prefix' => $row['circa'],
                        'month' => $row['month'],
                        'day' => $row['day'],
                        'year' => $row['year'],
                        'era' => $row['era'],
                        'suffix' => ''
                    ];
                }
                $datafields->free();
                $con->next_result();

                //Schedule Field
                $datafields = $con->store_result();
                while($row = $datafields->fetch_assoc()) {
                    if(!array_key_exists($row['rid'], $ridsToKids))
                        continue;
                    $kid = $ridsToKids[$row['rid']];

                    $value = array();
                    $begin = explode('[!]',$row['value']);
                    foreach($begin as $date) {
                        $harddate = explode(' ',$date)[0];
                        $value[] = $harddate;
                    }

                    $records[$kid][$fields[$row['flid']]['name']] = $value;
                }
                $datafields->free();
                $con->next_result();

                //Documents Field
                $datafields = $con->store_result();
                while($row = $datafields->fetch_assoc()) {
                    if(!array_key_exists($row['rid'], $ridsToKids))
                        continue;
                    $kid = $ridsToKids[$row['rid']];

                    $url = $row['rid'].'/fl'.$row['flid'] . '/';
                    $files = explode('[!]',$row['documents']);
                    $file = $files[0];
                    $info = [
                        'originalName' => explode('[Name]',$file)[1],
                        'size' => floatval(explode('[Size]',$file)[1])/1000 . " mb",
                        'type' => explode('[Type]',$file)[1],
                        'localName' => $url.explode('[Name]',$file)[1]
                    ];

                    $records[$kid][$fields[$row['flid']]['name']] = $info;
                }
                $datafields->free();
                $con->next_result();

                //Gallery Field
                $datafields = $con->store_result();
                while($row = $datafields->fetch_assoc()) {
                    if(!array_key_exists($row['rid'], $ridsToKids))
                        continue;
                    $kid = $ridsToKids[$row['rid']];

                    $url = $row['rid'].'/fl'.$row['flid'] . '/';
                    $files = explode('[!]',$row['images']);
                    $file = $files[0];
                    $info = [
                        'originalName' => explode('[Name]',$file)[1],
                        'size' => floatval(explode('[Size]',$file)[1])/1000 . " mb",
                        'type' => explode('[Type]',$file)[1],
                        'localName' => $url.explode('[Name]',$file)[1]
                    ];

                    $records[$kid][$fields[$row['flid']]['name']] = $info;
                }
                $datafields->free();
                $con->next_result();

                //Associator Field
                $datafields = $con->store_result();
                while($row = $datafields->fetch_assoc()) {
                    if(!array_key_exists($row['rid'], $ridsToKids))
                        continue;
                    $kid = $ridsToKids[$row['rid']];

                    $records[$kid][$fields[$row['flid']]['name']] = explode(',',$row['value']);
                }
                $datafields->free();

                return json_encode($records);
                break;
            case self::XML:
                //Gonna treat things a little different
                $recordData = $records;
                $records = '<?xml version="1.0" encoding="utf-8"?><Records>';

                //Begin data
                $con->multi_query($finalDataSelect);

                //Text Field
                $datafields = $con->store_result();
                while($row = $datafields->fetch_assoc()) {
                    $kid = $ridsToKids[$row['rid']];
                    if(!array_key_exists($kid,$recordData))
                        continue;

                    $fieldxml = '<'.$fields[$row['flid']]['nickname'].' type="'.$fields[$row['flid']]['type'].'">';

                    $fieldxml .= htmlspecialchars($row['text'], ENT_XML1, 'UTF-8');

                    $fieldxml .= '</'.$fields[$row['flid']]['nickname'].'>';
                    if($recordData[$kid] == [])
                        $recordData[$kid] = $fieldxml;
                    else
                        $recordData[$kid] .= $fieldxml;
                }
                $datafields->free();
                $con->next_result();

                //Number Field
                $datafields = $con->store_result();
                while($row = $datafields->fetch_assoc()) {
                    $kid = $ridsToKids[$row['rid']];
                    if(!array_key_exists($kid,$recordData))
                        continue;

                    $fieldxml = '<'.$fields[$row['flid']]['nickname'].' type="'.$fields[$row['flid']]['type'].'">';

                    $fieldxml .= htmlspecialchars((float)$row['number'], ENT_XML1, 'UTF-8');

                    $fieldxml .= '</'.$fields[$row['flid']]['nickname'].'>';
                    if($recordData[$kid] == [])
                        $recordData[$kid] = $fieldxml;
                    else
                        $recordData[$kid] .= $fieldxml;
                }
                $datafields->free();
                $con->next_result();

                //Rich Text Field
                $datafields = $con->store_result();
                while($row = $datafields->fetch_assoc()) {
                    $kid = $ridsToKids[$row['rid']];
                    if(!array_key_exists($kid,$recordData))
                        continue;

                    $fieldxml = '<'.$fields[$row['flid']]['nickname'].' type="'.$fields[$row['flid']]['type'].'">';

                    $fieldxml .= htmlspecialchars($row['rawtext'], ENT_XML1, 'UTF-8');

                    $fieldxml .= '</'.$fields[$row['flid']]['nickname'].'>';
                    if($recordData[$kid] == [])
                        $recordData[$kid] = $fieldxml;
                    else
                        $recordData[$kid] .= $fieldxml;
                }
                $datafields->free();
                $con->next_result();

                //List Field
                $datafields = $con->store_result();
                while($row = $datafields->fetch_assoc()) {
                    $kid = $ridsToKids[$row['rid']];
                    if(!array_key_exists($kid,$recordData))
                        continue;

                    $fieldxml = '<'.$fields[$row['flid']]['nickname'].' type="'.$fields[$row['flid']]['type'].'">';

                    $fieldxml .= htmlspecialchars($row['option'], ENT_XML1, 'UTF-8');

                    $fieldxml .= '</'.$fields[$row['flid']]['nickname'].'>';
                    if($recordData[$kid] == [])
                        $recordData[$kid] = $fieldxml;
                    else
                        $recordData[$kid] .= $fieldxml;
                }
                $datafields->free();
                $con->next_result();

                //Multi-Select List Field
                $datafields = $con->store_result();
                while($row = $datafields->fetch_assoc()) {
                    $kid = $ridsToKids[$row['rid']];
                    if(!array_key_exists($kid,$recordData))
                        continue;

                    $fieldxml = '<'.$fields[$row['flid']]['nickname'].' type="'.$fields[$row['flid']]['type'].'">';

                    $opts = explode('[!]',$row['options']);
                    foreach($opts as $opt) {
                        $fieldxml .= '<value>'.htmlspecialchars($opt, ENT_XML1, 'UTF-8').'</value>';
                    }

                    $fieldxml .= '</'.$fields[$row['flid']]['nickname'].'>';
                    if($recordData[$kid] == [])
                        $recordData[$kid] = $fieldxml;
                    else
                        $recordData[$kid] .= $fieldxml;
                }
                $datafields->free();
                $con->next_result();

                //Generated List Field
                $datafields = $con->store_result();
                while($row = $datafields->fetch_assoc()) {
                    $kid = $ridsToKids[$row['rid']];
                    if(!array_key_exists($kid,$recordData))
                        continue;

                    $fieldxml = '<'.$fields[$row['flid']]['nickname'].' type="'.$fields[$row['flid']]['type'].'">';

                    $opts = explode('[!]',$row['options']);
                    foreach($opts as $opt) {
                        $fieldxml .= '<value>'.htmlspecialchars($opt, ENT_XML1, 'UTF-8').'</value>';
                    }

                    $fieldxml .= '</'.$fields[$row['flid']]['nickname'].'>';
                    if($recordData[$kid] == [])
                        $recordData[$kid] = $fieldxml;
                    else
                        $recordData[$kid] .= $fieldxml;
                }
                $datafields->free();
                $con->next_result();

                //Combo List Field
                $datafields = $con->store_result();
                while($row = $datafields->fetch_assoc()) {
                    $kid = $ridsToKids[$row['rid']];
                    if(!array_key_exists($kid,$recordData))
                        continue;

                    $fieldxml = '<'.$fields[$row['flid']]['nickname'].' type="'.$fields[$row['flid']]['type'].'">';

                    $dataone = explode('[!data!]',$row['value']);
                    $datatwo = explode('[!data!]',$row['val2']);
                    $numberone = explode('[!data!]',$row['val3']);
                    $numbertwo = explode('[!data!]',$row['val4']);
                    $typeone = explode('[Type]', explode('[!Field1!][Type]', $fields[$row['flid']]['options'])[1])[0];
                    $typetwo = explode('[Type]', explode('[!Field2!][Type]', $fields[$row['flid']]['options'])[1])[0];
                    if($typeone=='Number')
                        $cnt = sizeof($numberone);
                    else
                        $cnt = sizeof($dataone);
                    $nameone = explode('[Name]', explode('[Type][Name]', $fields[$row['flid']]['options'])[1])[0];
                    $nametwo = explode('[Name]', explode('[Type][Name]', $fields[$row['flid']]['options'])[2])[0];
                    for($c=0;$c<$cnt;$c++) {
                        switch($typeone) {
                            case Field::_MULTI_SELECT_LIST:
                            case Field::_GENERATED_LIST:
                                $valone = '';
                                $vals = explode('[!]',$dataone[$c]);
                                foreach($vals as $v) {
                                    $valone .= '<value>'.htmlspecialchars($v, ENT_XML1, 'UTF-8').'</value>';
                                }
                                break;
                            case Field::_NUMBER:
                                $valone = htmlspecialchars($numberone[$c], ENT_XML1, 'UTF-8');
                                break;
                            default:
                                $valone = htmlspecialchars($dataone[$c], ENT_XML1, 'UTF-8');
                                break;
                        }
                        switch($typetwo) {
                            case Field::_MULTI_SELECT_LIST:
                            case Field::_GENERATED_LIST:
                                $valtwo = '';
                                $vals = explode('[!]',$datatwo[$c]);
                                foreach($vals as $v) {
                                    $valtwo .= '<value>'.htmlspecialchars($v, ENT_XML1, 'UTF-8').'</value>';
                                }
                                break;
                            case Field::_NUMBER:
                                $valtwo = htmlspecialchars($numbertwo[$c], ENT_XML1, 'UTF-8');
                                break;
                            default:
                                $valtwo = htmlspecialchars($datatwo[$c], ENT_XML1, 'UTF-8');
                                break;
                        }
                        $fieldxml .= '<Value><'.$nameone.'>'.$valone.'</'.$nameone.'><'.$nametwo.'>'.$valtwo.'</'.$nametwo.'></Value>';
                    }


                    $fieldxml .= '</'.$fields[$row['flid']]['nickname'].'>';
                    if($recordData[$kid] == [])
                        $recordData[$kid] = $fieldxml;
                    else
                        $recordData[$kid] .= $fieldxml;
                }
                $datafields->free();
                $con->next_result();

                //Date Field
                $datafields = $con->store_result();
                while($row = $datafields->fetch_assoc()) {
                    $kid = $ridsToKids[$row['rid']];
                    if(!array_key_exists($kid,$recordData))
                        continue;

                    $fieldxml = '<'.$fields[$row['flid']]['nickname'].' type="'.$fields[$row['flid']]['type'].'">';

                    $fieldxml .= '<Circa>'.$row['circa'].'</Circa>';
                    $fieldxml .= '<Month>'.$row['month'].'</Month>';
                    $fieldxml .= '<Day>'.$row['day'].'</Day>';
                    $fieldxml .= '<Year>'.$row['year'].'</Year>';
                    $fieldxml .= '<Era>'.$row['era'].'</Era>';

                    $fieldxml .= '</'.$fields[$row['flid']]['nickname'].'>';
                    if($recordData[$kid] == [])
                        $recordData[$kid] = $fieldxml;
                    else
                        $recordData[$kid] .= $fieldxml;
                }
                $datafields->free();
                $con->next_result();

                //Schedule Field
                $datafields = $con->store_result();
                while($row = $datafields->fetch_assoc()) {
                    $kid = $ridsToKids[$row['rid']];
                    if(!array_key_exists($kid,$recordData))
                        continue;

                    $fieldxml = '<'.$fields[$row['flid']]['nickname'].' type="'.$fields[$row['flid']]['type'].'">';

                    $begin = explode('[!]',$row['value']);
                    $cnt = sizeof($begin);
                    $end = explode('[!]',$row['val2']);
                    $allday = explode('[!]',$row['val3']);
                    $desc = explode('[!]',$row['val4']);
                    for($i=0;$i<$cnt;$i++) {
                        if($allday[$i]==1) {
                            $formatBegin = date("m/d/Y", strtotime($begin[$i]));
                            $formatEnd = date("m/d/Y", strtotime($end[$i]));
                        } else {
                            $formatBegin = date("m/d/Y h:i A", strtotime($begin[$i]));
                            $formatEnd = date("m/d/Y h:i A", strtotime($end[$i]));
                        }
                        $fieldxml .= '<Event>';
                        $fieldxml .= '<Title>' . htmlspecialchars($desc[$i], ENT_XML1, 'UTF-8') . '</Title>';
                        $fieldxml .= '<Begin>' . htmlspecialchars($formatBegin, ENT_XML1, 'UTF-8') . '</Begin>';
                        $fieldxml .= '<End>' . htmlspecialchars($formatEnd, ENT_XML1, 'UTF-8') . '</End>';
                        $fieldxml .= '<All_Day>' . htmlspecialchars($allday[$i], ENT_XML1, 'UTF-8') . '</All_Day>';
                        $fieldxml .= '</Event>';
                    }

                    $fieldxml .= '</'.$fields[$row['flid']]['nickname'].'>';
                    if($recordData[$kid] == [])
                        $recordData[$kid] = $fieldxml;
                    else
                        $recordData[$kid] .= $fieldxml;
                }
                $datafields->free();
                $con->next_result();

                //Documents Field
                $datafields = $con->store_result();
                while($row = $datafields->fetch_assoc()) {
                    $kid = $ridsToKids[$row['rid']];
                    if(!array_key_exists($kid,$recordData))
                        continue;

                    $fieldxml = '<'.$fields[$row['flid']]['nickname'].' type="'.$fields[$row['flid']]['type'].'">';

                    $url = url('app/files/p'.$form->pid.'/f'.$form->fid.'/r'.$row['rid'].'/fl'.$row['flid']) . '/';
                    $files = explode('[!]',$row['documents']);
                    foreach($files as $file) {
                        $fieldxml .= '<File>';
                        $fieldxml .= '<Name>' . htmlspecialchars(explode('[Name]',$file)[1], ENT_XML1, 'UTF-8') . '</Name>';
                        $fieldxml .= '<Size>' . floatval(explode('[Size]',$file)[1])/1000 . ' mb</Size>';
                        $fieldxml .= '<Type>' . explode('[Type]',$file)[1] . '</Type>';
                        $fieldxml .= '<Url>' . htmlspecialchars($url.explode('[Name]',$file)[1], ENT_XML1, 'UTF-8') . '</Url>';
                        $fieldxml .= '</File>';
                    }

                    $fieldxml .= '</'.$fields[$row['flid']]['nickname'].'>';
                    if($recordData[$kid] == [])
                        $recordData[$kid] = $fieldxml;
                    else
                        $recordData[$kid] .= $fieldxml;
                }
                $datafields->free();
                $con->next_result();

                //Gallery Field
                $datafields = $con->store_result();
                while($row = $datafields->fetch_assoc()) {
                    $kid = $ridsToKids[$row['rid']];
                    if(!array_key_exists($kid,$recordData))
                        continue;

                    $fieldxml = '<'.$fields[$row['flid']]['nickname'].' type="'.$fields[$row['flid']]['type'].'">';

                    $url = url('app/files/p'.$form->pid.'/f'.$form->fid.'/r'.$row['rid'].'/fl'.$row['flid']) . '/';
                    $files = explode('[!]',$row['images']);
                    $captions = (!is_null($row['captions']) && $row['captions']!='') ? explode('[!]',$row['captions']) : null;
                    for($gi=0;$gi<sizeof($files);$gi++) {
                        $fieldxml .= '<File>';
                        $fieldxml .= '<Name>' . htmlspecialchars(explode('[Name]',$files[$gi])[1], ENT_XML1, 'UTF-8') . '</Name>';
                        if(!is_null($captions))
                            $fieldxml .= '<Caption>' . htmlspecialchars($captions[$gi], ENT_XML1, 'UTF-8') . '</Caption>';
                        else
                            $fieldxml .= '<Caption></Caption>';
                        $fieldxml .= '<Size>' . floatval(explode('[Size]',$files[$gi])[1])/1000 . ' mb</Size>';
                        $fieldxml .= '<Type>' . explode('[Type]',$files[$gi])[1] . '</Type>';
                        $fieldxml .= '<Url>' . htmlspecialchars($url.explode('[Name]',$files[$gi])[1], ENT_XML1, 'UTF-8') . '</Url>';
                        $fieldxml .= '</File>';
                    }

                    $fieldxml .= '</'.$fields[$row['flid']]['nickname'].'>';
                    if($recordData[$kid] == [])
                        $recordData[$kid] = $fieldxml;
                    else
                        $recordData[$kid] .= $fieldxml;
                }
                $datafields->free();
                $con->next_result();

                //Playlist Field
                $datafields = $con->store_result();
                while($row = $datafields->fetch_assoc()) {
                    $kid = $ridsToKids[$row['rid']];
                    if(!array_key_exists($kid,$recordData))
                        continue;

                    $fieldxml = '<'.$fields[$row['flid']]['nickname'].' type="'.$fields[$row['flid']]['type'].'">';

                    $url = url('app/files/p'.$form->pid.'/f'.$form->fid.'/r'.$row['rid'].'/fl'.$row['flid']) . '/';
                    $files = explode('[!]',$row['audio']);
                    foreach($files as $file) {
                        $fieldxml .= '<File>';
                        $fieldxml .= '<Name>' . htmlspecialchars(explode('[Name]',$file)[1], ENT_XML1, 'UTF-8') . '</Name>';
                        $fieldxml .= '<Size>' . floatval(explode('[Size]',$file)[1])/1000 . ' mb</Size>';
                        $fieldxml .= '<Type>' . explode('[Type]',$file)[1] . '</Type>';
                        $fieldxml .= '<Url>' . htmlspecialchars($url.explode('[Name]',$file)[1], ENT_XML1, 'UTF-8') . '</Url>';
                        $fieldxml .= '</File>';
                    }

                    $fieldxml .= '</'.$fields[$row['flid']]['nickname'].'>';
                    if($recordData[$kid] == [])
                        $recordData[$kid] = $fieldxml;
                    else
                        $recordData[$kid] .= $fieldxml;
                }
                $datafields->free();
                $con->next_result();

                //Video Field
                $datafields = $con->store_result();
                while($row = $datafields->fetch_assoc()) {
                    $kid = $ridsToKids[$row['rid']];
                    if(!array_key_exists($kid,$recordData))
                        continue;

                    $fieldxml = '<'.$fields[$row['flid']]['nickname'].' type="'.$fields[$row['flid']]['type'].'">';

                    $url = url('app/files/p'.$form->pid.'/f'.$form->fid.'/r'.$row['rid'].'/fl'.$row['flid']) . '/';
                    $files = explode('[!]',$row['video']);
                    foreach($files as $file) {
                        $fieldxml .= '<File>';
                        $fieldxml .= '<Name>' . htmlspecialchars(explode('[Name]',$file)[1], ENT_XML1, 'UTF-8') . '</Name>';
                        $fieldxml .= '<Size>' . floatval(explode('[Size]',$file)[1])/1000 . ' mb</Size>';
                        $fieldxml .= '<Type>' . explode('[Type]',$file)[1] . '</Type>';
                        $fieldxml .= '<Url>' . htmlspecialchars($url.explode('[Name]',$file)[1], ENT_XML1, 'UTF-8') . '</Url>';
                        $fieldxml .= '</File>';
                    }

                    $fieldxml .= '</'.$fields[$row['flid']]['nickname'].'>';
                    if($recordData[$kid] == [])
                        $recordData[$kid] = $fieldxml;
                    else
                        $recordData[$kid] .= $fieldxml;
                }
                $datafields->free();
                $con->next_result();

                //Model Field
                $datafields = $con->store_result();
                while($row = $datafields->fetch_assoc()) {
                    $kid = $ridsToKids[$row['rid']];
                    if(!array_key_exists($kid,$recordData))
                        continue;

                    $fieldxml = '<'.$fields[$row['flid']]['nickname'].' type="'.$fields[$row['flid']]['type'].'">';

                    $url = url('app/files/p'.$form->pid.'/f'.$form->fid.'/r'.$row['rid'].'/fl'.$row['flid']) . '/';
                    $files = explode('[!]',$row['model']);
                    foreach($files as $file) {
                        $fieldxml .= '<File>';
                        $fieldxml .= '<Name>' . htmlspecialchars(explode('[Name]',$file)[1], ENT_XML1, 'UTF-8') . '</Name>';
                        $fieldxml .= '<Size>' . floatval(explode('[Size]',$file)[1])/1000 . ' mb</Size>';
                        $fieldxml .= '<Type>' . explode('[Type]',$file)[1] . '</Type>';
                        $fieldxml .= '<Url>' . htmlspecialchars($url.explode('[Name]',$file)[1], ENT_XML1, 'UTF-8') . '</Url>';
                        $fieldxml .= '</File>';
                    }

                    $fieldxml .= '</'.$fields[$row['flid']]['nickname'].'>';
                    if($recordData[$kid] == [])
                        $recordData[$kid] = $fieldxml;
                    else
                        $recordData[$kid] .= $fieldxml;
                }
                $datafields->free();
                $con->next_result();

                //Geolocator Field
                $datafields = $con->store_result();
                while($row = $datafields->fetch_assoc()) {
                    $kid = $ridsToKids[$row['rid']];
                    if(!array_key_exists($kid,$recordData))
                        continue;

                    $fieldxml = '<'.$fields[$row['flid']]['nickname'].' type="'.$fields[$row['flid']]['type'].'">';

                    $desc = explode('[!]',$row['value']);
                    $cnt = sizeof($desc);
                    $address = explode('[!]',$row['val2']);
                    $latlon = explode('[!latlon!]',$row['val3']);
                    $utm = explode('[!utm!]',$row['val4']);
                    for($i=0;$i<$cnt;$i++) {
                        $ll = explode('[!]',$latlon[$i]);
                        $u = explode('[!]',$utm[$i]);
                        $fieldxml .= '<Location>';
                        $fieldxml .= '<Desc>' .$desc[$i]. '</Desc>';
                        $fieldxml .= '<Lat>' .$ll[0]. '</Lat>';
                        $fieldxml .= '<Lon>' .$ll[1]. '</Lon>';
                        $fieldxml .= '<Zone>' .$u[0]. '</Zone>';
                        $fieldxml .= '<East>' .$u[1]. '</East>';
                        $fieldxml .= '<North>' .$u[2]. '</North>';
                        $fieldxml .= '<Address>' .$address[$i]. '</Address>';
                        $fieldxml .= '</Location>';
                    }

                    $fieldxml .= '</'.$fields[$row['flid']]['nickname'].'>';
                    if($recordData[$kid] == [])
                        $recordData[$kid] = $fieldxml;
                    else
                        $recordData[$kid] .= $fieldxml;
                }
                $datafields->free();
                $con->next_result();

                //Associator Field
                $datafields = $con->store_result();
                while($row = $datafields->fetch_assoc()) {
                    $kid = $ridsToKids[$row['rid']];
                    if(!array_key_exists($kid,$recordData))
                        continue;

                    $fieldxml = '<'.$fields[$row['flid']]['nickname'].' type="'.$fields[$row['flid']]['type'].'">';

                    $aRecs = explode(',',$row['value']);
                    $fieldxml .= '<Record>'.implode('</Record><Record>',$aRecs).'</Record>';

                    $fieldxml .= '</'.$fields[$row['flid']]['nickname'].'>';
                    if($recordData[$kid] == [])
                        $recordData[$kid] = $fieldxml;
                    else
                        $recordData[$kid] .= $fieldxml;
                }
                $datafields->free();

                //Next we see if metadata is requested
                if($useOpts && isset($options['revAssoc']) && $options['revAssoc']) {
                    $revAssoc = "SELECT aSupp.record as main, aSupp.flid as flid, recs.kid as linker FROM ".$prefix."associator_support as aSupp 
                      LEFT JOIN ".$prefix."records as recs on aSupp.rid=recs.rid WHERE aSupp.record in (".implode(', ',$rids).")";

                    $meta = [];
                    $datafields = $con->query($revAssoc);
                    while($row = $datafields->fetch_assoc()) {
                        $kid = $ridsToKids[$row['main']];
                        if(!array_key_exists($kid,$recordData))
                            continue;

                        $meta[$kid]["reverseAssociations"][$row['flid']][] = $row['linker'];
                    }
                    mysqli_free_result($datafields);

                    foreach($meta as $mkid => $mt) {
                        if(isset($mt['reverseAssociations'])) {
                            $raXML = '<reverseAssociations>';
                            foreach ($mt['reverseAssociations'] as $flid => $rAssocs) {
                                foreach($rAssocs as $rA) {
                                    $raXML .= "<Record flid='$flid'>$rA</Record>";
                                }
                            }
                            $raXML .= '</reverseAssociations>';
                            if(isset($recordData[$kid]))
                                $recordData[$mkid] .= $raXML;
                            else
                                $recordData[$mkid] = $raXML;
                        }
                    }
                }

                //Now we have an array of kids to their field data
                //We need to loop back and add them to the xml
                foreach($recordData as $kid => $data) {
                    $records .= "<Record kid='$kid'>$data</Record>";
                }
                $records .= '</Records>';
                if($dataOnly) {
                    return $records;
                } else {
                    $dt = new \DateTime();
                    $format = $dt->format('Y_m_d_H_i_s');
                    $path = storage_path("app/exports/record_export_$format.xml");
                    file_put_contents($path, $records);
                    return $path;
                }
                break;
            default:
                mysqli_close($con);
                return null;
                break;
        }
    }

    /**
     * Gets record info for an associated record. TODO::Maybe down the road improve this
     *
     * @param  int $rid - Record ID
     * @param  \mysqli $con - Connection to DB
     * @param  int $fid - Form ID
     * @param  string $fIndex - What name index we should grab
     * @return array - The record data
     */
    private function getSingleRecordForAssoc($rid, $con, $fid, $fIndex) {
        $record = [];

        $prefix = config('database.connections.mysql.prefix');

        //Grab information about the form's fields
        $form = FormController::getForm($fid);
        $fieldMods = $form->fields()->get();
        $fields = array();
        foreach($fieldMods as $field) {
            $fArray = array();
            $fArray['flid'] = $field->flid;
            $fArray['name'] = $field->name;
            $fArray['type'] = $field->type;
            $fArray['nickname'] = $field->slug;
            $fArray['options'] = $field->options;

            //We want both so we can get field regardless of having id or slug
            $fields[$field->flid] = $fArray;
            $fields[$field->slug] = $fArray;
        }

        //Prep the table statements
        $textselect = "SELECT `flid`, `text` FROM ".$prefix."text_fields where `rid`=$rid";
        $numberselect = "SELECT `flid`, `number` FROM ".$prefix."number_fields where `rid`=$rid";
        $richtextselect = "SELECT `flid`, `rawtext` FROM ".$prefix."rich_text_fields where `rid`=$rid";
        $listselect = "SELECT `flid`, `option` FROM ".$prefix."list_fields where `rid`=$rid";
        $multiselectlistselect = "SELECT `flid`, `options` FROM ".$prefix."multi_select_list_fields where `rid`=$rid";
        $generatedlistselect = "SELECT `flid`, `options` FROM ".$prefix."generated_list_fields where `rid`=$rid";
        $combolistselect = "SELECT `flid`, GROUP_CONCAT(if(`field_num`=1, `data`, null) ORDER BY `list_index` ASC SEPARATOR '[!data!]' ) as `value`,
                  GROUP_CONCAT(if(`field_num`=2, `data`, null) ORDER BY `list_index` ASC SEPARATOR '[!data!]' ) as `val2`,
                  GROUP_CONCAT(if(`field_num`=1, `number`, null) ORDER BY `list_index` ASC SEPARATOR '[!data!]' ) as `val3`,
                  GROUP_CONCAT(if(`field_num`=2, `number`, null) ORDER BY `list_index` ASC SEPARATOR '[!data!]' ) as `val4` 
                  FROM ".$prefix."combo_support where `rid`=$rid group by `flid`";
        $dateselect = "SELECT `flid`, `circa`, `month`, `day`, `year`, `era` FROM ".$prefix."date_fields where `rid`=$rid";
        $scheduleselect = "SELECT `flid`, GROUP_CONCAT(`begin` SEPARATOR '[!]') as `value`, 
                  GROUP_CONCAT(`end` SEPARATOR '[!]') as `val2`, 
                  GROUP_CONCAT(`allday` SEPARATOR '[!]') as `val3`,
                  GROUP_CONCAT(`desc` SEPARATOR '[!]') as `val4` 
                  FROM ".$prefix."schedule_support where `rid`=$rid group by `flid`";
        $documentsselect = "SELECT `flid`, `documents` FROM ".$prefix."documents_fields where `rid`=$rid";
        $galleryselect = "SELECT `flid`, `images`, `captions` FROM ".$prefix."gallery_fields where `rid`=$rid";
        $playlistselect = "SELECT `flid`, `audio` FROM ".$prefix."playlist_fields where `rid`=$rid";
        $videoselect = "SELECT `flid`, `video` FROM ".$prefix."video_fields where `rid`=$rid";
        $modelselect = "SELECT `flid`, `model` FROM ".$prefix."model_fields where `rid`=$rid";
        $geolocatorselect = "SELECT `flid`, GROUP_CONCAT(`desc` SEPARATOR '[!]') as `value`, 
                  GROUP_CONCAT(`address` SEPARATOR '[!]') as `val2`, 
                  GROUP_CONCAT(CONCAT_WS('[!]', `lat`, `lon`) SEPARATOR '[!latlon!]') as `val3`, 
                  GROUP_CONCAT(CONCAT_WS('[!]', `zone`, `easting`, `northing`) SEPARATOR '[!utm!]') as `val4` 
                  FROM ".$prefix."geolocator_support where `rid`=$rid group by `flid`";
        $associatorselect = "SELECT af.flid as `flid`, GROUP_CONCAT(aRec.kid SEPARATOR ',') as `value` 
                  FROM ".$prefix."associator_support as af left join ".$prefix."records as aRec on af.record=aRec.rid 
                  where af.`rid`=$rid group by `flid`";

        $datafields = $con->query($textselect);
        while ($row = $datafields->fetch_assoc()) {
            $fieldIndex = $fields[$row['flid']][$fIndex];

            $record[$fieldIndex] = $row['text'];
        }
        mysqli_free_result($datafields);

        $datafields = $con->query($numberselect);
        while ($row = $datafields->fetch_assoc()) {
            $fieldIndex = $fields[$row['flid']][$fIndex];

            $record[$fieldIndex] = $row['number'];
        }
        mysqli_free_result($datafields);

        $datafields = $con->query($richtextselect);
        while ($row = $datafields->fetch_assoc()) {
            $fieldIndex = $fields[$row['flid']][$fIndex];

            $record[$fieldIndex] = $row['rawtext'];
        }
        mysqli_free_result($datafields);

        $datafields = $con->query($listselect);
        while ($row = $datafields->fetch_assoc()) {
            $fieldIndex = $fields[$row['flid']][$fIndex];

            $record[$fieldIndex] = $row['option'];
        }
        mysqli_free_result($datafields);

        $datafields = $con->query($multiselectlistselect);
        while ($row = $datafields->fetch_assoc()) {
            $fieldIndex = $fields[$row['flid']][$fIndex];

            $record[$fieldIndex] = explode('[!]', $row['options']);
        }
        mysqli_free_result($datafields);

        $datafields = $con->query($generatedlistselect);
        while ($row = $datafields->fetch_assoc()) {
            $fieldIndex = $fields[$row['flid']][$fIndex];

            $record[$fieldIndex] = explode('[!]', $row['options']);
        }
        mysqli_free_result($datafields);

        $datafields = $con->query($combolistselect);
        while ($row = $datafields->fetch_assoc()) {
            $fieldIndex = $fields[$row['flid']][$fIndex];

            $value = array();
            $dataone = explode('[!data!]', $row['value']);
            $datatwo = explode('[!data!]', $row['val2']);
            $numberone = explode('[!data!]', $row['val3']);
            $numbertwo = explode('[!data!]', $row['val4']);
            $typeone = explode('[Type]', explode('[!Field1!][Type]', $fields[$row['flid']]['options'])[1])[0];
            $typetwo = explode('[Type]', explode('[!Field2!][Type]', $fields[$row['flid']]['options'])[1])[0];
            if ($typeone == 'Number')
                $cnt = sizeof($numberone);
            else
                $cnt = sizeof($dataone);
            $nameone = explode('[Name]', explode('[Type][Name]', $fields[$row['flid']]['options'])[1])[0];
            $nametwo = explode('[Name]', explode('[Type][Name]', $fields[$row['flid']]['options'])[2])[0];

            for ($c = 0; $c < $cnt; $c++) {
                $val = [];

                switch ($typeone) {
                    case Field::_MULTI_SELECT_LIST:
                    case Field::_GENERATED_LIST:
                        $valone = explode('[!]', $dataone[$c]);
                        break;
                    case Field::_NUMBER:
                        $valone = $numberone[$c];
                        break;
                    default:
                        $valone = $dataone[$c];
                        break;
                }

                switch ($typetwo) {
                    case Field::_MULTI_SELECT_LIST:
                    case Field::_GENERATED_LIST:
                        $valtwo = explode('[!]', $datatwo[$c]);
                        break;
                    case Field::_NUMBER:
                        $valtwo = $numbertwo[$c];
                        break;
                    default:
                        $valtwo = $datatwo[$c];
                        break;
                }

                $val[$nameone] = $valone;
                $val[$nametwo] = $valtwo;

                $value[] = $val;
            }

            $record[$fieldIndex] = $value;
        }
        mysqli_free_result($datafields);

        $datafields = $con->query($dateselect);
        while ($row = $datafields->fetch_assoc()) {
            $fieldIndex = $fields[$row['flid']][$fIndex];

            $record[$fieldIndex] = [
                'circa' => $row['circa'],
                'month' => $row['month'],
                'day' => $row['day'],
                'year' => $row['year'],
                'era' => $row['era']
            ];
        }
        mysqli_free_result($datafields);

        $datafields = $con->query($scheduleselect);
        while ($row = $datafields->fetch_assoc()) {
            $fieldIndex = $fields[$row['flid']][$fIndex];

            $value = array();
            $begin = explode('[!]', $row['value']);
            $cnt = sizeof($begin);
            $end = explode('[!]', $row['val2']);
            $allday = explode('[!]', $row['val3']);
            $desc = explode('[!]', $row['val4']);
            for ($i = 0; $i < $cnt; $i++) {
                if ($allday[$i] == 1) {
                    $formatBegin = date("m/d/Y", strtotime($begin[$i]));
                    $formatEnd = date("m/d/Y", strtotime($end[$i]));
                } else {
                    $formatBegin = date("m/d/Y h:i A", strtotime($begin[$i]));
                    $formatEnd = date("m/d/Y h:i A", strtotime($end[$i]));
                }
                $info = [
                    'begin' => $formatBegin,
                    'end' => $formatEnd,
                    'allday' => $allday[$i],
                    'desc' => $desc[$i]
                ];
                $value[] = $info;
            }

            $record[$fieldIndex] = $value;
        }
        mysqli_free_result($datafields);

        $datafields = $con->query($documentsselect);
        while ($row = $datafields->fetch_assoc()) {
            $fieldIndex = $fields[$row['flid']][$fIndex];

            $url = url('app/files/p' . $form->pid . '/f' . $form->fid . '/r' . $row['rid'] . '/fl' . $row['flid']) . '/';
            $value = array();
            $files = explode('[!]', $row['documents']);
            foreach ($files as $file) {
                $info = [
                    'name' => explode('[Name]', $file)[1],
                    'size' => floatval(explode('[Size]', $file)[1]) / 1000 . " mb",
                    'type' => explode('[Type]', $file)[1],
                    'url' => $url . explode('[Name]', $file)[1]
                ];
                $value[] = $info;
            }
            $record[$fieldIndex] = $value;
        }
        mysqli_free_result($datafields);

        $datafields = $con->query($galleryselect);
        while ($row = $datafields->fetch_assoc()) {
            $fieldIndex = $fields[$row['flid']][$fIndex];

            $url = url('app/files/p' . $form->pid . '/f' . $form->fid . '/r' . $row['rid'] . '/fl' . $row['flid']) . '/';
            $value = array();
            $files = explode('[!]', $row['images']);
            $captions = (!is_null($row['captions']) && $row['captions'] != '') ? explode('[!]', $row['captions']) : null;
            for ($gi = 0; $gi < sizeof($files); $gi++) {
                $info = [
                    'name' => explode('[Name]', $files[$gi])[1],
                    'size' => floatval(explode('[Size]', $files[$gi])[1]) / 1000 . " mb",
                    'type' => explode('[Type]', $files[$gi])[1],
                    'url' => $url . explode('[Name]', $files[$gi])[1]
                ];
                if (!is_null($captions))
                    $info['caption'] = $captions[$gi];
                else
                    $info['caption'] = '';
                $value[] = $info;
            }
            $record[$fieldIndex] = $value;
        }
        mysqli_free_result($datafields);

        $datafields = $con->query($playlistselect);
        while ($row = $datafields->fetch_assoc()) {
            $fieldIndex = $fields[$row['flid']][$fIndex];

            $url = url('app/files/p' . $form->pid . '/f' . $form->fid . '/r' . $row['rid'] . '/fl' . $row['flid']) . '/';
            $value = array();
            $files = explode('[!]', $row['audio']);
            foreach ($files as $file) {
                $info = [
                    'name' => explode('[Name]', $file)[1],
                    'size' => floatval(explode('[Size]', $file)[1]) / 1000 . " mb",
                    'type' => explode('[Type]', $file)[1],
                    'url' => $url . explode('[Name]', $file)[1]
                ];
                $value[] = $info;
            }
            $record[$fieldIndex] = $value;
        }
        mysqli_free_result($datafields);

        $datafields = $con->query($videoselect);
        while ($row = $datafields->fetch_assoc()) {
            $fieldIndex = $fields[$row['flid']][$fIndex];

            $url = url('app/files/p' . $form->pid . '/f' . $form->fid . '/r' . $row['rid'] . '/fl' . $row['flid']) . '/';
            $value = array();
            $files = explode('[!]', $row['video']);
            foreach ($files as $file) {
                $info = [
                    'name' => explode('[Name]', $file)[1],
                    'size' => floatval(explode('[Size]', $file)[1]) / 1000 . " mb",
                    'type' => explode('[Type]', $file)[1],
                    'url' => $url . explode('[Name]', $file)[1]
                ];
                $value[] = $info;
            }
            $record[$fieldIndex] = $value;
        }
        mysqli_free_result($datafields);

        $datafields = $con->query($modelselect);
        while ($row = $datafields->fetch_assoc()) {
            $fieldIndex = $fields[$row['flid']][$fIndex];

            $url = url('app/files/p' . $form->pid . '/f' . $form->fid . '/r' . $row['rid'] . '/fl' . $row['flid']) . '/';
            $value = array();
            $files = explode('[!]', $row['model']);
            foreach ($files as $file) {
                $info = [
                    'name' => explode('[Name]', $file)[1],
                    'size' => floatval(explode('[Size]', $file)[1]) / 1000 . " mb",
                    'type' => explode('[Type]', $file)[1],
                    'url' => $url . explode('[Name]', $file)[1]
                ];
                $value[] = $info;
            }
            $record[$fieldIndex] = $value;
        }
        mysqli_free_result($datafields);

        $datafields = $con->query($geolocatorselect);
        while ($row = $datafields->fetch_assoc()) {
            $fieldIndex = $fields[$row['flid']][$fIndex];

            $value = array();
            $desc = explode('[!]', $row['value']);
            $cnt = sizeof($desc);
            $address = explode('[!]', $row['val2']);
            $latlon = explode('[!latlon!]', $row['val3']);
            $utm = explode('[!utm!]', $row['val4']);
            for ($i = 0; $i < $cnt; $i++) {
                $ll = explode('[!]', $latlon[$i]);
                $u = explode('[!]', $utm[$i]);
                $info = [
                    'desc' => $desc[$i],
                    'lat' => $ll[0],
                    'lon' => $ll[1],
                    'zone' => $u[0],
                    'east' => $u[1],
                    'north' => $u[2],
                    'address' => $address[$i],
                ];

                $value[] = $info;
            }

            $record[$fieldIndex] = $value;
        }
        mysqli_free_result($datafields);

        $datafields = $con->query($associatorselect);
        while ($row = $datafields->fetch_assoc()) {
            $fieldIndex = $fields[$row['flid']][$fIndex];

            $record[$fieldIndex] = explode(',', $row['value']);
        }
        mysqli_free_result($datafields);

        return $record;
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