<?php namespace App\KoraFields;

use Illuminate\Support\Str;
use Illuminate\Http\Request;

class GalleryField extends FileTypeField {

    /*
    |--------------------------------------------------------------------------
    | Gallery Field
    |--------------------------------------------------------------------------
    |
    | This model represents the gallery field in Kora3
    |
    | NOTE: Because of caption data associated with the gallery field, some
    | parent functions are overwritten.
    |
    */

    /**
     * @var string - Views for the typed field options
     */
    const FIELD_OPTIONS_VIEW = "partials.fields.options.gallery";
    const FIELD_ADV_OPTIONS_VIEW = "partials.fields.advanced.gallery";
    const FIELD_ADV_INPUT_VIEW = null;
    const FIELD_INPUT_VIEW = "partials.records.input.gallery";
    const FIELD_DISPLAY_VIEW = "partials.records.display.gallery";

    /**
     * @var array - Supported file types in this field
     */
    const SUPPORTED_TYPES = ['image/jpeg','image/gif','image/png'];

    /**
     * Get the field options view.
     *
     * @return string - The view
     */
    public function getFieldOptionsView() {
        return self::FIELD_OPTIONS_VIEW;
    }

    /**
     * Get the field options view for advanced field creation.
     *
     * @return string - The view
     */
    public function getAdvancedFieldOptionsView() {
        return self::FIELD_ADV_OPTIONS_VIEW;
    }

    /**
     * Get the field input view for advanced field search.
     *
     * @return string - The view
     */
    public function getAdvancedSearchInputView() {
        return self::FIELD_ADV_INPUT_VIEW;
    }

    /**
     * Get the field input view for record creation.
     *
     * @return string - The view
     */
    public function getFieldInputView() {
        return self::FIELD_INPUT_VIEW;
    }

    /**
     * Get the field input view for record creation.
     *
     * @return string - The view
     */
    public function getFieldDisplayView() {
        return self::FIELD_DISPLAY_VIEW;
    }

    /**
     * Gets the default options string for a new field.
     *
     * @param  Request $request
     * @return array - The default options
     */
    public function getDefaultOptions($type = null) {
        return ['FieldSize' => null, 'MaxFiles' => null, 'FileTypes' => self::SUPPORTED_TYPES];
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
        $flid = $field['flid'];
        $captions = !is_null($request->input('file_captions'.$flid)) ? $request->input('file_captions'.$flid) : null;

        //Do the main stuff
        $files = parent::processRecordData($field, $value, $request);

        if(!is_null($files)) {
            //Add the captions
            $files = json_decode($files,true);
            foreach($files as $index => $file) {
                if(!is_null($captions) && isset($captions[$index]))
                    $files[$index]['caption'] = $captions[$index];
            }
            $files = json_encode($files);
        }

        return $files;
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
            $return .= "<div>".$file['original_name']."</div>";
            $return .= "<div>".$file['caption']."</div>";
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
        $originRid = $request->originRid;

        //See where we are looking first
        if(is_null($originRid))
            $currDir = storage_path('app/tmpFiles/impU' . \Auth::user()->id);
        else
            $currDir = storage_path('app/tmpFiles/impU' . \Auth::user()->id . '/' . $originRid);

        //Make destination directory
        $newDir = storage_path('app/tmpFiles/recordU' . \Auth::user()->id);
        if(!file_exists($newDir))
            mkdir($newDir, 0775, true);

        $value = explode(' | ', $value);

        foreach($value as $file) {
            $blob = explode(' [CAPTION] ', $file);
            $file = $caption = '';

            if (count($blob) == 2) {
                list($file, $caption) = $blob;
            } else {
                $file = $blob[0];
            }

            //move file from imp temp to tmp files
            if (!copy($currDir . '/' . $file, $newDir . '/' . $file)) {
                return response()->json(["status"=>false,"message"=>"json_validation_error",
                    "record_validation_error"=>[$request->kid => "$flid is missing name for a file"]],500);
            } else {
                //add input for this file
                array_push($files, $file);
                array_push($captions, $caption);
            }
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
     * @param  bool $simple - Is this a simple xml field value
     *
     * @return Request - Processed data
     */
    public function processImportDataXML($flid, $field, $value, $request, $simple = false) {
        $files = array();
        $originRid = $request->originRid;

        //See where we are looking first
        if(is_null($originRid))
            $currDir = storage_path( 'app/tmpFiles/impU' . \Auth::user()->id);
        else
            $currDir = storage_path('app/tmpFiles/impU' . \Auth::user()->id . '/' . $originRid);

        //Make destination directory
        $newDir = storage_path('app/tmpFiles/recordU' . \Auth::user()->id);
        if(file_exists($newDir)) {
            foreach(new \DirectoryIterator($newDir) as $file) {
                if($file->isFile())
                    unlink($newDir . '/' . $file->getFilename());
            }
        } else {
            mkdir($newDir, 0775, true);
        }

        if($simple) {
            $name = (string)$value;
            //move file from imp temp to tmp files
            if(!file_exists($currDir . '/' . $name)) {
                //Before we fail, let's see first if it's just failing because the originRid was specified
                // and not because the file doesn't actually exist. We will now force look into the ZIPs root folder
                $currDir = storage_path( 'app/tmpFiles/impU' . \Auth::user()->id);
                if(!file_exists($currDir . '/' . $name))
                    return response()->json(["status" => false, "message" => "xml_validation_error",
                        "record_validation_error" => [$request->kid => "$flid: trouble finding file $name"]], 500);
            }
            copy($currDir . '/' . $name, $newDir . '/' . $name);
            //add input for this file
            array_push($files, $name);
        } else {
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
                copy($currDir . '/' . $name, $newDir . '/' . $name);
                //add input for this file
                array_push($files, ['original_name' => $name, 'caption' => $value->Caption]);
            }
        }

        $request[$flid] = $files;

        return $request;
    }

    /**
     * Formats data for XML record display.
     *
     * @param  string $field - Field ID
     * @param  string $value - Data to format
     *
     * @return mixed - Processed data
     */
    public function processXMLData($field, $value) { //TODO::CASTLE
        //Same as parent but with captions
    }

    /**
     * For a test record, add test data to field.
     *
     * @param  string $url - Url for File Type Fields
     * @return mixed - The data
     */
    public function getTestData($url = null) {
        $types = self::getMimeTypes();
        if(!array_key_exists('jpeg', $types))
            $type = 'application/octet-stream';
        else
            $type = $types['jpeg'];

        $fileIDString = $url['flid'] . $url['rid'] . '_';
        $newName = $fileIDString.'image.jpeg';

        //Hash the file
        $checksum = hash_file('sha256', public_path('assets/testFiles/image.jpeg'));

        $file = [
            'original_name' => 'image.jpeg',
            'local_name' => $newName,
            'caption' => 'Mountain peaking through the clouds.',
            'url' => url('files').'/'.$newName,
            'size' => 154491,
            'type' => $type,
            'checksum' => $checksum //TODO:: eventually hardcode this
        ];

        $storageType = 'LaravelStorage'; //TODO:: make this a config once we actually support other storage types
        switch($storageType) {
            case 'LaravelStorage':
                $newPath = storage_path('app/files/' . $url['pid'] . '/' . $url['fid'] . '/' . $url['rid']);
                mkdir($newPath, 0775, true);
                copy(public_path('assets/testFiles/image.jpeg'),
                    $newPath . '/'.$newName);
                break;
            default:
                break;
        }

        return json_encode([$file]);
    }

    /**
     * Provides an example of the field's structure in an export to help with importing records.
     *
     * @param  string $slug - Field nickname
     * @param  string $expType - Type of export
     * @return mixed - The example
     */
    public function getExportSample($slug, $type) { //TODO::CASTLE
        switch($type) {
            case "XML":
                $xml = '<'.$slug.'>';
                $xml .= '<File>';
                $xml .= '<Name>' . utf8_encode('FILENAME 1') . '</Name>';
                $xml .= '</File>';
                $xml .= '<File>';
                $xml .= '<Name>' . utf8_encode('FILENAME 2') . '</Name>';
                $xml .= '<Caption>' . utf8_encode('Example of one that has a caption!') . '</Caption>';
                $xml .= '</File>';
                $xml .= '<File>';
                $xml .= '<Name>' . utf8_encode('so on...') . '</Name>';
                $xml .= '</File>';
                $xml .= '</' . $slug . '>';

                $xml .= '<' . $slug . ' simple="simple">';
                $xml .= utf8_encode('FILENAME');
                $xml .= '</' . $slug . '>';

                return $xml;
                break;
            case "JSON":
                $fieldArray = [];

                $fileArray = [];
                $fileArray['name'] = 'FILENAME 1';
                $fieldArray[$slug]['value'][] = $fileArray;

                $fileArray = [];
                $fileArray['name'] = 'FILENAME2';
                $fileArray['caption'] = 'Example of one that has a caption!';
                $fieldArray[$slug][] = $fileArray;

                $fileArray = [];
                $fileArray['name'] = 'so on...';
                $fieldArray[$slug][] = $fileArray;

                return $fieldArray;
                break;
        }
    }

    ///////////////////////////////////////////////END ABSTRACT FUNCTIONS///////////////////////////////////////////////

    /**
     * Returns default mime list, if file types not saved in field options.
     *
     * @param  int $flid - File field that record file will be loaded to
     * @return array - The list
     */
    public function getDefaultMIMEList() {
        return self::SUPPORTED_TYPES;
    }
}
