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
        if(!FormController::validProjForm($pid,$fid))
            return redirect('projects/'.$pid);

        $form = FormController::getForm($fid);

        if(!\Auth::user()->isFormAdmin($form))
            return redirect('projects/'.$pid.'/forms/'.$fid);

        $rids = DB::table("records")->where("fid", "=", $fid)->select("rid")->get();

        // The DB call returns an array of StdObj so we get the rids out of the objects.
        $rids = array_map( function($obj) {
            return $obj->rid;
        }, $rids);

        // Download tracker to stop the loading bar when finished.
        $tracker = new DownloadTracker();
        $tracker->fid = $form->fid;
        $tracker->save(); //TODO:: is this doing anything?

        $output = $this->exportWithRids($rids, $type);

        if(file_exists($output)) { // File exists, so we download it.
            header("Content-Disposition: attachment; filename=\"" . basename($output) . "\"");
            header("Content-Type: application/octet-stream");
            header("Content-Length: " . filesize($output));

            readfile($output);

            $tracker->delete();
        } else { // File does not exist, so some kind of error occurred, and we redirect.
            $tracker->delete();

            flash()->overlay(trans("records_index.exporterror"), trans("controller_admin.whoops"));
            return redirect("projects/" . $pid . "/forms/" . $fid . "/records");
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
     * @param  string $dataOnly - No file, just data!
     * @return string - The system path to the exported file
     */
    public function exportWithRids(array $rids, $format = self::JSON, $dataOnly = false) {
        $format = strtoupper($format);

        if(! self::isValidFormat($format))
            return null;

        $chunks = array_chunk($rids, 500);

        switch($format) {
            case self::JSON:
                $records = [];

                foreach($chunks as $chunk) {
                    $records = self::getRecordMetadata($chunk);

                    $datafields = self::getDataRows($chunk);
                    foreach($datafields as $data){
                        $kid = $data->pid.'-'.$data->fid.'-'.$data->rid;

                        switch($data->type) {
                            case Field::_TEXT:
                                $records[$kid][$data->slug]['value'] = $data->value;
                                $records[$kid][$data->slug]['type'] = $data->type;
                                break;
                            case Field::_RICH_TEXT:
                                $records[$kid][$data->slug]['value'] = $data->value;
                                $records[$kid][$data->slug]['type'] = $data->type;
                                break;
                            case Field::_NUMBER:
                                $records[$kid][$data->slug]['value'] = $data->value;
                                $records[$kid][$data->slug]['type'] = $data->type;
                                break;
                            case Field::_LIST:
                                $records[$kid][$data->slug]['value'] = $data->value;
                                $records[$kid][$data->slug]['type'] = $data->type;
                                break;
                            case Field::_MULTI_SELECT_LIST:
                                $records[$kid][$data->slug]['value'] = explode('[!]',$data->value);
                                $records[$kid][$data->slug]['type'] = $data->type;
                                break;
                            case Field::_GENERATED_LIST:
                                $records[$kid][$data->slug]['value'] = explode('[!]',$data->value);
                                $records[$kid][$data->slug]['type'] = $data->type;
                                break;
                            case Field::_COMBO_LIST:
                                //$records[$kid][$data->slug] = $data->value; TODO::SUPPORT
                                break;
                            case Field::_DATE:
                                $records[$kid][$data->slug]['value'] = [
                                    'circa' => $data->value,
                                    'month' => $data->val2,
                                    'day' => $data->val3,
                                    'year' => $data->val4,
                                    'era' => $data->val5,
                                    'date_object' => ""
                                ]; //TODO::Date object
                                $records[$kid][$data->slug]['type'] = $data->type;
                                break;
                            case Field::_SCHEDULE:
                                //$records[$kid][$data->slug] = $data->value; TODO::SUPPORT
                                break;
                            case Field::_GEOLOCATOR:
                                //$records[$kid][$data->slug] = $data->value; TODO::SUPPORT
                                break;
                            case Field::_DOCUMENTS:
                                $url = env("STORAGE_URL").'files/p'.$data->pid.'/f'.$data->fid.'/r'.$data->rid.'/fl'.$data->flid . '/';
                                $value = array();
                                $files = explode('[!]',$data->value);
                                foreach($files as $file) {
                                    $info = [
                                        'name' => explode('[Name]',$file)[1],
                                        'size' => floatval(explode('[Size]',$file)[1])/1000 . " mb",
                                        'type' => explode('[Type]',$file)[1],
                                        'url' => $url.explode('[Name]',$file)[1]
                                    ];
                                    array_push($value,$info);
                                }
                                $records[$kid][$data->slug]['value'] = $value;
                                $records[$kid][$data->slug]['type'] = $data->type;
                                break;
                            case Field::_GALLERY:
                                $url = env("STORAGE_URL").'files/p'.$data->pid.'/f'.$data->fid.'/r'.$data->rid.'/fl'.$data->flid . '/';
                                $value = array();
                                $files = explode('[!]',$data->value);
                                foreach($files as $file) {
                                    $info = [
                                        'name' => explode('[Name]',$file)[1],
                                        'size' => floatval(explode('[Size]',$file)[1])/1000 . " mb",
                                        'type' => explode('[Type]',$file)[1],
                                        'url' => $url.explode('[Name]',$file)[1]
                                    ];
                                    array_push($value,$info);
                                }
                                $records[$kid][$data->slug]['value'] = $value;
                                $records[$kid][$data->slug]['type'] = $data->type;
                                break;
                            case Field::_PLAYLIST:
                                $url = env("STORAGE_URL").'files/p'.$data->pid.'/f'.$data->fid.'/r'.$data->rid.'/fl'.$data->flid . '/';
                                $value = array();
                                $files = explode('[!]',$data->value);
                                foreach($files as $file) {
                                    $info = [
                                        'name' => explode('[Name]',$file)[1],
                                        'size' => floatval(explode('[Size]',$file)[1])/1000 . " mb",
                                        'type' => explode('[Type]',$file)[1],
                                        'url' => $url.explode('[Name]',$file)[1]
                                    ];
                                    array_push($value,$info);
                                }
                                $records[$kid][$data->slug]['value'] = $value;
                                $records[$kid][$data->slug]['type'] = $data->type;
                                break;
                            case Field::_VIDEO:
                                $url = env("STORAGE_URL").'files/p'.$data->pid.'/f'.$data->fid.'/r'.$data->rid.'/fl'.$data->flid . '/';
                                $value = array();
                                $files = explode('[!]',$data->value);
                                foreach($files as $file) {
                                    $info = [
                                        'name' => explode('[Name]',$file)[1],
                                        'size' => floatval(explode('[Size]',$file)[1])/1000 . " mb",
                                        'type' => explode('[Type]',$file)[1],
                                        'url' => $url.explode('[Name]',$file)[1]
                                    ];
                                    array_push($value,$info);
                                }
                                $records[$kid][$data->slug]['value'] = $value;
                                $records[$kid][$data->slug]['type'] = $data->type;
                                break;
                            case Field::_3D_MODEL:
                                $url = env("STORAGE_URL").'files/p'.$data->pid.'/f'.$data->fid.'/r'.$data->rid.'/fl'.$data->flid . '/';
                                $file = $data->value;
                                $value = array(
                                    [
                                        'name' => explode('[Name]',$file)[1],
                                        'size' => floatval(explode('[Size]',$file)[1])/1000 . " mb",
                                        'type' => explode('[Type]',$file)[1],
                                        'url' => $url.explode('[Name]',$file)[1]
                                    ]
                                );
                                $records[$kid][$data->slug]['value'] = $value;
                                $records[$kid][$data->slug]['type'] = $data->type;
                                break;
                            case Field::_ASSOCIATOR:
                                //$records[$kid][$data->slug] = $data->value; TODO::SUPPORT
                                break;
                            default:
                                break;
                        }
                    }
                }

                $records = json_encode($records);

                if($dataOnly) {
                    return $records;
                } else {
                    $dt = new \DateTime();
                    $format = $dt->format('Y_m_d_H_i_s');
                    $path = env("BASE_PATH") . "storage/app/exports/export_$format.json";

                    file_put_contents($path, $records);

                    return $path;
                }
                break;
            case self::XML:
                $records = '<?xml version="1.0" encoding="utf-8"?><Records>';
                $recordData = [];

                foreach($chunks as $chunk) {
                    $datafields = self::getDataRows($chunk);

                    foreach($datafields as $data) {
                        $kid = $data->pid.'-'.$data->fid.'-'.$data->rid;

                        $fieldxml = '<'.$data->slug.' type="'.$data->type.'">';
                        switch($data->type) {
                            case Field::_TEXT:
                                $fieldxml .= htmlspecialchars($data->value, ENT_XML1, 'UTF-8');
                                break;
                            case Field::_RICH_TEXT:
                                $fieldxml .= htmlspecialchars($data->value, ENT_XML1, 'UTF-8');
                                break;
                            case Field::_NUMBER:
                                $fieldxml .= htmlspecialchars($data->value, ENT_XML1, 'UTF-8');
                                break;
                            case Field::_LIST:
                                $fieldxml .= htmlspecialchars($data->value, ENT_XML1, 'UTF-8');
                                break;
                            case Field::_MULTI_SELECT_LIST:
                                $opts = explode('[!]',$data->value);
                                foreach($opts as $opt) {
                                    $fieldxml .= '<value>'.htmlspecialchars($opt, ENT_XML1, 'UTF-8').'</value>';
                                }
                                break;
                            case Field::_GENERATED_LIST:
                                $opts = explode('[!]',$data->value);
                                foreach($opts as $opt) {
                                    $fieldxml .= '<value>'.htmlspecialchars($opt, ENT_XML1, 'UTF-8').'</value>';
                                }
                                break;
                            case Field::_COMBO_LIST:
                                //$records[$kid][$data->slug] = $data->value; TODO::SUPPORT
                                break;
                            case Field::_DATE:
                                $fieldxml .= '<Circa>'.$data->value.'</Circa>';
                                $fieldxml .= '<Month>'.$data->val2.'</Month>';
                                $fieldxml .= '<Day>'.$data->val3.'</Day>';
                                $fieldxml .= '<Year>'.$data->val4.'</Year>';
                                $fieldxml .= '<Era>'.$data->val5.'</Era>';
                                //TODO::Date object
                                break;
                            case Field::_SCHEDULE:
                                //$records[$kid][$data->slug] = $data->value; TODO::SUPPORT
                                break;
                            case Field::_GEOLOCATOR:
                                //$records[$kid][$data->slug] = $data->value; TODO::SUPPORT
                                break;
                            case Field::_DOCUMENTS:
                                $url = env("STORAGE_URL").'files/p'.$data->pid.'/f'.$data->fid.'/r'.$data->rid.'/fl'.$data->flid . '/';
                                $files = explode('[!]',$data->value);
                                foreach($files as $file) {
                                    $fieldxml .= '<File>';
                                    $fieldxml .= '<Name>' . htmlspecialchars(explode('[Name]',$file)[1], ENT_XML1, 'UTF-8') . '</Name>';
                                    $fieldxml .= '<Size>' . floatval(explode('[Size]',$file)[1])/1000 . ' mb</Size>';
                                    $fieldxml .= '<Type>' . explode('[Type]',$file)[1] . '</Type>';
                                    $fieldxml .= '<Url>' . htmlspecialchars($url.explode('[Name]',$file)[1], ENT_XML1, 'UTF-8') . '</Url>';
                                    $fieldxml .= '</File>';
                                }
                                break;
                            case Field::_GALLERY:
                                $url = env("STORAGE_URL").'files/p'.$data->pid.'/f'.$data->fid.'/r'.$data->rid.'/fl'.$data->flid . '/';
                                $files = explode('[!]',$data->value);
                                foreach($files as $file) {
                                    $fieldxml .= '<File>';
                                    $fieldxml .= '<Name>' . htmlspecialchars(explode('[Name]',$file)[1], ENT_XML1, 'UTF-8') . '</Name>';
                                    $fieldxml .= '<Size>' . floatval(explode('[Size]',$file)[1])/1000 . ' mb</Size>';
                                    $fieldxml .= '<Type>' . explode('[Type]',$file)[1] . '</Type>';
                                    $fieldxml .= '<Url>' . htmlspecialchars($url.explode('[Name]',$file)[1], ENT_XML1, 'UTF-8') . '</Url>';
                                    $fieldxml .= '</File>';
                                }
                                break;
                            case Field::_PLAYLIST:
                                $url = env("STORAGE_URL").'files/p'.$data->pid.'/f'.$data->fid.'/r'.$data->rid.'/fl'.$data->flid . '/';
                                $files = explode('[!]',$data->value);
                                foreach($files as $file) {
                                    $fieldxml .= '<File>';
                                    $fieldxml .= '<Name>' . htmlspecialchars(explode('[Name]',$file)[1], ENT_XML1, 'UTF-8') . '</Name>';
                                    $fieldxml .= '<Size>' . floatval(explode('[Size]',$file)[1])/1000 . ' mb</Size>';
                                    $fieldxml .= '<Type>' . explode('[Type]',$file)[1] . '</Type>';
                                    $fieldxml .= '<Url>' . htmlspecialchars($url.explode('[Name]',$file)[1], ENT_XML1, 'UTF-8') . '</Url>';
                                    $fieldxml .= '</File>';
                                }
                                break;
                            case Field::_VIDEO:
                                $url = env("STORAGE_URL").'files/p'.$data->pid.'/f'.$data->fid.'/r'.$data->rid.'/fl'.$data->flid . '/';
                                $files = explode('[!]',$data->value);
                                foreach($files as $file) {
                                    $fieldxml .= '<File>';
                                    $fieldxml .= '<Name>' . htmlspecialchars(explode('[Name]',$file)[1], ENT_XML1, 'UTF-8') . '</Name>';
                                    $fieldxml .= '<Size>' . floatval(explode('[Size]',$file)[1])/1000 . ' mb</Size>';
                                    $fieldxml .= '<Type>' . explode('[Type]',$file)[1] . '</Type>';
                                    $fieldxml .= '<Url>' . htmlspecialchars($url.explode('[Name]',$file)[1], ENT_XML1, 'UTF-8') . '</Url>';
                                    $fieldxml .= '</File>';
                                }
                                break;
                            case Field::_3D_MODEL:
                                $url = env("STORAGE_URL").'files/p'.$data->pid.'/f'.$data->fid.'/r'.$data->rid.'/fl'.$data->flid . '/';
                                $file = $data->value;

                                $fieldxml .= '<File>';
                                $fieldxml .= '<Name>' . htmlspecialchars(explode('[Name]',$file)[1], ENT_XML1, 'UTF-8') . '</Name>';
                                $fieldxml .= '<Size>' . floatval(explode('[Size]',$file)[1])/1000 . ' mb</Size>';
                                $fieldxml .= '<Type>' . explode('[Type]',$file)[1] . '</Type>';
                                $fieldxml .= '<Url>' . htmlspecialchars($url.explode('[Name]',$file)[1], ENT_XML1, 'UTF-8') . '</Url>';
                                $fieldxml .= '</File>';
                                break;
                            case Field::_ASSOCIATOR:
                                //$records[$kid][$data->slug] = $data->value; TODO::SUPPORT
                                break;
                            default:
                                break;
                        }

                        $fieldxml .= '</'.$data->slug.'>';

                        if(isset($recordData[$kid]))
                            $recordData[$kid] .= $fieldxml;
                        else
                            $recordData[$kid] = $fieldxml;
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
                    $path = env("BASE_PATH") . "storage/app/exports/export_$format.xml";

                    file_put_contents($path, $records);

                    return $path;
                }
            default:
                return '';
                break;
        }
    }

    /**
     * Get the data rows back for a set of records.
     *
     * @param  int $rids - Record IDs
     * @return array - Data for the records
     */
    public static function getDataRows($rids) {
        $rid = implode(', ',$rids);
        return DB::select("SELECT tf.rid as `rid`, tf.text as `value`, NULL as `val2`, NULL as `val3`, NULL as `val4`, NULL as `val5`, fl.slug, fl.type, fl.pid, fl.fid, fl.flid 
FROM kora3_text_fields as tf left join kora3_fields as fl on tf.flid=fl.flid where tf.rid in ($rid)
union all

SELECT nf.rid as `rid`, nf.number as `value`, NULL as `val2`, NULL as `val3`, NULL as `val4`, NULL as `val5`, fl.slug, fl.type, fl.pid, fl.fid, fl.flid 
FROM kora3_number_fields as nf left join kora3_fields as fl on nf.flid=fl.flid where nf.rid in ($rid)  
union all

SELECT rtf.rid as `rid`, rtf.rawtext as `value`, NULL as `val2`, NULL as `val3`, NULL as `val4`, NULL as `val5`, fl.slug, fl.type, fl.pid, fl.fid, fl.flid 
FROM kora3_rich_text_fields as rtf left join kora3_fields as fl on rtf.flid=fl.flid where rtf.rid in ($rid)  
union all

SELECT lf.rid as `rid`, lf.option as `value`, NULL as `val2`, NULL as `val3`, NULL as `val4`, NULL as `val5`, fl.slug, fl.type, fl.pid, fl.fid, fl.flid 
FROM kora3_list_fields as lf left join kora3_fields as fl on lf.flid=fl.flid where lf.rid in ($rid)  
union all

SELECT mslf.rid as `rid`, mslf.options as `value`, NULL as `val2`, NULL as `val3`, NULL as `val4`, NULL as `val5`, fl.slug, fl.type, fl.pid, fl.fid, fl.flid 
FROM kora3_multi_select_list_fields as mslf left join kora3_fields as fl on mslf.flid=fl.flid where mslf.rid in ($rid)  
union all

SELECT glf.rid as `rid`, glf.options as `value`, NULL as `val2`, NULL as `val3`, NULL as `val4`, NULL as `val5`, fl.slug, fl.type, fl.pid, fl.fid, fl.flid 
FROM kora3_generated_list_fields as glf left join kora3_fields as fl on glf.flid=fl.flid where glf.rid in ($rid)  
union all

SELECT clf.rid as `rid`, NULL as `value`, NULL as `val2`, NULL as `val3`, NULL as `val4`, NULL as `val5`, fl.slug, fl.type, fl.pid, fl.fid, fl.flid 
FROM kora3_combo_list_fields as clf left join kora3_fields as fl on clf.flid=fl.flid where clf.rid in ($rid) 
union all

SELECT df.rid as `rid`, df.circa as `value`, df.month as `val2`,df.day as `val3`,df.year as `val4`,df.era as `val5`,fl.slug, fl.type, fl.pid, fl.fid, fl.flid 
FROM kora3_date_fields as df left join kora3_fields as fl on df.flid=fl.flid where df.rid in ($rid) 
union all

SELECT sf.rid as `rid`, NULL as `value`, NULL as `val2`, NULL as `val3`, NULL as `val4`, NULL as `val5`, fl.slug, fl.type, fl.pid, fl.fid, fl.flid 
FROM kora3_schedule_fields as sf left join kora3_fields as fl on sf.flid=fl.flid where sf.rid in ($rid) 
union all

SELECT docf.rid as `rid`, docf.documents as `value`, NULL as `val2`, NULL as `val3`, NULL as `val4`, NULL as `val5`, fl.slug, fl.type, fl.pid, fl.fid, fl.flid 
FROM kora3_documents_fields as docf left join kora3_fields as fl on docf.flid=fl.flid where docf.rid in ($rid) 
union all

SELECT galf.rid as `rid`, galf.images as `value`, NULL as `val2`, NULL as `val3`, NULL as `val4`, NULL as `val5`, fl.slug, fl.type, fl.pid, fl.fid, fl.flid 
FROM kora3_gallery_fields as galf left join kora3_fields as fl on galf.flid=fl.flid where galf.rid in ($rid) 
union all

SELECT pf.rid as `rid`, pf.audio as `value`, NULL as `val2`, NULL as `val3`, NULL as `val4`, NULL as `val5`, fl.slug, fl.type, fl.pid, fl.fid, fl.flid 
FROM kora3_playlist_fields as pf left join kora3_fields as fl on pf.flid=fl.flid where pf.rid in ($rid) 
union all

SELECT vf.rid as `rid`, vf.video as `value`, NULL as `val2`, NULL as `val3`, NULL as `val4`, NULL as `val5`, fl.slug, fl.type, fl.pid, fl.fid, fl.flid 
FROM kora3_video_fields as vf left join kora3_fields as fl on vf.flid=fl.flid where vf.rid in ($rid) 
union all

SELECT mf.rid as `rid`, mf.model as `value`, NULL as `val2`, NULL as `val3`, NULL as `val4`, NULL as `val5`, fl.slug, fl.type, fl.pid, fl.fid, fl.flid 
FROM kora3_model_fields as mf left join kora3_fields as fl on mf.flid=fl.flid where mf.rid in ($rid) 
union all

SELECT gf.rid as `rid`, NULL as `value`, NULL as `val2`, NULL as `val3`, NULL as `val4`, NULL as `val5`, fl.slug, fl.type, fl.pid, fl.fid, fl.flid 
FROM kora3_geolocator_fields as gf left join kora3_fields as fl on gf.flid=fl.flid where gf.rid in ($rid) 
union all

SELECT af.rid as `rid`, NULL as `value`, NULL as `val2`, NULL as `val3`, NULL as `val4`, NULL as `val5`, fl.slug, fl.type, fl.pid, fl.fid, fl.flid 
FROM kora3_associator_fields as af left join kora3_fields as fl on af.flid=fl.flid where af.rid in ($rid) ;");
    }

    /**
     * Get the metadeta back for a set of records.
     *
     * @param  int $rid - Record IDs
     * @return array - Metadata for the records
     */
    public static function getRecordMetadata($rids) {
        $meta = [];
        $kidPairs = [];
        $rid = implode(', ',$rids);

        $part1 = DB::select("SELECT r.rid, r.kid, r.created_at, r.updated_at, u.username FROM kora3_records as r LEFT JOIN kora3_users as u on r.owner=u.id WHERE r.rid in ($rid)");
        foreach($part1 as $row) {
            $meta[$row->kid]["created"] = $row->created_at;
            $meta[$row->kid]["updated"] = $row->updated_at;
            $meta[$row->kid]["owner"] = $row->username;
            $kidPairs[$row->rid] = $row->kid;
        }

        $part2 = DB::select("SELECT aSupp.record as main, recs.kid as linker FROM kora3_associator_support as aSupp LEFT JOIN kora3_records as recs on aSupp.rid=recs.rid WHERE aSupp.record in ($rid)");

        foreach($part2 as $row) {
            $meta[$kidPairs[$row->main]]["reverseAssociations"][] = $row->linker;
        }

        return $meta;
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