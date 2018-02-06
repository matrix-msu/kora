<?php namespace App;

use App\Http\Controllers\FieldController;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class GalleryField extends FileTypeField  {

    /*
    |--------------------------------------------------------------------------
    | Gallery Field
    |--------------------------------------------------------------------------
    |
    | This model represents the gallery field in Kora3
    |
    */

    /**
     * @var string - Views for the typed field options
     */
    const FIELD_OPTIONS_VIEW = "partials.fields.options.gallery";
    const FIELD_ADV_OPTIONS_VIEW = "partials.field_option_forms.gallery";

    /**
     * @var array - Attributes that can be mass assigned to model
     */
    protected $fillable = [
        'rid',
        'flid',
        'images'
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
     * Gets the default options string for a new field.
     *
     * @param  Request $request
     * @return string - The default options
     */
    public function getDefaultOptions(Request $request) {
        return '[!FieldSize!]0[!FieldSize!][!ThumbSmall!]150x150[!ThumbSmall!][!ThumbLarge!]300x300[!ThumbLarge!][!MaxFiles!]0[!MaxFiles!][!FileTypes!][!FileTypes!]';
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

        if($request->filesize=='')
            $request->filesize = 0;

        if($request->maxfiles=='')
            $request->maxfiles = 0;

        $sx = $request->small_x;
        $sy = $request->small_y;
        if($sx=='')
            $sx = 150;
        if($sy=='')
            $sy = 150;
        $small = $sx.'x'.$sy;

        $lx = $request->large_x;
        $ly = $request->large_y;
        if($lx=='')
            $lx = 300;
        if($ly=='')
            $ly = 300;
        $large = $lx.'x'.$ly;

        $field->updateRequired($request->required);
        $field->updateSearchable($request);
        $field->updateOptions('FieldSize', $request->filesize);
        $field->updateOptions('MaxFiles', $request->maxfiles);
        $field->updateOptions('FileTypes', $filetype);
        $field->updateOptions('ThumbSmall', $small);
        $field->updateOptions('ThumbLarge', $large);

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
    public function createNewRecordField($field, $record, $value, $request) {
        if(glob(config('app.base_path') . 'storage/app/tmpFiles/' . $value . '/*.*') != false) {
            $this->flid = $field->flid;
            $this->rid = $record->rid;
            $this->fid = $field->fid;
            $infoString = '';
            $infoArray = array();
            $newPath = config('app.base_path') . 'storage/app/files/p' . $field->pid . '/f' . $field->fid . '/r' . $record->rid . '/fl' . $field->flid;
            //make the three directories
            mkdir($newPath, 0775, true);
            mkdir($newPath . '/thumbnail', 0775, true);
            mkdir($newPath . '/medium', 0775, true);
            if(file_exists(config('app.base_path') . 'storage/app/tmpFiles/' . $value)) {
                $types = self::getMimeTypes();
                foreach(new \DirectoryIterator(config('app.base_path') . 'storage/app/tmpFiles/' . $value) as $file) {
                    if($file->isFile()) {
                        if(!array_key_exists($file->getExtension(), $types))
                            $type = 'application/octet-stream';
                        else
                            $type = $types[$file->getExtension()];
                        $info = '[Name]' . $file->getFilename() . '[Name][Size]' . $file->getSize() . '[Size][Type]' . $type . '[Type]';
                        $infoArray[$file->getFilename()] = $info;
                        copy(config('app.base_path') . 'storage/app/tmpFiles/' . $value . '/' . $file->getFilename(),
                            $newPath . '/' . $file->getFilename());
                        copy(config('app.base_path') . 'storage/app/tmpFiles/' . $value . '/thumbnail/' . $file->getFilename(),
                            $newPath . '/thumbnail/' . $file->getFilename());
                        copy(config('app.base_path') . 'storage/app/tmpFiles/' . $value . '/medium/' . $file->getFilename(),
                            $newPath . '/medium/' . $file->getFilename());
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
            $this->images = $infoString;
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
        if(glob(config('app.base_path').'storage/app/tmpFiles/'.$value.'/*.*') != false) {
            $gal_files_exist = false; // if this remains false, then the files were deleted and row should be removed from table

            //clear the old files before moving the update over
            //we only want to remove files that are being replaced by new versions
            //we keep old files around for revision purposes
            $newNames = array();
            //scan the tmpFile as these will be the "new ones"
            if(file_exists(config('app.base_path') . 'storage/app/tmpFiles/' . $value)) {
                foreach(new \DirectoryIterator(config('app.base_path') . 'storage/app/tmpFiles/' . $value) as $file) {
                    array_push($newNames,$file->getFilename());
                }
            }
            //actually clear them
            $field = FieldController::getField($this->flid);
            foreach(new \DirectoryIterator(config('app.base_path').'storage/app/files/p'.$field->pid.'/f'.$field->fid.'/r'.$this->rid.'/fl'.$field->flid) as $file) {
                if($file->isFile() and in_array($file->getFilename(),$newNames)) {
                    unlink(config('app.base_path').'storage/app/files/p'.$field->pid.'/f'.$field->fid.'/r'.$this->rid.'/fl'.$field->flid.'/'.$file->getFilename());
                    unlink(config('app.base_path').'storage/app/files/p'.$field->pid.'/f'.$field->fid.'/r'.$this->rid.'/fl'.$field->flid.'/thumbnail/'.$file->getFilename());
                    unlink(config('app.base_path').'storage/app/files/p'.$field->pid.'/f'.$field->fid.'/r'.$this->rid.'/fl'.$field->flid.'/medium/'.$file->getFilename());
                }
            }
            //build new stuff
            $infoString = '';
            $infoArray = array();
            if(file_exists(config('app.base_path') . 'storage/app/tmpFiles/' . $value)) {
                $types = self::getMimeTypes();
                foreach(new \DirectoryIterator(config('app.base_path') . 'storage/app/tmpFiles/' . $value) as $file) {
                    if($file->isFile()) {
                        if(!array_key_exists($file->getExtension(),$types))
                            $type = 'application/octet-stream';
                        else
                            $type =  $types[$file->getExtension()];
                        $info = '[Name]' . $file->getFilename() . '[Name][Size]' . $file->getSize() . '[Size][Type]' . $type . '[Type]';
                        $infoArray[$file->getFilename()] = $info;
                        copy(config('app.base_path') . 'storage/app/tmpFiles/' . $value . '/' . $file->getFilename(),
                            config('app.base_path').'storage/app/files/p'.$field->pid.'/f'.$field->fid.'/r'.$this->rid.'/fl'.$field->flid . '/' . $file->getFilename());
                        copy(config('app.base_path') . 'storage/app/tmpFiles/' . $value . '/thumbnail/' . $file->getFilename(),
                            config('app.base_path').'storage/app/files/p'.$field->pid.'/f'.$field->fid.'/r'.$this->rid.'/fl'.$field->flid . '/thumbnail/' . $file->getFilename());
                        copy(config('app.base_path') . 'storage/app/tmpFiles/' . $value . '/medium/' . $file->getFilename(),
                            config('app.base_path').'storage/app/files/p'.$field->pid.'/f'.$field->fid.'/r'.$this->rid.'/fl'.$field->flid . '/medium/' . $file->getFilename());

                        $gal_files_exist = true;
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
            $this->images = $infoString;
            $this->save();

            if(!$gal_files_exist)
                $this->delete();
        }
    }

    /**
     * Takes data from a mass assignment operation and applies it to an individual field.
     *
     * @param  Field $field - The field to represent record data
     * @param  Record $record - Record being written to
     * @param  String $formFieldValue - The value to be assigned
     * @param  Request $request
     * @param  bool $overwrite - Overwrite if data exists
     */
    public function massAssignRecordField($field, $record, $formFieldValue, $request, $overwrite=0) {
        //TODO::mass assign?
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
        $maxfiles = FieldController::getFieldOption($field,'MaxFiles');
        if($maxfiles==0) {$maxfiles=1;}
        $newPath = config('app.base_path') . 'storage/app/files/p' . $field->pid . '/f' . $field->fid . '/r' . $record->rid . '/fl' . $field->flid;
        //make the three directories
        mkdir($newPath, 0775, true);
        mkdir($newPath . '/thumbnail', 0775, true);
        mkdir($newPath . '/medium', 0775, true);
        for($q=0;$q<$maxfiles;$q++) {
            $types = self::getMimeTypes();
            if(!array_key_exists('png', $types))
                $type = 'application/octet-stream';
            else
                $type = $types['png'];
            $info = '[Name]gallery' . $q . '.png[Name][Size]54827[Size][Type]' . $type . '[Type]';
            $infoArray['gallery' . $q . '.png'] = $info;
            copy(config('app.base_path') . 'public/testFiles/gallery.png',
                $newPath . '/gallery' . $q . '.png');
            copy(config('app.base_path') . 'public/testFiles/medium/gallery.png',
                $newPath . '/medium/gallery' . $q . '.png');
            copy(config('app.base_path') . 'public/testFiles/thumbnail/gallery.png',
                $newPath . '/thumbnail/gallery' . $q . '.png');
        }
        $infoString = implode('[!]',$infoArray);
        $this->images = $infoString;
        $this->save();
    }

    /**
     * Validates the record data for a field against the field's options.
     *
     * @param  Field $field - The
     * @param  mixed $value - Record data
     * @param  Request $request
     * @return string - Potential error message
     */
    public function validateField($field, $value, $request) {
        $req = $field->required;

        if($req==1) {
            if(glob(config('app.base_path').'storage/app/tmpFiles/'.$value.'/*.*') == false)
                return $field->name."_required";
        }

        return "field_validated";
    }

    /**
     * Performs a rollback function on an individual field's record data.
     *
     * @param  Field $field - The field being rolled back
     * @param  Revision $revision - The revision being rolled back
     * @param  bool $exists - Field for record exists
     */
    public function rollbackField($field, Revision $revision, $exists=true) {
        if(!is_array($revision->data))
            $revision->data = json_decode($revision->data, true);

        if(is_null($revision->data[Field::_GALLERY][$field->flid]['data']))
            return null;

        // If the field doesn't exist or was explicitly deleted, we create a new one.
        if($revision->type == Revision::DELETE || !$exists) {
            $this->flid = $field->flid;
            $this->fid = $revision->fid;
            $this->rid = $revision->rid;
        }

        $this->images = $revision->data[Field::_GALLERY][$field->flid]['data'];
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
            $data['images'] = explode('[!]', $this->images);
        else
            $data['images'] = null;

        return $data;
    }

    /**
     * Get the required information for a revision data array.
     *
     * @param  Field $field - Optional field to get storage options for certain typed fields
     * @return mixed - The revision data
     */
    public function getRevisionData($field = null) {
        return $this->images;
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
                $xml = '<' . Field::xmlTagClear($slug) . ' type="Gallery">';
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

                return $xml;
                break;
            case "JSON":
                $fieldArray = [$slug => ['type' => 'Gallery']];

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
        $currDir = config('app.base_path') . 'storage/app/tmpFiles/impU' . $uToken;
        $newDir = config('app.base_path') . 'storage/app/tmpFiles/f' . $flid . 'u' . $uToken;
        if(file_exists($newDir)) {
            foreach(new \DirectoryIterator($newDir) as $file) {
                if($file->isFile())
                    unlink($newDir . '/' . $file->getFilename());
            }
            if(file_exists($newDir . '/thumbnail')) {
                foreach(new \DirectoryIterator($newDir . '/thumbnail') as $file) {
                    if($file->isFile())
                        unlink($newDir . '/thumbnail/' . $file->getFilename());
                }
            }
            if(file_exists($newDir . '/medium')) {
                foreach(new \DirectoryIterator($newDir . '/medium') as $file) {
                    if($file->isFile())
                        unlink($newDir . '/medium/' . $file->getFilename());
                }
            }
        } else {
            mkdir($newDir, 0775, true);
            mkdir($newDir . '/thumbnail', 0775, true);
            mkdir($newDir . '/medium', 0775, true);
        }
        $field = FieldController::getField($flid);
        foreach($jsonField->files as $file) {
            $name = $file->name;
            //move file from imp temp to tmp files
            copy($currDir . '/' . $name, $newDir . '/' . $name);
            $smallParts = explode('x',FieldController::getFieldOption($field,'ThumbSmall'));
            $tImage = new \Imagick($newDir . '/' . $name);
            $tImage->thumbnailImage($smallParts[0],$smallParts[1],true);
            $tImage->writeImage($newDir . '/thumbnail/' . $name);
            $largeParts = explode('x',FieldController::getFieldOption($field,'ThumbLarge'));
            $mImage = new \Imagick($newDir . '/' . $name);
            $mImage->thumbnailImage($largeParts[0],$largeParts[1],true);
            $mImage->writeImage($newDir . '/medium/' . $name);
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
        return DB::table("gallery_fields")
            ->select("rid")
            ->where("flid", "=", $flid)
            ->whereRaw("MATCH (`images`) AGAINST (? IN BOOLEAN MODE)", [$arg])
            ->distinct()
            ->pluck('rid');
    }

    /**
     * Performs an advanced search on this field and returns any results.
     *
     * @param  int $flid - Field ID
     * @param  array $query - The advance search user query
     * @return Builder - The RIDs that match search
     */
    public function getAdvancedSearchQuery($flid, $query) {
        $processed = $query[$flid."_input"]. "*[Name]";

        return DB::table("gallery_fields")
            ->select("rid")
            ->where("flid", "=", $flid)
            ->whereRaw("MATCH (`images`) AGAINST (? IN BOOLEAN MODE)", [$processed])
            ->distinct();
    }

    ///////////////////////////////////////////////END ABSTRACT FUNCTIONS///////////////////////////////////////////////

    /**
     * Gets the image associated with the Gallery Field of a particular record.
     *
     * @param  int $pid - Project ID
     * @param  string $filename - Name of image file
     * @param  string $type - Get either the full image or a thumbnail of the image
     * @return string - html for the file download
     */
    public function getImgDisplay($pid, $filename, $type) {
        if($type == 'thumbnail' | $type == 'medium')
            $file_path = config('app.base_path').'storage/app/files/p'.$pid.'/f'.$this->fid.'/r'.$this->rid.'/fl'.$this->flid.'/'.$type.'/'. $filename;
        else
            $file_path = config('app.base_path').'storage/app/files/p'.$pid.'/f'.$this->fid.'/r'.$this->rid.'/fl'.$this->flid . '/' . $filename;

        if(file_exists($file_path)) {
            // Send Download
            return response()->download($file_path, $filename, [
                'Content-Length: '. filesize($file_path)
            ]);
        } else {
            // Error
            return response()->json(["status"=>false,"message"=>"file_doesnt_exist"],500);
        }
    }
}
