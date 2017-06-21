<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class GalleryField extends FileTypeField  {

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

        $galleryfield = GalleryField::where("flid", "=", $field->flid)->where("rid", "=", $revision->rid)->first();

        // If the field doesn't exist or was explicitly deleted, we create a new one.
        if ($revision->type == Revision::DELETE || is_null($galleryfield)) {
            $galleryfield = new GalleryField();
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
}
