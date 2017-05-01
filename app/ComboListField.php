<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ComboListField extends BaseField {

    const SUPPORT_NAME = "combo_support";

    protected $fillable = [
        'rid',
        'flid',
        'ftype1',
        'ftype2'
    ];

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
     * @throws \Exception
     */
    public function delete() {
        $this->deleteData();
        parent::delete();
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
                'fid' => $this->fid,
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
                'fid' => $this->fid,
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
        return DB::table(self::SUPPORT_NAME)->select("*")
            ->where("rid", "=", $this->rid)
            ->where("flid", "=", $this->flid)
            ->orderBy('list_index');
    }

    /**
     * True if there is data associated with a particular Combo List field.
     *
     * @return bool
     */
    public function hasData() {
        return !! $this->data()->count();
    }

    /**
     * @param null $field
     * @return array
     */
    public function getRevisionData($field = null) {
        $field = Field::where('flid', '=', $this->flid)->first();

        $name_1 = self::getComboFieldName($field, 'one');
        $name_2 = self::getComboFieldName($field, 'two');

        return [
            'options' => self::dataToOldFormat($this->data()->get()),
            'name_1' => $name_1,
            'name_2' => $name_2
        ];
    }

    /**
     * Rollback a combo list field based on a revision.
     *
     * @param Revision $revision
     * @param Field $field
     * @return ComboListField
     */
    public static function rollback(Revision $revision, Field $field) {
        if (!is_array($revision->data)) {
            $revision->data = json_decode($revision->data, true);
        }

        if (is_null($revision->data[Field::_COMBO_LIST][$field->flid]['data'])) {
            return null;
        }

        $combolistfield = ComboListField::where("flid", "=", $field->flid)->where("rid", "=", $revision->rid)->first();

        // If the field doesn't exist or was explicitly deleted, we create a new one.
        if ($revision->type == Revision::DELETE || is_null($combolistfield)) {
            $combolistfield = new ComboListField();
            $combolistfield->flid = $field->flid;
            $combolistfield->fid = $revision->fid;
            $combolistfield->rid = $revision->rid;
        }

        $combolistfield->save();

        $type_1 = ComboListField::getComboFieldType($field, "one");
        $type_2 = ComboListField::getComboFieldName($field, "two");

        $combolistfield->updateData($revision->data[Field::_COMBO_LIST][$field->flid]['data']['options'], $type_1, $type_2);

        return $combolistfield;
    }

    /**
     * Puts an array of data into the old format.
     *      - "Old Format" meaning, and array of the data options formatted as
     *        [!f1!]<Field 1 Data>[!f1!][!f2!]<Field 2 Data>[!f2!]
     *
     * @param array $data, array of StdObjects representing data options.
     * @param bool $array_string, should this be in the old *[!val!]*[!val!]...[!val!]* format?
     * @return array | string
     */
    public static function dataToOldFormat(array $data, $array_string = false) {
        $formatted = [];
        for($i = 0; $i < count($data); $i++) {
            $op1 = $data[$i];
            $op2 = $data[++$i];

            if ($op1->field_num == 2) {
                $tmp = $op1;
                $op1 = $op2;
                $op2 = $tmp;
            }

            if (! is_null($op1->data)) {
                $val1 = $op1->data;
            }
            else {
                $val1 = $op1->number + 0;
            }

            if (! is_null($op2->data)) {
                $val2 = $op2->data;
            }
            else {
                $val2 = $op2->number + 0;
            }


            $formatted[] = "[!f1!]"
                . $val1
                . "[!f1!]"
                . "[!f2!]"
                . $val2
                . "[!f2!]";
        }

        if($array_string) {
            return implode("[!val!]", $formatted);
        }

        return $formatted;
    }

    /**
     * Delete data associated with this field.
     */
    public function deleteData() {
        DB::table(self::SUPPORT_NAME)
            ->where("rid", "=", $this->rid)
            ->where("flid", "=", $this->flid)
            ->delete();
    }

    /**
     * Updates a combo list's data.
     *
     * @param array $data
     * @param $type_1
     * @param $type_2
     */
    public function updateData(array $data, $type_1, $type_2) {
        $this->deleteData();
        $this->addData($data, $type_1, $type_2);
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
            return DB::table(self::SUPPORT_NAME)->select("*")->where("id", "<", 0);
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
            $prefix = ($prefix == "") ? self::SUPPORT_NAME : substr($prefix, 0, -1);
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
        return DB::table(self::SUPPORT_NAME)
            ->select("rid")
            ->where("flid", "=", $flid);
    }
}
