<?php namespace App\Http\Controllers;

use App\ComboListField;
use App\FieldHelpers\AssociatorSearch;
use App\FieldHelpers\gPoint;
use App\FieldHelpers\UploadHandler;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Geocoder\HttpAdapter\CurlHttpAdapter;
use Geocoder\Provider\NominatimProvider;
use Illuminate\Http\Request;

class FieldAjaxController extends Controller {

    public function validateComboListOpt($pid, $fid, $flid, Request $request){
        $field = FieldController::getField($flid);

        $valone = $request->valone;
        $valtwo = $request->valtwo;
        $typeone = $request->typeone;
        $typetwo = $request->typetwo;

        if($valone=="" | $valtwo==""){
            return trans('controller_field.valueboth');
        }

        if($typeone=='Text'){
            $regex = ComboListField::getComboFieldOption($field,'Regex','one');
            if(($regex!=null | $regex!="") && !preg_match($regex,$valone)){
                return trans('controller_field.v1regex');
            }
        }else if($typeone=='Number'){
            $max = ComboListField::getComboFieldOption($field,'Max','one');
            $min = ComboListField::getComboFieldOption($field,'Min','one');
            $inc = ComboListField::getComboFieldOption($field,'Increment','one');

            if($valone<$min | $valone>$max){
                return trans('controller_field.v1num');
            }

            if(fmod(floatval($valone),floatval($inc))!=0){
                return trans('controller_field.v1numinc');
            }
        }else if($typeone=='List'){
            $opts = explode('[!]',ComboListField::getComboFieldOption($field,'Options','one'));

            if(!in_array($valone,$opts)){
                return trans('controller_field.v1list');
            }
        }else if($typeone=='Multi-Select List'){
            $opts = explode('[!]',ComboListField::getComboFieldOption($field,'Options','one'));

            if(sizeof(array_diff($valone,$opts))>0){
                return trans('controller_field.v1mslist');
            }
        }else if($typeone=='Generated List'){
            $regex = ComboListField::getComboFieldOption($field,'Regex','one');

            if($regex != null | $regex != "") {
                foreach ($valone as $val) {
                    if (!preg_match($regex, $val)) {
                        return trans('controller_field.v1genlist');
                    }
                }
            }
        }

        if($typetwo=='Text'){
            $regex = ComboListField::getComboFieldOption($field,'Regex','two');
            if(($regex!=null | $regex!="") && !preg_match($regex,$valtwo)){
                return trans('controller_field.v2regex');
            }

            $fieldtwoval = '[!f2!]'.$valtwo.'[!f2!]';
        }else if($typetwo=='Number'){
            $max = ComboListField::getComboFieldOption($field,'Max','two');
            $min = ComboListField::getComboFieldOption($field,'Min','two');
            $inc = ComboListField::getComboFieldOption($field,'Increment','two');

            if($valtwo<$min | $valtwo>$max){
                return trans('controller_field.v2num');
            }
            if(fmod(floatval($valtwo),floatval($inc))!=0){
                return trans('controller_field.v2numinc');
            }
        }else if($typetwo=='List'){
            $opts = explode('[!]',ComboListField::getComboFieldOption($field,'Options','two'));

            if(!in_array($valtwo,$opts)){
                return trans('controller_field.v2list');
            }
        }else if($typetwo=='Multi-Select List'){
            $opts = explode('[!]',ComboListField::getComboFieldOption($field,'Options','two'));

            if(sizeof(array_diff($valtwo,$opts))>0){
                return trans('controller_field.v2mslist');
            }
        }else if($typetwo=='Generated List'){
            $regex = ComboListField::getComboFieldOption($field,'Regex','two');

            if($regex != null | $regex != "") {
                foreach ($valtwo as $val) {
                    if (!preg_match($regex, $val)) {
                        return trans('controller_field.v2genlist');
                    }
                }
            }
        }
        return '';
    }

    public function geoConvert(Request $request){
        if($request->type == 'latlon'){
            $lat = $request->lat;
            $lon = $request->lon;

            //to utm
            $con = new gPoint();
            $con->gPoint();
            $con->setLongLat($lon,$lat);
            $con->convertLLtoTM();
            $utm = $con->utmZone.':'.$con->utmEasting.','.$con->utmNorthing;

            //to address
            $con = new \Geocoder\Geocoder();
            $con->registerProviders([
                new NominatimProvider(
                    new CurlHttpAdapter(), 'http://nominatim.openstreetmap.org/', 'en'
                )
            ]);
            try {
                $res = $con->geocode($lat.', '.$lon);
                $addr = $res->getStreetNumber().' '.$res->getStreetName().' '.$res->getCity().' '.$res->getRegion();
            } catch(\Exception $e){
                $addr = 'null';
            }

            $result = '[LatLon]'.$lat.','.$lon.'[LatLon][UTM]'.$utm.'[UTM][Address]'.$addr.'[Address]';

            return $result;
        }else if($request->type == 'utm'){
            $zone = $request->zone;
            $east = $request->east;
            $north = $request->north;

            //to latlon
            $con = new gPoint();
            $con->gPoint();
            $con->setUTM($east,$north,$zone);
            $con->convertTMtoLL();
            $lat = $con->lat;
            $lon = $con->long;

            //to address
            $con = new \Geocoder\Geocoder();
            $con->registerProviders([
                new NominatimProvider(
                    new CurlHttpAdapter(), 'http://nominatim.openstreetmap.org/', 'en'
                )
            ]);
            try {
                $res = $con->geocode($lat.', '.$lon);
                $addr = $res->getStreetNumber().' '.$res->getStreetName().' '.$res->getCity().' '.$res->getRegion();
            } catch(\Exception $e){
                $addr = 'null';
            }

            $result = '[LatLon]'.$lat.','.$lon.'[LatLon][UTM]'.$zone.':'.$east.','.$north.'[UTM][Address]'.$addr.'[Address]';

            return $result;
        }else if($request->type == 'geo') {
            $addr = $request->addr;

            //to latlon
            $con = new \Geocoder\Geocoder();
            $con->registerProviders([
                new NominatimProvider(
                    new CurlHttpAdapter(), 'http://nominatim.openstreetmap.org/', 'en'
                )
            ]);
            try {
                $res = $con->geocode($addr);
                $lat = $res->getLatitude();
                $lon = $res->getLongitude();
            } catch(\Exception $e){
                $lat = 'null';
                $lon = 'null';
            }

            //to utm
            if($lat != 'null' && $lon != 'null') {
                $con = new gPoint();
                $con->gPoint();
                $con->setLongLat($lon,$lat);
                $con->convertLLtoTM();

                $utm = $con->utmZone.':'.$con->utmEasting.','.$con->utmNorthing;
            }else{
                $utm = 'null:null.null';
            }

            $result = '[LatLon]'.$lat.','.$lon.'[LatLon][UTM]'.$utm.'[UTM][Address]'.$addr.'[Address]';

            return $result;
        }
    }

    public function saveTmpFile($flid, Request $request){
        $field = FieldController::getField($flid);
        $uid = \Auth::user()->id;
        $dir = env('BASE_PATH').'storage/app/tmpFiles/f'.$flid.'u'.$uid;

        $maxFileNum = FieldController::getFieldOption($field, 'MaxFiles');
        $fileNumRequest = sizeof($_FILES['file'.$flid]['name']);
        if (glob($dir.'/*.*') != false)
        {
            $fileNumDisk = count(glob($dir.'/*.*'));
        }
        else
        {
            $fileNumDisk = 0;
        }

        $maxFieldSize = FieldController::getFieldOption($field, 'FieldSize')*1024; //conversion of kb to bytes
        $fileSizeRequest = 0;
        foreach($_FILES['file'.$flid]['size'] as $size){
            $fileSizeRequest += $size;
        }
        $fileSizeDisk = 0;
        if(file_exists($dir)) {
            foreach (new \DirectoryIterator($dir) as $file) {
                if ($file->isFile()) {
                    $fileSizeDisk += $file->getSize();
                }
            }
        }

        if($field->type=='Gallery') {
            $smThumbs = explode('x', FieldController::getFieldOption($field, 'ThumbSmall'));
            $lgThumbs = explode('x', FieldController::getFieldOption($field, 'ThumbLarge'));
        }

        $validTypes = true;
        $fileTypes = explode('[!]',FieldController::getFieldOption($field, 'FileTypes'));
        $fileTypesRequest = $_FILES['file'.$flid]['type'];
        if((sizeof($fileTypes)!=1 | $fileTypes[0]!='') && $field->type != '3D-Model') {
            foreach ($fileTypesRequest as $type) {
                if (!in_array($type,$fileTypes)){
                    $validTypes = false;
                }
            }
        }else if($field->type=='Gallery'){
            foreach ($fileTypesRequest as $type) {
                if (!in_array($type,['image/jpeg','image/gif','image/png'])){
                    $validTypes = false;
                }
            }
        }else if($field->type=='Playlist'){
            foreach ($fileTypesRequest as $type) {
                if (!in_array($type,['audio/mp3','audio/wav','audio/ogg'])){
                    $validTypes = false;
                }
            }
        }else if($field->type=='Video'){
            foreach ($fileTypesRequest as $type) {
                if (!in_array($type,['video/mp4','video/ogg'])){
                    $validTypes = false;
                }
            }
        }else if($field->type=='3D-Model'){
            foreach ($_FILES['file'.$flid]['name'] as $file) {
                $filetype = explode('.',$file);
                $type = array_pop($filetype);
                if (!in_array($type,['obj','stl'])){
                    $validTypes = false;
                }
            }
        }

        $options = array();
        $options['flid'] = 'f'.$flid.'u'.$uid;
        if($field->type=='Gallery') {
            $options['image_versions']['thumbnail']['max_width'] = $smThumbs[0];
            $options['image_versions']['thumbnail']['max_height'] = $smThumbs[1];
            $options['image_versions']['medium']['max_width'] = $lgThumbs[0];
            $options['image_versions']['medium']['max_height'] = $lgThumbs[1];
        }
        if(!$validTypes){
            echo 'InvalidType';
        } else if($maxFileNum !=0 && $fileNumRequest+$fileNumDisk>$maxFileNum){
            echo 'TooManyFiles';
        } else if($maxFieldSize !=0 && $fileSizeRequest+$fileSizeDisk>$maxFieldSize){
            echo 'MaxSizeReached';
        } else {
            $upload_handler = new UploadHandler($options);
        }
    }

    public function delTmpFile($flid, $filename, Request $request){
        $uid = \Auth::user()->id;
        $options = array();
        $options['flid'] = $flid;
        $options['filename'] = $filename;
        $upload_handler = new UploadHandler($options);
    }

    public function getFileDownload($rid, $flid, $filename){
        $record = RecordController::getRecord($rid);
        $field = FieldController::getField($flid);

        // Check if file exists in app/storage/file folder
        $file_path = env('BASE_PATH').'storage/app/files/p'.$record->pid.'/f'.$record->fid.'/r'.$record->rid.'/fl'.$field->flid . '/' . $filename;
        if (file_exists($file_path))
        {
            // Send Download
            return response()->download($file_path, $filename, [
                'Content-Length: '. filesize($file_path)
            ]);
        }
        else
        {
            // Error
            exit(trans('controller_field.nofile'));
        }
    }

    public function getImgDisplay($rid, $flid, $filename, $type){
        $record = RecordController::getRecord($rid);
        $field = FieldController::getField($flid);
        if($type == 'thumbnail' | $type == 'medium'){
            $file_path = env('BASE_PATH').'storage/app/files/p'.$record->pid.'/f'.$record->fid.'/r'.$record->rid.'/fl'.$field->flid.'/'.$type.'/'. $filename;
        }else{
            $file_path = env('BASE_PATH').'storage/app/files/p'.$record->pid.'/f'.$record->fid.'/r'.$record->rid.'/fl'.$field->flid . '/' . $filename;

        }

        if (file_exists($file_path))
        {
            // Send Download
            return response()->download($file_path, $filename, [
                'Content-Length: '. filesize($file_path)
            ]);
        }
        else
        {
            // Error
            exit(trans('controller_field.nofile'));
        }
    }

    public function assocSearch($pid, $fid, $flid, Request $request){
        $field = FieldController::getField($flid);

        return AssociatorSearch::keywordSearch($request->keyword, $field);
    }

}
