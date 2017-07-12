<?php namespace App;

use App\Http\Controllers\FieldController;
use App\Http\Controllers\RecordController;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GalleryField extends FileTypeField  {

    const FIELD_OPTIONS_VIEW = "fields.options.gallery";
    const FIELD_ADV_OPTIONS_VIEW = "partials.field_option_forms.gallery";

    protected $fillable = [
        'rid',
        'flid',
        'images'
    ];

    /**
     * @param null $field
     * @return string
     */
    public function getRevisionData($field = null) {
        return $this->images;
    }

    public function getFieldOptionsView(){
        return self::FIELD_OPTIONS_VIEW;
    }

    public function getAdvancedFieldOptionsView(){
        return self::FIELD_ADV_OPTIONS_VIEW;
    }

    public function getDefaultOptions(Request $request){
        return '[!FieldSize!]0[!FieldSize!][!ThumbSmall!]150x150[!ThumbSmall!][!ThumbLarge!]300x300[!ThumbLarge!][!MaxFiles!]0[!MaxFiles!][!FileTypes!][!FileTypes!]';
    }

    public function updateOptions($field, Request $request, $return=true) {
        $filetype = $request->filetype[0];
        for($i=1;$i<sizeof($request->filetype);$i++){
            $filetype .= '[!]'.$request->filetype[$i];
        }

        if($request->filesize==''){
            $request->filesize = 0;
        }
        if($request->maxfiles==''){
            $request->maxfiles = 0;
        }

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

        if($return) {
            flash()->overlay(trans('controller_field.optupdate'), trans('controller_field.goodjob'));
            return redirect('projects/' . $field->pid . '/forms/' . $field->fid . '/fields/' . $field->flid . '/options');
        } else {
            return '';
        }
    }

    public static function getExportSample($field,$type){
        switch ($type){
            case "XML":
                $xml = '<' . Field::xmlTagClear($field->slug) . ' type="' . $field->type . '">';
                $xml .= '<File>';
                $xml .= '<Name>' . utf8_encode('FILENAME 1') . '</Name>';
                $xml .= '</File>';
                $xml .= '<File>';
                $xml .= '<Name>' . utf8_encode('FILENAME 2') . '</Name>';
                $xml .= '</File>';
                $xml .= '<File>';
                $xml .= '<Name>' . utf8_encode('so on...') . '</Name>';
                $xml .= '</File>';
                $xml .= '</' . Field::xmlTagClear($field->slug) . '>';

                return $xml;
                break;
            case "JSON":
                $fieldArray = array('name' => $field->slug, 'type' => $field->type);
                $fieldArray['files'] = array();

                $fileArray = array();
                $fileArray['name'] = 'FILENAME 1';
                array_push($fieldArray['files'], $fileArray);

                $fileArray = array();
                $fileArray['name'] = 'FILENAME2';
                array_push($fieldArray['files'], $fileArray);

                $fileArray = array();
                $fileArray['name'] = 'so on...';
                array_push($fieldArray['files'], $fileArray);

                return $fieldArray;
                break;
        }

    }

    public static function createNewRecordField($field, $record, $value, $request){
        if(glob(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value . '/*.*') != false){
            $gf = new self();
            $gf->flid = $field->flid;
            $gf->rid = $record->rid;
            $gf->fid = $field->fid;
            $infoString = '';
            $infoArray = array();
            $newPath = env('BASE_PATH') . 'storage/app/files/p' . $field->pid . '/f' . $field->fid . '/r' . $record->rid . '/fl' . $field->flid;
            //make the three directories
            mkdir($newPath, 0775, true);
            mkdir($newPath . '/thumbnail', 0775, true);
            mkdir($newPath . '/medium', 0775, true);
            if (file_exists(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value)) {
                $types = self::getMimeTypes();
                foreach (new \DirectoryIterator(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value) as $file) {
                    if ($file->isFile()) {
                        if (!array_key_exists($file->getExtension(), $types))
                            $type = 'application/octet-stream';
                        else
                            $type = $types[$file->getExtension()];
                        $info = '[Name]' . $file->getFilename() . '[Name][Size]' . $file->getSize() . '[Size][Type]' . $type . '[Type]';
                        $infoArray[$file->getFilename()] = $info;
                        copy(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value . '/' . $file->getFilename(),
                            $newPath . '/' . $file->getFilename());
                        copy(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value . '/thumbnail/' . $file->getFilename(),
                            $newPath . '/thumbnail/' . $file->getFilename());
                        copy(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value . '/medium/' . $file->getFilename(),
                            $newPath . '/medium/' . $file->getFilename());
                    }
                }
                foreach($request->input('file'.$field->flid) as $fName){
                    if($fName!=''){
                        if ($infoString == '') {
                            $infoString = $infoArray[$fName];
                        } else {
                            $infoString .= '[!]' . $infoArray[$fName];
                        }
                    }
                }
            }
            $gf->images = $infoString;
            $gf->save();
        }
    }

    public static function editRecordField($field, $record, $value, $request){
        if(self::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->first() != null
            | glob(env('BASE_PATH').'storage/app/tmpFiles/'.$value.'/*.*') != false){
            $gal_files_exist = false; // if this remains false, then the files were deleted and row should be removed from table


            //we need to check if the field exist first
            if(self::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->first() != null){
                $gf = self::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->first();
            }else {
                $gf = new self();
                $gf->flid = $field->flid;
                $gf->rid = $record->rid;
                $gf->fid = $record->fid;
                $newPath = env('BASE_PATH').'storage/app/files/p'.$field->pid.'/f'.$field->fid.'/r'.$record->rid.'/fl'.$field->flid;

                if(!file_exists($newPath)) {
                    mkdir($newPath, 0775, true);
                    mkdir($newPath.'/thumbnail',0775,true);
                    mkdir($newPath.'/medium',0775,true);

                }
            }
            //clear the old files before moving the update over
            //we only want to remove files that are being replaced by new versions
            //we keep old files around for revision purposes
            $newNames = array();
            //scan the tmpFile as these will be the "new ones"
            if(file_exists(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value)) {
                foreach (new \DirectoryIterator(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value) as $file) {
                    array_push($newNames,$file->getFilename());
                }
            }
            //actually clear them
            foreach (new \DirectoryIterator(env('BASE_PATH').'storage/app/files/p'.$field->pid.'/f'.$field->fid.'/r'.$record->rid.'/fl'.$field->flid) as $file) {
                if ($file->isFile() and in_array($file->getFilename(),$newNames)) {
                    unlink(env('BASE_PATH').'storage/app/files/p'.$field->pid.'/f'.$field->fid.'/r'.$record->rid.'/fl'.$field->flid.'/'.$file->getFilename());
                    unlink(env('BASE_PATH').'storage/app/files/p'.$field->pid.'/f'.$field->fid.'/r'.$record->rid.'/fl'.$field->flid.'/thumbnail/'.$file->getFilename());
                    unlink(env('BASE_PATH').'storage/app/files/p'.$field->pid.'/f'.$field->fid.'/r'.$record->rid.'/fl'.$field->flid.'/medium/'.$file->getFilename());
                }
            }
            //build new stuff
            $infoString = '';
            $infoArray = array();
            if(file_exists(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value)) {
                $types = self::getMimeTypes();
                foreach (new \DirectoryIterator(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value) as $file) {
                    if ($file->isFile()) {
                        if(!array_key_exists($file->getExtension(),$types))
                            $type = 'application/octet-stream';
                        else
                            $type =  $types[$file->getExtension()];
                        $info = '[Name]' . $file->getFilename() . '[Name][Size]' . $file->getSize() . '[Size][Type]' . $type . '[Type]';
                        $infoArray[$file->getFilename()] = $info;
                        copy(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value . '/' . $file->getFilename(),
                            env('BASE_PATH').'storage/app/files/p'.$field->pid.'/f'.$field->fid.'/r'.$record->rid.'/fl'.$field->flid . '/' . $file->getFilename());
                        copy(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value . '/thumbnail/' . $file->getFilename(),
                            env('BASE_PATH').'storage/app/files/p'.$field->pid.'/f'.$field->fid.'/r'.$record->rid.'/fl'.$field->flid . '/thumbnail/' . $file->getFilename());
                        copy(env('BASE_PATH') . 'storage/app/tmpFiles/' . $value . '/medium/' . $file->getFilename(),
                            env('BASE_PATH').'storage/app/files/p'.$field->pid.'/f'.$field->fid.'/r'.$record->rid.'/fl'.$field->flid . '/medium/' . $file->getFilename());

                        $gal_files_exist = true;
                    }
                }
                foreach($request->input('file'.$field->flid) as $fName){
                    if($fName!=''){
                        if ($infoString == '') {
                            $infoString = $infoArray[$fName];
                        } else {
                            $infoString .= '[!]' . $infoArray[$fName];
                        }
                    }
                }
            }
            $gf->images = $infoString;
            $gf->save();


            if(!$gal_files_exist){
                $gf->delete();
            }
        }
    }

    public static function massAssignRecordField($flid, $record, $form_field_value, $overwrite){
        //TODO::mass assign
    }

    public static function createTestRecordField($field, $record){
        $gf = new self();
        $gf->flid = $field->flid;
        $gf->rid = $record->rid;
        $gf->fid = $field->fid;
        $infoArray = array();
        $maxfiles = FieldController::getFieldOption($field,'MaxFiles');
        if($maxfiles==0){$maxfiles=1;}
        $newPath = env('BASE_PATH') . 'storage/app/files/p' . $field->pid . '/f' . $field->fid . '/r' . $record->rid . '/fl' . $field->flid;
        //make the three directories
        mkdir($newPath, 0775, true);
        mkdir($newPath . '/thumbnail', 0775, true);
        mkdir($newPath . '/medium', 0775, true);
        for ($q=0;$q<$maxfiles;$q++) {
            $types = self::getMimeTypes();
            if (!array_key_exists('png', $types))
                $type = 'application/octet-stream';
            else
                $type = $types['png'];
            $info = '[Name]gallery' . $q . '.png[Name][Size]54827[Size][Type]' . $type . '[Type]';
            $infoArray['gallery' . $q . '.png'] = $info;
            copy(env('BASE_PATH') . 'public/testFiles/gallery.png',
                $newPath . '/gallery' . $q . '.png');
            copy(env('BASE_PATH') . 'public/testFiles/medium/gallery.png',
                $newPath . '/medium/gallery' . $q . '.png');
            copy(env('BASE_PATH') . 'public/testFiles/thumbnail/gallery.png',
                $newPath . '/thumbnail/gallery' . $q . '.png');
        }
        $infoString = implode('[!]',$infoArray);
        $gf->images = $infoString;
        $gf->save();
    }

    public static function setRestfulAdvSearch($data, $field, $request){
        $request->request->add([$field->flid.'_input' => $data->input]);

        return $request;
    }

    public static function setRestfulRecordData($field, $flid, $recRequest, $uToken){
        $files = array();
        $currDir = env('BASE_PATH') . 'storage/app/tmpFiles/impU' . $uToken;
        $newDir = env('BASE_PATH') . 'storage/app/tmpFiles/f' . $flid . 'u' . $uToken;
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
        foreach($field->files as $file) {
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

    public static function getRecordPresetArray($field, $record, $data, $flid_array){
        $galfield = GalleryField::where('rid', '=', $record->rid)->where('flid', '=', $field->flid)->first();

        if (!empty($galfield->images)) {
            $data['images'] = explode('[!]', $galfield->images);
        }
        else {
            $data['images'] = null;
        }

        $flid_array[] = $field->flid;

        return array($data,$flid_array,true);
    }

    /**
     * Rollback a gallery field based on a revision.
     *
     * ** Assumes $revision->data is json decoded. **
     *
     * @param Revision $revision
     * @param Field $field
     * @return GalleryField
     */
    public static function rollback(Revision $revision, Field $field) {
        if (!is_array($revision->data)) {
            $revision->data = json_decode($revision->data, true);
        }

        if (is_null($revision->data[Field::_GALLERY][$field->flid]['data'])) {
            return null;
        }

        $galleryfield = self::where("flid", "=", $field->flid)->where("rid", "=", $revision->rid)->first();

        // If the field doesn't exist or was explicitly deleted, we create a new one.
        if ($revision->type == Revision::DELETE || is_null($galleryfield)) {
            $galleryfield = new self();
            $galleryfield->flid = $field->flid;
            $galleryfield->fid = $revision->fid;
            $galleryfield->rid = $revision->rid;
        }

        $galleryfield->images = $revision->data[Field::_GALLERY][$field->flid]['data'];
        $galleryfield->save();

        return $galleryfield;
    }

    /**
     * Build the advanced search query.
     *
     * @param $flid
     * @param $query
     * @return Builder
     */
    public static function getAdvancedSearchQuery($flid, $query) {
        $processed = $query[$flid."_input"]. "*[Name]";

        return DB::table("gallery_fields")
            ->select("rid")
            ->where("flid", "=", $flid)
            ->whereRaw("MATCH (`images`) AGAINST (? IN BOOLEAN MODE)", [$processed])
            ->distinct();
    }

    public static function validate($field, $value){
        $req = $field->required;

        if($req==1){
            if(glob(env('BASE_PATH').'storage/app/tmpFiles/'.$value.'/*.*') == false)
                return $field->name.trans('fieldhelpers_val.file');
        }
    }

    /**
     * Gets the image associated with the Gallery Field of a particular record.
     *
     * @param  int $rid - Record ID
     * @param  int $flid - Field ID
     * @param  string $filename - Name of image file
     * @param  string $type - Get either the full image or a thumbnail of the image
     * @return string - html for the file download
     */
    public static function getImgDisplay($rid, $flid, $filename, $type){
        $record = RecordController::getRecord($rid);
        $field = FieldController::getField($flid);
        if($type == 'thumbnail' | $type == 'medium') {
            $file_path = env('BASE_PATH').'storage/app/files/p'.$record->pid.'/f'.$record->fid.'/r'.$record->rid.'/fl'.$field->flid.'/'.$type.'/'. $filename;
        } else {
            $file_path = env('BASE_PATH').'storage/app/files/p'.$record->pid.'/f'.$record->fid.'/r'.$record->rid.'/fl'.$field->flid . '/' . $filename;

        }

        if(file_exists($file_path)) {
            // Send Download
            return response()->download($file_path, $filename, [
                'Content-Length: '. filesize($file_path)
            ]);
        } else {
            // Error
            exit(trans('controller_field.nofile'));
        }
    }
}
