<?php namespace App\KoraFields;

use App\FieldHelpers\UploadHandler;
use App\Form;
use App\Http\Controllers\FieldController;
use App\Http\Controllers\RecordController;
use Illuminate\Http\Request;
use ZipArchive;

abstract class FileTypeField extends BaseField {

    /*
    |--------------------------------------------------------------------------
    | File Typed Field
    |--------------------------------------------------------------------------
    |
    | This model represents the abstract class for the fields that have files
    |  associated with them
    |
    */

    /**
     * @var array - Maps file field constant names to file variable names
     */
    public static $FILE_DATA_TYPES = [ //TODO::CASTLE
        Form::_DOCUMENTS => "documents",
//        Field::_GALLERY => "images",
//        Field::_PLAYLIST => "audio",
//        Field::_VIDEO => "video",
//        Field::_3D_MODEL => "model",
    ];

    /**
     * @var array - Maps file field constant names to valid file memes
     */
    public static $FILE_MIME_TYPES = [ //TODO::CASTLE
//        Field::_GALLERY => ['image/jpeg','image/gif','image/png','image/bmp'],
//        Field::_PLAYLIST => ['audio/mp3','audio/wav','audio/ogg'],
//        Field::_VIDEO => ['video/mp4','video/ogg'],
//        Field::_3D_MODEL => ['obj','stl','application/octet-stream','image/jpeg','image/png'],
    ];

    /**
     * Saves a temporary version of an uploaded file.
     *
     * @param  int $flid - File field that record file will be loaded to
     * @param  Request $request
     */
    public static function saveTmpFile($fid, $flid) {
        $field = FieldController::getField($flid, $fid);
        $uid = \Auth::user()->id;
        $dir = storage_path('recordU'.$uid);

        $maxFileNum = $field['options']['MaxFiles'];
        $fileNumRequest = sizeof($_FILES['file'.$flid]['name']);
        if(glob($dir.'/*.*') != false)
            $fileNumDisk = count(glob($dir.'/*.*'));
        else
            $fileNumDisk = 0;

        $maxFieldSize = $field['options']['FieldSize']*1024; //conversion of kb to bytes
        $fileSizeRequest = 0;
        foreach($_FILES['file'.$flid]['size'] as $size) {
            $fileSizeRequest += $size;
        }

        $fileSizeDisk = 0;
        if(file_exists($dir)) {
            foreach(new \DirectoryIterator($dir) as $file) {
                if($file->isFile())
                    $fileSizeDisk += $file->getSize();
            }
        }

//        if($field->type==Field::_GALLERY) { //TODO::CASTLE
//            $smThumbs = explode('x', FieldController::getFieldOption($field, 'ThumbSmall'));
//            $lgThumbs = explode('x', FieldController::getFieldOption($field, 'ThumbLarge'));
//        }

        $validTypes = true;
        if($field['type'] == Form::_DOCUMENTS)
            $fileTypes = !empty($field['options']['FileTypes']) ? $field['options']['FileTypes'] : self::getMimeTypesClean();
        else
            $fileTypes = !empty($field['options']['FileTypes']) ? $field['options']['FileTypes'] : self::$FILE_MIME_TYPES[$field['type']];
        $fileTypesRequest = $_FILES['file'.$flid]['type'];

        if(!empty($fileTypes)) {
            foreach ($fileTypesRequest as $type) {
                //This statement guards against Safari's lack of file type recognition //TODO::CASTLE
//                if ($field->type == Field::_PLAYLIST && $type == "audio/mpeg")
//                    $type = "audio/mp3";

                if (!in_array($type, $fileTypes))
                    $validTypes = false;
            }
        }

        $options = array();
        $options['fid'] = $fid;
        $options['flid'] = $flid;
        $options['folder'] = 'recordU'.$uid;
//        if($field->type==Field::_GALLERY) { //TODO::CASTLE
//            $options['image_versions']['thumbnail']['max_width'] = $smThumbs[0];
//            $options['image_versions']['thumbnail']['max_height'] = $smThumbs[1];
//            $options['image_versions']['medium']['max_width'] = $lgThumbs[0];
//            $options['image_versions']['medium']['max_height'] = $lgThumbs[1];
//        }

        if(!$validTypes) {
            echo 'InvalidType';
        } else if($maxFileNum !=0 && $fileNumRequest+$fileNumDisk>$maxFileNum) {
            echo 'TooManyFiles';
        } else if($maxFieldSize !=0 && $fileSizeRequest+$fileSizeDisk>$maxFieldSize) {
            echo 'MaxSizeReached';
        } else {
            $upload_handler = new UploadHandler($options);
        }
    }

    /**
     * Removes a temporary file for a particular field.
     *
     * @param  int $flid - File field to clear temp files for
     * @param  string $name - Name of the file to delete
     * @param  Request $request
     */
    public static function delTmpFile($fid, $flid, $filename) {
        $uid = \Auth::user()->id;
        $options = array();
        $options['fid'] = $fid;
        $options['flid'] = $flid;
        $options['filename'] = $filename;
        $options['folder'] = 'recordU'.$uid;
        $options['deleteThat'] = true;
        $upload_handler = new UploadHandler($options);
    }

    /**
     * Downloads a file from a particular record field.
     *
     * @param  int $kid - Record Kora ID
     * @param  string $filename - Name of the file
     * @return string - html for the file download
     */
    public static function getFileDownload($kid, $filename) {
        $record = RecordController::getRecord($kid);

        // Check if file exists in app/storage/file folder
        $file_path = storage_path('app/files/'.$record->project_id.'/'.$record->form_id.'/'.$record->id.'/'.$filename);
        if(file_exists($file_path)) {
            // Send Download
            return response()->download($file_path, $filename, [
                'Content-Length: '. filesize($file_path)
            ]);
        } else {
            return response()->json(["status" => false, "message" => "file_doesnt_exist"], 500);
        }
    }

    /**
     * Downloads a zip file from a particular record field.
     *
     * @param  int $rid - Record ID
     * @param  int $flid - Field ID
     * @param  string $filename - Name of the file
     * @return string - html for the file download
     */
    public static function getZipDownload($rid, $flid, $filename) { //TODO::CASTLE
        $record = RecordController::getRecord($rid);
        $field = FieldController::getField($flid);

        // Check if directory app/storage/file folder exists
        $dir_path = storage_path('app/files/p'.$record->pid.'/f'.$record->fid.'/r'.$record->rid.'/fl'.$field->flid);
        if(file_exists($dir_path)) {
            $zip_name = $filename . '_export' . date("Y_m_d_His") . '.zip';
            $zip_dir = storage_path('app/' . ($filename != '' ? $filename : 'zip_exports'));
            $zip = new ZipArchive;

            if ($zip->open($zip_dir . '/' . $zip_name, ZipArchive::CREATE) === TRUE) {
                foreach (new \DirectoryIterator($dir_path) as $file) {
                    if ($file->isFile()) {
                        $content = file_get_contents($file->getRealPath());
                        $zip->addFromString($file->getFilename(), $content);
                    }
                }
                $zip->close();
            }

            // Set Header
            $headers = array(
                'Content-Type' => 'application/octet-stream',
            );
            $filetopath = $zip_dir . '/' . $zip_name;
            // Create Download Response
            if(file_exists($filetopath)){
                return response()->download($filetopath, $zip_name, $headers);
            } else {
                return response()->json(["status"=>false,"message"=>"zip_doesnt_exist"],500);
            }
        } else {
            return response()->json(["status"=>false,"message"=>"directory_doesnt_exist"],500);
        }
    }

    /**
     * Searches standard list of MIME file types.
     *
     * @param  array - The MIME types
     */
    public static function getMimeTypes() {
        $types=array();
        foreach(@explode("\n",@file_get_contents('http://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types'))as $x)
            if(isset($x[0])&&$x[0]!=='#'&&preg_match_all('#([^\s]+)#',$x,$out)&&isset($out[1])&&($c=count($out[1]))>1)
                for($i=1;$i<$c;$i++)
                    $types[$out[1][$i]]=$out[1][0];
        return $types;
    }

    /**
     * Searches standard list of MIME file types. Makes a clean list for html selects.
     *
     * @param  array - The MIME types
     */
    public static function getMimeTypesClean() {
        $types=array();
        foreach(@explode("\n",@file_get_contents('http://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types'))as $x)
            if(isset($x[0])&&$x[0]!=='#'&&preg_match_all('#([^\s]+)#',$x,$out)&&isset($out[1])&&($c=count($out[1]))>1)
                for($i=1;$i<$c;$i++)
                    $types[$out[1][0]]=$out[1][0];
        return $types;
    }
}