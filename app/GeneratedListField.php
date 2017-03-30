<?php namespace App;

use App\Http\Controllers\FieldController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class GeneratedListField extends BaseField {

    protected $fillable = [
        'rid',
        'flid',
        'options'
    ];

    public static function getList($field, $blankOpt=false)
    {
        $dbOpt = FieldController::getFieldOption($field, 'Options');
        $options = array();

        if ($dbOpt == '') {
            //skip
        } else if (!strstr($dbOpt, '[!]')) {
            $options = [$dbOpt => $dbOpt];
        } else {
            $opts = explode('[!]', $dbOpt);
            foreach ($opts as $opt) {
                $options[$opt] = $opt;
            }
        }

        if ($blankOpt) {
            $options = array('' => '') + $options;
        }

        return $options;
    }

    /**
     * Determines if to metadata can be called on the generated list field.
     *
     * @return bool
     */
    public function isMetafiable() {
        return ! empty($this->options);
    }

    /**
     * Returns the generated list's options as an array.
     *
     * @param Field $field, unneeded.
     * @return Builder
     */
    public function toMetadata(Field $field) {
        return explode("[!]", $this->options);
    }

    /**
     * @param null $field
     * @return string
     */
    public function getRevisionData($field = null) {
        return $this->options;
    }

    /**
     * Rollback a generated list field based on a revision.
     *
     * ** Assumes $revision->data is json decoded. **
     *
     * @param Revision $revision
     * @param Field $field
     */
    public static function rollback(Revision $revision, Field $field) {
        $genfield = GeneratedListField::where("flid", "=", $field->flid)->where("rid", "=", $revision->rid)->first();

        // // If the field doesn't exist or was explicitly deleted, we create a new one.
        if ($revision->type == Revision::DELETE || is_null($genfield)) {
            $genfield = new GeneratedListField();
            $genfield->flid = $field->flid;
            $genfield->rid = $revision->rid;
            $genfield->fid = $revision->fid;
        }

        $genfield->options = $revision->data[Field::_GENERATED_LIST][$field->flid];
        $genfield->save();
    }

    /**
     * Builds the advanced search query.
     * Advanced queries for Gen List Fields accept any record that has at least one of the desired parameters.
     *
     * @param $flid
     * @param $query
     * @return Builder
     */
    public static function getAdvancedSearchQuery($flid, $query) {
        $inputs = $query[$flid."_input"];

        $query = DB::table("generated_list_fields")
            ->select("rid")
            ->where("flid", "=", $flid);

        MultiSelectListField::buildAdvancedMultiSelectListQuery($query, $inputs);

        return $query->distinct();
    }
}
