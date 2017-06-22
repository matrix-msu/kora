<?php namespace App;

use App\Http\Controllers\FieldController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class MultiSelectListField extends BaseField {

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
     * @param null $field
     * @return string
     */
    public function getRevisionData($field = null) {
        return $this->options;
    }

    /**
     * Rollback a multiselect list field based on a revision.
     *
     * @param Revision $revision
     * @param Field $field
     * @return MultiSelectListField
     */
    public static function rollback(Revision $revision, Field $field) {
        if (!is_array($revision->data)) {
            $revision->data = json_decode($revision->data, true);
        }

        if(is_null($revision->data[Field::_MULTI_SELECT_LIST][$field->flid]['data'])) {
            return null;
        }

        $mslfield = self::where("flid", "=", $field->flid)->where("rid", "=", $revision->rid)->first();

        // If the field doesn't exist or was explicitly deleted, we create a new one.
        if ($revision->type == Revision::DELETE || is_null($mslfield)) {
            $mslfield = new self();
            $mslfield->flid = $field->flid;
            $mslfield->rid = $revision->rid;
            $mslfield->fid = $revision->fid;
        }

        $mslfield->options = $revision->data[Field::_MULTI_SELECT_LIST][$field->flid]['data'];
        $mslfield->save();

        return $mslfield;
    }

    /**
     * Build the advanced search query.
     * Advanced queries for MSL Fields accept any record that has at least one of the desired parameters.
     *
     * @param $flid
     * @param $query
     * @return Builder
     */
    public static function getAdvancedSearchQuery($flid, $query) {
        $inputs = $query[$flid."_input"];

        $query = DB::table("multi_select_list_fields")
            ->select("rid")
            ->where("flid", "=", $flid);

        self::buildAdvancedMultiSelectListQuery($query, $inputs);

        return $query->distinct();
    }

    /**
     * Build the advanced search query for a multi select list. (Works for Generated List too.)
     *
     * @param Builder $db_query
     * @param array $inputs, input values
     */
    public static function buildAdvancedMultiSelectListQuery(Builder &$db_query, $inputs) {
        $db_query->where(function($db_query) use ($inputs) {
            foreach($inputs as $input) {
                $db_query->orWhereRaw("MATCH (`options`) AGAINST (? IN BOOLEAN MODE)",
                    [Search::processArgument($input, Search::ADVANCED_METHOD)]);
            }
        });
    }
}
