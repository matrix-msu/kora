<?php namespace App;

use App\Http\Controllers\FieldController;
use Illuminate\Database\Eloquent\Model;

class ListField extends BaseField {

    protected $fillable = [
        'rid',
        'flid',
        'option'
    ];

    public function keywordSearchQuery($query, $arg) {
        // TODO: Implement keywordSearchQuery() method.
    }

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

}
