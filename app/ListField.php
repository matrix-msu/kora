<?php namespace App;

use App\Http\Controllers\FieldController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Builder;

class ListField extends BaseField {

    protected $fillable = [
        'rid',
        'flid',
        'option'
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
     * @param null $field
     * @return string
     */
    public function getRevisionData($field = null) {
        return $this->option;
    }

    /**
     * Rollback a list field based on a revision.
     *
     * @param Revision $revision
     * @param Field $field
     * @return ListField
     */
    public static function rollback(Revision $revision, Field $field) {
        if (!is_array($revision->data)) {
            $revision->data = json_decode($revision->data, true);
        }

        if (is_null($revision->data[Field::_LIST][$field->flid]['data'])) {
            return null;
        }

        $listfield = ListField::where("flid", "=", $field->flid)->where("rid", "=", $revision->rid)->first();

        // If the field doesn't exist or was explicitly deleted, we create a new one.
        if ($revision->type == Revision::DELETE || is_null($listfield)) {
            $listfield = new ListField();
            $listfield->flid = $field->flid;
            $listfield->rid = $revision->rid;
            $listfield->fid = $revision->fid;
        }

        $listfield->option = $revision->data[Field::_LIST][$field->flid]['data'];
        $listfield->save();

        return $listfield;
    }

    /**
     * Build the advanced query for a list field.
     *
     * @param $flid, field id.
     * @param $query, query array.
     * @return Builder
     */
    public static function getAdvancedSearchQuery($flid, $query) {
        $db_query = DB::table("list_fields")
            ->select("rid")
            ->where("flid", "=", $flid);
        $input = $query[$flid . "_input"];

        self::buildAdvancedListQuery($db_query, $input);

        return $db_query->distinct();
    }

    /**
     * Build and advanced query for list field.
     *
     * @param Builder $db_query, reference to query to build.
     * @param string $input, input value from form.
     */
    public static function buildAdvancedListQuery(Builder &$db_query, $input) {
        $db_query->whereRaw("MATCH (`option`) AGAINST (? IN BOOLEAN MODE)",
            [Search::processArgument($input, Search::ADVANCED_METHOD)]);
    }
}
