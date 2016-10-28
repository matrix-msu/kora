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

    /**
     * @var array
     */
    const VALID_FORMATS = [ self::JSON, self::XML ];

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
            return redirect('projects');
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


/*        $records = Record::where('fid', '=', $fid)->get();
        $fields = Field::where('fid', '=', $fid)->get();

        $fieldsInfo = array();
        foreach($fields as $field){
            $fieldsInfo[$field->flid]['slug'] = $field->slug;
            $fieldsInfo[$field->flid]['type'] = $field->type;
        }
        //dd($records);

        if($type=='xml') {
            $xml='<?xml version="1.0" encoding="utf-8"?><Records>';

            foreach ($records as $record) {

                $xml .= '<Record kid="' . $record->kid . '">';
                $tf = TextField::where('rid', '=', $record->rid)->get();
                foreach($tf as $f) {
                    $xml .= '<' . $this->xmlTagClear($fieldsInfo[$f->flid]['slug']) . ' type="' . $fieldsInfo[$f->flid]['type'] . '">';
                    $value = $f->text;
                    $xml .= utf8_encode($value);
                    $xml .= '</' . $this->xmlTagClear($fieldsInfo[$f->flid]['slug']) . '>';
                }
                $rf = RichTextField::where('rid', '=', $record->rid)->get();
                foreach($rf as $f) {
                    $xml .= '<' . $this->xmlTagClear($fieldsInfo[$f->flid]['slug']) . ' type="' . $fieldsInfo[$f->flid]['type'] . '">';
                    $value = $f->rawtext;
                    $xml .= utf8_encode($value);
                    $xml .= '</' . $this->xmlTagClear($fieldsInfo[$f->flid]['slug']) . '>';
                }
                $nf = NumberField::where('rid', '=', $record->rid)->get();
                foreach($nf as $f) {
                    $xml .= '<' . $this->xmlTagClear($fieldsInfo[$f->flid]['slug']) . ' type="' . $fieldsInfo[$f->flid]['type'] . '">';
                    $value = (float)$f->number;
                    $xml .= utf8_encode($value);
                    $xml .= '</' . $this->xmlTagClear($fieldsInfo[$f->flid]['slug']) . '>';
                }
                $lf = ListField::where('rid', '=', $record->rid)->get();
                foreach($lf as $f) {
                    $xml .= '<' . $this->xmlTagClear($fieldsInfo[$f->flid]['slug']) . ' type="' . $fieldsInfo[$f->flid]['type'] . '">';
                    $value = $f->option;
                    $xml .= utf8_encode($value);
                    $xml .= '</' . $this->xmlTagClear($fieldsInfo[$f->flid]['slug']) . '>';
                }
                $msf = MultiSelectListField::where('rid', '=', $record->rid)->get();
                foreach($msf as $f) {
                    $xml .= '<' . $this->xmlTagClear($fieldsInfo[$f->flid]['slug']) . ' type="' . $fieldsInfo[$f->flid]['type'] . '">';
                    $options = explode('[!]', $f->options);
                    foreach ($options as $opt) {
                        $xml .= '<value>' . utf8_encode($opt) . '</value>';
                    }
                    $xml .= '</' . $this->xmlTagClear($fieldsInfo[$f->flid]['slug']) . '>';
                }
                $glf = GeneratedListField::where('rid', '=', $record->rid)->get();
                foreach($glf as $f) {
                    $xml .= '<' . $this->xmlTagClear($fieldsInfo[$f->flid]['slug']) . ' type="' . $fieldsInfo[$f->flid]['type'] . '">';
                    $options = explode('[!]', $f->options);
                    foreach ($options as $opt) {
                        $xml .= '<value>' . utf8_encode($opt) . '</value>';
                    }
                    $xml .= '</' . $this->xmlTagClear($fieldsInfo[$f->flid]['slug']) . '>';
                }
                $clf = ComboListField::where('rid', '=', $record->rid)->get();
                foreach($clf as $f) {
                    $xml .= '<' . $this->xmlTagClear($fieldsInfo[$f->flid]['slug']) . ' type="' . $fieldsInfo[$f->flid]['type'] . '">';
                    $field = FieldController::getField($f->flid);
                    $typeone = ComboListField::getComboFieldType($field, 'one');
                    $typetwo = ComboListField::getComboFieldType($field, 'two');
                    $nameone = ComboListField::getComboFieldName($field, 'one');
                    $nametwo = ComboListField::getComboFieldName($field, 'two');
                    $vals = explode('[!val!]', $f->options);
                    foreach ($vals as $val) {
                        $valone = explode('[!f1!]', $val)[1];
                        $valtwo = explode('[!f2!]', $val)[1];
                        $xml .= '<Value>';
                        $xml .= '<' . $this->xmlTagClear($nameone) . '>';
                        if ($typeone == 'Text' | $typeone == 'Number' | $typeone == 'List')
                            $xml .= utf8_encode($valone);
                        else if ($typeone == 'Multi-Select List' | $typeone == 'Generated List') {
                            $valone = explode('[!]', $valone);
                            foreach ($valone as $vone) {
                                $xml .= '<value>' . utf8_encode($vone) . '</value>';
                            }
                        }
                        $xml .= '</' . $this->xmlTagClear($nameone) . '>';
                        $xml .= '<' . $this->xmlTagClear($nametwo) . '>';
                        if ($typetwo == 'Text' | $typetwo == 'Number' | $typetwo == 'List')
                            $xml .= htmlentities($valtwo);
                        else if ($typetwo == 'Multi-Select List' | $typetwo == 'Generated List') {
                            $valtwo = explode('[!]', $valtwo);
                            foreach ($valtwo as $vtwo) {
                                $xml .= '<value>' . utf8_encode($vtwo) . '</value>';
                            }
                        }
                        $xml .= '</' . $this->xmlTagClear($nametwo) . '>';
                        $xml .= '</Value>';
                    }
                    $xml .= '</' . $this->xmlTagClear($fieldsInfo[$f->flid]['slug']) . '>';
                }
                $df = DateField::where('rid', '=', $record->rid)->get();
                foreach($df as $f) {
                    $xml .= '<' . $this->xmlTagClear($fieldsInfo[$f->flid]['slug']) . ' type="' . $fieldsInfo[$f->flid]['type'] . '">';
                    $value = '<Circa>' . utf8_encode($f->circa) . '</Circa>';
                    $value .= '<Month>' . utf8_encode($f->month) . '</Month>';
                    $value .= '<Day>' . utf8_encode($f->day) . '</Day>';
                    $value .= '<Year>' . utf8_encode($f->year) . '</Year>';
                    $value .= '<Era>' . utf8_encode($f->era) . '</Era>';
                    $xml .= $value;
                    $xml .= '</' . $this->xmlTagClear($fieldsInfo[$f->flid]['slug']) . '>';
                }
                $sf = ScheduleField::where('rid', '=', $record->rid)->get();
                foreach($sf as $f) {
                    $xml .= '<' . $this->xmlTagClear($fieldsInfo[$f->flid]['slug']) . ' type="' . $fieldsInfo[$f->flid]['type'] . '">';
                    $value = '';
                    $events = explode('[!]', $f->events);
                    foreach ($events as $event) {
                        $titleTime = explode(': ', $event);
                        $startEnd = explode(' - ',$titleTime[1]);
                        $start = explode(' ', $startEnd[0]);
                        $end = explode(' ', $startEnd[1]);

                        $value .= '<Event>';
                        $value .= '<Title>' . $titleTime[0] . '</Title>';
                        if(sizeof($start)==1) {
                            $value .= '<Start>' . $start[0] . '</Start>';
                            $value .= '<End>' . $end[0] . '</End>';
                            $value .= '<All_Day>' . utf8_encode(1) . '</All_Day>';
                        }else{
                            $value .= '<Start>' . $start[0] .' '. $start[1] .' '. $start[2] . '</Start>';
                            $value .= '<End>' . $end[0] .' '. $end[1] .' '. $end[2] . '</End>';
                            $value .= '<All_Day>' . utf8_encode(0) . '</All_Day>';
                        }
                        $value .= '</Event>';
                    }
                    $xml .= $value;
                    $xml .= '</' . $this->xmlTagClear($fieldsInfo[$f->flid]['slug']) . '>';
                }
                $df = DocumentsField::where('rid', '=', $record->rid)->get();
                foreach($df as $f) {
                    $xml .= '<' . $this->xmlTagClear($fieldsInfo[$f->flid]['slug']) . ' type="' . $fieldsInfo[$f->flid]['type'] . '">';
                    $files = explode('[!]', $f->documents);
                    foreach ($files as $file) {
                        $xml .= '<File>';
                        $xml .= '<Name>' . utf8_encode(explode('[Name]', $file)[1]) . '</Name>';
                        $xml .= '<Size>' . utf8_encode(explode('[Size]', $file)[1]/1000) . ' mb</Size>';
                        $xml .= '<Type>' . utf8_encode(explode('[Type]', $file)[1]) . '</Type>';
                        $xml .= '</File>';
                    }
                    $xml .= '</' . $this->xmlTagClear($fieldsInfo[$f->flid]['slug']) . '>';
                }
                $gf = GalleryField::where('rid', '=', $record->rid)->get();
                foreach($gf as $f) {
                    $xml .= '<' . $this->xmlTagClear($fieldsInfo[$f->flid]['slug']) . ' type="' . $fieldsInfo[$f->flid]['type'] . '">';
                    $files = explode('[!]', $f->images);
                    foreach ($files as $file) {
                        $xml .= '<File>';
                        $xml .= '<Name>' . utf8_encode(explode('[Name]', $file)[1]) . '</Name>';
                        $xml .= '<Size>' . utf8_encode(explode('[Size]', $file)[1]/1000) . ' mb</Size>';
                        $xml .= '<Type>' . utf8_encode(explode('[Type]', $file)[1]) . '</Type>';
                        $xml .= '</File>';
                    }
                    $xml .= '</' . $this->xmlTagClear($fieldsInfo[$f->flid]['slug']) . '>';
                }
                $pf = PlaylistField::where('rid', '=', $record->rid)->get();
                foreach($pf as $f) {
                    $xml .= '<' . $this->xmlTagClear($fieldsInfo[$f->flid]['slug']) . ' type="' . $fieldsInfo[$f->flid]['type'] . '">';
                    $files = explode('[!]', $f->audio);
                    foreach ($files as $file) {
                        $xml .= '<File>';
                        $xml .= '<Name>' . utf8_encode(explode('[Name]', $file)[1]) . '</Name>';
                        $xml .= '<Size>' . utf8_encode(explode('[Size]', $file)[1]/1000) . ' mb</Size>';
                        $xml .= '<Type>' . utf8_encode(explode('[Type]', $file)[1]) . '</Type>';
                        $xml .= '</File>';
                    }
                    $xml .= '</' . $this->xmlTagClear($fieldsInfo[$f->flid]['slug']) . '>';
                }
                $vf = VideoField::where('rid', '=', $record->rid)->get();
                foreach($vf as $f) {
                    $xml .= '<' . $this->xmlTagClear($fieldsInfo[$f->flid]['slug']) . ' type="' . $fieldsInfo[$f->flid]['type'] . '">';
                    $files = explode('[!]', $f->video);
                    foreach ($files as $file) {
                        $xml .= '<File>';
                        $xml .= '<Name>' . utf8_encode(explode('[Name]', $file)[1]) . '</Name>';
                        $xml .= '<Size>' . utf8_encode(explode('[Size]', $file)[1]/1000) . ' mb</Size>';
                        $xml .= '<Type>' . utf8_encode(explode('[Type]', $file)[1]) . '</Type>';
                        $xml .= '</File>';
                    }
                    $xml .= '</' . $this->xmlTagClear($fieldsInfo[$f->flid]['slug']) . '>';
                }
                $mf = ModelField::where('rid', '=', $record->rid)->get();
                foreach($mf as $f) {
                    $xml .= '<' . $this->xmlTagClear($fieldsInfo[$f->flid]['slug']) . ' type="' . $fieldsInfo[$f->flid]['type'] . '">';
                    $value = $f->model;
                    $xml .= '<File>';
                    $xml .= '<Name>' . utf8_encode(explode('[Name]', $value)[1]) . '</Name>';
                    $xml .= '<Size>' . utf8_encode(explode('[Size]', $value)[1]/1000) . ' mb</Size>';
                    $xml .= '<Type>' . utf8_encode(explode('[Type]', $value)[1]) . '</Type>';
                    $xml .= '</File>';
                    $xml .= '</' . $this->xmlTagClear($fieldsInfo[$f->flid]['slug']) . '>';
                }
                $gf = GeolocatorField::where('rid', '=', $record->rid)->get();
                foreach($gf as $f) {
                    $xml .= '<' . $this->xmlTagClear($fieldsInfo[$f->flid]['slug']) . ' type="' . $fieldsInfo[$f->flid]['type'] . '">';
                    $locations = explode('[!]', $f->locations);
                    foreach ($locations as $loc) {
                        $latlon = explode('[LatLon]', $loc)[1];
                        $utm = explode('[UTM]', $loc)[1];
                        $utm_coor = explode(':', $utm)[1];
                        $xml .= '<Location>';
                        $xml .= '<Desc>' . utf8_encode(explode('[Desc]', $loc)[1]) . '</Desc>';
                        $xml .= '<Lat>' . utf8_encode(explode(',', $latlon)[0]) . '</Lat>';
                        $xml .= '<Lon>' . utf8_encode(explode(',', $latlon)[1]) . '</Lon>';
                        $xml .= '<Zone>' . utf8_encode(explode(':', $utm)[0]) . '</Zone>';
                        $xml .= '<East>' . utf8_encode(explode(',', $utm_coor)[0]) . '</East>';
                        $xml .= '<North>' . utf8_encode(explode(',', $utm_coor)[1]) . '</North>';
                        $xml .= '<Address>' . utf8_encode(explode('[Address]', $loc)[1]) . '</Address>';
                        $xml .= '</Location>';
                    }
                    $xml .= '</' . $this->xmlTagClear($fieldsInfo[$f->flid]['slug']) . '>';
                }

                $xml .= '</Record>';
            }
            $xml .= '</Records>';

            header("Content-Disposition: attachment; filename=".$form->name.'_recordData_'.Carbon::now().'.xml');
            header("Content-Type: application/octet-stream; ");

            echo $xml;
        } else if($type=='json'){
            $json=array('Records'=>array());

            foreach ($records as $record) {
                $recArray = array('kid'=>$record->kid, 'Fields'=>array());

                $tf = TextField::where('rid', '=', $record->rid)->get();
                foreach($tf as $f) {
                    $fieldArray = array('name' => $fieldsInfo[$f->flid]['slug'], 'type' => $fieldsInfo[$f->flid]['type']);
                    $value = $f->text;
                    $fieldArray['text'] = $value;
                    array_push($recArray['Fields'], $fieldArray);
                }
                $rtf = RichTextField::where('rid', '=', $record->rid)->get();
                foreach($rtf as $f) {
                    $fieldArray = array('name' => $fieldsInfo[$f->flid]['slug'], 'type' => $fieldsInfo[$f->flid]['type']);
                    $value = $f->rawtext;
                    $fieldArray['richtext'] = $value;
                    array_push($recArray['Fields'], $fieldArray);
                }
                $nf = NumberField::where('rid', '=', $record->rid)->get();
                foreach($nf as $f) {
                    $fieldArray = array('name' => $fieldsInfo[$f->flid]['slug'], 'type' => $fieldsInfo[$f->flid]['type']);
                    $value = $f->number;
                    $fieldArray['number'] = $value;
                    array_push($recArray['Fields'], $fieldArray);
                }
                $lf = ListField::where('rid', '=', $record->rid)->get();
                foreach($lf as $f) {
                    $fieldArray = array('name' => $fieldsInfo[$f->flid]['slug'], 'type' => $fieldsInfo[$f->flid]['type']);
                    $value = $f->option;
                    $fieldArray['option'] = $value;
                    array_push($recArray['Fields'], $fieldArray);
                }
                $mlf = MultiSelectListField::where('rid', '=', $record->rid)->get();
                foreach($mlf as $f) {
                    $fieldArray = array('name' => $fieldsInfo[$f->flid]['slug'], 'type' => $fieldsInfo[$f->flid]['type']);
                    $options = explode('[!]', $f->options);
                    $fieldArray['options'] = $options;
                    array_push($recArray['Fields'], $fieldArray);
                }
                $glf = GeneratedListField::where('rid', '=', $record->rid)->get();
                foreach($glf as $f) {
                    $fieldArray = array('name' => $fieldsInfo[$f->flid]['slug'], 'type' => $fieldsInfo[$f->flid]['type']);
                    $options = explode('[!]', $f->options);
                    $fieldArray['options'] = $options;
                    array_push($recArray['Fields'], $fieldArray);
                }
                $clf = ComboListField::where('rid', '=', $record->rid)->get();
                foreach($clf as $f) {
                    $fieldArray = array('name' => $fieldsInfo[$f->flid]['slug'], 'type' => $fieldsInfo[$f->flid]['type']);
                    $field = FieldController::getField($f->flid);
                    $typeone = ComboListField::getComboFieldType($field, 'one');
                    $typetwo = ComboListField::getComboFieldType($field, 'two');
                    $nameone = ComboListField::getComboFieldName($field, 'one');
                    $nametwo = ComboListField::getComboFieldName($field, 'two');
                    $vals = explode('[!val!]', $f->options);
                    $fieldArray['values'] = array();
                    foreach ($vals as $val) {
                        $valArray = array();
                        $valone = explode('[!f1!]', $val)[1];
                        $valtwo = explode('[!f2!]', $val)[1];

                        if ($typeone == 'Text' | $typeone == 'Number' | $typeone == 'List')
                            $valArray[$nameone] = $valone;
                        else if ($typeone == 'Multi-Select List' | $typeone == 'Generated List') {
                            $valArray[$nameone] = explode('[!]', $valone);
                        }

                        if ($typetwo == 'Text' | $typetwo == 'Number' | $typetwo == 'List')
                            $valArray[$nametwo] = $valtwo;
                        else if ($typetwo == 'Multi-Select List' | $typetwo == 'Generated List') {
                            $valArray[$nametwo] = explode('[!]', $valtwo);
                        }

                        array_push($fieldArray['values'],$valArray);
                    }
                    array_push($recArray['Fields'], $fieldArray);
                }
                $df = DateField::where('rid', '=', $record->rid)->get();
                foreach($df as $f) {
                    $fieldArray = array('name' => $fieldsInfo[$f->flid]['slug'], 'type' => $fieldsInfo[$f->flid]['type']);
                    $fieldArray['circa'] = $f->circa;
                    $fieldArray['month'] = $f->month;
                    $fieldArray['day'] = $f->day;
                    $fieldArray['year'] = $f->year;
                    $fieldArray['era'] = $f->era;
                    array_push($recArray['Fields'], $fieldArray);
                }
                $sf = ScheduleField::where('rid', '=', $record->rid)->get();
                foreach($sf as $f) {
                    $fieldArray = array('name' => $fieldsInfo[$f->flid]['slug'], 'type' => $fieldsInfo[$f->flid]['type']);
                    $value = '';
                    $events = explode('[!]', $f->events);
                    $fieldArray['events'] = array();
                    foreach ($events as $event) {
                        $titleTime = explode(': ', $event);
                        $startEnd = explode(' - ',$titleTime[1]);
                        $start = explode(' ', $startEnd[0]);
                        $end = explode(' ', $startEnd[1]);

                        $eventArray = array();
                        $eventArray['title'] = $titleTime[0];
                        if (sizeof($start) == 1) {
                            $eventArray['start'] = $start[0];
                            $eventArray['end'] = $end[0];
                            $eventArray['allday'] = 1;
                        } else {
                            $eventArray['start'] = $start[0] .' '. $start[1] .' '. $start[2];
                            $eventArray['end'] = $end[0] .' '. $end[1] .' '. $end[2];
                            $eventArray['allday'] = 0;
                        }
                        array_push($fieldArray['events'],$eventArray);
                    }
                    array_push($recArray['Fields'], $fieldArray);
                }
                $df = DocumentsField::where('rid', '=', $record->rid)->get();
                foreach($df as $f) {
                    $fieldArray = array('name' => $fieldsInfo[$f->flid]['slug'], 'type' => $fieldsInfo[$f->flid]['type']);
                    $files = explode('[!]', $f->documents);
                    $fieldArray['files'] = array();
                    foreach ($files as $file) {
                        $fileArray = array();
                        $fileArray['name'] = explode('[Name]', $file)[1];
                        $fileArray['size'] = (explode('[Size]', $file)[1]/1000).' mb';
                        $fileArray['type'] = explode('[Type]', $file)[1];
                        array_push($fieldArray['files'],$fileArray);
                    }
                    array_push($recArray['Fields'], $fieldArray);
                }
                $gf = GalleryField::where('rid', '=', $record->rid)->get();
                foreach($gf as $f) {
                    $fieldArray = array('name' => $fieldsInfo[$f->flid]['slug'], 'type' => $fieldsInfo[$f->flid]['type']);
                    $files = explode('[!]', $f->images);
                    $fieldArray['files'] = array();
                    foreach ($files as $file) {
                        $fileArray = array();
                        $fileArray['name'] = explode('[Name]', $file)[1];
                        $fileArray['size'] = (explode('[Size]', $file)[1]/1000).' mb';
                        $fileArray['type'] = explode('[Type]', $file)[1];
                        array_push($fieldArray['files'],$fileArray);
                    }
                    array_push($recArray['Fields'], $fieldArray);
                }
                $pf = PlaylistField::where('rid', '=', $record->rid)->get();
                foreach($pf as $f) {
                    $fieldArray = array('name' => $fieldsInfo[$f->flid]['slug'], 'type' => $fieldsInfo[$f->flid]['type']);
                    $files = explode('[!]', $f->audio);
                    $fieldArray['files'] = array();
                    foreach ($files as $file) {
                        $fileArray = array();
                        $fileArray['name'] = explode('[Name]', $file)[1];
                        $fileArray['size'] = (explode('[Size]', $file)[1]/1000).' mb';
                        $fileArray['type'] = explode('[Type]', $file)[1];
                        array_push($fieldArray['files'],$fileArray);
                    }
                    array_push($recArray['Fields'], $fieldArray);
                }
                $vf = VideoField::where('rid', '=', $record->rid)->get();
                foreach($vf as $f) {
                    $fieldArray = array('name' => $fieldsInfo[$f->flid]['slug'], 'type' => $fieldsInfo[$f->flid]['type']);
                    $files = explode('[!]', $f->video);
                    $fieldArray['files'] = array();
                    foreach ($files as $file) {
                        $fileArray = array();
                        $fileArray['name'] = explode('[Name]', $file)[1];
                        $fileArray['size'] = (explode('[Size]', $file)[1]/1000).' mb';
                        $fileArray['type'] = explode('[Type]', $file)[1];
                        array_push($fieldArray['files'],$fileArray);
                    }
                    array_push($recArray['Fields'], $fieldArray);
                }
                $mf = ModelField::where('rid', '=', $record->rid)->get();
                foreach($mf as $f) {
                    $fieldArray = array('name' => $fieldsInfo[$f->flid]['slug'], 'type' => $fieldsInfo[$f->flid]['type']);
                    $file = $f->model;
                    $fieldArray['files'] = array();
                    $fileArray = array();
                    $fileArray['name'] = explode('[Name]', $file)[1];
                    $fileArray['size'] = (explode('[Size]', $file)[1]/1000).' mb';
                    $fileArray['type'] = explode('[Type]', $file)[1];
                    array_push($fieldArray['files'],$fileArray);
                    array_push($recArray['Fields'], $fieldArray);
                }
                $gf = GeolocatorField::where('rid', '=', $record->rid)->get();
                foreach($gf as $f) {
                    $fieldArray = array('name' => $fieldsInfo[$f->flid]['slug'], 'type' => $fieldsInfo[$f->flid]['type']);
                    $locations = explode('[!]', $f->locations);
                    $fieldArray['locations'] = array();
                    foreach ($locations as $loc) {
                        $locArray = array();

                        $latlon = explode('[LatLon]', $loc)[1];
                        $utm = explode('[UTM]', $loc)[1];
                        $utm_coor = explode(':', $utm)[1];

                        $locArray['desc'] = explode('[Desc]', $loc)[1];
                        $locArray['lat'] = explode(',', $latlon)[0];
                        $locArray['lon'] = explode(',', $latlon)[1];
                        $locArray['zone'] = explode(':', $utm)[0];
                        $locArray['east'] = explode(',', $utm_coor)[0];
                        $locArray['north'] = explode(',', $utm_coor)[1];
                        $locArray['address'] = explode('[Address]', $loc)[1];
                        array_push($fieldArray['locations'],$locArray);
                    }
                    array_push($recArray['Fields'], $fieldArray);
                }

                array_push($json['Records'],$recArray);
            }
            $json = json_encode($json);

            header("Content-Disposition: attachment; filename=".$form->name.'_recordData_'.Carbon::now().'.json');
            header("Content-Type: application/octet-stream; ");

            echo $json;
        }
*/
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
            return redirect('projects');
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
            return redirect('projects');
        }

        $form = FormController::getForm($fid);

        if (!\Auth::user()->isFormAdmin($form)) {
            return redirect('projects/' . $pid . '/forms/' . $fid);
        }

        $formArray = array();

        $formArray['name'] = $form->name;
        $formArray['slug'] = $form->slug;
        $formArray['desc'] = $form->description;
        $formArray['layout'] = $form->layout;
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