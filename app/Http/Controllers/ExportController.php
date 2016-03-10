<?php namespace App\Http\Controllers;

use App\ComboListField;
use App\DateField;
use App\DocumentsField;
use App\Field;
use App\Form;
use App\GalleryField;
use App\GeneratedListField;
use App\GeolocatorField;
use App\Http\Requests;
use App\Http\Controllers\Controller;

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
use Illuminate\Http\Request;

class ExportController extends Controller {

    public function exportRecords($pid, $fid, $type){
        if(!FormController::validProjForm($pid,$fid)){
            return redirect('projects');
        }

        $form = FormController::getForm($fid);

        if(!\Auth::user()->isFormAdmin($form)){
            return redirect('projects/'.$pid.'/forms/'.$fid);
        }



        $records = Record::where('fid', '=', $fid)->get();
        $fields = Field::where('fid', '=', $fid)->get();
        //dd($records);

        if($type=='xml') {
            $xml='<Records>';

            foreach ($records as $record) {
                $xml .= '<Record kid="' . $record->kid . '">';

                foreach ($fields as $field) {
                    $xml .= '<' . htmlentities($field->slug) . ' type="' . $field->type . '">';

                    if ($field->type == 'Text') {
                        $f = TextField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->get()->first();
                        if (!is_null($f)) {
                            $value = $f->text;
                            $xml .= htmlentities($value);
                        }
                    } else if ($field->type == 'Rich Text') {
                        $f = RichTextField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->get()->first();
                        if (!is_null($f)) {
                            $value = $f->rawtext;
                            $xml .= htmlentities($value);
                        }
                    } else if ($field->type == 'Number') {
                        $f = NumberField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->get()->first();
                        if (!is_null($f)) {
                            $value = $f->number;
                            $xml .= htmlentities($value);
                        }
                    } else if ($field->type == 'List') {
                        $f = ListField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->get()->first();
                        if (!is_null($f)) {
                            $value = $f->option;
                            $xml .= htmlentities($value);
                        }
                    } else if ($field->type == 'Multi-Select List') {
                        $f = MultiSelectListField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->get()->first();
                        if (!is_null($f)) {
                            $options = explode('[!]', $f->options);
                            foreach ($options as $opt) {
                                $xml .= '<value>' . htmlentities($opt) . '</value>';
                            }
                        }
                    } else if ($field->type == 'Generated List') {
                        $f = GeneratedListField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->get()->first();
                        if (!is_null($f)) {
                            $options = explode('[!]', $f->options);
                            foreach ($options as $opt) {
                                $xml .= '<value>' . htmlentities($opt) . '</value>';
                            }
                        }
                    } else if ($field->type == 'Combo List') {
                        $f = ComboListField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->get()->first();
                        if (!is_null($f)) {
                            $typeone = ComboListField::getComboFieldType($field, 'one');
                            $typetwo = ComboListField::getComboFieldType($field, 'two');
                            $vals = explode('[!val!]', $f->options);
                            foreach ($vals as $val) {
                                $valone = explode('[!f1!]', $val)[1];
                                $valtwo = explode('[!f2!]', $val)[1];
                                $xml .= '<Value>';
                                $xml .= '<Field_One>';
                                if ($typeone == 'Text' | $typeone == 'Number' | $typeone == 'List')
                                    $xml .= htmlentities($valone);
                                else if ($typeone == 'Multi-Select List' | $typeone == 'Generated List') {
                                    $valone = explode('[!]', $valone);
                                    foreach ($valone as $vone) {
                                        $xml .= '<value>' . htmlentities($vone) . '</value>';
                                    }
                                }
                                $xml .= '</Field_One>';
                                $xml .= '<Field_Two>';
                                if ($typetwo == 'Text' | $typetwo == 'Number' | $typetwo == 'List')
                                    $xml .= htmlentities($valtwo);
                                else if ($typetwo == 'Multi-Select List' | $typetwo == 'Generated List') {
                                    $valtwo = explode('[!]', $valtwo);
                                    foreach ($valtwo as $vtwo) {
                                        $xml .= '<value>' . htmlentities($vtwo) . '</value>';
                                    }
                                }
                                $xml .= '</Field_Two>';
                                $xml .= '</Value>';
                            }
                        }
                    } else if ($field->type == 'Date') {
                        $f = DateField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->get()->first();
                        if (!is_null($f)) {
                            $value = '<Circa>' . htmlentities($f->circa) . '</Circa>';
                            $value .= '<Month>' . htmlentities($f->month) . '</Month>';
                            $value .= '<Day>' . htmlentities($f->day) . '</Day>';
                            $value .= '<Year>' . htmlentities($f->year) . '</Year>';
                            $value .= '<Era>' . htmlentities($f->era) . '</Era>';
                            $xml .= $value;
                        }
                    } else if ($field->type == 'Schedule') {
                        $f = ScheduleField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->get()->first();
                        if (!is_null($f)) {
                            $value = '';
                            $events = explode('[!]', $f->events);
                            foreach ($events as $event) {
                                $parts = explode(' ', $event);
                                if (sizeof($parts) == 8) {
                                    $value .= '<Event>';
                                    $value .= '<Title>' . htmlentities(substr($parts[0], 0, -1)) . '</Title>';
                                    $value .= '<Start>' . htmlentities($parts[1] . ' ' . $parts[2] . ' ' . $parts[3]) . '</Start>';
                                    $value .= '<End>' . htmlentities($parts[5] . ' ' . $parts[6] . ' ' . $parts[7]) . '</End>';
                                    $value .= '<All_Day>' . htmlentities(0) . '</All_Day>';
                                    $value .= '</Event>';
                                } else { //all day event
                                    $value .= '<Event>';
                                    $value .= '<Title>' . htmlentities(substr($parts[0], 0, -1)) . '</Title>';
                                    $value .= '<Start>' . htmlentities($parts[1]) . '</Start>';
                                    $value .= '<End>' . htmlentities($parts[3]) . '</End>';
                                    $value .= '<All_Day>' . htmlentities(1) . '</All_Day>';
                                    $value .= '</Event>';
                                }
                            }
                            $xml .= $value;
                        }
                    } else if ($field->type == 'Documents') {
                        $f = DocumentsField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->get()->first();
                        if (!is_null($f)) {
                            $files = explode('[!]', $f->documents);
                            foreach ($files as $file) {
                                $xml .= '<File>';
                                $xml .= '<Name>' . htmlentities(explode('[Name]', $file)[1]) . '</Name>';
                                $xml .= '<Size>' . htmlentities(explode('[Size]', $file)[1]) . '</Size>';
                                $xml .= '<Type>' . htmlentities(explode('[Type]', $file)[1]) . '</Type>';
                                $xml .= '</File>';
                            }
                        }
                    } else if ($field->type == 'Gallery') {
                        $f = GalleryField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->get()->first();
                        if (!is_null($f)) {
                            $files = explode('[!]', $f->images);
                            foreach ($files as $file) {
                                $xml .= '<File>';
                                $xml .= '<Name>' . htmlentities(explode('[Name]', $file)[1]) . '</Name>';
                                $xml .= '<Size>' . htmlentities(explode('[Size]', $file)[1]) . '</Size>';
                                $xml .= '<Type>' . htmlentities(explode('[Type]', $file)[1]) . '</Type>';
                                $xml .= '</File>';
                            }
                        }
                    } else if ($field->type == 'Playlist') {
                        $f = PlaylistField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->get()->first();
                        if (!is_null($f)) {
                            $files = explode('[!]', $f->audio);
                            foreach ($files as $file) {
                                $xml .= '<File>';
                                $xml .= '<Name>' . htmlentities(explode('[Name]', $file)[1]) . '</Name>';
                                $xml .= '<Size>' . htmlentities(explode('[Size]', $file)[1]) . '</Size>';
                                $xml .= '<Type>' . htmlentities(explode('[Type]', $file)[1]) . '</Type>';
                                $xml .= '</File>';
                            }
                        }
                    } else if ($field->type == 'Video') {
                        $f = VideoField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->get()->first();
                        if (!is_null($f)) {
                            $files = explode('[!]', $f->video);
                            foreach ($files as $file) {
                                $xml .= '<File>';
                                $xml .= '<Name>' . htmlentities(explode('[Name]', $file)[1]) . '</Name>';
                                $xml .= '<Size>' . htmlentities(explode('[Size]', $file)[1]) . '</Size>';
                                $xml .= '<Type>' . htmlentities(explode('[Type]', $file)[1]) . '</Type>';
                                $xml .= '</File>';
                            }
                        }
                    } else if ($field->type == '3D-Model') {
                        $f = ModelField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->get()->first();
                        if (!is_null($f)) {
                            $value = $f->model;
                            $xml .= '<File>';
                            $xml .= '<Name>' . htmlentities(explode('[Name]', $value)[1]) . '</Name>';
                            $xml .= '<Size>' . htmlentities(explode('[Size]', $value)[1]) . '</Size>';
                            $xml .= '<Type>' . htmlentities(explode('[Type]', $value)[1]) . '</Type>';
                            $xml .= '</File>';
                        }
                    } else if ($field->type == 'Geolocator') {
                        $f = GeolocatorField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->get()->first();
                        if (!is_null($f)) {
                            $locations = explode('[!]', $f->locations);
                            foreach ($locations as $loc) {
                                $latlon = explode('[LatLon]', $loc)[1];
                                $utm = explode('[UTM]', $loc)[1];
                                $utm_coor = explode(':', $utm)[1];
                                $xml .= '<Location>';
                                $xml .= '<Desc>' . htmlentities(explode('[Desc]', $loc)[1]) . '</Desc>';
                                $xml .= '<Lat>' . htmlentities(explode(',', $latlon)[0]) . '</Lat>';
                                $xml .= '<Lon>' . htmlentities(explode(',', $latlon)[1]) . '</Lon>';
                                $xml .= '<Zone>' . htmlentities(explode(':', $utm)[0]) . '</Zone>';
                                $xml .= '<East>' . htmlentities(explode(',', $utm_coor)[0]) . '</East>';
                                $xml .= '<North>' . htmlentities(explode(',', $utm_coor)[1]) . '</North>';
                                $xml .= '<Address>' . htmlentities(explode('[Address]', $loc)[1]) . '</Address>';
                                $xml .= '</Location>';
                            }
                        }
                    }

                    $xml .= '</' . htmlentities($field->slug) . '>';
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

                foreach ($fields as $field) {
                    $fieldArray = array('name' => $field->slug,'type'=>$field->type);

                    if ($field->type == 'Text') {
                        $f = TextField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->get()->first();
                        if (!is_null($f)) {
                            $value = $f->text;
                            $fieldArray['text'] = $value;
                        }
                    } else if ($field->type == 'Rich Text') {
                        $f = RichTextField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->get()->first();
                        if (!is_null($f)) {
                            $value = $f->rawtext;
                            $fieldArray['richtext'] = $value;
                        }
                    } else if ($field->type == 'Number') {
                        $f = NumberField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->get()->first();
                        if (!is_null($f)) {
                            $value = $f->number;
                            $fieldArray['number'] = $value;
                        }
                    } else if ($field->type == 'List') {
                        $f = ListField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->get()->first();
                        if (!is_null($f)) {
                            $value = $f->option;
                            $fieldArray['option'] = $value;
                        }
                    } else if ($field->type == 'Multi-Select List') {
                        $f = MultiSelectListField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->get()->first();
                        if (!is_null($f)) {
                            $options = explode('[!]', $f->options);
                            $fieldArray['options'] = $options;
                        }
                    } else if ($field->type == 'Generated List') {
                        $f = GeneratedListField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->get()->first();
                        if (!is_null($f)) {
                            $options = explode('[!]', $f->options);
                            $fieldArray['options'] = $options;
                        }
                    } else if ($field->type == 'Combo List') {
                        $f = ComboListField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->get()->first();
                        if (!is_null($f)) {
                            $typeone = ComboListField::getComboFieldType($field, 'one');
                            $typetwo = ComboListField::getComboFieldType($field, 'two');
                            $vals = explode('[!val!]', $f->options);
                            $fieldArray['values'] = array();
                            foreach ($vals as $val) {
                                $valArray = array();
                                $valone = explode('[!f1!]', $val)[1];
                                $valtwo = explode('[!f2!]', $val)[1];

                                if ($typeone == 'Text' | $typeone == 'Number' | $typeone == 'List')
                                    $valArray['field_one'] = $valone;
                                else if ($typeone == 'Multi-Select List' | $typeone == 'Generated List') {
                                    $valArray['field_one'] = explode('[!]', $valone);
                                }

                                if ($typetwo == 'Text' | $typetwo == 'Number' | $typetwo == 'List')
                                    $valArray['field_two'] = $valtwo;
                                else if ($typetwo == 'Multi-Select List' | $typetwo == 'Generated List') {
                                    $valArray['field_two'] = explode('[!]', $valtwo);
                                }

                                array_push($fieldArray['values'],$valArray);
                            }
                        }
                    } else if ($field->type == 'Date') {
                        $f = DateField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->get()->first();
                        if (!is_null($f)) {
                            $fieldArray['circa'] = $f->circa;
                            $fieldArray['month'] = $f->month;
                            $fieldArray['day'] = $f->day;
                            $fieldArray['year'] = $f->year;
                            $fieldArray['era'] = $f->era;
                        }
                    } else if ($field->type == 'Schedule') {
                        $f = ScheduleField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->get()->first();
                        if (!is_null($f)) {
                            $value = '';
                            $events = explode('[!]', $f->events);
                            $fieldArray['events'] = array();
                            foreach ($events as $event) {
                                $parts = explode(' ', $event);
                                $eventArray = array();
                                if (sizeof($parts) == 8) {
                                    $eventArray['title'] = substr($parts[0], 0, -1);
                                    $eventArray['start'] = $parts[1] . ' ' . $parts[2] . ' ' . $parts[3];
                                    $eventArray['end'] = $parts[5] . ' ' . $parts[6] . ' ' . $parts[7];
                                    $eventArray['allday'] = 0;
                                } else { //all day event
                                    $eventArray['title'] = substr($parts[0], 0, -1);
                                    $eventArray['start'] = $parts[1];
                                    $eventArray['end'] = $parts[3];
                                    $eventArray['allday'] = 1;
                                }
                                array_push($fieldArray['events'],$eventArray);
                            }
                        }
                    } else if ($field->type == 'Documents') {
                        $f = DocumentsField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->get()->first();
                        if (!is_null($f)) {
                            $files = explode('[!]', $f->documents);
                            $fieldArray['files'] = array();
                            foreach ($files as $file) {
                                $fileArray = array();
                                $fileArray['name'] = explode('[Name]', $file)[1];
                                $fileArray['size'] = explode('[Size]', $file)[1];
                                $fileArray['type'] = explode('[Type]', $file)[1];
                                array_push($fieldArray['files'],$fileArray);
                            }
                        }
                    } else if ($field->type == 'Gallery') {
                        $f = GalleryField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->get()->first();
                        if (!is_null($f)) {
                            $files = explode('[!]', $f->images);
                            $fieldArray['files'] = array();
                            foreach ($files as $file) {
                                $fileArray = array();
                                $fileArray['name'] = explode('[Name]', $file)[1];
                                $fileArray['size'] = explode('[Size]', $file)[1];
                                $fileArray['type'] = explode('[Type]', $file)[1];
                                array_push($fieldArray['files'],$fileArray);
                            }
                        }
                    } else if ($field->type == 'Playlist') {
                        $f = PlaylistField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->get()->first();
                        if (!is_null($f)) {
                            $files = explode('[!]', $f->audio);
                            $fieldArray['files'] = array();
                            foreach ($files as $file) {
                                $fileArray = array();
                                $fileArray['name'] = explode('[Name]', $file)[1];
                                $fileArray['size'] = explode('[Size]', $file)[1];
                                $fileArray['type'] = explode('[Type]', $file)[1];
                                array_push($fieldArray['files'],$fileArray);
                            }
                        }
                    } else if ($field->type == 'Video') {
                        $f = VideoField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->get()->first();
                        if (!is_null($f)) {
                            $files = explode('[!]', $f->video);
                            $fieldArray['files'] = array();
                            foreach ($files as $file) {
                                $fileArray = array();
                                $fileArray['name'] = explode('[Name]', $file)[1];
                                $fileArray['size'] = explode('[Size]', $file)[1];
                                $fileArray['type'] = explode('[Type]', $file)[1];
                                array_push($fieldArray['files'],$fileArray);
                            }
                        }
                    } else if ($field->type == '3D-Model') {
                        $f = ModelField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->get()->first();
                        if (!is_null($f)) {
                            $file = $f->model;
                            $fieldArray['files'] = array();
                            $fileArray = array();
                            $fileArray['name'] = explode('[Name]', $file)[1];
                            $fileArray['size'] = explode('[Size]', $file)[1];
                            $fileArray['type'] = explode('[Type]', $file)[1];
                            array_push($fieldArray['files'],$fileArray);
                        }
                    } else if ($field->type == 'Geolocator') {
                        $f = GeolocatorField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->get()->first();
                        if (!is_null($f)) {
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
                        }
                    }

                    array_push($recArray['Fields'],$fieldArray);
                }

                array_push($json['Records'],$recArray);
            }
            $json = json_encode($json);

            header("Content-Disposition: attachment; filename=".$form->name.'_recordData_'.Carbon::now().'.json');
            header("Content-Type: application/octet-stream; ");

            echo $json;
        } else if($type=='csv'){

        }
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
}
