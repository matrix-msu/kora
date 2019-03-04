<?php namespace App;

use App\Http\Controllers\FieldController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class PlaylistField extends FileTypeField  {

    /*
    |--------------------------------------------------------------------------
    | Playlist Field
    |--------------------------------------------------------------------------
    |
    | This model represents the playlist field in Kora3
    |
    */

    /**
     * @var string - Views for the typed field options
     */
    const FIELD_OPTIONS_VIEW = "partials.fields.options.playlist";
    const FIELD_ADV_OPTIONS_VIEW = "partials.fields.advanced.playlist";
    const FIELD_ADV_INPUT_VIEW = "partials.records.advanced.filetype";
    const FIELD_INPUT_VIEW = "partials.records.input.playlist";
    const FIELD_DISPLAY_VIEW = "partials.records.display.playlist";

    /**
     * @var string - Data column used for sort
     */
    const SORT_COLUMN = null;

    /**
     * @var array - Attributes that can be mass assigned to model
     */
    protected $fillable = [
        'rid',
        'flid',
        'audio'
    ];

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
     * Get the field options view for advanced field creation.
     *
     * @return string - Column name
     */
    public function getSortColumn() {
        return self::SORT_COLUMN;
    }

    /**
     * Gets the default options string for a new field.
     *
     * @param  Request $request
     * @return string - The default options
     */
    public function getDefaultOptions(Request $request) {
        return '[!FieldSize!][!FieldSize!][!MaxFiles!][!MaxFiles!][!FileTypes!]audio/mp3[!]audio/wav[!]audio/ogg[!FileTypes!]';
    }

    /**
     * Gets an array of all the fields options.
     *
     * @param  Field $field
     * @return array - The options array
     */
    public function getOptionsArray(Field $field) {
        $options = array();

        $options['FieldFileSize'] = FieldController::getFieldOption($field, 'FieldSize');
        $options['MaxFileAmount'] = FieldController::getFieldOption($field, 'MaxFiles');
        $options['AllowedFileTypes'] = explode('[!]',FieldController::getFieldOption($field, 'FileTypes'));

        return $options;
    }

    /**
     * Update the options for a field
     *
     * @param  Field $field - Field to update options
     * @param  Request $request
     * @return Redirect
     */
    public function updateOptions($field, Request $request) {
        $filetype = $request->filetype[0];
        for($i=1;$i<sizeof($request->filetype);$i++) {
            $filetype .= '[!]'.$request->filetype[$i];
        }


        $field->updateRequired($request->required);
        $field->updateSearchable($request);
        $field->updateOptions('FieldSize', $request->filesize);
        $field->updateOptions('MaxFiles', $request->maxfiles);
        $field->updateOptions('FileTypes', $filetype);

        return redirect('projects/' . $field->pid . '/forms/' . $field->fid . '/fields/' . $field->flid . '/options')
            ->with('k3_global_success', 'field_options_updated');
    }

    /**
     * Creates a typed field to store record data.
     *
     * @param  Field $field - The field to represent record data
     * @param  Record $record - Record being created
     * @param  string $value - Data to add
     * @param  Request $request
     */
    public function createNewRecordField($field, $record, $value, $request){
        if(glob(storage_path('app/tmpFiles/' . $value . '/*.*')) != false) {
            $this->flid = $field->flid;
            $this->rid = $record->rid;
            $this->fid = $field->fid;
            $infoString = '';
            $infoArray = array();
            $newPath = storage_path('app/files/p' . $field->pid . '/f' . $field->fid . '/r' . $record->rid . '/fl' . $field->flid);
            if(!file_exists($newPath))
                mkdir($newPath, 0775, true);
            if(file_exists(storage_path('app/tmpFiles/' . $value))) {
                $types = self::getMimeTypes();
                foreach(new \DirectoryIterator(storage_path('app/tmpFiles/' . $value)) as $file) {
                    if($file->isFile()) {
                        if(!array_key_exists($file->getExtension(), $types))
                            $type = 'application/octet-stream';
                        else
                            $type = $types[$file->getExtension()];
                        $info = '[Name]' . $file->getFilename() . '[Name][Size]' . $file->getSize() . '[Size][Type]' . $type . '[Type]';
                        $infoArray[$file->getFilename()] = $info;
                        if(isset($request->mass_creation_num))
                            copy(storage_path('app/tmpFiles/' . $value . '/' . $file->getFilename()),
                                $newPath . '/' . $file->getFilename());
                        else
                            rename(storage_path('app/tmpFiles/' . $value . '/' . $file->getFilename()),
                            $newPath . '/' . $file->getFilename());
                    }
                }
                foreach($request->input('file'.$field->flid) as $fName) {
                    if($fName!='') {
                        if($infoString == '')
                            $infoString = $infoArray[$fName];
                        else
                            $infoString .= '[!]' . $infoArray[$fName];
                    }
                }
            }
            $this->audio = $infoString;
            $this->save();
        }
    }

    /**
     * Edits a typed field that has record data.
     *
     * @param  string $value - Data to add
     * @param  Request $request
     */
    public function editRecordField($value, $request) {
        if(glob(storage_path('app/tmpFiles/' . $value . '/*.*')) != false) {
            $pla_files_exist = false; // if this remains false, then the files were deleted and row should be removed from table

            //clear the old files before moving the update over
            //we only want to remove files that are being replaced by new versions
            //we keep old files around for revision purposes
            $newNames = array();
            //scan the tmpFile as these will be the "new ones"
            if(file_exists(storage_path('app/tmpFiles/' . $value))) {
                foreach(new \DirectoryIterator(storage_path('app/tmpFiles/' . $value)) as $file) {
                    array_push($newNames,$file->getFilename());
                }
            }
            //actually clear them
            $field = FieldController::getField($this->flid);
            foreach(new \DirectoryIterator(storage_path('app/files/p'.$field->pid.'/f'.$field->fid.'/r'.$this->rid.'/fl'.$field->flid)) as $file) {
                if($file->isFile() and in_array($file->getFilename(),$newNames))
                    unlink(storage_path('app/files/p'.$field->pid.'/f'.$field->fid.'/r'.$this->rid.'/fl'.$field->flid.'/'.$file->getFilename()));
            }
            //build new stuff
            $infoString = '';
            $infoArray = array();
            if(file_exists(storage_path('app/tmpFiles/' . $value))) {
                $types = self::getMimeTypes();
                foreach(new \DirectoryIterator(storage_path('app/tmpFiles/' . $value)) as $file) {
                    if($file->isFile()) {
                        if(!array_key_exists($file->getExtension(),$types))
                            $type = 'application/octet-stream';
                        else
                            $type =  $types[$file->getExtension()];
                        $info = '[Name]' . $file->getFilename() . '[Name][Size]' . $file->getSize() . '[Size][Type]' . $type . '[Type]';
                        $infoArray[$file->getFilename()] = $info;
                        rename(storage_path('app/tmpFiles/' . $value . '/' . $file->getFilename()),
                            storage_path('app/files/p'.$field->pid.'/f'.$field->fid.'/r'.$this->rid.'/fl'.$field->flid . '/' . $file->getFilename()));
                        $pla_files_exist = true;
                    }
                }
                foreach($request->input('file'.$field->flid) as $fName) {
                    if($fName!='') {
                        if($infoString == '')
                            $infoString = $infoArray[$fName];
                        else
                            $infoString .= '[!]' . $infoArray[$fName];
                    }
                }
            }
            $this->audio = $infoString;
            $this->save();

            if(!$pla_files_exist)
                $this->delete();
        } else {
            //DELETE THE FILES SINCE WE REMOVED THEM
            $field = FieldController::getField($this->flid);
            foreach(new \DirectoryIterator(storage_path('app/files/p'.$field->pid.'/f'.$field->fid.'/r'.$this->rid.'/fl'.$field->flid)) as $file) {
                if($file->isFile())
                    unlink(storage_path('app/files/p'.$field->pid.'/f'.$field->fid.'/r'.$this->rid.'/fl'.$field->flid.'/'.$file->getFilename()));
            }

            $this->delete();
        }
    }

    /**
     * Takes data from a mass assignment operation and applies it to an individual field.
     *
     * @param  Field $field - The field to represent record data
     * @param  String $formFieldValue - The value to be assigned
     * @param  Request $request
     * @param  bool $overwrite - Overwrite if data exists
     */
    public function massAssignRecordField($field, $formFieldValue, $request, $overwrite=0) {
        //We don't allow so do nothing
    }

    /**
     * Takes data from a mass assignment operation and applies it to an individual field for a record subset.
     *
     * @param  Field $field - The field to represent record data
     * @param  String $formFieldValue - The value to be assigned
     * @param  Request $request
     * @param  array $rids - Overwrite if data exists
     */
    public function massAssignSubsetRecordField($field, $formFieldValue, $request, $rids) {
        //We don't allow so do nothing
    }

    /**
     * For a test record, add test data to field.
     *
     * @param  Field $field - The field to represent record data
     * @param  Record $record - Test record being created
     */
    public function createTestRecordField($field, $record) {
        $this->flid = $field->flid;
        $this->rid = $record->rid;
        $this->fid = $field->fid;
        $infoArray = array();

        $newPath = storage_path('app/files/p' . $field->pid . '/f' . $field->fid . '/r' . $record->rid . '/fl' . $field->flid);
        mkdir($newPath, 0775, true);

        $types = self::getMimeTypes();
        if(!array_key_exists('mp3', $types))
            $type = 'application/octet-stream';
        else
            $type = $types['mp3'];
        $info = '[Name]kora.mp3[Name][Size]372001[Size][Type]' . $type . '[Type]';
        $infoArray['kora.mp3'] = $info;
        copy(public_path('assets/testFiles/kora.mp3'),
            $newPath . '/kora.mp3');

        $infoString = implode('[!]',$infoArray);
        $this->audio = $infoString;
        $this->save();
    }

    /**
     * Validates the record data for a field against the field's options.
     *
     * @param  Field $field - The field to validate
     * @param  Request $request
     * @param  bool $forceReq - Do we want to force a required value even if the field itself is not required?
     * @return array - Array of errors
     */
    public function validateField($field, $request, $forceReq = false) {
        $req = $field->required;
        if(Auth::guest())
            $value = 'f'.$field->flid.'u'.$request['userId'];
        else
            $value = 'f'.$field->flid.'u'.Auth::user()->id;

        if($req==1 | $forceReq) {
            if(glob(storage_path('app/tmpFiles/' . $value . '/*.*')) == false)
                return [$field->flid => $field->name.' is required'];
        }

        return array();
    }

    /**
     * Performs a rollback function on an individual field's record data.
     *
     * @param  Field $field - The field being rolled back
     * @param  Revision $revision - The revision being rolled back
     * @param  bool $exists - Field for record exists
     */
    public function rollbackField($field, Revision $revision, $exists=true) {
        if(!is_array($revision->oldData))
            $revision->oldData = json_decode($revision->oldData, true);

        if(!isset($revision->oldData[Field::_PLAYLIST][$field->flid]['data']))
            return null;

        // If the field doesn't exist or was explicitly deleted, we create a new one.
        if($revision->type == Revision::DELETE || !$exists) {
            $this->flid = $field->flid;
            $this->fid = $revision->fid;
            $this->rid = $revision->rid;
        }

        $this->audio = $revision->oldData[Field::_PLAYLIST][$field->flid]['data'];
        $this->save();
    }

    /**
     * Get the arrayed version of the field data to store in a record preset.
     *
     * @param  array $data - The data array representing the record preset
     * @param  bool $exists - Typed field exists and has data
     * @return array - The updated $data
     */
    public function getRecordPresetArray($data, $exists=true) {
        if($exists)
            $data['audio'] = explode('[!]', $this->audio);
        else
            $data['audio'] = null;

        return $data;
    }

    /**
     * Get the required information for a revision data array.
     *
     * @param  Field $field - Optional field to get storage options for certain typed fields
     * @return mixed - The revision data
     */
    public function getRevisionData($field = null) {
        return $this->audio;
    }

    /**
     * Provides an example of the field's structure in an export to help with importing records.
     *
     * @param  string $slug - Field nickname
     * @param  string $expType - Type of export
     * @return mixed - The example
     */
    public function getExportSample($slug,$type) {
        switch($type) {
            case "XML":
                $xml = '<' . Field::xmlTagClear($slug) . ' type="Playlist">';
                $xml .= '<File>';
                $xml .= '<Name>' . utf8_encode('FILENAME 1') . '</Name>';
                $xml .= '</File>';
                $xml .= '<File>';
                $xml .= '<Name>' . utf8_encode('FILENAME 2') . '</Name>';
                $xml .= '</File>';
                $xml .= '<File>';
                $xml .= '<Name>' . utf8_encode('so on...') . '</Name>';
                $xml .= '</File>';
                $xml .= '</' . Field::xmlTagClear($slug) . '>';

                $xml .= '<' . Field::xmlTagClear($slug) . ' type="Playlist" simple="simple">';
                $xml .= utf8_encode('FILENAME');
                $xml .= '</' . Field::xmlTagClear($slug) . '>';

                return $xml;
                break;
            case "JSON":
                $fieldArray = [$slug => ['type' => 'Playlist']];

                $fileArray = [];
                $fileArray['name'] = 'FILENAME 1';
                $fieldArray[$slug]['value'][] = $fileArray;

                $fileArray = [];
                $fileArray['name'] = 'FILENAME2';
                $fieldArray[$slug]['value'][] = $fileArray;

                $fileArray = [];
                $fileArray['name'] = 'so on...';
                $fieldArray[$slug]['value'][] = $fileArray;

                return $fieldArray;
                break;
        }
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
        $request->request->add([$flid.'_input' => $data->input]);

        return $request;
    }

    /**
     * Updates the request for an API to mimic record creation .
     *
     * @param  array $jsonField - JSON representation of field data
     * @param  int $flid - Field ID
     * @param  Request $recRequest
     * @param  int $uToken - Custom generated user token for file fields and tmp folders
     * @return Request - The update request
     */
    public function setRestfulRecordData($jsonField, $flid, $recRequest, $uToken=null) {
        $files = array();
        $currDir = storage_path('app/tmpFiles/impU' . $uToken);
        $newDir = storage_path('app/tmpFiles/f' . $flid . 'u' . $uToken);
        if(file_exists($newDir)) {
            foreach(new \DirectoryIterator($newDir) as $file) {
                if($file->isFile())
                    unlink($newDir . '/' . $file->getFilename());
            }
        } else {
            mkdir($newDir, 0775, true);
        }
        foreach($jsonField->value as $file) {
            $name = $file->name;
            //move file from imp temp to tmp files
            copy($currDir . '/' . $name, $newDir . '/' . $name);
            //add input for this file
            array_push($files, $name);
        }
        $recRequest['file' . $flid] = $files;
        $recRequest[$flid] = 'f' . $flid . 'u' . $uToken;

        return $recRequest;
    }

    /**
     * Performs a keyword search on this field and returns any results.
     *
     * @param  int $flid - Field ID
     * @param  string $arg - The keywords
     * @return array - The RIDs that match search
     */
    public function keywordSearchTyped($flid, $arg) {
        return DB::table("playlist_fields")
            ->select("rid")
            ->where("flid", "=", $flid)
            ->where('audio','LIKE',"%$arg%")
            ->distinct()
            ->pluck('rid')
            ->toArray();
    }

    /**
     * Performs an advanced search on this field and returns any results.
     *
     * @param  int $flid - Field ID
     * @param  array $query - The advance search user query
     * @return array - The RIDs that match search
     */
    public function advancedSearchTyped($flid, $query) {
        $arg = $query[$flid."_input"];
        $arg = Search::prepare($arg);

        return DB::table("playlist_fields")
            ->select("rid")
            ->where("flid", "=", $flid)
            ->where('audio','LIKE',"%$arg%")
            ->distinct()
            ->pluck('rid')
            ->toArray();
    }

    ///////////////////////////////////////////////END ABSTRACT FUNCTIONS///////////////////////////////////////////////
}