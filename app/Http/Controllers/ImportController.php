<?php namespace App\Http\Controllers;

use App\ComboListField;
use App\DateField;
use App\DocumentsField;
use App\Field;
use App\Form;
use App\FormGroup;
use App\GalleryField;
use App\GeneratedListField;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\ListField;
use App\Metadata;
use App\MultiSelectListField;
use App\OptionPreset;
use App\Project;
use App\ProjectGroup;
use App\Record;
use App\RecordPreset;
use App\RichTextField;
use App\ScheduleField;
use App\TextField;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ImportController extends Controller {

    public function exportSample($pid, $fid, $type){
        if(!FormController::validProjForm($pid,$fid)){
            return redirect('projects');
        }

        $form = FormController::getForm($fid);

        if(!\Auth::user()->isFormAdmin($form)){
            return redirect('projects/'.$pid.'/forms/'.$fid);
        }

        $fields = Field::where('fid', '=', $fid)->get();

        if($type=='XML') {
            $xml = '<?xml version="1.0" encoding="utf-8"?><Records>';
            $xml .= '<Record kid="OPTIONAL KID FOR RECORD. USE TO COMPLETE ASSOCIATED REFERENCES">';

            foreach ($fields as $field) {
                if ($field->type == "Text") {
                    $xml .= '<' . $this->xmlTagClear($field->slug) . ' type="' . $field->type . '">';
                    $xml .= utf8_encode('TEXT VALUE');
                    $xml .= '</' . $this->xmlTagClear($field->slug) . '>';
                }
                else if ($field->type == "Rich Text") {
                    $xml .= '<' . $this->xmlTagClear($field->slug) . ' type="' . $field->type . '">';
                    $xml .= utf8_encode('<b>RICH TEXT VALUE</b>');
                    $xml .= '</' . $this->xmlTagClear($field->slug) . '>';
                }
                else if ($field->type == "Number") {
                    $xml .= '<' . $this->xmlTagClear($field->slug) . ' type="' . $field->type . '">';
                    $xml .= utf8_encode('1337');
                    $xml .= '</' . $this->xmlTagClear($field->slug) . '>';
                }
                else if ($field->type == "List") {
                    $xml .= '<' . $this->xmlTagClear($field->slug) . ' type="' . $field->type . '">';
                    $xml .= utf8_encode('LIST VALUE');
                    $xml .= '</' . $this->xmlTagClear($field->slug) . '>';
                }
                else if ($field->type == "Multi-Select List") {
                    $xml .= '<' . $this->xmlTagClear($field->slug) . ' type="' . $field->type . '">';
                    $xml .= '<value>' . utf8_encode('LIST VALUE 1') . '</value>';
                    $xml .= '<value>' . utf8_encode('LIST VALUE 2') . '</value>';
                    $xml .= '<value>' . utf8_encode('so on...') . '</value>';
                    $xml .= '</' . $this->xmlTagClear($field->slug) . '>';
                }
                else if ($field->type == "Generated List") {
                    $xml .= '<' . $this->xmlTagClear($field->slug) . ' type="' . $field->type . '">';
                    $xml .= '<value>' . utf8_encode('LIST VALUE 1') . '</value>';
                    $xml .= '<value>' . utf8_encode('LIST VALUE 2') . '</value>';
                    $xml .= '<value>' . utf8_encode('so on...') . '</value>';
                    $xml .= '</' . $this->xmlTagClear($field->slug) . '>';
                }
                else if ($field->type == "Combo List") {
                    $xml .= '<' . $this->xmlTagClear($field->slug) . ' type="' . $field->type . '">';
                    $typeone = ComboListField::getComboFieldType($field, 'one');
                    $typetwo = ComboListField::getComboFieldType($field, 'two');
                    $nameone = ComboListField::getComboFieldName($field, 'one');
                    $nametwo = ComboListField::getComboFieldName($field, 'two');
                    $xml .= '<Value>';
                    $xml .= '<' . $this->xmlTagClear($nameone) . '>';
                    if ($typeone == 'Text' | $typeone == 'Number' | $typeone == 'List')
                        $xml .= utf8_encode('VALUE');
                    else if ($typeone == 'Multi-Select List' | $typeone == 'Generated List') {
                        $xml .= '<value>'.utf8_encode('VALUE 1').'</value>';
                        $xml .= '<value>'.utf8_encode('VALUE 2').'</value>';
                        $xml .= '<value>'.utf8_encode('so on..').'</value>';
                    }
                    $xml .= '</' . $this->xmlTagClear($nameone) . '>';
                    $xml .= '<' . $this->xmlTagClear($nametwo) . '>';
                    if ($typetwo == 'Text' | $typetwo == 'Number' | $typetwo == 'List')
                        $xml .= utf8_encode('VALUE');
                    else if ($typetwo == 'Multi-Select List' | $typetwo == 'Generated List') {
                        $xml .= '<value>'.utf8_encode('VALUE 1').'</value>';
                        $xml .= '<value>'.utf8_encode('VALUE 2').'</value>';
                        $xml .= '<value>'.utf8_encode('so on..').'</value>';
                    }
                    $xml .= '</' . $this->xmlTagClear($nametwo) . '>';
                    $xml .= '</Value>';
                    $xml .= '</' . $this->xmlTagClear($field->slug) . '>';
                }
                else if ($field->type == "Date") {
                    $xml .= '<' . $this->xmlTagClear($field->slug) . ' type="' . $field->type . '">';
                    $value = '<Circa>' . utf8_encode('1 IF CIRCA. 0 IF NOT') . '</Circa>';
                    $value .= '<Month>' . utf8_encode('NUMERIC VALUE OF MONTH (i.e. 08)') . '</Month>';
                    $value .= '<Day>' . utf8_encode('19') . '</Day>';
                    $value .= '<Year>' . utf8_encode('1990') . '</Year>';
                    $value .= '<Era>' . utf8_encode('CE OR BCE') . '</Era>';
                    $xml .= $value;
                    $xml .= '</' . $this->xmlTagClear($field->slug) . '>';
                }
                else if ($field->type == "Schedule") {
                    $xml .= '<' . $this->xmlTagClear($field->slug) . ' type="' . $field->type . '">';
                    $value = '<Event>';
                    $value .= '<Title>' . utf8_encode('EVENT TITLE 1') . '</Title>';
                    $value .= '<Start>' . '08/19/1990 12:00 AM' . '</Start>';
                    $value .= '<End>' . '08/19/1990 12:30 AM' . '</End>';
                    $value .= '<All_Day>' . utf8_encode('0 FOR TIMED EVENT') . '</All_Day>';
                    $value .= '</Event>';
                    $value .= '<Event>';
                    $value .= '<Title>' . utf8_encode('EVENT TITLE 2') . '</Title>';
                    $value .= '<Start>' . '08/19/1990' . '</Start>';
                    $value .= '<End>' . '08/20/1990' . '</End>';
                    $value .= '<All_Day>' . utf8_encode('1 FOR ALL DAY EVENT') . '</All_Day>';
                    $value .= '</Event>';
                    $xml .= $value;
                    $xml .= '</' . $this->xmlTagClear($field->slug) . '>';
                }
                else if ($field->type == "Documents") {
                    $xml .= '<' . $this->xmlTagClear($field->slug) . ' type="' . $field->type . '">';
                    $xml .= '<File>';
                    $xml .= '<Name>' . utf8_encode('FILENAME 1') . '</Name>';
                    $xml .= '</File>';
                    $xml .= '<File>';
                    $xml .= '<Name>' . utf8_encode('FILENAME 2') . '</Name>';
                    $xml .= '</File>';
                    $xml .= '<File>';
                    $xml .= '<Name>' . utf8_encode('so on...') . '</Name>';
                    $xml .= '</File>';
                    $xml .= '</' . $this->xmlTagClear($field->slug) . '>';
                }
                else if ($field->type == "Gallery") {
                    $xml .= '<' . $this->xmlTagClear($field->slug) . ' type="' . $field->type . '">';
                    $xml .= '<File>';
                    $xml .= '<Name>' . utf8_encode('FILENAME 1') . '</Name>';
                    $xml .= '</File>';
                    $xml .= '<File>';
                    $xml .= '<Name>' . utf8_encode('FILENAME 2') . '</Name>';
                    $xml .= '</File>';
                    $xml .= '<File>';
                    $xml .= '<Name>' . utf8_encode('so on...') . '</Name>';
                    $xml .= '</File>';
                    $xml .= '</' . $this->xmlTagClear($field->slug) . '>';
                }
                else if ($field->type == "Playlist") {
                    $xml .= '<' . $this->xmlTagClear($field->slug) . ' type="' . $field->type . '">';
                    $xml .= '<File>';
                    $xml .= '<Name>' . utf8_encode('FILENAME 1') . '</Name>';
                    $xml .= '</File>';
                    $xml .= '<File>';
                    $xml .= '<Name>' . utf8_encode('FILENAME 2') . '</Name>';
                    $xml .= '</File>';
                    $xml .= '<File>';
                    $xml .= '<Name>' . utf8_encode('so on...') . '</Name>';
                    $xml .= '</File>';
                    $xml .= '</' . $this->xmlTagClear($field->slug) . '>';
                }
                else if ($field->type == "Video") {
                    $xml .= '<' . $this->xmlTagClear($field->slug) . ' type="' . $field->type . '">';
                    $xml .= '<File>';
                    $xml .= '<Name>' . utf8_encode('FILENAME 1') . '</Name>';
                    $xml .= '</File>';
                    $xml .= '<File>';
                    $xml .= '<Name>' . utf8_encode('FILENAME 2') . '</Name>';
                    $xml .= '</File>';
                    $xml .= '<File>';
                    $xml .= '<Name>' . utf8_encode('so on...') . '</Name>';
                    $xml .= '</File>';
                    $xml .= '</' . $this->xmlTagClear($field->slug) . '>';
                }
                else if ($field->type == "3D-Model") {
                    $xml .= '<' . $this->xmlTagClear($field->slug) . ' type="' . $field->type . '">';
                    $xml .= '<File>';
                    $xml .= '<Name>' . utf8_encode('FILENAME') . '</Name>';
                    $xml .= '</File>';
                    $xml .= '</' . $this->xmlTagClear($field->slug) . '>';
                }
                else if ($field->type == "Geolocator") {
                    $xml .= '<' . $this->xmlTagClear($field->slug) . ' type="' . $field->type . '">';
                    $xml .= '<Location>';
                    $xml .= '<Desc>' . utf8_encode('LOCATION DESCRIPTION') . '</Desc>';
                    $xml .= '<Lat>' . utf8_encode('i.e. 13') . '</Lat>';
                    $xml .= '<Lon>' . utf8_encode('i.e. 14.5') . '</Lon>';
                    $xml .= '<Zone>' . utf8_encode('i.e. 38T') . '</Zone>';
                    $xml .= '<East>' . utf8_encode('i.e. 59233.235234') . '</East>';
                    $xml .= '<North>' . utf8_encode('i.e. 52833.265454') . '</North>';
                    $xml .= '<Address>' . utf8_encode('TEXTUAL REPRESENTATION OF LOCATION') . '</Address>';
                    $xml .= '</Location>';
                    $xml .= '</' . $this->xmlTagClear($field->slug) . '>';
                }
            }

            $xml .= '</Record></Records>';

            header("Content-Disposition: attachment; filename=" . $form->name . '_exampleData.xml');
            header("Content-Type: application/octet-stream; ");

            echo $xml;
        } else if($type=='JSON') {
            $json=array('Records'=>array());
            $recArray = array('kid'=>"OPTIONAL KID FOR RECORD. USE TO COMPLETE ASSOCIATED REFERENCES", 'Fields'=>array());

            foreach ($fields as $field) {
                if ($field->type == "Text") {
                    $fieldArray = array('name' => $field->slug, 'type' => $field->type);
                    $fieldArray['text'] = 'TEXT VALUE';
                    array_push($recArray['Fields'], $fieldArray);
                }
                else if ($field->type == "Rich Text") {
                    $fieldArray = array('name' => $field->slug, 'type' => $field->type);
                    $fieldArray['richtext'] = '<b>RICH TEXT VALUE</b>';
                    array_push($recArray['Fields'], $fieldArray);
                }
                else if ($field->type == "Number") {
                    $fieldArray = array('name' => $field->slug, 'type' => $field->type);
                    $fieldArray['number'] = 1337;
                    array_push($recArray['Fields'], $fieldArray);
                }
                else if ($field->type == "List") {
                    $fieldArray = array('name' => $field->slug, 'type' => $field->type);
                    $fieldArray['option'] = 'VALUE';
                    array_push($recArray['Fields'], $fieldArray);
                }
                else if ($field->type == "Multi-Select List") {
                    $fieldArray = array('name' => $field->slug, 'type' => $field->type);
                    $options = array('LIST VALUE 1','LIST VALUE 2','so on...');
                    $fieldArray['options'] = $options;
                    array_push($recArray['Fields'], $fieldArray);
                }
                else if ($field->type == "Generated List") {
                    $fieldArray = array('name' => $field->slug, 'type' => $field->type);
                    $options = array('LIST VALUE 1','LIST VALUE 2','so on...');
                    $fieldArray['options'] = $options;
                    array_push($recArray['Fields'], $fieldArray);
                }
                else if ($field->type == "Combo List") {
                    $fieldArray = array('name' => $field->slug, 'type' => $field->type);
                    $typeone = ComboListField::getComboFieldType($field, 'one');
                    $typetwo = ComboListField::getComboFieldType($field, 'two');
                    $nameone = ComboListField::getComboFieldName($field, 'one');
                    $nametwo = ComboListField::getComboFieldName($field, 'two');

                    $fieldArray['values'] = array();
                    $valArray = array();

                    if ($typeone == 'Text' | $typeone == 'Number' | $typeone == 'List')
                        $valArray[$nameone] = 'VALUE';
                    else if ($typeone == 'Multi-Select List' | $typeone == 'Generated List') {
                        $valArray[$nameone] = array('VALUE 1','VALUE 2','so on...');
                    }

                    if ($typetwo == 'Text' | $typetwo == 'Number' | $typetwo == 'List')
                        $valArray[$nametwo] = 'VALUE';
                    else if ($typetwo == 'Multi-Select List' | $typetwo == 'Generated List') {
                        $valArray[$nametwo] = array('VALUE 1','VALUE 2','so on...');
                    }

                    array_push($fieldArray['values'], $valArray);

                    array_push($recArray['Fields'], $fieldArray);
                }
                else if ($field->type == "Date") {
                    $fieldArray = array('name' => $field->slug, 'type' => $field->type);
                    $fieldArray['circa'] = '1 IF CIRCA. 0 IF NOT';
                    $fieldArray['month'] = 'NUMERIC VALUE OF MONTH (i.e. 08)';
                    $fieldArray['day'] = 19;
                    $fieldArray['year'] = 1990;
                    $fieldArray['era'] = 'CE OR BCE';
                    array_push($recArray['Fields'], $fieldArray);
                }
                else if ($field->type == "Schedule") {
                    $fieldArray = array('name' => $field->slug, 'type' => $field->type);
                    $fieldArray['events'] = array();

                    $eventArray = array();
                    $eventArray['title'] = 'EVENT TITLE 1';
                    $eventArray['start'] = '08/19/1990 12:00 AM';
                    $eventArray['end'] = '08/19/1990 12:30 AM';
                    $eventArray['allday'] = '0 FOR TIMED EVENT';
                    array_push($fieldArray['events'], $eventArray);

                    $eventArray = array();
                    $eventArray['title'] = 'EVENT TITLE 2';
                    $eventArray['start'] = '08/19/1990';
                    $eventArray['end'] = '08/20/1990';
                    $eventArray['allday'] = '1 FOR ALL DAY EVENT';
                    array_push($fieldArray['events'], $eventArray);

                    array_push($recArray['Fields'], $fieldArray);
                }
                else if ($field->type == "Documents") {
                    $fieldArray = array('name' => $field->slug, 'type' => $field->type);
                    $fieldArray['files'] = array();

                    $fileArray = array();
                    $fileArray['name'] = 'FILENAME 1';
                    array_push($fieldArray['files'], $fileArray);

                    $fileArray = array();
                    $fileArray['name'] = 'FILENAME2';
                    array_push($fieldArray['files'], $fileArray);

                    $fileArray = array();
                    $fileArray['name'] = 'so on...';
                    array_push($fieldArray['files'], $fileArray);

                    array_push($recArray['Fields'], $fieldArray);
                }
                else if ($field->type == "Gallery") {
                    $fieldArray = array('name' => $field->slug, 'type' => $field->type);
                    $fieldArray['files'] = array();

                    $fileArray = array();
                    $fileArray['name'] = 'FILENAME 1';
                    array_push($fieldArray['files'], $fileArray);

                    $fileArray = array();
                    $fileArray['name'] = 'FILENAME2';
                    array_push($fieldArray['files'], $fileArray);

                    $fileArray = array();
                    $fileArray['name'] = 'so on...';
                    array_push($fieldArray['files'], $fileArray);

                    array_push($recArray['Fields'], $fieldArray);
                }
                else if ($field->type == "Playlist") {
                    $fieldArray = array('name' => $field->slug, 'type' => $field->type);
                    $fieldArray['files'] = array();

                    $fileArray = array();
                    $fileArray['name'] = 'FILENAME 1';
                    array_push($fieldArray['files'], $fileArray);

                    $fileArray = array();
                    $fileArray['name'] = 'FILENAME2';
                    array_push($fieldArray['files'], $fileArray);

                    $fileArray = array();
                    $fileArray['name'] = 'so on...';
                    array_push($fieldArray['files'], $fileArray);

                    array_push($recArray['Fields'], $fieldArray);
                }
                else if ($field->type == "Video") {
                    $fieldArray = array('name' => $field->slug, 'type' => $field->type);
                    $fieldArray['files'] = array();

                    $fileArray = array();
                    $fileArray['name'] = 'FILENAME 1';
                    array_push($fieldArray['files'], $fileArray);

                    $fileArray = array();
                    $fileArray['name'] = 'FILENAME2';
                    array_push($fieldArray['files'], $fileArray);

                    $fileArray = array();
                    $fileArray['name'] = 'so on...';
                    array_push($fieldArray['files'], $fileArray);

                    array_push($recArray['Fields'], $fieldArray);
                }
                else if ($field->type == "Model") {
                    $fieldArray = array('name' => $field->slug, 'type' => $field->type);
                    $fieldArray['files'] = array();

                    $fileArray = array();
                    $fileArray['name'] = 'FILENAME 1';
                    array_push($fieldArray['files'], $fileArray);

                    array_push($recArray['Fields'], $fieldArray);
                }
                else if ($field->type == "Geolocator") {
                    $fieldArray = array('name' => $field->slug, 'type' => $field->type);
                    $fieldArray['locations'] = array();
                    $locArray = array();

                    $locArray['desc'] = 'LOCATION DESCRIPTION';
                    $locArray['lat'] = 'i.e. 13';
                    $locArray['lon'] = 'i.e. 14.5';
                    $locArray['zone'] = 'i.e. 38T';
                    $locArray['east'] = 'i.e. 59233.235234';
                    $locArray['north'] = 'i.e. 52833.265454';
                    $locArray['address'] = 'TEXTUAL REPRESENTATION OF LOCATION';
                    array_push($fieldArray['locations'], $locArray);

                    array_push($recArray['Fields'], $fieldArray);
                }
            }

            array_push($json['Records'],$recArray);

            $json = json_encode($json);

            header("Content-Disposition: attachment; filename=".$form->name.'_exampleData.json');
            header("Content-Type: application/octet-stream; ");

            echo $json;
        }
    }

    private function xmlTagClear($value){
        $value = htmlentities($value);
        $value = str_replace(' ','_',$value);

        return $value;
    }

    public function matchupFields($pid, $fid, Request $request){
        $form = FormController::getForm($fid);

        if(!\Auth::user()->admin && !\Auth::user()->isFormAdmin($form)){
            return 'Error: ';
        }

        //if zip file
        if(!is_null($request->file('files'))) {
            $zip = new \ZipArchive();
            $res = $zip->open($request->file('files'));
            if($res){
                $dir = env('BASE_PATH').'storage/app/tmpFiles/impU'.\Auth::user()->id;
                if(file_exists($dir)) {
                    //clear import directory
                    $files = new \RecursiveIteratorIterator(
                        new \RecursiveDirectoryIterator($dir),
                        \RecursiveIteratorIterator::LEAVES_ONLY
                    );
                    foreach ($files as $file) {
                        // Skip directories (they would be added automatically)
                        if (!$file->isDir()) {
                            unlink($file);
                        }
                    }
                }
                $zip->extractTo($dir.'/');
                $zip->close();
            }
        }

        $type = $request->type;

        $tagNames = array();
        $recordObjs = array();

        if($type=='xml') {
            $xml = simplexml_load_file($request->file('records'));

            foreach ($xml->children() as $record) {
                array_push($recordObjs, $record->asXML());
                foreach ($record->children() as $fields) {
                    array_push($tagNames, $fields->getName());
                }
            }

            $tagNames = array_unique($tagNames);
        }else if($type=='json'){
            $json = json_decode(file_get_contents($request->file('records')),true);

            foreach ($json['Records'] as $record) {
                array_push($recordObjs, $record);
                foreach ($record['Fields'] as $fields) {
                    array_push($tagNames, $fields['name']);
                }
            }

            $tagNames = array_unique($tagNames);
        }

        $fields = $form->fields()->get();

        $table = '<div id="matchup_table" style="overflow: auto">';

        $table .= '<div>';
        $table .= '<span style="float:left;width:50%;margin-bottom:10px"><b>'.trans('controller_input.slug').'</b></span>';
        $table .= '<span style="float:left;width:50%;margin-bottom:10px"><b>'.trans('controller_input.xml').'</b></span>';
        $table .= '</div>';

        foreach ($fields as $field){
            $table .= '<div>';
            $table .= '<span style="float:left;width:50%;margin-bottom:10px">';
            $table .= $field->name.' ('.$field->slug.')';
            $table .= '</span>';
            $table .= '<input type="hidden" class="slugs" value="'.$field->slug.'">';
            $table .= '<span style="float:left;width:50%;margin-bottom:10px">';
            $table .= '<select class="tags">';
            $table .= '<option></option>';
            foreach($tagNames as $name){
                if($field->slug==$name) {
                    $table .= '<option selected>' . $name . '</option>';
                }
                else
                    $table .= '<option>'.$name.'</option>';
            }
            $table .= '</select>';
            $table .= '</span>';
            $table .= '</div>';
        }

        $table .= '</div>';

        $table .= '<div class="form-group">';
           $table .= '<button type="button" class="form-control btn btn-primary" id="submit_records">'.trans('controller_input.records').'</button>';
        $table .= '</div>';

        $result = array();
        $result['records'] = $recordObjs;
        $result['matchup'] = $table;
        $result['type'] = $type;

        return $result;
    }

    public function importRecord($pid, $fid, Request $request){
        $matchup = $request->table;

        $record = $request->record;

        $recRequest = new Request();
        $recRequest['userId'] = \Auth::user()->id;

        if($request->type=='xml') {
            $record = simplexml_load_string($record);

            $originKid = $record->attributes()->kid;
            $originRid = explode('-', $originKid)[2];

            foreach ($record->children() as $key => $field) {
                $fieldSlug = $matchup[$key];
                $flid = Field::where('slug', '=', $fieldSlug)->get()->first()->flid;
                $type = $field->attributes()->type;

                if ($type == 'Text' | $type == 'Rich Text' | $type == 'Number' | $type == 'List')
                    $recRequest[$flid] = (string)$field;
                else if ($type == 'Multi-Select List') {
                    $recRequest[$flid] = (array)$field->value;
                } else if ($type == 'Generated List') {
                    $recRequest[$flid] = (array)$field->value;
                } else if ($type == 'Combo List') {
                    $values = array();
                    $nameone = str_replace(" ","_",ComboListField::getComboFieldName(FieldController::getField($flid), 'one'));
                    $nametwo = str_replace(" ","_",ComboListField::getComboFieldName(FieldController::getField($flid), 'two'));
                    foreach ($field->Value as $val) {
                        if ((string)$val->{$nameone} != '')
                            $fone = '[!f1!]' . (string)$val->{$nameone} . '[!f1!]';
                        else if (sizeof($val->{$nameone}->value) == 1)
                            $fone = '[!f1!]' . (string)$val->{$nameone}->value . '[!f1!]';
                        else
                            $fone = '[!f1!]' . FieldController::listArrayToString((array)$val->{$nameone}->value) . '[!f1!]';


                        if ((string)$val->{$nametwo} != '')
                            $ftwo = '[!f2!]' . (string)$val->{$nametwo} . '[!f2!]';
                        else if (sizeof($val->{$nametwo}->value) == 1)
                            $ftwo = '[!f2!]' . (string)$val->{$nametwo}->value . '[!f2!]';
                        else
                            $ftwo = '[!f2!]' . FieldController::listArrayToString((array)$val->{$nametwo}->value) . '[!f2!]';

                        array_push($values, $fone . $ftwo);
                    }
                    $recRequest[$flid] = '';
                    $recRequest[$flid . '_val'] = $values;
                } else if ($type == 'Date') {
                    $recRequest['circa_' . $flid] = (string)$field->Circa;
                    $recRequest['month_' . $flid] = (string)$field->Month;
                    $recRequest['day_' . $flid] = (string)$field->Day;
                    $recRequest['year_' . $flid] = (string)$field->Year;
                    $recRequest['era_' . $flid] = (string)$field->Era;
                    $recRequest[$flid] = '';
                } else if ($type == 'Schedule') {
                    $events = array();
                    foreach ($field->Event as $event) {
                        $string = $event->Title . ': ' . $event->Start . ' - ' . $event->End;
                        array_push($events, $string);
                    }
                    $recRequest[$flid] = $events;
                } else if ($type == 'Geolocator') {
                    $geo = array();
                    foreach ($field->Location as $loc) {
                        $string = '[Desc]' . $loc->Desc . '[Desc]';
                        $string .= '[LatLon]' . $loc->Lat . ',' . $loc->Lon . '[LatLon]';
                        $string .= '[UTM]' . $loc->Zone . ':' . $loc->East . ',' . $loc->North . '[UTM]';
                        $string .= '[Address]' . $loc->Address . '[Address]';
                        array_push($geo, $string);
                    }
                    $recRequest[$flid] = $geo;
                } else if ($type == 'Documents' | $type == 'Playlist' | $type == 'Video' | $type == '3D-Model') {
                    $files = array();
                    $currDir = env('BASE_PATH') . 'storage/app/tmpFiles/impU' . \Auth::user()->id . '/r' . $originRid . '/fl' . $flid;
                    $newDir = env('BASE_PATH') . 'storage/app/tmpFiles/f' . $flid . 'u' . \Auth::user()->id;
                    if (file_exists($newDir)) {
                        foreach (new \DirectoryIterator($newDir) as $file) {
                            if ($file->isFile()) {
                                unlink($newDir . '/' . $file->getFilename());
                            }
                        }
                    } else {
                        mkdir($newDir, 0775, true);
                    }
                    foreach ($field->File as $file) {
                        $name = (string)$file->Name;
                        //move file from imp temp to tmp files
                        copy($currDir . '/' . $name, $newDir . '/' . $name);
                        //add input for this file
                        array_push($files, $name);
                    }
                    $recRequest['file' . $flid] = $files;
                    $recRequest[$flid] = 'f' . $flid . 'u' . \Auth::user()->id;
                } else if ($type == 'Gallery') {
                    $files = array();
                    $currDir = env('BASE_PATH') . 'storage/app/tmpFiles/impU' . \Auth::user()->id . '/r' . $originRid . '/fl' . $flid;
                    $newDir = env('BASE_PATH') . 'storage/app/tmpFiles/f' . $flid . 'u' . \Auth::user()->id;
                    if (file_exists($newDir)) {
                        foreach (new \DirectoryIterator($newDir) as $file) {
                            if ($file->isFile()) {
                                unlink($newDir . '/' . $file->getFilename());
                            }
                        }
                        if (file_exists($newDir . '/thumbnail')) {
                            foreach (new \DirectoryIterator($newDir . '/thumbnail') as $file) {
                                if ($file->isFile()) {
                                    unlink($newDir . '/thumbnail/' . $file->getFilename());
                                }
                            }
                        }
                        if (file_exists($newDir . '/medium')) {
                            foreach (new \DirectoryIterator($newDir . '/medium') as $file) {
                                if ($file->isFile()) {
                                    unlink($newDir . '/medium/' . $file->getFilename());
                                }
                            }
                        }
                    } else {
                        mkdir($newDir, 0775, true);
                        mkdir($newDir . '/thumbnail', 0775, true);
                        mkdir($newDir . '/medium', 0775, true);
                    }
                    foreach ($field->File as $file) {
                        $name = (string)$file->Name;
                        //move file from imp temp to tmp files
                        copy($currDir . '/' . $name, $newDir . '/' . $name);
                        copy($currDir . '/thumbnail/' . $name, $newDir . '/thumbnail/' . $name);
                        copy($currDir . '/medium/' . $name, $newDir . '/medium/' . $name);
                        if (file_exists($currDir . '/thumbnail'))
                            copy($currDir . '/thumbnail/' . $name, $newDir . '/thumbnail/' . $name);
                        else{
                            $smallParts = explode('x',FieldController::getFieldOption($field,'ThumbSmall'));
                            $tImage = new \Imagick($newDir . '/' . $name);
                            $tImage->thumbnailImage($smallParts[0],$smallParts[1],true);
                            $tImage->writeImage($newDir . '/thumbnail/' . $name);
                        }
                        if (file_exists($currDir . '/medium'))
                            copy($currDir . '/medium/' . $name, $newDir . '/medium/' . $name);
                        else{
                            $largeParts = explode('x',FieldController::getFieldOption($field,'ThumbLarge'));
                            $mImage = new \Imagick($newDir . '/' . $name);
                            $mImage->thumbnailImage($largeParts[0],$largeParts[1],true);
                            $mImage->writeImage($newDir . '/medium/' . $name);
                        }
                        //add input for this file
                        array_push($files, $name);
                    }
                    $recRequest['file' . $flid] = $files;
                    $recRequest[$flid] = 'f' . $flid . 'u' . \Auth::user()->id;
                }
            }
        }else if($request->type=='json'){
            $originKid = $record['kid'];
            $originRid = explode('-', $originKid)[2];

            foreach ($record['Fields'] as $field) {
                $fieldSlug = $matchup[$field['name']];
                $flid = Field::where('slug', '=', $fieldSlug)->get()->first()->flid;
                $type = $field['type'];

                if ($type == 'Text'){
                    $recRequest[$flid] = $field['text'];
                } else if ($type == 'Rich Text'){
                    $recRequest[$flid] = $field['richtext'];
                } else if ($type == 'Number'){
                    $recRequest[$flid] = $field['number'];
                } else if ($type == 'List') {
                    $recRequest[$flid] = $field['option'];
                } else if ($type == 'Multi-Select List') {
                    $recRequest[$flid] = $field['options'];
                } else if ($type == 'Generated List') {
                    $recRequest[$flid] = $field['options'];
                } else if ($type == 'Combo List') {
                    $values = array();
                    $nameone = ComboListField::getComboFieldName(FieldController::getField($flid), 'one');
                    $nametwo = ComboListField::getComboFieldName(FieldController::getField($flid), 'two');
                    foreach ($field['values'] as $val) {
                        if (!is_array($val[$nameone]))
                            $fone = '[!f1!]' . $val[$nameone] . '[!f1!]';
                        else
                            $fone = '[!f1!]' . FieldController::listArrayToString($val[$nameone]) . '[!f1!]';


                        if (!is_array($val[$nametwo]))
                            $ftwo = '[!f2!]' . $val[$nametwo] . '[!f2!]';
                        else
                            $ftwo = '[!f2!]' . FieldController::listArrayToString($val[$nametwo]) . '[!f2!]';

                        array_push($values, $fone . $ftwo);
                    }
                    $recRequest[$flid] = '';
                    $recRequest[$flid . '_val'] = $values;
                } else if ($type == 'Date') {
                    $recRequest['circa_' . $flid] = $field['circa'];
                    $recRequest['month_' . $flid] = $field['month'];
                    $recRequest['day_' . $flid] = $field['day'];
                    $recRequest['year_' . $flid] = $field['year'];
                    $recRequest['era_' . $flid] = $field['era'];
                    $recRequest[$flid] = '';
                } else if ($type == 'Schedule') {
                    $events = array();
                    foreach ($field['events'] as $event) {
                        $string = $event['title'] . ': ' . $event['start'] . ' - ' . $event['end'];
                        array_push($events, $string);
                    }
                    $recRequest[$flid] = $events;
                } else if ($type == 'Geolocator') {
                    $geo = array();
                    foreach ($field['locations'] as $loc) {
                        $string = '[Desc]' . $loc['desc'] . '[Desc]';
                        $string .= '[LatLon]' . $loc['lat'] . ',' . $loc['lon'] . '[LatLon]';
                        $string .= '[UTM]' . $loc['zone'] . ':' . $loc['east'] . ',' . $loc['north'] . '[UTM]';
                        $string .= '[Address]' . $loc['address'] . '[Address]';
                        array_push($geo, $string);
                    }
                    $recRequest[$flid] = $geo;
                } else if ($type == 'Documents' | $type == 'Playlist' | $type == 'Video' | $type == '3D-Model') {
                    $files = array();
                    $currDir = env('BASE_PATH') . 'storage/app/tmpFiles/impU' . \Auth::user()->id . '/r' . $originRid . '/fl' . $flid;
                    $newDir = env('BASE_PATH') . 'storage/app/tmpFiles/f' . $flid . 'u' . \Auth::user()->id;
                    if (file_exists($newDir)) {
                        foreach (new \DirectoryIterator($newDir) as $file) {
                            if ($file->isFile()) {
                                unlink($newDir . '/' . $file->getFilename());
                            }
                        }
                    } else {
                        mkdir($newDir, 0775, true);
                    }
                    foreach ($field['files'] as $file) {
                        $name = $file['name'];
                        //move file from imp temp to tmp files
                        copy($currDir . '/' . $name, $newDir . '/' . $name);
                        //add input for this file
                        array_push($files, $name);
                    }
                    $recRequest['file' . $flid] = $files;
                    $recRequest[$flid] = 'f' . $flid . 'u' . \Auth::user()->id;
                } else if ($type == 'Gallery') {
                    $files = array();
                    $currDir = env('BASE_PATH') . 'storage/app/tmpFiles/impU' . \Auth::user()->id . '/r' . $originRid . '/fl' . $flid;
                    $newDir = env('BASE_PATH') . 'storage/app/tmpFiles/f' . $flid . 'u' . \Auth::user()->id;
                    if (file_exists($newDir)) {
                        foreach (new \DirectoryIterator($newDir) as $file) {
                            if ($file->isFile()) {
                                unlink($newDir . '/' . $file->getFilename());
                            }
                        }
                        if (file_exists($newDir . '/thumbnail')) {
                            foreach (new \DirectoryIterator($newDir . '/thumbnail') as $file) {
                                if ($file->isFile()) {
                                    unlink($newDir . '/thumbnail/' . $file->getFilename());
                                }
                            }
                        }
                        if (file_exists($newDir . '/medium')) {
                            foreach (new \DirectoryIterator($newDir . '/medium') as $file) {
                                if ($file->isFile()) {
                                    unlink($newDir . '/medium/' . $file->getFilename());
                                }
                            }
                        }
                    } else {
                        mkdir($newDir, 0775, true);
                        mkdir($newDir . '/thumbnail', 0775, true);
                        mkdir($newDir . '/medium', 0775, true);
                    }
                    foreach ($field['files'] as $file) {
                        $name = $file['name'];
                        //move file from imp temp to tmp files
                        copy($currDir . '/' . $name, $newDir . '/' . $name);
                        copy($currDir . '/thumbnail/' . $name, $newDir . '/thumbnail/' . $name);
                        copy($currDir . '/medium/' . $name, $newDir . '/medium/' . $name);
                        if (file_exists($currDir . '/thumbnail'))
                            copy($currDir . '/thumbnail/' . $name, $newDir . '/thumbnail/' . $name);
                        else{
                            $smallParts = explode('x',FieldController::getFieldOption($field,'ThumbSmall'));
                            $tImage = new \Imagick($newDir . '/' . $name);
                            $tImage->thumbnailImage($smallParts[0],$smallParts[1],true);
                            $tImage->writeImage($newDir . '/thumbnail/' . $name);
                        }
                        if (file_exists($currDir . '/medium'))
                            copy($currDir . '/medium/' . $name, $newDir . '/medium/' . $name);
                        else{
                            $largeParts = explode('x',FieldController::getFieldOption($field,'ThumbLarge'));
                            $mImage = new \Imagick($newDir . '/' . $name);
                            $mImage->thumbnailImage($largeParts[0],$largeParts[1],true);
                            $mImage->writeImage($newDir . '/medium/' . $name);
                        }
                        //add input for this file
                        array_push($files, $name);
                    }
                    $recRequest['file' . $flid] = $files;
                    $recRequest[$flid] = 'f' . $flid . 'u' . \Auth::user()->id;
                }
            }
        }

        $recCon = new RecordController();
        $recCon->store($pid,$fid,$recRequest);

        return '';
    }

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function importForm($pid, Request $request){
        $project = ProjectController::getProject($pid);

        if(!\Auth::user()->admin && !\Auth::user()->isProjectAdmin($project)){
            return redirect('projects/'.$pid);
        }

        $file = $request->file('form');

        $fileArray = json_decode(file_get_contents($file));

        //dd($fileArray);

        $form = new Form();

        $form->pid = $project->pid;
        $form->name = $fileArray->name;
        if (Form::where('slug', '=', $fileArray->slug)->exists()) {
            $unique = false;
            $i=1;
            while(!$unique){
                if(Form::where('slug', '=', $fileArray->slug.$i)->exists()){
                    $i++;
                }else{
                    $form->slug = $fileArray->slug.$i;
                    $unique = true;
                }
            }
        }else{
            $form->slug = $fileArray->slug;
        }
        $form->description = $fileArray->desc;
        $form->layout = $fileArray->layout;
        $form->preset = $fileArray->preset;
        $form->public_metadata = $fileArray->metadata;

        $form->save();

        //make admin group
        $admin = $this->makeFormAdminGroup($form);
        $this->makeFormDefaultGroup($form);
        $form->adminGID = $admin->id;
        $form->save();

        //record presets
        $recPresets = $fileArray->recPresets;

        foreach($recPresets as $pre) {
            $rec = new RecordPreset();

            $rec->fid = $form->fid;
            $rec->name = $pre->name;
            $rec->preset = $pre->preset;

            $rec->save();
        }

        $fields = $fileArray->fields;

        foreach($fields as $fieldArray){
            $field = new Field();

            $field->pid = $project->pid;
            $field->fid = $form->fid;
            $field->type = $fieldArray->type;
            $field->name = $fieldArray->name;
            if (Field::where('slug', '=', $fieldArray->slug)->exists()) {
                $unique = false;
                $i=1;
                while(!$unique){
                    if(Field::where('slug', '=', $fieldArray->slug.$i)->exists()){
                        $i++;
                    }else{
                        $field->slug = $fieldArray->slug.$i;
                        $unique = true;
                    }
                }
            }else{
                $field->slug = $fieldArray->slug;
            }
            $field->desc = $fieldArray->desc;
            $field->required = $fieldArray->required;
            $field->default = $fieldArray->default;
            $field->options = $fieldArray->options;

            $field->save();

            //fix layout
            $form->layout = str_replace('<ID>'.$fieldArray->slug.'</ID>','<ID>'.$field->flid.'</ID>',$form->layout);
            $form->save();

            //metadata
            if($fieldArray->metadata!=""){
                $meta = new Metadata();
                $meta->flid = $field->flid;
                $meta->pid = $project->pid;
                $meta->fid = $form->fid;
                $meta->name = $fieldArray->metadata;
                $meta->save();
            }
        }

        flash()->overlay(trans('controller_form.create'),trans('controller_form.goodjob'));

        return redirect('projects/'.$form->pid);
    }

    public function importFormK2($pid, Request $request){
        $project = ProjectController::getProject($pid);

        if(!\Auth::user()->admin && !\Auth::user()->isProjectAdmin($project)){
            return redirect('projects/'.$pid);
        }

        $file = $request->file('form');
        $scheme = simplexml_load_file($file);
        $nodes = array();
        $fieldNameArrayForRecordInsert = array();

        //init form
        $form = new Form();

        $form->pid = $pid;
        $form->preset = 0;
        $form->public_metadata = 0;
        $form->layout = '<LAYOUT></LAYOUT>';
        $form->save();

        $admin = $this->makeFormAdminGroup($form);
        $this->makeFormDefaultGroup($form);
        $form->adminGID = $admin->id;
        $form->save();

        //do stuff
        foreach ($scheme->children() as $category => $value) {
            if($category=='SchemeDesc'){
                $name = $value->Name->__toString();
                $desc = $value->Description->__toString();

                $form->name = $name;
                $slug = str_replace(' ','_',$name);
                $z=1;
                while(Form::slugExists($slug)){
                    $slug .= $z;
                    $z++;
                }
                $form->slug = $slug;
                $form->description = $desc;
                $form->save();
            }else if($category=='Collections'){
                foreach($value->children() as $collection){
                    $coll = array();
                    $coll['id'] = (int)$collection->id;
                    $coll['name'] = $collection->Name->__toString();
                    $coll['fields'] = array();
                    array_push($nodes,$coll);
                }
            }else if($category=='Controls'){
                foreach($value->children() as $name => $control) {
                    if($name != 'systimestamp' && $name != 'recordowner') {
                        $type = $control->Type->__toString();
                        $collid = (int)$control->CollId;
                        $desc = $control->Description->__toString();
                        $req = (int)$control->Required;
                        $search = (int)$control->Searchable;
                        $showresults = (int)$control->showInResults;
                        $options = $control->options->__toString();
                        $optXML = simplexml_load_string($options);
                        $newOpts = '';
                        $newDef = '';
                        $newType = '';

                        switch($type){
                            case 'TextControl':
                                $def = $optXML->defaultValue->__toString();
                                $textType = $optXML->textEditor->__toString();
                                if($textType=='plain'){
                                    $regex = $optXML->regex->__toString();
                                    $rows = (int)$optXML->rows;
                                    $multiline = 0;
                                    if($rows>1)
                                        $multiline = 1;

                                    $newOpts = "[!Regex!]".$regex."[!Regex!][!MultiLine!]".$multiline."[!MultiLine!]";
                                    $newDef = $def;
                                    $newType = "Text";
                                }else if($textType=='rich'){
                                    $newOpts = "";
                                    $newDef = $def;
                                    $newType = "Rich Text";
                                }
                                break;
                            case 'MultiTextControl':
                                $def = (array)$optXML->defaultValue->value;
                                $defOpts = '';
                                if(isset($def[0])) {
                                    $defOpts = $def[0];
                                    for ($i = 1; $i < sizeof($def); $i++) {
                                        $defOpts .= '[!]' . $def[$i];
                                    }
                                }
                                $regex = $optXML->regex->__toString();

                                $newOpts = "[!Regex!]".$regex."[!Regex!][!Options!]".$defOpts."[!Options!]";
                                $newDef = $defOpts;
                                $newType = "Generated List";
                                break;
                            case 'DateControl':
                                $startY = (int)$optXML->startYear;
                                $endY = (int)$optXML->endYear;
                                $era = $optXML->era->__toString();
                                $format = $optXML->displayFormat->__toString();
                                $defYear = (int)$optXML->defaultValue->year;
                                $defMon = (int)$optXML->defaultValue->month;
                                $defDay = (int)$optXML->defaultValue->day;
                                $prefix = $optXML->prefixes->__toString();
                                $circa = 'No';
                                $for = 'MMDDYYYY';
                                if($prefix=="circa"){$circa="Yes";}
                                if($format=="MDY"){$for="MMDDYYYY";}
                                else if($format=="DMY"){$for="DDMMYYYY";}
                                else if($format=="YMD"){$for="YYYYMMDD";}

                                $newOpts = "[!Circa!]".$circa."[!Circa!][!Start!]".$startY."[!Start!][!End!]".$endY."[!End!][!Format!]".$for."[!Format!][!Era!]".$era."[!Era!]";
                                $newDef = "[M]".$defMon."[M][D]".$defDay."[D][Y]".$defYear."[Y]";
                                $newType = "Date";
                                break;
                            case 'MultiDateControl':
                                $startY = (int)$optXML->startYear;
                                $endY = (int)$optXML->endYear;
                                $def = (array)$optXML->defaultValue;
                                if(isset($def["date"]))
                                    $def = $def["date"];
                                else{
                                    $def=array();
                                }
                                $defOpts = '';
                                if(isset($def[0])) {
                                    $defOpts = "Event 1: " . $def[0]->month . "/" . $def[0]->day . "/" . $def[0]->year . " - " . $def[0]->month . "/" . $def[0]->day . "/" . $def[0]->year;
                                    for ($i = 1; $i < sizeof($def); $i++) {
                                        $defOpts .= '[!]' . "Event " . ($i + 1) . ": " . $def[$i]->month . "/" . $def[$i]->day . "/" . $def[$i]->year . " - " . $def[$i]->month . "/" . $def[$i]->day . "/" . $def[$i]->year;
                                    }
                                }

                                $newOpts = "[!Start!]".$startY."[!Start!][!End!]".$endY."[!End!][!Calendar!]No[!Calendar!]";
                                $newDef = $defOpts;
                                $newType = "Schedule";
                                break;
                            case 'FileControl':
                                $maxSize = (int)$optXML->maxSize;
                                $allowed = (array)$optXML->allowedMIME->mime;
                                $allOpts = '';
                                if(isset($allowed[0])) {
                                    $allOpts = $allowed[0];
                                    for ($i = 1; $i < sizeof($allowed); $i++) {
                                        $allOpts .= '[!]' . $allowed[$i];
                                    }
                                }

                                $newOpts = "[!FieldSize!]".$maxSize."[!FieldSize!][!MaxFiles!]0[!MaxFiles!][!FileTypes!]".$allOpts."[!FileTypes!]";
                                $newType = "Documents";
                                break;
                            case 'ImageControl':
                                $maxSize = (int)$optXML->maxSize;
                                $allowed = (array)$optXML->allowedMIME->mime;
                                $allOpts = '';
                                if(isset($allowed[0])) {
                                    $allOpts = $allowed[0];
                                    for ($i = 1; $i < sizeof($allowed); $i++) {
                                        if ($allowed[$i] != "image/pjpeg" && $allowed[$i] != "image/x-png")
                                            $allOpts .= '[!]' . $allowed[$i];
                                    }
                                }
                                $thumbW = (int)$optXML->thumbWidth;
                                $thumbH = (int)$optXML->thumbHeight;

                                $newOpts = "[!FieldSize!]".$maxSize."[!FieldSize!][!ThumbSmall!]".$thumbW."x".$thumbH."[!ThumbSmall!][!ThumbLarge!]".($thumbW*2)."x".($thumbH*2)."[!ThumbLarge!][!MaxFiles!]0[!MaxFiles!][!FileTypes!]".$allOpts."[!FileTypes!]";
                                $newType = "Gallery";
                                break;
                            case 'ListControl':
                                $opts = (array)$optXML->option;
                                $allOpts = '';
                                if(isset($opts[0])) {
                                    $allOpts = $opts[0];
                                    for ($i = 1; $i < sizeof($opts); $i++) {
                                        $allOpts .= '[!]' . $opts[$i];
                                    }
                                }
                                $def = $optXML->defaultValue->__toString();

                                $newOpts = "[!Options!]".$allOpts."[!Options!]";
                                $newDef = $def;
                                $newType = "List";
                                break;
                            case 'MultiListControl':
                                $opts = (array)$optXML->option;
                                $allOpts = '';
                                if(isset($opts[0])) {
                                    $allOpts = $opts[0];
                                    for ($i = 1; $i < sizeof($opts); $i++) {
                                        $allOpts .= '[!]' . $opts[$i];
                                    }
                                }
                                $def = (array)$optXML->defaultValue->option;
                                $defOpts = '';
                                if(isset($def[0])) {
                                    $defOpts = $def[0];
                                    for ($i = 1; $i < sizeof($def); $i++) {
                                        $defOpts .= '[!]' . $def[$i];
                                    }
                                }

                                $newOpts = "[!Options!]".$allOpts."[!Options!]";
                                $newDef = $defOpts;
                                $newType = "Multi-Select List";
                                break;
                            case 'AssociatorControl':
                                $newOpts = "[!SearchForms!][!SearchForms!]";
                                $newType = "Associator";
                                break;
                        }

                        //save it
                        $field = new Field();
                        $field->pid = $form->pid;
                        $field->fid = $form->fid;
                        $field->type = $newType;
                        $field->name = $name;
                        $slug = str_replace(' ','_',$name);
                        $z=1;
                        while(Field::slugExists($slug)){
                            $slug .= $z;
                            $z++;
                        }
                        $field->slug = $slug;
                        $fieldNameArrayForRecordInsert[$name] = $slug;
                        $field->desc = $desc;
                        $field->required = $req;
                        $field->searchable = $search;
                        $field->extsearch = $search;
                        $field->viewable = $showresults;
                        $field->viewresults = $showresults;
                        $field->extview = $showresults;
                        $field->default = $newDef;
                        $field->options = $newOpts;
                        $field->save();

                        //place in appropriate node
                        foreach($nodes as $key => $node){
                            if($collid == $node['id']) {
                                $collFields = $node['fields'];
                                array_push($collFields, $field->flid);
                                $nodes[$key]['fields'] = $collFields;
                            }
                        }
                    }
                }
            }
        }

        //update form layout
        $newLay = '<LAYOUT>';
        foreach($nodes as $node){
            $newLay .= "<NODE title='".$node['name']."'>";
            foreach($node['fields'] as $fid){
                $newLay .= '<ID>'.$fid.'</ID>';
            }
            $newLay .= '</NODE>';
        }
        $newLay .= '</LAYOUT>';
        $form->layout = $newLay;
        $form->save();

        //NOW WE LOOK FOR RECORDS
        if(!is_null($request->file('records'))) {
            $file = $request->file('records');
            $records = simplexml_load_file($file);
            $zipDir = env('BASE_PATH').'storage/app/tmpFiles/f'.$form->fid.'u'.\Auth::user()->id.'/';

            if(!is_null($request->file('files'))){
                $fileZIP = $request->file('files');

                $zip = new \ZipArchive();
                if($zip->open($fileZIP) === TRUE){
                    if(mkdir($zipDir)) {
                        $zip->extractTo($zipDir);
                        $zip->close();
                    }
                }
            }

            foreach($records->Record as $record){
                $recModel = new Record();
                $recModel->pid = $form->pid;
                $recModel->fid = $form->fid;
                $recModel->owner = \Auth::user()->id;
                $recModel->save();

                $recModel->kid = $recModel->pid."-".$recModel->fid."-".$recModel->rid;
                $recModel->save();

                $usedMultiples = array();

                foreach($record->children() as $name => $value){
                    //for multi style controls, move on if name already user
                    if(in_array($name,$usedMultiples)){continue;}
                    //ignore standard control types and process
                    if($name != 'systimestamp' && $name != 'recordowner') {
                        $slug = $fieldNameArrayForRecordInsert[$name];
                        $field = Field::where('slug','=',$slug)->get()->first();

                        switch($field->type) {
                            case 'Text':
                                $value = (string)$value;

                                if($value!="") {
                                    $text = new TextField();
                                    $text->rid = $recModel->rid;
                                    $text->fid = $recModel->fid;
                                    $text->flid = $field->flid;
                                    $text->text = $value;
                                    $text->save();
                                }
                                break;
                            case 'Rich Text':
                                $value = (string)$value;

                                if($value!="") {
                                    $rich = new RichTextField();
                                    $rich->rid = $recModel->rid;
                                    $rich->fid = $recModel->fid;
                                    $rich->flid = $field->flid;
                                    $rich->rawtext = $value;
                                    $rich->save();
                                }
                                break;
                            case 'Generated List':
                                array_push($usedMultiples,$name);
                                $opts = (array)$record->$name;
                                if(isset($opts[0])){
                                    $optStr = $opts[0];
                                    for ($i = 1; $i < sizeof($opts); $i++) {
                                        $optStr .= '[!]' . $opts[$i];
                                    }

                                    $gen = new GeneratedListField();
                                    $gen->rid = $recModel->rid;
                                    $gen->fid = $recModel->fid;
                                    $gen->flid = $field->flid;
                                    $gen->options = $optStr;
                                    $gen->save();
                                }
                                break;
                            case 'Date':
                                $circa=0;
                                if(isset($value->attributes()["prefix"])){
                                    if($value->attributes()["prefix"] == "circa"){
                                        $circa=1;
                                    }
                                }
                                $dateStr = (string)$value;
                                if($dateStr!="") {
                                    $dateArray = explode(' ',$dateStr);
                                    if(FieldController::getFieldOption($field,'Era')=='Yes'){
                                        $era = $dateArray[1];
                                    }else{
                                        $era = 'CE';
                                    }
                                    $dateParts = explode("/",$dateArray[0]);

                                    $date = new DateField();
                                    $date->rid = $recModel->rid;
                                    $date->fid = $recModel->fid;
                                    $date->flid = $field->flid;
                                    $date->circa = $circa;
                                    $date->month = $dateParts[0];
                                    $date->day = $dateParts[1];
                                    $date->year = $dateParts[2];
                                    $date->era = $era;
                                    $date->save();
                                }
                                break;
                            case 'Schedule':
                                array_push($usedMultiples,$name);
                                $opts = (array)$record->$name;
                                if(isset($opts[0])){
                                    //CREATE THE VALUE
                                    $z=1;
                                    $dateStr = explode(' ',$opts[0])[0];
                                    $eventStr = 'Event '.$z.': '.$dateStr.' - '.$dateStr;
                                    $z++;
                                    for ($i = 1; $i < sizeof($opts); $i++) {
                                        $dateStr = explode(' ',$opts[$i])[0];
                                        $eventStr .= '[!]Event '.$z.': '.$dateStr.' - '.$dateStr;
                                        $z++;
                                    }

                                    $sched = new ScheduleField();
                                    $sched->rid = $recModel->rid;
                                    $sched->fid = $recModel->fid;
                                    $sched->flid = $field->flid;
                                    $sched->save();

                                    $sched->addEvents(explode("[!]", $eventStr));
                                }
                                break;
                            case 'Documents':
                                $realname='';
                                if(isset($value->attributes()["originalName"])){
                                    $realname = $value->attributes()["originalName"];
                                }
                                $localname = (string)$value;

                                if($localname!='') {
                                    $docs = new DocumentsField();
                                    $docs->rid = $recModel->rid;
                                    $docs->fid = $recModel->fid;
                                    $docs->flid = $field->flid;

                                    //Make folder
                                    $newPath = env('BASE_PATH') . 'storage/app/files/p' . $form->pid . '/f' . $form->fid . '/r' . $recModel->rid . '/fl' . $field->flid.'/';
                                    mkdir($newPath, 0775, true);

                                    //Move file
                                    rename($zipDir.$localname,$newPath.$realname);

                                    //Get file info
                                    $mimes = DocumentsField::getMimeTypes();
                                    $ext = pathinfo($newPath.$realname,PATHINFO_EXTENSION);
                                    if (!array_key_exists($ext, $mimes))
                                        $type = 'application/octet-stream';
                                    else
                                        $type = $mimes[$ext];

                                    $name = '[Name]'.$realname.'[Name]';
                                    $size = '[Size]'.filesize($newPath.$realname).'[Size]';
                                    $typeS = '[Type]'.$type.'[Type]';
                                    //Build file string
                                    $info = $name.$size.$typeS;
                                    $docs->documents = $info;
                                    $docs->save();
                                }
                                break;
                            case 'Gallery':
                                $realname='';
                                if(isset($value->attributes()["originalName"])){
                                    $realname = $value->attributes()["originalName"];
                                }
                                $localname = (string)$value;

                                if($localname!='') {
                                    $gal = new GalleryField();
                                    $gal->rid = $recModel->rid;
                                    $gal->fid = $recModel->fid;
                                    $gal->flid = $field->flid;

                                    //Make folder
                                    $newPath = env('BASE_PATH') . 'storage/app/files/p' . $form->pid . '/f' . $form->fid . '/r' . $recModel->rid . '/fl' . $field->flid.'/';
                                    $newPathM = $newPath.'medium/';
                                    $newPathT = $newPath.'thumbnail/';
                                    mkdir($newPath, 0775, true);
                                    mkdir($newPathM, 0775, true);
                                    mkdir($newPathT, 0775, true);

                                    //Move files
                                    rename($zipDir.$localname,$newPath.$realname);

                                    //Create thumbs
                                    $smallParts = explode('x',FieldController::getFieldOption($field,'ThumbSmall'));
                                    $largeParts = explode('x',FieldController::getFieldOption($field,'ThumbLarge'));
                                    $tImage = new \Imagick($newPath.$realname);
                                    $mImage = new \Imagick($newPath.$realname);
                                    $tImage->thumbnailImage($smallParts[0],$smallParts[1],true);
                                    $mImage->thumbnailImage($largeParts[0],$largeParts[1],true);
                                    $tImage->writeImage($newPathT.$realname);
                                    $mImage->writeImage($newPathM.$realname);

                                    //Get file info
                                    $mimes = DocumentsField::getMimeTypes();
                                    $ext = pathinfo($newPath.$realname,PATHINFO_EXTENSION);
                                    if (!array_key_exists($ext, $mimes))
                                        $type = 'application/octet-stream';
                                    else
                                        $type = $mimes[$ext];

                                    $name = '[Name]'.$realname.'[Name]';
                                    $size = '[Size]'.filesize($newPath.$realname).'[Size]';
                                    $typeS = '[Type]'.$type.'[Type]';
                                    //Build file string
                                    $info = $name.$size.$typeS;
                                    $gal->images = $info;
                                    $gal->save();
                                }
                                break;
                            case 'List':
                                $value = (string)$value;

                                if($value!="") {
                                    $list = new ListField();
                                    $list->rid = $recModel->rid;
                                    $list->fid = $recModel->fid;
                                    $list->flid = $field->flid;
                                    $list->option = $value;
                                    $list->save();
                                }
                                break;
                            case 'Multi-Select List':
                                array_push($usedMultiples,$name);
                                $opts = (array)$record->$name;
                                if(isset($opts[0])){
                                    $optStr = $opts[0];
                                    for ($i = 1; $i < sizeof($opts); $i++) {
                                        $optStr .= '[!]' . $opts[$i];
                                    }

                                    $msl = new MultiSelectListField();
                                    $msl->rid = $recModel->rid;
                                    $msl->fid = $recModel->fid;
                                    $msl->flid = $field->flid;
                                    $msl->options = $optStr;
                                    $msl->save();
                                }
                                break;
                        }
                    }
                }
            }

            //clean tmp folder
            rmdir($zipDir);
        }

        flash()->overlay(trans('controller_form.create'),trans('controller_form.goodjob'));

        return redirect('projects/'.$form->pid);
    }

    private function importFormNoFile($pid, $fileArray){
        $project = ProjectController::getProject($pid);

        //dd($fileArray);

        $form = new Form();

        $form->pid = $project->pid;
        $form->name = $fileArray->name;
        if (Form::where('slug', '=', $fileArray->slug)->exists()) {
            $unique = false;
            $i=1;
            while(!$unique){
                if(Form::where('slug', '=', $fileArray->slug.$i)->exists()){
                    $i++;
                }else{
                    $form->slug = $fileArray->slug.$i;
                    $unique = true;
                }
            }
        }else{
            $form->slug = $fileArray->slug;
        }
        $form->description = $fileArray->desc;
        $form->layout = $fileArray->layout;
        $form->preset = $fileArray->preset;
        $form->public_metadata = $fileArray->metadata;

        $form->save();

        //make admin group
        $admin = $this->makeFormAdminGroup($form);
        $this->makeFormDefaultGroup($form);
        $form->adminGID = $admin->id;
        $form->save();

        //record presets
        $recPresets = $fileArray->recPresets;

        foreach($recPresets as $pre) {
            $rec = new RecordPreset();

            $rec->fid = $form->fid;
            $rec->name = $pre->name;
            $rec->preset = $pre->preset;

            $rec->save();
        }

        $fields = $fileArray->fields;

        foreach($fields as $fieldArray){
            $field = new Field();

            $field->pid = $project->pid;
            $field->fid = $form->fid;
            $field->type = $fieldArray->type;
            $field->name = $fieldArray->name;
            if (Field::where('slug', '=', $fieldArray->slug)->exists()) {
                $unique = false;
                $i=1;
                while(!$unique){
                    if(Field::where('slug', '=', $fieldArray->slug.$i)->exists()){
                        $i++;
                    }else{
                        $field->slug = $fieldArray->slug.$i;
                        $unique = true;
                    }
                }
            }else{
                $field->slug = $fieldArray->slug;
            }
            $field->desc = $fieldArray->desc;
            $field->required = $fieldArray->required;
            $field->default = $fieldArray->default;
            $field->options = $fieldArray->options;

            $field->save();

            //fix layout
            $form->layout = str_replace('<ID>'.$fieldArray->slug.'</ID>','<ID>'.$field->flid.'</ID>',$form->layout);
            $form->save();

            //metadata
            if($fieldArray->metadata!=""){
                $meta = new Metadata();
                $meta->flid = $field->flid;
                $meta->pid = $project->pid;
                $meta->fid = $form->fid;
                $meta->name = $fieldArray->metadata;
                $meta->save();
            }
        }
    }

    private function makeFormAdminGroup(Form $form)
    {
        $groupName = $form->name;
        $groupName .= ' Admin Group';

        $adminGroup = new FormGroup();
        $adminGroup->name = $groupName;
        $adminGroup->fid = $form->fid;
        $adminGroup->save();

        $formProject = $form->project()->first();
        $projectAdminGroup = $formProject->adminGroup()->first();

        $projectAdmins = $projectAdminGroup->users()->get();
        $idArray = [];

        //Add all current project admins to the form's admin group.
        foreach($projectAdmins as $projectAdmin)
            $idArray[] .= $projectAdmin->id;


        $idArray = array_unique(array_merge(array(\Auth::user()->id), $idArray));

        if (!empty($idArray))
            $adminGroup->users()->attach($idArray);

        $adminGroup->create = 1;
        $adminGroup->edit = 1;
        $adminGroup->delete = 1;
        $adminGroup->ingest = 1;
        $adminGroup->modify = 1;
        $adminGroup->destroy = 1;

        $adminGroup->save();

        return $adminGroup;
    }

    /**
     * Creates the form's admin Group.
     *
     * @param $project
     * @param $request
     * @return FormGroup
     */
    private function makeFormDefaultGroup(Form $form)
    {
        $groupName = $form->name;
        $groupName .= ' Default Group';

        $defaultGroup = new FormGroup();
        $defaultGroup->name = $groupName;
        $defaultGroup->fid = $form->fid;
        $defaultGroup->save();

        $defaultGroup->create = 0;
        $defaultGroup->edit = 0;
        $defaultGroup->delete = 0;
        $defaultGroup->ingest = 0;
        $defaultGroup->modify = 0;
        $defaultGroup->destroy = 0;

        $defaultGroup->save();
    }

    public function importProject(Request $request){
        if(!\Auth::user()->admin){
            return redirect('projects/');
        }

        $file = $request->file('project');

        $fileArray = json_decode(file_get_contents($file));

        //dd($fileArray);

        $proj = new Project();

        $proj->name = $fileArray->name;
        if (Project::where('slug', '=', $fileArray->slug)->exists()) {
            $unique = false;
            $i=1;
            while(!$unique){
                if(Project::where('slug', '=', $fileArray->slug.$i)->exists()){
                    $i++;
                }else{
                    $proj->slug = $fileArray->slug.$i;
                    $unique = true;
                }
            }
        }else{
            $proj->slug = $fileArray->slug;
        }
        $proj->description = $fileArray->description;
        $proj->active = 1;

        $proj->save();

        //make admin group
        $admin = $this->makeProjAdminGroup($proj);
        $this->makeProjAdminGroup($proj);
        $proj->adminGID = $admin->id;
        $proj->save();

        $optPresets = $fileArray->optPresets;

        foreach($optPresets as $opt) {
            $pre = new OptionPreset();

            $pre->pid = $proj->pid;
            $pre->type = $opt->type;
            $pre->name = $opt->name;
            $pre->preset = $opt->preset;
            $pre->shared = $opt->shared;

            $pre->save();
        }

        $forms = $fileArray->forms;

        foreach($forms as $form) {
            $this->importFormNoFile($proj->pid,$form);
        }

        flash()->overlay(trans('controller_project.create'),trans('controller_project.goodjob'));

        return redirect('projects');
    }

    private function makeProjAdminGroup($project)
    {
        $groupName = $project->name;
        $groupName .= ' Admin Group';

        $adminGroup = new ProjectGroup();
        $adminGroup->name = $groupName;
        $adminGroup->pid = $project->pid;
        $adminGroup->save();

        $adminGroup->users()->attach(array(\Auth::user()->id));

        $adminGroup->create = 1;
        $adminGroup->edit = 1;
        $adminGroup->delete = 1;

        $adminGroup->save();

        return $adminGroup;
    }

    /**
     * Creates the form's admin Group.
     *
     * @param $project
     * @param $request
     * @return FormGroup
     */
    private function makeProjectDefaultGroup($project)
    {
        $groupName = $project->name;
        $groupName .= ' Default Group';

        $defaultGroup = new ProjectGroup();
        $defaultGroup->name = $groupName;
        $defaultGroup->pid = $project->pid;
        $defaultGroup->save();

        $defaultGroup->create = 0;
        $defaultGroup->edit = 0;
        $defaultGroup->delete = 0;

        $defaultGroup->save();
    }

}
