<?php namespace App\KoraFields;

use App\Form;
use App\Record;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ModelField extends FileTypeField {

    /*
    |--------------------------------------------------------------------------
    | Model Field
    |--------------------------------------------------------------------------
    |
    | This model represents the 3d-model field in Kora3
    |
    */

    /**
     * @var string - Views for the typed field options
     */
    const FIELD_OPTIONS_VIEW = "partials.fields.options.3dmodel";
    const FIELD_ADV_OPTIONS_VIEW = "partials.fields.advanced.3dmodel";
    const FIELD_ADV_INPUT_VIEW = null;
    const FIELD_INPUT_VIEW = "partials.records.input.3dmodel";
    const FIELD_DISPLAY_VIEW = "partials.records.display.3dmodel";

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
     * Gets the default options string for a new field.
     *
     * @return array - The default options
     */
    public function getDefaultOptions($type = null) {
        return ['FieldSize' => '', 'MaxFiles' => '', 'FileTypes' => ['obj','stl','application/octet-stream','image/jpeg','image/png'],
            'ModelColor' => '#ddd', 'BackColorOne' => '#2E4F5E', 'BackColorTwo' => '#152730'];
    }

    /**
     * Update the options for a field
     *
     * @param  array $field - Field to update options
     * @param  Request $request
     * @param  int $flid - The field internal name
     * @return array - The updated field array
     */
    public function updateOptions($field, Request $request, $flid = null) {
        if($request->filesize==0)
            $request->filesize = null;
        if($request->maxfiles==0)
            $request->maxfiles = null;

        $field['default'] = $request->default;
        $field['options']['FieldSize'] = $request->filesize;
        $field['options']['MaxFiles'] = $request->maxfiles;
        $field['options']['FileTypes'] = isset($request->filetype) ? $request->filetype : [];
        $field['options']['ModelColor'] = $request->color;
        $field['options']['BackColorOne'] = $request->backone;
        $field['options']['BackColorTwo'] = $request->backtwo;

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

        if($req==1 | $forceReq) {
            if(glob(storage_path('app/tmpFiles/' . $value . '/*.*')) == false)
                return [$flid => $field['name'].' is required'];
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
        $uid = Auth::user()->id;
        $tmpPath = 'app/tmpFiles/recordU' . $uid;
        if(glob(storage_path($tmpPath.'/*.*')) != false) {
            $files = [];
            $infoArray = array();
            $newPath = storage_path('app/files/' . $request->pid . '/' . $request->fid . '/' . $request->rid);
            $dataURL = $request->pid . '/' . $request->fid . '/' . $request->rid . '/';

            if(!file_exists($newPath))
                mkdir($newPath, 0775, true);
            if(file_exists(storage_path($tmpPath))) {
                $types = self::getMimeTypes();
                foreach(new \DirectoryIterator(storage_path($tmpPath)) as $file) {
                    if($file->isFile()) {
                        if(!array_key_exists($file->getExtension(), $types))
                            $type = 'application/octet-stream';
                        else
                            $type = $types[$file->getExtension()];
                        $info = ['name' => $file->getFilename(), 'size' => $file->getSize(), 'type' => $type,
                            'url' => $dataURL.urlencode($file->getFilename())];
                        $infoArray[$file->getFilename()] = $info;
                        if(isset($request->mass_creation_num)) {
                            copy(storage_path($tmpPath . '/' . $file->getFilename()),
                                $newPath . '/' . $file->getFilename());
                        } else {
                            rename(storage_path($tmpPath . '/' . $file->getFilename()),
                                $newPath . '/' . $file->getFilename());
                        }
                    }
                }
                foreach($value as $index => $fName) {
                    $files[] = $infoArray[$fName];
                }
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
    public function processImportData($flid, $field, $value, $request) {
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

        foreach($value as $file) {
            if(!isset($file['name']))
                return response()->json(["status"=>false,"message"=>"json_validation_error",
                    "record_validation_error"=>[$request->kid => "$flid is missing name for a file"]],500);
            $name = $file['name'];
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
                array_push($files, $name);
            }
        }

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
     *
     * @return mixed - Processed data
     */
    public function processXMLData($field, $value) {
        $files = json_decode($value,true);
        $xml = "<$field>";
        foreach($files as $file) {
            $xml .= '<File>'.$file['name'].'</File>';
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
        return null;
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
     * For a test record, add test data to field.
     *
     * @param  string $url - Url for File Type Fields
     * @return mixed - The data
     */
    public function getTestData($url = null) {
        $newPath = storage_path('app/files/'.$url);

        if(!file_exists($newPath))
            mkdir($newPath, 0775, true);

        $types = self::getMimeTypes();
        if(!array_key_exists('stl', $types))
            $type = 'application/octet-stream';
        else
            $type = $types['stl'];

        $file = [
            'name' => 'model.stl',
            'url' => $url,
            'size' => 9484,
            'type' => $type
        ];

        copy(public_path('assets/testFiles/model.stl'),
            $newPath . '/model.stl');

        return json_encode([$file]);
    }

    /**
     * Provides an example of the field's structure in an export to help with importing records.
     *
     * @param  string $slug - Field nickname
     * @param  string $expType - Type of export
     * @return mixed - The example
     */
    public function getExportSample($slug, $type) {
        switch($type) {
            case "XML":
                $xml = '<'.$slug.'>';
                $xml .= '<File>';
                $xml .= '<Name>' . utf8_encode('FILENAME 1') . '</Name>';
                $xml .= '</File>';
                $xml .= '<File>';
                $xml .= '<Name>' . utf8_encode('FILENAME 2') . '</Name>';
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
                $fieldArray[$slug][] = $fileArray;

                $fileArray = [];
                $fileArray['name'] = 'so on...';
                $fieldArray[$slug][] = $fileArray;

                return $fieldArray;
                break;
        }
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
    public function keywordSearchTyped($flid, $arg, $recordMod, $negative = false) { //TODO::CASTLE
        if($negative)
            $param = 'NOT LIKE';
        else
            $param = 'LIKE';

        $value = $recordMod->newQuery()
            ->select("id")
            ->whereJsonContains($flid, $arg)
            ->pluck('id')
            ->toArray();
        dd($value);
    }

    /**
     * Updates the request for an API search to mimic the advanced search structure.
     *
     * @param  array $data - Data from the search
     * @param  int $flid - Field ID
     * @param  Request $request
     * @return Request - The update request
     */
    public function setRestfulAdvSearch($data, $flid, $request) {
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
    public function advancedSearchTyped($flid, $query, $recordMod, $negative = false) {
        return null;
    }
}
