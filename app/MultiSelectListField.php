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

    /**
     * Keyword search for a multi-select list field.
     *  Note: "partial" applies to whole values inside the options, rather than a partial match
     *  to a subset of the field's options.
     *   E.g. if $field->options = "one[!]two", a partial search for "on" returns true.
     *
     * @param array $args, Array of arguments for the search to use.
     * @param bool $partial, True if partial values should be considered in the search.
     * @return bool, True if the search parameters are satisfied.
     */
    public function keywordSearch(array $args, $partial)
    {
        $options = explode('[!]', $this->options);

        foreach($options as $option) {
            if (self::keywordRoutine($args, $partial, $option)) {
                return true; // Return on first match.
            }
        }

        return false;
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
     * Determines if to metadata can be called on the msl field.
     *
     * @return bool
     */
    public function isMetafiable() {
        return ! empty($this->options);
    }

    /**
     * Returns the msl field's options as an array.
     *
     * @param Field $field, unneeded.
     * @return array
     */
    public function toMetadata(Field $field) {
        return explode("[!]", $this->options);
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

        $query->where(function($query) use($inputs) {
            foreach($inputs as $input) {
                $query->orWhereRaw("MATCH (`options`) AGAINST (? IN BOOLEAN MODE)",
                    [Search::processArgument($input, Search::ADVANCED_METHOD)]);
            }
        });

        return $query;
    }
}
