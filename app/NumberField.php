<?php namespace App;

use App\Http\Controllers\FieldController;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NumberField extends BaseField {

    /**
     * Epsilon value for comparison purposes.
     * Used to match between values in MySQL.
     *
     * @type float
     */
    CONST EPSILON = 0.0001;

    protected $fillable = [
        'rid',
        'flid',
        'number'
    ];

    public static function getOptions(){
        return '[!Max!][!Max!][!Min!][!Min!][!Increment!]1[!Increment!][!Unit!][!Unit!]';
    }

    /**
     * @param null $field
     * @return array
     */
    public function getRevisionData($field = null) {
        return [
            'number' => $this->number,
            'unit' => FieldController::getFieldOption($field, 'Unit')
        ];
    }

    /**
     * Rollback a number field based on a revision.
     *
     * @param Revision $revision
     * @param Field $field
     * @return NumberField
     */
    public static function rollback(Revision $revision, Field $field) {
        if (!is_array($revision->data)) {
            $revision->data = json_decode($revision->data, true);
        }

        if (is_null($revision->data[Field::_NUMBER][$field->flid]['data']['number'])) {
            return null;
        }

        $numberfield = self::where('flid', '=', $field->flid)->where('rid', '=', $revision->rid)->first();

        // If the field doesn't exist or was explicitly deleted, we create a new one.
        if ($revision->type == Revision::DELETE || is_null($numberfield)) {
            $numberfield = new self();
            $numberfield->flid = $field->flid;
            $numberfield->rid = $revision->rid;
            $numberfield->fid = $revision->fid;
        }

        $numberfield->number = $revision->data[Field::_NUMBER][$field->flid]['data']['number'];
        $numberfield->save();

        return $numberfield;
    }

    /**
     * Builds the advanced query for a number field.
     * More explicitly, this will build a search range in MySQL based off the inputs.
     *
     * @param $flid, field id
     * @param $query, query array
     * @return Builder
     */
    public static function getAdvancedSearchQuery($flid, $query) {
        $left = $query[$flid . "_left"];
        $right = $query[$flid . "_right"];
        $invert = isset($query[$flid . "_invert"]);

        $query = DB::table("number_fields")
            ->select("rid")
            ->where("flid", "=", $flid);

        self::buildAdvancedNumberQuery($query, $left, $right, $invert);

        return $query->distinct();
    }

    /**
     * Build an advanced search number field query.
     *
     * @param Builder $query, query to build upon.
     * @param string $left, input from the form, left index.
     * @param string $right, input from the form, right index.
     * @param bool $invert, inverts the search range if true.
     * @param string $prefix, for dealing with joined tables.
     */
    public static function buildAdvancedNumberQuery(Builder &$query, $left, $right, $invert, $prefix = "") {
        // Determine the interval we should search over. With epsilons to account for float rounding.
        if ($left == "") {
            if ($invert) { // (right, inf)
                $query->where($prefix . "number", ">", floatval($right) - self::EPSILON);
            }
            else { // (-inf, right]
                $query->where($prefix . "number", "<=", floatval($right) + self::EPSILON);
            }
        }
        else if ($right == "") {
            if ($invert) { // (-inf, left)
                $query->where($prefix . "number", "<", floatval($left) + self::EPSILON);
            }
            else { // [left, inf)
                $query->where($prefix . "number", ">=", floatval($left) - self::EPSILON);
            }
        }
        else {
            if ($invert) { // (-inf, left) union (right, inf)
                $query->whereNotBetween($prefix . "number", [floatval($left) - self::EPSILON,
                    floatval($right) + self::EPSILON]);
            }
            else { // [left, right]
                $query->whereBetween($prefix . "number", [floatval($left) - self::EPSILON,
                    floatval($right) + self::EPSILON]);
            }
        }
    }

    public static function validate($field, $value){
        $req = $field->required;

        if($req==1 && ($value==null | $value=="")){
            return $field->name.trans('fieldhelpers_val.req');
        }
    }
}
