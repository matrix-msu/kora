<?php namespace App;

use App\Http\Controllers\FieldController;
use App\Http\Controllers\RevisionController;
use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpSpec\Exception\Exception;

class ScheduleField extends BaseField {

    const SUPPORT_NAME = "schedule_support";
    const FIELD_OPTIONS_VIEW = "fields.options.schedule";
    const FIELD_ADV_OPTIONS_VIEW = "partials.field_option_forms.schedule";

    protected $fillable = [
        'rid',
        'flid',
        'events'
    ];

    public function getFieldOptionsView(){
        return self::FIELD_OPTIONS_VIEW;
    }

    public function getAdvancedFieldOptionsView(){
        return self::FIELD_ADV_OPTIONS_VIEW;
    }

    public function getDefaultOptions(Request $request){
        return '[!Start!]1900[!Start!][!End!]2020[!End!][!Calendar!]No[!Calendar!]';
    }

    public function updateOptions($field, Request $request, $return=true) {
        $reqDefs = $request->default;
        $default = $reqDefs[0];
        for($i=1;$i<sizeof($reqDefs);$i++){
            $default .= '[!]'.$reqDefs[$i];
        }

        if($request->start=='' | $request->start == 0){
            $request->start = 1;
        }
        if($request->end==''){
            $request->end = 9999;
        }

        $field->updateRequired($request->required);
        $field->updateSearchable($request);
        $field->updateDefault($default);
        $field->updateOptions('Start', $request->start);
        $field->updateOptions('End', $request->end);
        $field->updateOptions('Calendar', $request->cal);

        if($return) {
            flash()->overlay(trans('controller_field.optupdate'), trans('controller_field.goodjob'));
            return redirect('projects/' . $field->pid . '/forms/' . $field->fid . '/fields/' . $field->flid . '/options');
        } else {
            return '';
        }
    }

    public function createNewRecordField($field, $record, $value, $request){
        $this->flid = $field->flid;
        $this->rid = $record->rid;
        $this->fid = $field->fid;
        $this->save();

        $this->addEvents($value);
    }

    public function editRecordField($value, $request) {
        if(!is_null($this) && !is_null($value)){
            $this->updateEvents($value);
        }
        elseif(!is_null($this) && is_null($value)){
            $this->delete();
            $this->deleteEvents();
        }
    }

    public function massAssignRecordField($field, $record, $formFieldValue, $request, $overwrite=0) {
        $matching_record_fields = $record->schedulefields()->where("flid", '=', $field->flid)->get();
        $record->updated_at = Carbon::now();
        $record->save();
        if ($matching_record_fields->count() > 0) {
            $schedulefield = $matching_record_fields->first();
            if ($overwrite == true || $schedulefield->hasEvents()) {
                $revision = RevisionController::storeRevision($record->rid, 'edit');
                $schedulefield->updateEvents($formFieldValue);
                $schedulefield->save();
                $revision->oldData = RevisionController::buildDataArray($record);
                $revision->save();
            }
        } else {
            $this->createNewRecordField($field, $record, $formFieldValue, $request);
            $revision = RevisionController::storeRevision($record->rid, 'edit');
            $revision->oldData = RevisionController::buildDataArray($record);
            $revision->save();
        }
    }

    public function createTestRecordField($field, $record){
        $this->flid = $field->flid;
        $this->rid = $record->rid;
        $this->fid = $field->fid;
        $this->save();

        $this->addEvents(['K3TR: 01/03/1937 - 01/03/1937']);
    }

    public function validateField($field, $value, $request) {
        $req = $field->required;

        if($req==1 && ($value==null | $value=="")){
            return $field->name.trans('fieldhelpers_val.req');
        }
    }

    public function rollbackField($field, Revision $revision, $exists=true) {
        if (!is_array($revision->data)) {
            $revision->data = json_decode($revision->data, true);
        }

        if (is_null($revision->data[Field::_SCHEDULE][$field->flid]['data'])) {
            return null;
        }

        // If the field doesn't exist or was explicitly deleted, we create a new one.
        if ($revision->type == Revision::DELETE || !$exists) {
            $this->flid = $field->flid;
            $this->fid = $revision->fid;
            $this->rid = $revision->rid;
        }

        $this->save();
        $this->updateEvents($revision->data[Field::_SCHEDULE][$field->flid]['data']);
    }

    public function getRecordPresetArray($data, $exists=true) {
        if($exists) {
            $data['events'] = ScheduleField::eventsToOldFormat($this->events()->get());
        }
        else {
            $data['events'] = null;
        }

        return $data;
    }

    public function getExportSample($slug,$type) {
        switch ($type){
            case "XML":
                $xml = '<' . Field::xmlTagClear($slug) . ' type="Schedule">';
                $value = '<Event>';
                $value .= '<Title>' . utf8_encode('EVENT TITLE 1') . '</Title>';
                $value .= '<Start>' . '08/19/1990 12:00 AM' . '</Start>';
                $value .= '<End>' . '08/19/1990 12:30 AM' . '</End>';
                $value .= '<All_Day>' . utf8_encode('0 FOR TIMED EVENT') . '</All_Day>';
                $value .= '</Event>';
                $value .= '<Event>';
                $value .= '<Title>' . utf8_encode('EVENT TITLE 2') . '</Title>';
                $value .= '<Start>' . '08/19/1990' . '</Start>';
                $value .= '<End>' . '08/20/1990' . '</End>';
                $value .= '<All_Day>' . utf8_encode('1 FOR ALL DAY EVENT') . '</All_Day>';
                $value .= '</Event>';
                $xml .= $value;
                $xml .= '</' . Field::xmlTagClear($slug) . '>';

                return $xml;
                break;
            case "JSON":
                $fieldArray = array('name' => $slug, 'type' => 'Schedule');
                $fieldArray['events'] = array();

                $eventArray = array();
                $eventArray['title'] = 'EVENT TITLE 1';
                $eventArray['start'] = '08/19/1990 12:00 AM';
                $eventArray['end'] = '08/19/1990 12:30 AM';
                $eventArray['allday'] = '0 FOR TIMED EVENT';
                array_push($fieldArray['events'], $eventArray);

                $eventArray = array();
                $eventArray['title'] = 'EVENT TITLE 2';
                $eventArray['start'] = '08/19/1990';
                $eventArray['end'] = '08/20/1990';
                $eventArray['allday'] = '1 FOR ALL DAY EVENT';
                array_push($fieldArray['events'], $eventArray);

                return $fieldArray;
                break;
        }

    }

    public function setRestfulAdvSearch($data, $flid, $request) {
        if(isset($data->begin_month))
            $beginMonth = $data->begin_month;
        else
            $beginMonth = '';
        if(isset($data->begin_day))
            $beginDay = $data->begin_day;
        else
            $beginDay = '';
        if(isset($data->begin_year))
            $beginYear = $data->begin_year;
        else
            $beginYear = '';
        $request->request->add([$flid.'_begin_month' => $beginMonth]);
        $request->request->add([$flid.'_begin_day' => $beginDay]);
        $request->request->add([$flid.'_begin_year' => $beginYear]);
        if(isset($data->end_month))
            $endMonth = $data->end_month;
        else
            $endMonth = '';
        if(isset($data->end_day))
            $endDay = $data->end_day;
        else
            $endDay = '';
        if(isset($data->end_year))
            $endYear = $data->end_year;
        else
            $endYear = '';
        $request->request->add([$flid.'_end_month' => $endMonth]);
        $request->request->add([$flid.'_end_day' => $endDay]);
        $request->request->add([$flid.'_end_year' => $endYear]);

        return $request;
    }

    public function setRestfulRecordData($jsonField, $flid, $recRequest, $uToken=null) {
        $events = array();
        foreach($jsonField->events as $event) {
            $string = $event['title'] . ': ' . $event['start'] . ' - ' . $event['end'];
            array_push($events, $string);
        }
        $recRequest[$flid] = $events;

        return $recRequest;
    }

    public function keywordSearchTyped($fid, $arg, $method) {
        return DB::table(self::SUPPORT_NAME)
            ->select("rid")
            ->where("fid", "=", $fid)
            ->whereRaw("MATCH (`desc`) AGAINST (? IN BOOLEAN MODE)", [$arg])
            ->distinct();
    }

    public function getAdvancedSearchQuery($flid, $query) {
        $begin_month = ($query[$flid."_begin_month"] == "") ? 1 : intval($query[$flid."_begin_month"]);
        $begin_day = ($query[$flid."_begin_day"] == "") ? 1 : intval($query[$flid."_begin_day"]);
        $begin_year = ($query[$flid."_begin_year"] == "") ? 1 : intval($query[$flid."_begin_year"]);

        $end_month = ($query[$flid."_end_month"] == "") ? 1 : intval($query[$flid."_end_month"]);
        $end_day = ($query[$flid."_end_day"] == "") ? 1 : intval($query[$flid."_end_day"]);
        $end_year = ($query[$flid."_end_year"] == "") ? 1 : intval($query[$flid."_end_year"]);

        //
        // Advanced Search for schedule doesn't allow for time search, but we do store the time in some schedules entries.
        // So we search from 0:00:00 to 23:59:59 on the begin and end day respectively.
        //
        $begin = DateTime::createFromFormat("Y-m-d H:i:s", $begin_year."-".$begin_month."-".$begin_day." 00:00:00");
        $end = DateTime::createFromFormat("Y-m-d H:i:s", $end_year."-".$end_month."-".$end_day." 23:59:59");

        $query = DB::table(self::SUPPORT_NAME)
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

    ///////////////////////////////////////////////END ABSTRACT FUNCTIONS///////////////////////////////////////////////

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
     * Delete a schedule field, we must also delete its support fields.
     * @throws \Exception
     */
    public function delete() {
        $this->deleteEvents();
        parent::delete();
    }

    /**
     * Adds an event to the schedule_support table.
     * @param array $events an array of events, each specified in the following format:
     *      "description: mm/dd/yyyy - mm/dd/yyyy"
     *      or if there is a time specified
     *      "description: mm/dd/yyyy hh:mm A/PM - mm/dd/yyyy hh:mm A/PM"
     *      Note: This is how the form builds the strings.
     */
    public function addEvents(array $events) {
        $now = date("Y-m-d H:i:s");
        foreach($events as $event) {
            list($begin, $end, $desc, $allday) = self::processEvent($event);

            DB::table(self::SUPPORT_NAME)->insert(
                [
                    'rid' => $this->rid,
                    'fid' => $this->fid,
                    'flid' => $this->flid,
                    'begin' => $begin,
                    'end' => $end,
                    'desc' => $desc,
                    'allday' => $allday,
                    'created_at' => $now,
                    'updated_at' => $now
                ]
            );
        }
    }

    /**
     * Extract the logic to get the necessary information from the event string to the database.
     *
     * @param $event
     * @return array
     */
    private static function processEvent($event) {
        $event = explode(": ", $event);
        $desc = $event[0];

        $event = explode(" - ", $event[1]);

        $begin = trim($event[0]);
        $end = trim($event[1]);

        if (strpos($begin, ":") === false) { // No time specified.
            $begin = DateTime::createFromFormat("m/d/Y", $begin);
            $end = DateTime::createFromFormat("m/d/Y", $end);
            $allday = true;
        }
        else {
            $begin = DateTime::createFromFormat("m/d/Y g:i A", $begin);
            $end = DateTime::createFromFormat("m/d/Y g:i A", $end);
            $allday = false;
        }
        return [$begin, $end, $desc, $allday];
    }

    /**
     * Update events using the same method as add events.
     * The only reliable way to actually update is to delete all previous events and just add the updated versions.
     *
     * @param array $events
     */
    public function updateEvents(array $events) {
        $this->deleteEvents();
        $this->addEvents($events);
    }

    /**
     * Deletes all events associated with the schedule field.
     */
    public function deleteEvents() {
        DB::table(self::SUPPORT_NAME)
            ->where("rid", "=", $this->rid)
            ->where("flid", "=", $this->flid)
            ->delete();
    }

    /**
     * The query for events in a schedule field.
     * Use ->get() to obtain all events.
     *
     * Events will be in "Y-m-d H:i:s" format.
     *      For all day events use "m/d/Y" format.
     *      For non-all day use "m/d/Y g:i A" format.
     *
     * @return Builder
     */
    public function events() {
        return DB::table(self::SUPPORT_NAME)->select("*")
            ->where("flid", "=", $this->flid)
            ->where("rid", "=", $this->rid);
    }

    /**
     * True if there are events associated with a particular Schedule field.
     *
     * @return bool
     */
    public function hasEvents() {
        return !! $this->events()->count();
    }

    /**
     * @param null $field
     * @return array
     */
    public function getRevisionData($field = null) {
        return self::eventsToOldFormat($this->events()->get());
    }

    /**
     * Puts an array of events into the old format.
     *      - "Old Format" meaning, an array of the events formatted as
     *      <Description>: <Begin> - <End>
     *
     * @param array $events, array of StdObjects representing events.
     * @param bool $array_string, should this be in the old *[!]*[!]...[!]* format?
     * @return array | string
     */
    public static function eventsToOldFormat(array $events, $array_string = false) {
        $formatted = [];
        foreach($events as $event) {
            if ($event->allday) {
                $begin = DateTime::createFromFormat("Y-m-d H:i:s", $event->begin)->format("m/d/Y");
                $end = DateTime::createFromFormat("Y-m-d H:i:s", $event->end)->format("m/d/Y");
            }
            else {
                $begin = DateTime::createFromFormat("Y-m-d H:i:s", $event->begin)->format("m/d/Y g:i A");
                $end = DateTime::createFromFormat("Y-m-d H:i:s", $event->end)->format("m/d/Y g:i A");
            }

            $formatted[] = $event->desc . ": "
                . $begin . " - "
                . $end;
        }

        if ($array_string) {
            return implode("[!]", $formatted);
        }

        return $formatted;
    }

    /**
     * Get the support fields of a schedule field with a particular rid.
     *
     * @param $rid
     * @return Builder
     */
    public static function supportFields($rid) {
        return DB::table(self::SUPPORT_NAME)->where("rid", "=", $rid);
    }
}
