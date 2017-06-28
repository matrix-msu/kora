<?php namespace App;

use App\Http\Controllers\FieldController;
use App\Http\Controllers\RecordController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
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

    public static function getOptions(){
        return '[!FieldSize!]0[!FieldSize!][!ThumbSmall!]150x150[!ThumbSmall!][!ThumbLarge!]300x300[!ThumbLarge!][!MaxFiles!]0[!MaxFiles!][!FileTypes!][!FileTypes!]';
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

    public static function updateOptions($pid, $fid, $flid, $request){
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

        FieldController::updateRequired($pid, $fid, $flid, $request->required);
        FieldController::updateSearchable($pid, $fid, $flid, $request);
        FieldController::updateOptions($pid, $fid, $flid, 'FieldSize', $request->filesize);
        FieldController::updateOptions($pid, $fid, $flid, 'MaxFiles', $request->maxfiles);
        FieldController::updateOptions($pid, $fid, $flid, 'FileTypes', $filetype);
        FieldController::updateOptions($pid, $fid, $flid, 'ThumbSmall', $small);
        FieldController::updateOptions($pid, $fid, $flid, 'ThumbLarge', $large);
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
        $processed = self::processAdvancedSearchInput($query[$flid."_input"]);

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
