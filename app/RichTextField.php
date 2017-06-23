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
     * @param Field | null $field
     * @return string
     */
    public function getRevisionData($field = null) {
        return $this->rawtext;
    }

    /**
     * Rollback a rich text field based on a revision.
     *
     * @param Revision $revision
     * @param Field $field
     * @return RichTextField
     */
    public static function rollback(Revision $revision, Field $field) {
        if (!is_array($revision->data)) {
            $revision->data = json_decode($revision->data, true);
        }

        if (is_null($revision->data[Field::_RICH_TEXT][$field->flid]['data'])) {
            return null;
        }

        $richtextfield = self::where('flid', '=', $field->flid)->where('rid', '=', $revision->rid)->first();

        // If the field doesn't exist or was explicitly deleted, we create a new one.
        if ($revision->type == Revision::DELETE || is_null($richtextfield)) {
            $richtextfield = new self();
            $richtextfield->flid = $field->flid;
            $richtextfield->rid = $revision->rid;
            $richtextfield->fid = $revision->fid;
        }

        $richtextfield->rawtext = $revision->data[Field::_RICH_TEXT][$field->flid]['data'];
        $richtextfield->save();

        return $richtextfield;
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

    public static function validate($field, $value){
        $req = $field->required;

        if($req==1 && ($value==null | $value=="")){
            return $field->name.trans('fieldhelpers_val.req');
        }
    }
}