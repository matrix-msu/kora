<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\DB;

class RichTextField extends BaseField {

    protected $fillable = [
        'rid',
        'flid',
        'rawtext',
        'searchable_rawtext'
    ];

    /**
     * Saves the model.
     *
     * Instead of putting this everywhere the rawtext member is assigned we'll just override the member function.
     *
     * @param array $options
     * @return bool
     */
    public function save(array $options = array()) {
        $this->searchable_rawtext = strip_tags($this->rawtext);

        return parent::save($options);
    }

    /**
     * Determine if to metadata can be called on this field.
     *
     * @return bool
     */
    public function isMetafiable() {
        return ! empty($this->rawtext);
    }

    /**
     * Simply returns the rawtext.
     *
     * @param Field $field, unneeded.
     * @return string
     */
    public function toMetadata(Field $field) {
        return $this->rawtext;
    }

    /**
     * @param Field | null $field
     * @return string
     */
    public function getRevisionData($field = null) {
        return $this->rawtext;
    }

    /**
     * Rollback a rich text field based on a revision.
     *
     * ** Assumes $revision->data is json decoded. **
     *
     * @param Revision $revision
     * @param Field $field
     */
    public static function rollback(Revision $revision, Field $field) {
        $richtextfield = RichTextField::where('flid', '=', $field->flid)->where('rid', '=', $revision->rid)->first();

        // If the field doesn't exist or was explicitly deleted, we create a new one.
        if ($revision->type == Revision::DELETE || is_null($richtextfield)) {
            $richtextfield = new RichTextField();
            $richtextfield->flid = $field->flid;
            $richtextfield->rid = $revision->rid;
            $richtextfield->fid = $revision->fid;
        }

        $richtextfield->rawtext = $revision->data[Field::_RICH_TEXT][$field->flid];
        $richtextfield->save();
    }

    /**
     * Builds the advanced search query for a rich text field.
     *
     * @param $flid
     * @param array $query
     * @return Builder
     */
    public static function getAdvancedSearchQuery($flid, array $query) {
        return DB::table("rich_text_fields")
            ->select("rid")
            ->where("flid", "=", $flid)
            ->whereRaw("MATCH (`searchable_rawtext`) AGAINST (? IN BOOLEAN MODE)",
                [Search::processArgument($query[$flid . "_input"], Search::ADVANCED_METHOD)])
            ->distinct();
    }
}