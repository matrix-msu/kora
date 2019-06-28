<?php namespace App\KoraFields;

use App\FieldHelpers\UploadHandler;
use App\Form;
use App\Http\Controllers\RecordController;
use App\Record;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
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
     * Gets the default options string for a new field.
     *
     * @param  int $fid - Form ID
     * @param  string $slug - Name of database column based on field internal name
     * @param  array $options - Extra information we may need to set up about the field
     * @return array - The default options
     */
    public function addDatabaseColumn($fid, $slug, $options = null) {
        $table = new \CreateRecordsTable();
        $table->addJSONColumn($fid, $slug);
    }

    /**
     * Update the options for a field
     *
     * @param  array $field - Field to update options
     * @param  Request $request
     * @param  string $slug - Name of database column based on field internal name
     * @param  string $prefix - Table prefix
     * @return array - The updated field array
     */
    public function updateOptions($field, Request $request, $slug = null, $prefix = 'records_') {
        if($request->filesize==0 | $request->filesize=='')
            $request->filesize = null;
        if($request->maxfiles==0 | $request->maxfiles=='')
            $request->maxfiles = null;

        $field['default'] = $request->default;
        $field['options']['FieldSize'] = $request->filesize;
        $field['options']['MaxFiles'] = $request->maxfiles;
        $field['options']['FileTypes'] = isset($request->filetype) ? $request->filetype : [];

        return $field;
    }

    /**
     * Validates the record data for a field against the field's options.
     *
     * @param  int $flid - The field internal name
     * @param  array $field - The field data array to validate
     * @param  Request $request
     * @param  bool $forceReq - Do we want to force a required value even if the field itself is not required?
     * @return array - Array of errors
     */
    public function validateField($flid, $field, $request, $forceReq = false) {
        $req = $field['required'];
        if(Auth::guest())
            $value = 'recordU'.$request['userId'];
        else
            $value = 'recordU'.Auth::user()->id;

        $tmpPath = 'app/tmpFiles/' . $value;

        if($req==1 | $forceReq) {
            if(glob(storage_path($tmpPath . '/*.*')) == false)
                return [$flid => $field['name'].' is required'];
        }

        foreach(new \DirectoryIterator(storage_path($tmpPath)) as $file) {
            if($file->isFile()) {
                if(!self::validateRecordFileName($file->getFilename()))
                    return [$flid => $field['name'].' has file with illegal filename'];
            }
        }

        return array();
    }

    /**
     * Formats data for record entry.
     *
     * @param  array $field - The field to represent record data
     * @param  string $value - Data to add
     * @param  Request $request
     *
     * @return mixed - Processed data
     */
    public function processRecordData($field, $value, $request) {
        if($request->api && isset($request->userId))
            $uid = $request->userId;
        else
            $uid = Auth::user()->id;
        $tmpPath = 'app/tmpFiles/recordU' . $uid;

        //See if files were uploaded
        if(glob(storage_path($tmpPath.'/*.*')) != false) {
            $storageType = 'LaravelStorage'; //TODO:: make this a config once we actually support other storage types
            $files = [];
            $infoArray = array();
            //URL for accessing file publically
            $dataURL = url('files').'/'.$request->pid . '-' . $request->fid . '-' . $request->rid.'/';
            $fileIDString = $field['flid'] . $request->rid . '_';
            $types = self::getMimeTypes();

            switch($storageType) {
                case 'LaravelStorage':
                    $newPath = storage_path('app/files/' . $request->pid . '/' . $request->fid . '/' . $request->rid);
                    if(!file_exists($newPath))
                        mkdir($newPath, 0775, true);
                    else {
                        //empty path files, revisions already saved these files in case things go wrong
                        foreach(new \DirectoryIterator($newPath) as $file) {
                            if($file->isFile())
                                unlink($newPath.'/'.$file->getFilename());
                        }
                    }

                    foreach(new \DirectoryIterator(storage_path($tmpPath)) as $file) {
                        if($file->isFile()) {
                            $fileName = $file->getFilename();

                            //last validation check protector
                            if(!self::validateRecordFileName($fileName))
                                continue;

                            //Hash the file
                            $checksum = hash_file('sha256', $tmpPath . '/' . $fileName);

                            //Get the actual MEME type
                            if(!array_key_exists($file->getExtension(), $types))
                                $type = 'application/octet-stream';
                            else
                                $type = $types[$file->getExtension()];

                            //Store the info array
                            $info = ['name' => $fileName, 'size' => $file->getSize(), 'type' => $type,
                                'url' => $dataURL.urlencode($fileName), 'checksum' => $checksum];
                            $infoArray[$fileName] = $info;

                            //Move the file to its new home
                            copy(storage_path($tmpPath . '/' . $fileName), $newPath . '/' . $fileName);
                        }
                    }
                    break;
                default:
                    break;
            }

            foreach($value as $fName) {
                $files[] = $infoArray[$fName];
            }

            return json_encode($files);
        } else {
            return null;
        }
    }

    /**
     * Formats data for revision display.
     *
     * @param  mixed $data - The data to store
     * @param  Request $request
     *
     * @return mixed - Processed data
     */
    public function processRevisionData($data) {
        $data = json_decode($data,true);
        $return = '';
        foreach($data as $file) {
            $return .= "<div>".$file['name']."</div>";
        }

        return $return;
    }

    /**
     * Formats data for record entry.
     *
     * @param  string $flid - Field ID
     * @param  array $field - The field to represent record data
     * @param  array $value - Data to add
     * @param  Request $request
     *
     * @return Request - Processed data
     */
    public function processImportData($flid, $field, $value, $request) { //TODO::CASTLE
        $files = array();
        $originRid = $request->originRid;

        //See where we are looking first
        if(is_null($originRid))
            $currDir = storage_path( 'app/tmpFiles/impU' . \Auth::user()->id);
        else
            $currDir = storage_path('app/tmpFiles/impU' . \Auth::user()->id . '/' . $originRid);

        //Make destination directory
        $newDir = storage_path('app/tmpFiles/recordU' . \Auth::user()->id);
        if(!file_exists($newDir))
            mkdir($newDir, 0775, true);

        $value = explode(' | ', $value);

        foreach($value as $file) {
            if(!$file)
                return response()->json(["status"=>false,"message"=>"json_validation_error",
                    "record_validation_error"=>[$request->kid => "$flid is missing name for a file"]],500);
            if(!self::validateRecordFileName($file))
                return response()->json(["status"=>false,"message"=>"json_validation_error",
                    "record_validation_error"=>[$request->kid => "$flid has file with illegal filename"]],500);
            $name = $file;
            //move file from imp temp to tmp files
            copy($currDir . '/' . $name, $newDir . '/' . $name);
            //add input for this file
            array_push($files, $name);
        }
        $request[$flid] = $files;

        return $request;
    }

    /**
     * Formats data for record entry.
     *
     * @param  string $flid - Field ID
     * @param  array $field - The field to represent record data
     * @param  \SimpleXMLElement $value - Data to add
     * @param  Request $request
     *
     * @return Request - Processed data
     */
    public function processImportDataXML($flid, $field, $value, $request) { //TODO::CASTLE
        $files = array();
        $originRid = $request->originRid;

        //See where we are looking first
        if(is_null($originRid))
            $currDir = storage_path( 'app/tmpFiles/impU' . \Auth::user()->id);
        else
            $currDir = storage_path('app/tmpFiles/impU' . \Auth::user()->id . '/' . $originRid);

        //Make destination directory
        $newDir = storage_path('app/tmpFiles/recordU' . \Auth::user()->id);
        if(file_exists($newDir))
            mkdir($newDir, 0775, true);

        if(empty($value->File))
            return response()->json(["status"=>false,"message"=>"xml_validation_error",
                "record_validation_error"=>[$request->kid => "$flid format is incorrect for a File Type Field"]],500);
        foreach ($value->File as $file) {
            $name = (string)$file;
            //move file from imp temp to tmp files
            if(!file_exists($currDir . '/' . $name)) {
                //Before we fail, let's see first if it's just failing because the originRid was specified
                // and not because the file doesn't actually exist. We will now force look into the ZIPs root folder
                $currDir = storage_path( 'app/tmpFiles/impU' . \Auth::user()->id);
                if(!file_exists($currDir . '/' . $name))
                    return response()->json(["status" => false, "message" => "xml_validation_error",
                        "record_validation_error" => [$request->kid => "$flid: trouble finding file $name"]], 500);
            }
            if(!self::validateRecordFileName($name))
                return response()->json(["status"=>false,"message"=>"json_validation_error",
                    "record_validation_error"=>[$request->kid => "$flid has file with illegal filename"]],500);
            copy($currDir . '/' . $name, $newDir . '/' . $name);
            //add input for this file
            array_push($files, $name);
        }

        $request[$flid] = $files;

        return $request;
    }

    /**
     * Formats data for record entry.
     *
     * @param  string $flid - Field ID
     * @param  array $field - The field to represent record data
     * @param  array $value - Data to add
     * @param  Request $request
     *
     * @return Request - Processed data
     */
    public function processImportDataCSV($flid, $field, $value, $request) {
        $request[$flid] = $value;

        return $request;
    }

    /**
     * Formats data for record display.
     *
     * @param  array $field - The field to represent record data
     * @param  string $value - Data to display
     *
     * @return mixed - Processed data
     */
    public function processDisplayData($field, $value) {
        return json_decode($value,true);
    }

    /**
     * Formats data for XML record display.
     *
     * @param  string $field - Field ID
     * @param  string $value - Data to format
     *
     * @return mixed - Processed data
     */
    public function processXMLData($field, $value) {
        $files = json_decode($value,true);
        $xml = "<$field>";
        foreach($files as $file) {
            $xml .= '<File>';
            $xml .= '<Name>'.$file['name'].'</Name>';
            $xml .= '<Size>'.$file['size'].'</Size>';
            $xml .= '<Type>'.$file['type'].'</Type>';
            $xml .= '<Url>'.$file['url'].'</Url>';
            $xml .= '</File>';
        }
        $xml .= "</$field>";

        return $xml;
    }

    /**
     * Formats data for XML record display.
     *
     * @param  string $value - Data to format
     *
     * @return mixed - Processed data
     */
    public function processLegacyData($value) {
        //Legacy so only grab first file
        $file = json_decode($value,true)[0];

        return [
            'originalName' => $file['name'],
            'size' => $file['size'],
            'type' => $file['type'],
            'localName' => $file['url']
        ];
    }

    /**
     * Takes data from a mass assignment operation and applies it to an individual field.
     *
     * @param  Form $form - Form model
     * @param  string $flid - Field ID
     * @param  String $formFieldValue - The value to be assigned
     * @param  Request $request
     * @param  bool $overwrite - Overwrite if data exists
     */
    public function massAssignRecordField($form, $flid, $formFieldValue, $request, $overwrite = 0) {
        null;
    }

    /**
     * Takes data from a mass assignment operation and applies it to an individual field for a set of records.
     *
     * @param  Form $form - Form model
     * @param  string $flid - Field ID
     * @param  String $formFieldValue - The value to be assigned
     * @param  Request $request
     * @param  array $kids - The KIDs to update
     */
    public function massAssignSubsetRecordField($form, $flid, $formFieldValue, $request, $kids) {
        null;
    }

    /**
     * Performs a keyword search on this field and returns any results.
     *
     * @param  string $flid - Field ID
     * @param  string $arg - The keywords
     * @param  Record $recordMod - Model to search through
     * @param  boolean $negative - Get opposite results of the search
     * @return array - The RIDs that match search
     */
    public function keywordSearchTyped($flid, $arg, $recordMod, $form, $negative = false) {
        if($negative)
            $param = 'NOT LIKE';
        else
            $param = 'LIKE';

        $dbQuery = $recordMod->newQuery()
            ->select("id");

        $dbQuery->whereRaw("`$flid`->\"$[*].name\" $param \"$arg\"");

        return $dbQuery->pluck('id')
            ->toArray();
    }

    /**
     * Updates the request for an API search to mimic the advanced search structure.
     *
     * @param  array $data - Data from the search
     * @return array - The update request
     */
    public function setRestfulAdvSearch($data) {
        return null;
    }

    /**
     * Build the advanced query for a text field.
     *
     * @param  $flid, field id
     * @param  $query, contents of query.
     * @param  Record $recordMod - Model to search through
     * @param  boolean $negative - Get opposite results of the search
     * @return array - The RIDs that match search
     */
    public function advancedSearchTyped($flid, $query, $recordMod, $form, $negative = false) {
        return null;
    }

    ///////////////////////////////////////////////END ABSTRACT FUNCTIONS///////////////////////////////////////////////

    /**
     * Returns default mime list, if file types not saved in field options.
     *
     * @param  int $flid - File field that record file will be loaded to
     * @return array - The list
     */
    public abstract function getDefaultMIMEList();

    /**
     * Saves a temporary version of an uploaded file.
     *
     * @param  int $fid - Form ID
     * @param  int $flid - Field ID
     * @param  int $field - File field that record file will be loaded to
     * @param  Request $request
     */
    public function saveTmpFile($form, $flid, $field) {
        $uid = \Auth::user()->id;
        //We are going to store in the tmp directory in a user unique folder
        $dir = storage_path('recordU'.$uid);

        //Validate file names
        $validNames = true;
        foreach($_FILES['file'.$flid]['name'] as $name) {
            if(!self::validateRecordFileName($name))
                $validNames = false;
        }

        //Prep comparing of allowed number files, vs files already in tmp folder
        $maxFileNum = !is_null($field['options']['MaxFiles']) ? $field['options']['MaxFiles'] : 0;
        $fileNumRequest = sizeof($_FILES['file'.$flid]['name']);
        if(glob($dir.'/*.*') != false)
            $fileNumDisk = count(glob($dir.'/*.*'));
        else
            $fileNumDisk = 0;

        $maxFieldSize = $field['options']['FieldSize'];
    		if (trim($maxFieldSize) === '') {
    			$maxFieldSize = '0';
    		}
    		$maxFieldSize = $maxFieldSize * 1024;

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

        //Get directory of file types allowed in a particular file field
        $validTypes = true;
        $fileTypes = !empty($field['options']['FileTypes']) ? $field['options']['FileTypes'] : $this->getDefaultMIMEList();
        $fileTypesRequest = $_FILES['file'.$flid]['type'];

        //If no types specified, we allow all types, or whatever supported types are available for the field
        if(!empty($fileTypes)) {
            foreach ($fileTypesRequest as $type) {
                //This statement guards against Safari's lack of file type recognition
                if($field['type'] == Form::_PLAYLIST && $type == "audio/mpeg")
                    $type = "audio/mp3";

                if(!in_array($type, $fileTypes))
                    $validTypes = false;
            }
        }

        //Setting up options to work with the upload class plugin we use
        $options = array();
        $options['fid'] = $form->id;
        $options['flid'] = $flid;
        $options['folder'] = 'recordU'.$uid;

        if(!$validNames) {
            echo "InvalidFileNames";
        } else if(!$validTypes) {
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
    public function delTmpFile($fid, $flid, $filename) {
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
     * Checks the name of an incoming record file for valid characters.
     *
     * @param  string $name - Name of the file to validate
     * @param  Request $request
     */
    public static function validateRecordFileName($name) {
        //Make sure characters are legal
        //First character must be alphanumeric
        //Rest may include . _ and -
        if(!preg_match("/^[a-zA-Z0-9][a-zA-Z0-9\.\-\_]+$/", $name))
            return false;

        //Make sure there are no double periods
        if(strpos($name, '..') !== false)
            return false;

        return true;
    }

    /**
     * Public access link for a file. NOTE: Mirrors file download, but is publically accessible doesn't force download
     *
     * @param  string $kid - kora record that holds the file
     * @param  string $filename - Name of the file
     * @return mixed - the file
     */
    public static function publicRecordFile($kid, $filename) {
        $record = RecordController::getRecord($kid);
        $storageType = 'LaravelStorage'; //TODO:: make this a config once we actually support other storage types

        $thumb = \request('thumb');
        $createThumb = false;
        $thumbParts = null;
        if(!is_null($thumb)) {
            $createThumb = true;

            if(!preg_match("/^[0-9]+[x][0-9]+$/", $thumb))
                return response()->json(["status" => false, "message" => "bad_thumb_format"], 500);

            $thumbParts = explode('x',$thumb);

            //Define the name of the thumb
            $fileParts = explode('.',$filename);
            $ext = array_pop($fileParts);
            $thumbFilename = implode('.',$fileParts)."_$thumb.".$ext;
        }


        switch($storageType) {
            case 'LaravelStorage':
                // Check if file exists in app/storage/file folder
                $filePath = storage_path('app/files/'.$record->project_id.'/'.$record->form_id.'/'.$record->id.'/'.$filename);
                if(file_exists($filePath)) {
                    if($createThumb) {
                        $thumbPath = storage_path('app/files/'.$record->project_id.'/'.$record->form_id.'/'.$record->id.'/'.$thumbFilename);

                        //Check if we already made the thumb
                        if(!file_exists($thumbPath)) {
                            $tImage = new \Imagick($filePath);
                            $tImage->thumbnailImage($thumbParts[0], $thumbParts[1], true);
                            $tImage->writeImage($thumbPath);
                        }

                        //rename the file we are serving
                        $filePath = $thumbPath;
                    }

                    //This allows the thumbs to display properly
                    while (ob_get_level()) {
                        ob_end_clean();
                    }

                    // Send file, but define type for browsers sake
                    header('Content-Type: '. mime_content_type($filePath));
                    readfile($filePath);
                }
                break;
            default:
                break;
        }

        return response()->json(["status" => false, "message" => "file_doesnt_exist"], 500);
    }

    /**
     * Downloads a file from a particular record field.
     *
     * @param  int $kid - Record kora ID
     * @param  string $filename - Name of the file
     * @return Redirect - html for the file download
     */
    public static function getFileDownload($kid, $filename) {
        $record = RecordController::getRecord($kid);
        $storageType = 'LaravelStorage'; //TODO:: make this a config once we actually support other storage types

        switch($storageType) {
            case 'LaravelStorage':
                // Check if file exists in app/storage/file folder
                $filePath = storage_path('app/files/'.$record->project_id.'/'.$record->form_id.'/'.$record->id.'/'.$filename);
                if(file_exists($filePath)) {
                    // Send Download, dont define type so it guarantees a download
                    return response()->download($filePath, $filename, [
                        'Content-Length: '. filesize($filePath)
                    ]);
                }
                break;
            default:
                break;
        }

        return response()->json(["status" => false, "message" => "file_doesnt_exist"], 500);
    }

    /**
     * Downloads a zip of all files from a particular record.
     *
     * @param  int $kid - Record kora ID
     * @return string - html for the file download
     */
    public static function getZipDownload($kid) {
        $record = RecordController::getRecord($kid);
        $storageType = 'LaravelStorage'; //TODO:: make this a config once we actually support other storage types

        switch($storageType) {
            case 'LaravelStorage':
                // Check if file exists in app/storage/file folder
                $dir_path = storage_path('app/files/'.$record->project_id.'/'.$record->form_id.'/'.$record->id);
                if(file_exists($dir_path)) {
                    $zip_name = $kid . '_zip_export' . date("Y_m_d_His") . '.zip';
                    $zip_dir = storage_path('app/exports');
                    $zip = new ZipArchive();

                    if($zip->open($zip_dir . '/' . $zip_name, ZipArchive::CREATE) === TRUE) {
                        foreach(new \DirectoryIterator($dir_path) as $file) {
                            if($file->isFile()) {
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
                    if(file_exists($filetopath))
                        return response()->download($filetopath, $zip_name, $headers);
                }
                break;
            default:
                break;
        }

        return response()->json(["status" => false, "message" => "file_doesnt_exist"], 500);
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
