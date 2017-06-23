<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class VideoField extends FileTypeField {

    protected $fillable = [
        'rid',
        'flid',
        'video'
    ];

    public static function getOptions(){
        return '[!FieldSize!]0[!FieldSize!][!MaxFiles!]0[!MaxFiles!][!FileTypes!][!FileTypes!]';
    }

    /**
     * @param null $field
     * @return string
     */
    public function getRevisionData($field = null) {
        return $this->video;
    }

    /**
     * Rollback a video field based on a revision.
     *
     * @param Revision $revision
     * @param Field $field
     * @return VideoField
     */
    public static function rollback(Revision $revision, Field $field) {
        if (!is_array($revision->data)) {
            $revision->data = json_decode($revision->data, true);
        }

        if (is_null($revision->data[Field::_VIDEO][$field->flid]['data'])) {
            return null;
        }

        $videofield = self::where("flid", "=", $field->flid)->where("rid", "=", $revision->rid)->first();

        // If the field doesn't exist or was explicitly deleted, we create a new one.
        if ($revision->type == Revision::DELETE || is_null($videofield)) {
            $videofield = new self();
            $videofield->flid = $field->flid;
            $videofield->fid = $revision->fid;
            $videofield->rid = $revision->rid;
        }

        $videofield->video = $revision->data[Field::_VIDEO][$field->flid]['data'];
        $videofield->save();

        return $videofield;
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

        return DB::table("video_fields")
            ->select("rid")
            ->where("flid", "=", $flid)
            ->whereRaw("MATCH (`video`) AGAINST (? IN BOOLEAN MODE)", [$processed])
            ->distinct();
    }

    public static function validate($field, $value){
        $req = $field->required;

        if($req==1){
            if(glob(env('BASE_PATH').'storage/app/tmpFiles/'.$value.'/*.*') == false)
                return $field->name.trans('fieldhelpers_val.file');
        }
    }
}
