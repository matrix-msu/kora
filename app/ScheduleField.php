<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class ScheduleField extends BaseField {

    protected $fillable = [
        'rid',
        'flid',
        'events'
    ];

    /**
     * Keyword search for a schedule field.
     *
     * @param array $args, array of arguments for the search to use.
     * @param bool $partial, does not effect the search.
     * @return bool, True if the search parameters are satisfied.
     */
    public function keywordSearch(array $args, $partial)
    {
        return self::keywordRoutine($args, $partial, $this->events);
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

    /**
     * Schedule fields are always metafiable.
     *
     * @return bool
     */
    public function isMetafiable() {
        return true;
    }

    /**
     * Returns the events of the field as an array.
     *
     * @param Field $field, unneeded.
     * @return array
     */
    public function toMetadata(Field $field) {
        return explode("[!]", $this->events);
    }
}
