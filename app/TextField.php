<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class TextField extends BaseField {

    protected $fillable = [
        'rid',
        'flid',
        'text'
    ];

    /**
     * @param Field | null $field
     * @return string
     */
    public function getRevisionData($field = null) {
        return $this->text;
    }

    /**
     * Rollback a text field based on a revision.
     *
     * @param Revision $revision
     * @param Field $field
     * @return TextField
     */
    public static function rollback(Revision $revision, Field $field) {
        if (!is_array($revision->data)) {
            $revision->data = json_decode($revision->data, true);
        }

        if (is_null($revision->data[Field::_TEXT][$field->flid]['data'])) {
            return null;
        }

        $textfield = self::where("flid", "=", $field->flid)->where("rid", "=", $revision->rid)->first();

        // If the field doesn't exist or was explicitly deleted, we create a new one.
        if ($revision->type == Revision::DELETE || is_null($textfield)) {
            $textfield = new self();
            $textfield->flid = $field->flid;
            $textfield->rid = $revision->rid;
            $textfield->fid = $revision->fid;
        }

        $textfield->text = $revision->data[Field::_TEXT][$field->flid]['data'];
        $textfield->save();

        return $textfield;
    }

    /**
     * Build the advanced query for a text field.
     *
     * @param $flid, field id
     * @param $query, contents of query.
     * @return Builder
     */
    public static function getAdvancedSearchQuery($flid, $query) {
        return DB::table("text_fields")
            ->select("rid")
            ->where("flid", "=", $flid)
            ->whereRaw("MATCH (`text`) AGAINST (? IN BOOLEAN MODE)",
                [Search::processArgument($query[$flid . "_input"], Search::ADVANCED_METHOD)])
            ->distinct();
    }
}