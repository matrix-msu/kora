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

    /**
     * Keyword search on a list field.
     *
     * @param array $args, arguments for the search routine.
     * @param bool $partial, true if the search should return true for partial matches.
     * @return bool, true if parameters satisfied.
     */
    public function keywordSearch(array $args, $partial)
    {
        return self::keywordRoutine($args, $partial, $this->option);
    }

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
     * Determines if to metadata can be called on the list field.
     *
     * @return bool
     */
    public function isMetafiable() {
        return ! empty($this->option);
    }

    /**
     * Simply returns the option.
     *
     * @param Field $field, unneeded.
     * @return string
     */
    public function toMetadata(Field $field) {
        return $this->option;
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
