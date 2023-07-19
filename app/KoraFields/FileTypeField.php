<?php namespace App\KoraFields;

use App\FieldHelpers\UploadHandler;
use App\Form;
use App\Http\Controllers\FormController;
use App\Http\Controllers\RecordController;
use App\Record;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
     * @var string - The available storage types
     */
    const _LaravelStorage = "LaravelStorage";
    const _JoyentManta = "JoyentManta";

    /**
     * @var string - Method from CreateRecordsTable() for adding to DB
     */
    const FIELD_DATABASE_METHOD = 'addJSONColumn';

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

        $tmpPath = 'app/tmpFiles/' . $request->tmpFileDir;

        if(($req==1 || $forceReq) && is_null($request->{$flid}))
            return [$flid => $field['name'].' is required'];

        if(file_exists(storage_path($tmpPath)) && !is_null($request->{$flid})) {
            foreach(new \DirectoryIterator(storage_path($tmpPath)) as $file) {
                if($file->isFile() && in_array($file->getFilename(), $request->{$flid})) {
                    if(!self::validateRecordFileName($file->getFilename()))
                        return [$flid => $field['name'] . ' has file with illegal filename'];
                }
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
        $tmpPath = 'app/tmpFiles/'.$request->tmpFileDir;
        $flid = $field['flid'];
        $captions = !is_null($request->input('file_captions'.$flid)) ? $request->input('file_captions'.$flid) : null;

        //See if files were uploaded
        if(glob(storage_path($tmpPath.'/*.*')) != false) {
            $files = [];
            $kid = $request->pid . '-' . $request->fid . '-' . $request->rid;
            //URL for accessing file publically
            $dataURL = url('files').'/'.$kid.'/';
            $types = self::getMimeTypes();

            //Check if data already exists for this record and its field
            $oldData = RecordController::getRecord($kid);
            if(!is_null($oldData)) {
                $oldData = $oldData->{$flid};
                if(!is_null($oldData))
                    $oldData = json_decode($oldData, true);
            }

            foreach(new \DirectoryIterator(storage_path($tmpPath)) as $file) {
                if($file->isFile()) {
                    $fileName = $file->getFilename();
                    //last validation check protector
                    //Also check if filename is apart of this field data
                    if(!self::validateRecordFileName($fileName) || !in_array($fileName,$value))
                        continue;

                    //Hash the file
                    $checksum = hash_file('sha256', $tmpPath . '/' . $fileName);

                    //Get order index
                    $valKey = array_search($fileName, $value);

                    //Get caption
                    $caption = $captions[$valKey];

                    //Before we store, we need to compare to other files if they exist, to see if new file exists
                    $exists = false;
                    if(!is_null($oldData)) {
                        //foreach old record file
                        foreach($oldData as $oldFile) {
                            if($fileName==$oldFile['name'] && $checksum==$oldFile['checksum']) {
                                $exists = true;
                                $info = $oldFile;
                                //Update caption
                                $info['caption'] = $caption;
                                break;
                            }
                        }
                    }

                    if(!$exists) {
                        //Get the actual MEME type
                        if(!array_key_exists($file->getExtension(), $types))
                            $type = 'application/octet-stream';
                        else
                            $type = $types[$file->getExtension()];

                        //Get timestamp
                        $timestamp = time();

                        //Build info array
                        $info = ['name' => $fileName, 'size' => $file->getSize(), 'type' => $type, 'caption' => $caption,
                            'url' => $dataURL.urlencode($fileName), 'checksum' => $checksum, 'timestamp' => $timestamp];

                        switch(config('filesystems.kora_storage')) {
                            case self::_LaravelStorage:
                                $newPath = storage_path('app/files/' . $request->pid . '/' . $request->fid . '/' . $request->rid);
                                if(!file_exists($newPath))
                                    mkdir($newPath, 0775, true);

                                //Move the file to its new home
                                copy(storage_path($tmpPath . '/' . $fileName), $newPath . '/' . "$timestamp.$fileName");
                                break;
                            case self::_JoyentManta:
                                //TODO::MANTA
                                break;
                            default:
                                break;
                        }
                    }

                    //Store the info array
                    $files[$valKey] = $info;
                }
            }

            if(empty($files))
                return null;

            //Fixes weird json bug
            ksort($files);
            $files = array_values($files);

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
            $tsp = isset($file['timestamp']) ? ' ('.$file['timestamp'].')' : '';
            $caption = isset($file['caption']) && $file['caption']!='' ? ' ('.$file['caption'].')' : '';
            $return .= "<div>".$file['name']."$tsp</div>";
            $return .= "<div>".$caption."</div>";
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
    public function processImportData($flid, $field, $value, $request) {
        $files = $captions = array();

        if(isset($request->userId))
            $subpath = $request->userId;
        else
            $subpath = \Auth::user()->id;

        $currDir = storage_path( 'app/tmpFiles/impU' . $subpath);

        //Make destination directory
        $newDir = storage_path('app/tmpFiles/'.$request->tmpFileDir);
        if(!file_exists($newDir))
            mkdir($newDir, 0775, true);

        foreach($value as $file) {
            if(!isset($file['name']))
                return response()->json(["status"=>false,"message"=>"json_validation_error",
                    "record_validation_error"=>[$request->kid => "$flid is missing name for a file"]],500);

            $pathname = $file['name'];
            $parts = explode('/',$pathname);
            $name = end($parts);

            if(!file_exists($currDir . '/' . $pathname)) {
                if(file_exists($currDir . '/' . $request['kidForReimportingRecordFiles'] . '/' . $pathname))
                    $currDir .= '/' . $request['kidForReimportingRecordFiles'];
                else
                    return response()->json(["status" => false, "message" => "json_validation_error",
                        "record_validation_error" => [$request->kid => "$flid: trouble finding file $name"]], 500);
            }
            if(!self::validateRecordFileName($name))
                return response()->json(["status"=>false,"message"=>"json_validation_error",
                    "record_validation_error"=>[$request->kid => "$flid has file with illegal filename"]],500);
            //move file from imp temp to tmp files
            copy($currDir . '/' . $pathname, $newDir . '/' . $name);
            //add input for this file
            array_push($files, $name);

            if(isset($file["caption"]))
                array_push($captions, $file["caption"]);
            else
                array_push($captions, '');
        }

        $request['file_captions' . $flid] = $captions;
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
    public function processImportDataXML($flid, $field, $value, $request) {
        $files = $captions = array();

        $currDir = storage_path( 'app/tmpFiles/impU' . \Auth::user()->id);

        //Make destination directory
        $newDir = storage_path('app/tmpFiles/'.$request->tmpFileDir);
        if(!file_exists($newDir))
            mkdir($newDir, 0775, true);

        if(empty($value->File))
            return response()->json(["status"=>false,"message"=>"xml_validation_error",
                "record_validation_error"=>[$request->kid => "$flid format is incorrect for a File Type Field"]],500);
        foreach($value->File as $file) {
            $pathname = (string)$file->Name;
            $parts = explode('/',$pathname);
            $name = end($parts);
            //move file from imp temp to tmp files
            if(!file_exists($currDir . '/' . $pathname)) {
                if(file_exists($currDir . '/' . $request['kidForReimportingRecordFiles'] . '/' . $pathname))
                    $currDir .= '/' . $request['kidForReimportingRecordFiles'];
                else
                    return response()->json(["status" => false, "message" => "json_validation_error",
                        "record_validation_error" => [$request->kid => "$flid: trouble finding file $name"]], 500);
            }
            if(!self::validateRecordFileName($name))
                return response()->json(["status"=>false,"message"=>"json_validation_error",
                    "record_validation_error"=>[$request->kid => "$flid has file with illegal filename"]],500);
            copy($currDir . '/' . $pathname, $newDir . '/' . $name);
            //add input for this file
            array_push($files, $name);

            if(!empty($file->Caption))
                array_push($captions, (string)$file->Caption);
            else
                array_push($captions, '');
        }

        $request['file_captions' . $flid] = $captions;
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
        $files = $captions = array();

        if(isset($request->userId))
            $subpath = $request->userId;
        else
            $subpath = \Auth::user()->id;

        $currDir = storage_path( 'app/tmpFiles/impU' . $subpath);

        //Make destination directory
        $newDir = storage_path('app/tmpFiles/'.$request->tmpFileDir);
        if(!file_exists($newDir))
            mkdir($newDir, 0775, true);

        $newVal = array();
        $fileParts = explode('|', $value);
        foreach($fileParts as $fPart) {
            if(strpos($fPart, '[CAPTION]') !== false) {
                $capParts = explode('[CAPTION]', $fPart);
                $newFile = ['name' => trim($capParts[0]), 'caption' => trim($capParts[1])];
            } else {
                $newFile = ['name' => trim($fPart)];
            }
            $newVal[] = $newFile;
        }

        foreach($newVal as $file) {
            if(!isset($file['name']))
                return response()->json(["status"=>false,"message"=>"json_validation_error",
                    "record_validation_error"=>[$request->kid => "$flid is missing name for a file"]],500);

            $pathname = $file['name'];
            $parts = explode('/',$pathname);
            $name = end($parts);

            if(!file_exists($currDir . '/' . $pathname))
                return response()->json(["status" => false, "message" => "json_validation_error",
                    "record_validation_error" => [$request->kid => "$flid: trouble finding file $name"]], 500);
            if(!self::validateRecordFileName($name))
                return response()->json(["status"=>false,"message"=>"json_validation_error",
                    "record_validation_error"=>[$request->kid => "$flid has file with illegal filename"]],500);
            //move file from imp temp to tmp files
            copy($currDir . '/' . $pathname, $newDir . '/' . $name);
            //add input for this file
            array_push($files, $name);

            if(isset($file["caption"]))
                array_push($captions, $file["caption"]);
            else
                array_push($captions, '');
        }

        $request['file_captions' . $flid] = $captions;
        $request[$flid] = $files;

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
     * @param  int $fid - Form ID
     *
     * @return mixed - Processed data
     */
    public function processXMLData($field, $value, $fid = null) {
        $files = json_decode($value,true);
        $xml = "<$field>";
        foreach($files as $file) {
            $xml .= '<File>';
            $xml .= '<Name>'.$file['name'].'</Name>';
            $xml .= '<Caption>'.$file['caption'].'</Caption>';
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
     * @param  array $flids - Field ID
     * @param  string $arg - The keywords
     * @param  Record $recordMod - Model to search through
     * @param  boolean $negative - Get opposite results of the search
     * @return array - The RIDs that match search
     */
    public function keywordSearchTyped($flids, $arg, $recordMod, $form, $negative = false) {
        if($negative)
            $param = 'NOT LIKE';
        else
            $param = 'LIKE';

        $dbQuery = $recordMod->newQuery()
            ->select("id");

        $arg = strtolower($arg); //Solves the JSON mysql case-insensitive issue

        foreach($flids as $f) {
            if($negative) {
                $dbQuery = $dbQuery->orWhere(function($query) use ($f, $param, $arg) {
                    $query = $query->whereRaw("LOWER(`$f`->\"$[*].name\") $param \"$arg\"");
                    $query = $query->whereRaw("LOWER(`$f`->\"$[*].caption\") $param \"$arg\"");
                });
            } else {
                $dbQuery = $dbQuery->orWhere(function($query) use ($f, $param, $arg) {
                    $query = $query->orWhereRaw("LOWER(`$f`->\"$[*].name\") $param \"$arg\"");
                    $query = $query->orWhereRaw("LOWER(`$f`->\"$[*].caption\") $param \"$arg\"");
                });
            }
        }

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
    public function saveTmpFile($form, $flid, $field, $tmpDir) {
        //We are going to store in the tmp directory in a user unique folder
        $dir = storage_path($tmpDir);

        //Validate file names
        $validNames = true;
        foreach($_FILES['file'.$flid]['name'] as $name) {
            if(!self::validateRecordFileName($name))
                $validNames = false;
        }

        //Prep comparing of allowed number files, vs files already in tmp folder
        $maxFileNum = (!is_null($field['options']['MaxFiles']) && $field['options']['MaxFiles']!="") ? $field['options']['MaxFiles'] : 0;
        $fileNumRequest = sizeof($_FILES['file'.$flid]['name']);
        if(glob($dir.'/*.*') != false)
            $fileNumDisk = count(glob($dir.'/*.*'));
        else
            $fileNumDisk = 0;

        $maxFieldSize = (!is_null($field['options']['FieldSize']) && $field['options']['FieldSize']!="") ? $field['options']['FieldSize'] : 0;
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

                //Conversion to handle certain updated browsers
                if($field['type'] == Form::_3D_MODEL && $type == "model/obj")
                    $type = "obj";

                if(!in_array($type, $fileTypes))
                    $validTypes = false;
            }
        }

        //Setting up options to work with the upload class plugin we use
        $options = array();
        $options['fid'] = $form->id;
        $options['flid'] = $flid;
        $options['folder'] = $tmpDir;

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
    public function delTmpFile($fid, $flid, $filename, $tmpDir) {
        $options = array();
        $options['fid'] = $fid;
        $options['flid'] = $flid;
        $options['filename'] = $filename;
        $options['folder'] = $tmpDir;
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
        $form = FormController::getForm($record->form_id);

        //Need to get the actual local name of the file if it has a timestamp
        foreach($form->layout['fields'] as $flid => $field) {
            if($form->getFieldModel($field['type']) instanceof self && !is_null($record->{$flid})) {
                $files = json_decode($record->{$flid}, true);
                foreach($files as $recordFile) {
                    if($recordFile['name'] == $filename) {
                        $filename = isset($recordFile['timestamp']) ? $recordFile['timestamp'].'.'.$recordFile['name'] : $recordFile['name'];
                        break(2);
                    }
                }
            }
        }

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

        switch(config('filesystems.kora_storage')) {
            case self::_LaravelStorage:
                // Check if file exists in app/storage/file folder
                $filePath = storage_path('app/files/'.$record->project_id.'/'.$record->form_id.'/'.$record->id.'/'.$filename);
                if(file_exists($filePath)) {
                    $headers = getallheaders();
                    $lastModifiedValue = gmdate('D, d M Y H:i:s', filemtime($filePath));
                    $time = Carbon::createFromFormat('D, d M Y H:i:s', $lastModifiedValue);

                    if(isset($headers['if-modified-since'])) {
                        $requestTime = Carbon::createFromFormat('D, d M Y H:i:s T', $headers['if-modified-since']);
                        if($requestTime->gt($time)) {
                            header('HTTP/1.1 304 Not Modified');
                            exit;
                        }
                    }

                    if($createThumb) {
                        $thumbPath = storage_path('app/files/'.$record->project_id.'/'.$record->form_id.'/'.$record->id.'/'.$thumbFilename);

                        //Check if we already made the thumb
                        if(!file_exists($thumbPath)) {
                            try {
                                $tImage = new \Imagick($filePath);
                                $tImage->thumbnailImage($thumbParts[0], $thumbParts[1], true);
                                $tImage->writeImage($thumbPath);
                            } catch(\Exception $e) {
                                Log::error($e);
                                return response()->json(["status" => false, "message" => "thumb_generation_failed"], 500);
                            }
                        }

                        //rename the file we are serving
                        $filePath = $thumbPath;
                    }

                    //This allows the thumbs to display properly
                    while(ob_get_level()) {
                        ob_end_clean();
                    }

                    $filetype =mime_content_type($filePath);
                    $filesize = filesize($filePath);

                    //Helps us handle video streaming
                    header("Content-type: $filetype");
                    header("Last-Modified: $lastModifiedValue GMT", true, 200);
                    if(isset($_SERVER['HTTP_RANGE'])){ // do it for any device that supports byte-ranges not only iPhone
                        self::rangeDownload($filePath);
                    } else {
                        header("Content-length: $filesize");
                        readfile($filePath);
                    }
                    exit;
                }
                break;
            case self::_JoyentManta:
                //TODO::MANTA
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
        $form = FormController::getForm($record->form_id);

        //Need to get the actual local name of the file if it has a timestamp
        foreach($form->layout['fields'] as $flid => $field) {
            if($form->getFieldModel($field['type']) instanceof self && !is_null($record->{$flid})) {
                $files = json_decode($record->{$flid}, true);
                foreach($files as $recordFile) {
                    if($recordFile['name'] == $filename) {
                        $filename = isset($recordFile['timestamp']) ? $recordFile['timestamp'].'.'.$recordFile['name'] : $recordFile['name'];
                        break(2);
                    }
                }
            }
        }

        switch(config('filesystems.kora_storage')) {
            case self::_LaravelStorage:
                // Check if file exists in app/storage/file folder
                $filePath = storage_path('app/files/'.$record->project_id.'/'.$record->form_id.'/'.$record->id.'/'.$filename);
                if(file_exists($filePath)) {
                    // Send Download, dont define type so it guarantees a download
                    return response()->download($filePath, $filename, [
                        'Content-Length: '. filesize($filePath)
                    ]);
                }
                break;
            case self::_JoyentManta:
                //TODO::MANTA
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
        $form = FormController::getForm($record->form_id);

        //Build an array of the files that actually need to be zipped from every file field
        //This will ignore old record files
        //Also builds an array of local file names to original names to compensate for timestamps
        $fileArray = [];
        foreach($form->layout['fields'] as $flid => $field) {
            if($form->getFieldModel($field['type']) instanceof self && !is_null($record->{$flid})) {
                $files = json_decode($record->{$flid}, true);
                foreach($files as $recordFile) {
                    $localName = isset($recordFile['timestamp']) ? $recordFile['timestamp'].'.'.$recordFile['name'] : $recordFile['name'];
                    $fileArray[$localName] = $recordFile['name'];
                }
            }
        }

        switch(config('filesystems.kora_storage')) {
            case self::_LaravelStorage:
                // Check if file exists in app/storage/file folder
                $dir_path = storage_path('app/files/'.$record->project_id.'/'.$record->form_id.'/'.$record->id);
                if(file_exists($dir_path)) {
                    $zip_name = $kid . '_zip_export' . date("Y_m_d_His") . '.zip';
                    $zip_dir = storage_path('app/exports');
                    $zip = new ZipArchive();

                    if($zip->open($zip_dir . '/' . $zip_name, ZipArchive::CREATE) === TRUE) {
                        foreach(new \DirectoryIterator($dir_path) as $file) {
                            if($file->isFile() && array_key_exists($file->getFilename(),$fileArray)) {
                                $content = file_get_contents($file->getRealPath());
                                $zip->addFromString($fileArray[$file->getFilename()], $content);
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
            case self::_JoyentManta:
                //TODO::MANTA
                break;
            default:
                break;
        }

        return response()->json(["status" => false, "message" => "file_doesnt_exist"], 500);
    }

    /**
     * Searches standard list of MIME file types.
     *
     * @return array - The MIME types
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
     * @return array - The MIME types
     */
    public static function getMimeTypesClean() {
        $types=array();
        foreach(@explode("\n",@file_get_contents('http://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types'))as $x)
            if(isset($x[0])&&$x[0]!=='#'&&preg_match_all('#([^\s]+)#',$x,$out)&&isset($out[1])&&($c=count($out[1]))>1)
                for($i=1;$i<$c;$i++)
                    $types[$out[1][0]]=$out[1][0];
        return $types;
    }

    /**
     * Gets the resource file by byte range. Primarily allows for video streaming.
     *
     * @param  string $file - The file to grab
     */
    private static function rangeDownload($file){
        $fp = @fopen($file, 'rb');

        $size   = filesize($file); // File size
        $length = $size;           // Content length
        $start  = 0;               // Start byte
        $end    = $size - 1;       // End byte
        // Now that we've gotten so far without errors we send the accept range header
        /* At the moment we only support single ranges.
         * Multiple ranges requires some more work to ensure it works correctly
         * and comply with the spesifications: http://www.w3.org/Protocols/rfc2616/rfc2616-sec19.html#sec19.2
         *
         * Multirange support annouces itself with:
         * header('Accept-Ranges: bytes');
         *
         * Multirange content must be sent with multipart/byteranges mediatype,
         * (mediatype = mimetype)
         * as well as a boundry header to indicate the various chunks of data.
         */
        header("Accept-Ranges: 0-$length");
        // header('Accept-Ranges: bytes');
        // multipart/byteranges
        // http://www.w3.org/Protocols/rfc2616/rfc2616-sec19.html#sec19.2
        if (isset($_SERVER['HTTP_RANGE'])){
            $c_start = $start;
            $c_end   = $end;

            // Extract the range string
            list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
            // Make sure the client hasn't sent us a multibyte range
            if (strpos($range, ',') !== false){
                // (?) Shoud this be issued here, or should the first
                // range be used? Or should the header be ignored and
                // we output the whole content?
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes $start-$end/$size");
                // (?) Echo some info to the client?
                exit;
            } // fim do if
            // If the range starts with an '-' we start from the beginning
            // If not, we forward the file pointer
            // And make sure to get the end byte if spesified
            if ($range[0] == '-'){
                // The n-number of the last bytes is requested
                $c_start = $size - substr($range, 1);
            } else {
                $range  = explode('-', $range);
                $c_start = $range[0];
                $c_end   = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size;
            } // fim do if
            /* Check the range and make sure it's treated according to the specs.
             * http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
             */
            // End bytes can not be larger than $end.
            $c_end = ($c_end > $end) ? $end : $c_end;
            // Validate the requested range and return an error if it's not correct.
            if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size){
                header('HTTP/1.1 416 Requested Range Not Satisfiable');
                header("Content-Range: bytes $start-$end/$size");
                // (?) Echo some info to the client?
                exit;
            } // fim do if

            $start  = $c_start;
            $end    = $c_end;
            $length = $end - $start + 1; // Calculate new content length
            fseek($fp, $start);
            header('HTTP/1.1 206 Partial Content');
        } // fim do if

        // Notify the client the byte range we'll be outputting
        header("Content-Range: bytes $start-$end/$size");
        header("Content-Length: $length");

        // Start buffered download
        $buffer = 1024 * 8;
        while(!feof($fp) && ($p = ftell($fp)) <= $end){
            if ($p + $buffer > $end){
                // In case we're only outputtin a chunk, make sure we don't
                // read past the length
                $buffer = $end - $p + 1;
            } // fim do if

            set_time_limit(0); // Reset time limit for big files
            echo fread($fp, $buffer);
            flush(); // Free up memory. Otherwise large files will trigger PHP's memory limit.
        } // fim do while

        fclose($fp);
    }
}
