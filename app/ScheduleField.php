<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class ScheduleField extends BaseField {

    protected $fillable = [
        'rid',
        'flid',
        'events'
    ];


    public function keywordSearchQuery($arg) {
        // TODO: Implement keywordSearchQuery() method.
    }

    public function keywordSearch(array $args, $partial)
    {
        // TODO: Implement keyword_search() method.
    }

    public static function getDateList($field)
    {
        $def = $field->default;
        $options = array();

        if ($def == '') {
            //skip
        } else if (!strstr($def, '[!]')) {
            $options = [$def => $def];
        } else {
            $opts = explode('[!]', $def);
            foreach ($opts as $opt) {
                $options[$opt] = $opt;
            }
        }

        return $options;
    }

}
