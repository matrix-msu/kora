<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ComboListField extends BaseField {

    protected $fillable = [
        'rid',
        'flid',
        'options',
        'ftype1',
        'ftype2'
    ];

    /**
     * Keyword search for a combo list field.
     * This search simply uses the already existing search functions of text, number, list, multi-select list, and generated list.
     *
     * @param array $args, array of arguments for the search to use.
     * @param bool $partial, true if partial values should be considered.
     * @return bool, true if the search found something, false otherwise.
     */
    public function keywordSearch(array $args, $partial) {
        $field = Field::where('flid', '=', $this->flid)->first();
        $type1 = self::getComboFieldType($field, 'one');
        $type2 = self::getComboFieldType($field, 'two');

        $f1vals = explode("[!f1!]", $this->options);
        $f2vals = explode("[!f2!]", $this->options);

        //
        // Iterate through the field values and search them.
        //
        for ($i = 1; $i < count($f1vals); $i += 2) { // Every other value in the array will hold what we're interested in.
            $field = self::makeTempField($type1, $f1vals[$i]);
            if ($field->keywordSearch($args, $partial) == true) return true;

            $field = self::makeTempField($type2, $f2vals[$i]);
            if ($field->keywordSearch($args, $partial) == true) return true;
        }

        return false; // We didn't find anything.
    }

    /**
     * Creates a temporary field that we can execute keyword search on.
     *
     * @param string $type, the type of the field we are to create.
     * @param string $value, the value associated with the field.
     * @return BaseField, a field that we can execute keyword search on.
     */
    private static function makeTempField($type, $value) {
        switch($type) {
            case "Text":
                $field = new TextField();
                $field->text = $value;
                break;

            case "Number":
                $field = new NumberField();
                $field->number = $value;
                break;

            case "List":
                $field = new ListField();
                $field->option = $value;
                break;

            case "Multi-Select List":
                $field = new MultiSelectListField();
                $field->options = $value;
                break;

            case "Generated List":
                $field = new GeneratedListField();
                $field->options = $value;
                break;

            default:
                return null; // Something went wrong.
        }

        return $field;
    }

    public static function getComboList($field, $blankOpt=false, $fnum)
    {
        $dbOpt = ComboListField::getComboFieldOption($field, 'Options', $fnum);

        if($dbOpt === null) {
            return [];
        }

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

    public static function getComboFieldOption($field, $key, $num){
        $options = $field->options;
        if($num=='one')
            $opt = explode('[!Field1!]',$options)[1];
        else if($num=='two')
            $opt = explode('[!Field2!]',$options)[1];

        $tag = '[!'.$key.'!]';

        $exploded = explode($tag, $opt);

        if (sizeof($exploded) < 2) {
            return null;
        }

        $value = explode($tag,$opt)[1];

        return $value;
    }

    public static function getComboFieldName($field, $num){
        $options = $field->options;

        if($num=='one') {
            $oneOpts = explode('[!Field1!]', $options)[1];
            $name = explode('[Name]', $oneOpts)[1];
        }else if ($num=='two') {
            $twoOpts = explode('[!Field2!]', $options)[1];
            $name = explode('[Name]', $twoOpts)[1];
        }

        return $name;
    }

    public static function getComboFieldType($field, $num){
        $options = $field->options;

        if($num=='one') {
            $oneOpts = explode('[!Field1!]', $options)[1];
            $type = explode('[Type]', $oneOpts)[1];
        }else if ($num=='two') {
            $twoOpts = explode('[!Field2!]', $options)[1];
            $type = explode('[Type]', $twoOpts)[1];
        }

        return $type;
    }

    /**
     * Determine if to metadata can be called on the combo list field.
     *
     * @return bool
     */
    public function isMetafiable() {
        return ! empty($this->options);
    }

    /**
     * Returns a collection of the combo list field indexed by field name => field data.
     *
     * @param Field $field
     * @return Collection
     */
    public function toMetadata(Field $field) {
        $options = explode("[!val!]", $this->options);

        $combo_col = new Collection();
        foreach($options as $option) {
            $field_1 = explode("[!f1!]", $option)[1];
            $field_2 = explode("[!f2!]", $option)[1];

            $combo_col->put(self::getComboFieldName($field, "one"), $field_1);
            $combo_col->put(self::getComboFieldName($field, "two"), $field_2);
        }

        return $combo_col;
    }

    /**
     * Adds data to the combo list support field.
     *
     * @param array $data, data as formatted by the record entry.
     * @param string $type_1, type of first combo field.
     * @param string $type_2, type of second combo field.
     */
    public function addData(array $data, $type_1, $type_2) {
        $now = date("Y-m-d H:i:s");

        $inserts = [];

        $one_is_num = $type_1 == 'Number';
        $two_is_num = $type_2 == 'Number';

        $i = 0;
        foreach ($data as $entry) {
            $field_1_data = explode('[!f1!]', $entry)[1];
            $field_2_data = explode('[!f2!]', $entry)[1];

            $inserts[] = [
                'rid' => $this->rid,
                'flid' => $this->flid,
                'field_num' => 1,
                'list_index' => $i,
                'data' => (!$one_is_num) ? $field_1_data : null,
                'number' => ($one_is_num) ? $field_1_data : null,
                'created_at' => $now,
                'updated_at' => $now
            ];

            $inserts[] = [
                'rid' => $this->rid,
                'flid' => $this->flid,
                'list_index' => $i,
                'field_num' => 2,
                'data' => (!$two_is_num) ? $field_2_data : null,
                'number' => ($two_is_num) ? $field_2_data : null,
                'created_at' => $now,
                'updated_at' => $now
            ];

            $i++;
        }

        DB::table('combo_support')->insert($inserts);
    }

    /**
     * The query for data in a combo list field. Orders by the index data appears in the list.
     * Use ->get() to obtain all events.
     * @return Builder
     */
    public function data() {
        return DB::table("combo_support")->select("*")
            ->where("rid", "=", $this->rid)
            ->where("flid", "=", $this->flid)
            ->orderBy('list_index');
    }

    /**
     * Get advanced search query for a combo list field.
     *
     * @param mixed $flid, field id.
     * @param array $query, query array from the form.
     * @return Builder, the search query for the field rids.
     */
    public static function getAdvancedSearchQuery($flid, $query) {
        $field = Field::where("flid", "=", $flid)->first();
        $type_1 = self::getComboFieldType($field, 'one');
        $type_2 = self::getComboFieldType($field, 'two');

        $one_valid = $query[$flid . "_1_valid"] == "1";
        $two_valid = $query[$flid . "_2_valid"] == "1";

        // Return an impossible query if the two fields are somehow both invalid.
        // May seem extraneous, but this is required for chaining calls elsewhere.
        if (! ($one_valid || $two_valid)) {
            return DB::table("combo_support")->select("*")->where("id", "<", 0);
        }
        else if ($one_valid && $two_valid) {
            if ($query[$flid . "_operator"] == "and") {
                //
                // We need to join combo_support with itself.
                // Since each entry represents one sub-field in the combo list, an "and" operation
                // on a combo list would be impossible without two copies of everything.
                //

                $first_prefix = "one.";
                $second_prefix = "two.";

                $db_query = DB::table("combo_support AS " . substr($first_prefix, 0, -1))
                    ->select($first_prefix . "rid")
                    ->where($first_prefix . "flid", "=", $flid)
                    ->join("combo_support AS " . substr($second_prefix, 0, -1),
                        $first_prefix . "rid",
                        "=",
                        $second_prefix . "rid");

                $db_query->where(function($db_query) use ($flid, $query, $type_1, $first_prefix) {
                    self::buildAdvancedQueryRoutine($db_query, "1", $flid, $query, $type_1, $first_prefix);
                });
                $db_query->where(function($db_query) use ($flid, $query, $type_2, $second_prefix) {
                    self::buildAdvancedQueryRoutine($db_query, "2", $flid, $query, $type_2, $second_prefix);
                });

            }
            else { // OR operation.
                $db_query = self::makeAdvancedQueryRoutine($flid);
                $db_query->where(function($db_query) use ($flid, $query, $type_1) {
                   self::buildAdvancedQueryRoutine($db_query, "1", $flid, $query, $type_1);
                });
                $db_query->orWhere(function($db_query) use ($flid, $query, $type_2) {
                   self::buildAdvancedQueryRoutine($db_query, "2", $flid, $query, $type_2);
                });
            }
        }
        else if ($one_valid) {
            $db_query = self::makeAdvancedQueryRoutine($flid);
            self::buildAdvancedQueryRoutine($db_query, "1", $flid, $query, $type_1);
        }
        else { // two valid
            $db_query = self::makeAdvancedQueryRoutine($flid);
            self::buildAdvancedQueryRoutine($db_query, "2", $flid, $query, $type_2);
        }

        return $db_query->distinct();
    }

    /**
     * The logic to build up an advanced query.
     *
     * @param Builder $db_query, reference to the current query.
     * @param mixed $field_num, first or second field in the combo list.
     * @param int $flid, field id.
     * @param array $query, query array from the form.
     * @param string $type, the type of the combo field.
     * @param string $prefix, to deal with joined tables.
     */
    public static function buildAdvancedQueryRoutine(Builder &$db_query, $field_num, $flid, $query, $type, $prefix = "") {
        $db_query->where($prefix . "field_num", "=", $field_num);

        if ($type == Field::_NUMBER) {
            NumberField::buildAdvancedNumberQuery($db_query,
                $query[$flid . "_" . $field_num . "_left"],
                $query[$flid . "_" . $field_num . "_right"],
                isset($query[$flid . "_" . $field_num . "_invert"]),
                $prefix);
        }
        else {
            if ($type == Field::_LIST || $type == Field::_TEXT) {
                $inputs = [$query[$flid . "_" . $field_num . "_input"]];
            }
            else { // Generated or Multi-Select List
                $inputs = $query[$flid . "_" . $field_num . "_input"];
            }

            // Since we're using a raw query, we have to get the database prefix to match our alias.
            $db_prefix = DB::getTablePrefix();
            $prefix = ($prefix == "") ? "combo_support" : substr($prefix, 0, -1);
            $db_query->where(function($db_query) use ($inputs, $prefix, $db_prefix) {
                foreach($inputs as $input) {
                    $db_query->orWhereRaw("MATCH (`" . $db_prefix . $prefix . "`.`data`) AGAINST (? IN BOOLEAN MODE)",
                        [Search::processArgument($input, Search::ADVANCED_METHOD)]);
                }
            });
        }
    }

    /**
     * Makes the initial DB query.
     * @param int $flid, field id.
     * @return Builder, initial query.
     */
    public static function makeAdvancedQueryRoutine($flid) {
        return DB::table("combo_support")
            ->select("rid")
            ->where("flid", "=", $flid);
    }
}
