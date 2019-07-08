<?php namespace App\KoraFields;

use App\Record;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class GalleryField extends FileTypeField {

    /*
    |--------------------------------------------------------------------------
    | Gallery Field
    |--------------------------------------------------------------------------
    |
    | This model represents the gallery field in kora
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
            $return .= "<div>".$file['name']."</div>";
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

        if(isset($request->userId))
            $subpath = $request->userId;
        else
            $subpath = \Auth::user()->id;

        $currDir = storage_path( 'app/tmpFiles/impU' . $subpath);

        //Make destination directory
        $newDir = storage_path('app/tmpFiles/recordU' . $subpath);
        if(!file_exists($newDir))
            mkdir($newDir, 0775, true);

        foreach($value as $file) {
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
        $newDir = storage_path('app/tmpFiles/recordU' . \Auth::user()->id);
        if(!file_exists($newDir))
            mkdir($newDir, 0775, true);

        if(empty($value->File))
            return response()->json(["status"=>false,"message"=>"xml_validation_error",
                "record_validation_error"=>[$request->kid => "$flid format is incorrect for a File Type Field"]],500);
        foreach($value->File as $file) {
            $pathname = (string)$file->Name;
            $parts = explode('/',$pathname);
            $name = end($parts);
            var_dump($name);
            //move file from imp temp to tmp files
            if(!file_exists($currDir . '/' . $pathname))
                return response()->json(["status" => false, "message" => "xml_validation_error",
                    "record_validation_error" => [$request->kid => "$flid: trouble finding file $name"]], 500);
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
        $newDir = storage_path('app/tmpFiles/recordU' . $subpath);
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

        if($negative) {
            $dbQuery->whereRaw("`$flid`->\"$[*].original_name\" $param \"$arg\"");
            $dbQuery->whereRaw("`$flid`->\"$[*].caption\" $param \"$arg\"");
        } else {
            $dbQuery->orWhereRaw("`$flid`->\"$[*].original_name\" $param \"$arg\"");
            $dbQuery->orWhereRaw("`$flid`->\"$[*].caption\" $param \"$arg\"");
        }

        return $dbQuery->pluck('id')
            ->toArray();
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
