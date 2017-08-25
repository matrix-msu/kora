<?php namespace App;

use App\FieldHelpers\UploadHandler;
use App\Http\Controllers\FieldController;
use App\Http\Controllers\RecordController;
use Illuminate\Http\Request;

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
    public static $FILE_DATA_TYPES = [
        Field::_DOCUMENTS => "documents",
        Field::_GALLERY => "images",
        Field::_PLAYLIST => "audio",
        Field::_VIDEO => "video",
        Field::_3D_MODEL => "model",
    ];

    /**
     * @var array - Maps file field constant names to valid file memes
     */
    public static $FILE_MIME_TYPES = [
        Field::_GALLERY => ['image/jpeg','image/gif','image/png'],
        Field::_PLAYLIST => ['audio/mp3','audio/wav','audio/ogg'],
        Field::_VIDEO => ['video/mp4','video/ogg'],
        Field::_3D_MODEL => ['obj','stl'],
    ];

    /**
     * Parses the string representing all the files that a field has and returns an array of the file names.
     *
     * @return array - The names of the files associated with the field
     */
    public function getFileNames() {
        $type = Field::where("flid", '=', $this->flid)->first()->type;

        $infoString = $this->{self::$FILE_DATA_TYPES[$type]};

        if(is_null($infoString))
            return []; // Something went wrong!

        $fileNames = [];
        foreach(explode('[!]', $infoString) as $file) {
            $fileNames[] = explode('[Name]', $file)[1];
        }

        return $fileNames;
    }

    /**
     * Saves a temporary version of an uploaded file.
     *
     * @param  int $flid - File field that record file will be loaded to
     * @param  Request $request
     */
    public static function saveTmpFile($flid) {
        $field = FieldController::getField($flid);
        $uid = \Auth::user()->id;
        $dir = env('BASE_PATH').'storage/app/tmpFiles/f'.$flid.'u'.$uid;

        $maxFileNum = FieldController::getFieldOption($field, 'MaxFiles');
        $fileNumRequest = sizeof($_FILES['file'.$flid]['name']);
        if(glob($dir.'/*.*') != false)
            $fileNumDisk = count(glob($dir.'/*.*'));
        else
            $fileNumDisk = 0;

        $maxFieldSize = FieldController::getFieldOption($field, 'FieldSize')*1024; //conversion of kb to bytes
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

        if($field->type==Field::_GALLERY) {
            $smThumbs = explode('x', FieldController::getFieldOption($field, 'ThumbSmall'));
            $lgThumbs = explode('x', FieldController::getFieldOption($field, 'ThumbLarge'));
        }

        $validTypes = true;
        $fileTypes = explode('[!]',FieldController::getFieldOption($field, 'FileTypes'));
        $fileTypesRequest = array();
        if($field->type != Field::_3D_MODEL)
            $fileTypesRequest = $_FILES['file'.$flid]['type'];
        else
            $fileTypesRequest[] = pathinfo($_FILES['file'.$flid]['name'][0], PATHINFO_EXTENSION);

        if((sizeof($fileTypes)!=1 | $fileTypes[0]!='') && $field->type != Field::_3D_MODEL) {
            foreach($fileTypesRequest as $type) {
                if(!in_array($type,$fileTypes))
                    $validTypes = false;
            }
        } else if(array_key_exists($field->type, self::$FILE_MIME_TYPES)) {
            $fileTypes = self::$FILE_MIME_TYPES[$field->type];
            foreach($fileTypesRequest as $type) {
                if(!in_array($type,$fileTypes))
                    $validTypes = false;
            }
        }

        $options = array();
        $options['flid'] = 'f'.$flid.'u'.$uid;
        if($field->type==Field::_GALLERY) {
            $options['image_versions']['thumbnail']['max_width'] = $smThumbs[0];
            $options['image_versions']['thumbnail']['max_height'] = $smThumbs[1];
            $options['image_versions']['medium']['max_width'] = $lgThumbs[0];
            $options['image_versions']['medium']['max_height'] = $lgThumbs[1];
        }

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
    public static function delTmpFile($flid, $filename) {
        $options = array();
        $options['flid'] = $flid;
        $options['filename'] = $filename;
        $upload_handler = new UploadHandler($options);
    }

    /**
     * Downloads a file from a particular record field.
     *
     * @param  int $rid - Record ID
     * @param  int $flid - Field ID
     * @param  string $filename - Name of the file
     * @return string - html for the file download
     */
    public static function getFileDownload($rid, $flid, $filename) {
        $record = RecordController::getRecord($rid);
        $field = FieldController::getField($flid);

        // Check if file exists in app/storage/file folder
        $file_path = env('BASE_PATH').'storage/app/files/p'.$record->pid.'/f'.$record->fid.'/r'.$record->rid.'/fl'.$field->flid . '/' . $filename;
        if(file_exists($file_path)) {
            // Send Download
            return response()->download($file_path, $filename, [
                'Content-Length: '. filesize($file_path)
            ]);
        } else {
            return response()->json(["status"=>false,"message"=>"file_doesnt_exist"],500);
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
     * Helps with keyword search for file typed fields.
     *
     * @param  string $arg - The keywords
     * @param  string $method - Type of keyword search
     * @return string - Updated keyword search
     */
    protected function processArgumentForFileField($arg, $method) {
        // We only want to match with actual data in the name field
        if($method == Search::SEARCH_EXACT) {
            $arg = rtrim($arg, '"');
            $arg .= "[Name]\"";
        } else {
            $args = explode(" ", $arg);

            foreach($args as &$arg) {
                $arg .= "[Name]";
            }
            $arg = implode(" ",$args);
        }

        return $arg;
    }
}