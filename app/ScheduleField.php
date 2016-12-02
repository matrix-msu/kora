<?php namespace App;

use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

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

    public function delete() {
        DB::table("schedule_support")->where("project_id", "=", $this->pid)->delete();

        parent::delete();
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

    /**
     * @param array $options
     * @return bool
     */
    public function save(array $options = []) {
        // TODO: This is now how we should save these... see self::addEvent
        foreach(explode("[!]", $this->events) as $event) {

        }
        // unset($this->events); TODO: Consider how to properly save these things...
        return parent::save($options);
    }

    /**
     * Adds an event to the schedule_support table.
     * @param string $event an event represented in the following format:
     *      "description: mm/dd/yyyy - mm/dd/yyyy"
     *      or if there is a time specified
     *      "description: mm/dd/yyyy hh:mm A/PM - mm/dd/yyyy hh:mm A/PM"
     * This is how the form builds the strings.
     */
    public function addEvent($event) {
        $event = explode(": ", $event);
        $desc = $event[0];

        $event = explode(" - ", $event[1]);

        $begin = trim($event[0]);
        $end = trim($event[1]);

        if (strpos($begin, ":") === false) { // No time specified.
            $begin = DateTime::createFromFormat("m/d/Y", $begin);
            $end = DateTime::createFromFormat("m/d/Y", $end);
        }
        else {
            $begin = DateTime::createFromFormat("m/d/Y g:i A", $begin);
            $end = DateTime::createFromFormat("m/d/Y g:i A", $end);
        }

        $now = date("Y-m-d H:i:s");

        DB::table("schedule_support")->insert(
            [
                'rid' => $this->rid,
                'flid' => $this->flid,
                'begin' => $begin,
                'end' => $end,
                'desc' => $desc,
                'created_at' => $now,
                'updated_at' => $now
            ]
        );
    }

    /**
     * The query for events in a schedule field.
     * Use ->get() to obtain all events.
     * @return Builder
     */
    public function events() {
        return DB::table("schedule_support")->select("*")
            ->where("flid", "=", $this->flid)
            ->where("rid", "=", $this->rid);
    }

    /**
     * Get the support fields of a schedule field with a particular rid.
     *
     * @param $rid
     * @return Builder
     */
    public static function supportFields($rid) {
        return DB::table("schedule_support")->where("rid", "=", $rid);
    }

    /**
     * Build an advanced search query for a schedule field.
     *
     * @param $flid, field id.
     * @param $query, query array. 
     * @return Builder
     */
    public static function getAdvancedSearchQuery($flid, $query) {
        $begin_month = ($query[$flid."_begin_month"] == "") ? 1 : intval($query[$flid."_begin_month"]);
        $begin_day = ($query[$flid."_begin_day"] == "") ? 1 : intval($query[$flid."_begin_day"]);
        $begin_year = ($query[$flid."_begin_year"] == "") ? 1 : intval($query[$flid."_begin_year"]);

        $end_month = ($query[$flid."_end_month"] == "") ? 1 : intval($query[$flid."_end_month"]);
        $end_day = ($query[$flid."_end_day"] == "") ? 1 : intval($query[$flid."_end_day"]);
        $end_year = ($query[$flid."_end_year"] == "") ? 1 : intval($query[$flid."_end_year"]);

        //
        // Advanced Search for schedule doesn't allow for time search, but we do store the time in some schedules entries.
        // So we search from 0:00:00 to 23:59:59 on the begin and end day respectively
        //
        $begin = DateTime::createFromFormat("Y-m-d H:i:s", $begin_year."-".$begin_month."-".$begin_day." 00:00:00");
        $end = DateTime::createFromFormat("Y-m-d H:i:s", $end_year."-".$end_month."-".$end_day." 23:59:59");

        $query = DB::table("schedule_support")
            ->select("rid")
            ->where("flid", "=", $flid);

        $query->where(function($query) use ($begin, $end) {
            // We search over [search_begin, search_end] and return results if
            // the intersection of [search_begin, search_end] and [begin, end] is non-empty.
            $query->whereBetween("begin", [$begin, $end])
                ->orWhereBetween("end", [$begin, $end]);

            $query->orWhere(function($query) use($begin, $end) {
                $query->where("begin", "<=", $begin)
                    ->where("end", ">=", $end);
            });
        });

        return $query->distinct();
    }
}
