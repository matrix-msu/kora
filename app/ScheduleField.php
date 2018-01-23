<?php namespace App;

use App\Http\Controllers\RevisionController;
use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScheduleField extends BaseField {

    /*
    |--------------------------------------------------------------------------
    | Schedule Field
    |--------------------------------------------------------------------------
    |
    | This model represents the schedule field in Kora3
    |
    */

    /**
     * @var string - Support table name
     */
    const SUPPORT_NAME = "schedule_support";
    /**
     * @var string - Views for the typed field options
     */
    const FIELD_OPTIONS_VIEW = "fields.options.schedule";
    const FIELD_ADV_OPTIONS_VIEW = "partials.field_option_forms.schedule";

    /**
     * @var array - Attributes that can be mass assigned to model
     */
    protected $fillable = [
        'rid',
        'flid',
        'events'
    ];

    /**
     * Get the field options view.
     *
     * @return string - The view
     */
    public function getFieldOptionsView() {
        return self::FIELD_OPTIONS_VIEW;
    }

    /**
     * Get the field options view for advanced field creation.
     *
     * @return string - The view
     */
    public function getAdvancedFieldOptionsView() {
        return self::FIELD_ADV_OPTIONS_VIEW;
    }

    /**
     * Gets the default options string for a new field.
     *
     * @param  Request $request
     * @return string - The default options
     */
    public function getDefaultOptions(Request $request) {
        return '[!Start!]1900[!Start!][!End!]2020[!End!][!Calendar!]No[!Calendar!]';
    }

    /**
     * Update the options for a field
     *
     * @param  Field $field - Field to update options
     * @param  Request $request
     * @param  bool $return - Are we returning an error by string or redirect
     * @return mixed - The result
     */
    public function updateOptions($field, Request $request, $return=true) {
        $reqDefs = $request->default;
        $default = $reqDefs[0];
        for($i=1;$i<sizeof($reqDefs);$i++) {
            $default .= '[!]'.$reqDefs[$i];
        }

        if($request->start=='' | $request->start == 0)
            $request->start = 1;

        if($request->end=='')
            $request->end = 9999;

        $field->updateRequired($request->required);
        $field->updateSearchable($request);
        $field->updateDefault($default);
        $field->updateOptions('Start', $request->start);
        $field->updateOptions('End', $request->end);
        $field->updateOptions('Calendar', $request->cal);

        if($return) {
            return redirect('projects/' . $field->pid . '/forms/' . $field->fid . '/fields/' . $field->flid . '/options')
                ->with('k3_global_success', 'field_options_updated');
        } else {
            return response()->json(["status"=>true,"message"=>"field_options_updated"],200);
        }
    }

    /**
     * Creates a typed field to store record data.
     *
     * @param  Field $field - The field to represent record data
     * @param  Record $record - Record being created
     * @param  string $value - Data to add
     * @param  Request $request
     */
    public function createNewRecordField($field, $record, $value, $request) {
        $this->flid = $field->flid;
        $this->rid = $record->rid;
        $this->fid = $field->fid;
        $this->save();

        $this->addEvents($value);
    }

    /**
     * Edits a typed field that has record data.
     *
     * @param  string $value - Data to add
     * @param  Request $request
     */
    public function editRecordField($value, $request) {
        if(!is_null($this) && !is_null($value)) {
            $this->updateEvents($value);
        } else if(!is_null($this) && is_null($value)) {
            $this->delete();
            $this->deleteEvents();
        }
    }

    /**
     * Takes data from a mass assignment operation and applies it to an individual field.
     *
     * @param  Field $field - The field to represent record data
     * @param  Record $record - Record being written to
     * @param  String $formFieldValue - The value to be assigned
     * @param  Request $request
     * @param  bool $overwrite - Overwrite if data exists
     */
    public function massAssignRecordField($field, $record, $formFieldValue, $request, $overwrite=0) {
        $matching_record_fields = $record->schedulefields()->where("flid", '=', $field->flid)->get();
        $record->updated_at = Carbon::now();
        $record->save();
        if($matching_record_fields->count() > 0) {
            $schedulefield = $matching_record_fields->first();
            if($overwrite == true || $schedulefield->hasEvents()) {
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

    /**
     * For a test record, add test data to field.
     *
     * @param  Field $field - The field to represent record data
     * @param  Record $record - Test record being created
     */
    public function createTestRecordField($field, $record) {
        $this->flid = $field->flid;
        $this->rid = $record->rid;
        $this->fid = $field->fid;
        $this->save();

        $this->addEvents(['K3TR: 01/03/1937 - 01/03/1937']);
    }

    /**
     * Validates the record data for a field against the field's options.
     *
     * @param  Field $field - The
     * @param  mixed $value - Record data
     * @param  Request $request
     * @return string - Potential error message
     */
    public function validateField($field, $value, $request) {
        $req = $field->required;

        if($req==1 && ($value==null | $value==""))
            return $field->name."_required";

        return "field_validated";
    }

    /**
     * Performs a rollback function on an individual field's record data.
     *
     * @param  Field $field - The field being rolled back
     * @param  Revision $revision - The revision being rolled back
     * @param  bool $exists - Field for record exists
     */
    public function rollbackField($field, Revision $revision, $exists=true) {
        if(!is_array($revision->data))
            $revision->data = json_decode($revision->data, true);

        if(is_null($revision->data[Field::_SCHEDULE][$field->flid]['data']))
            return null;

        // If the field doesn't exist or was explicitly deleted, we create a new one.
        if($revision->type == Revision::DELETE || !$exists) {
            $this->flid = $field->flid;
            $this->fid = $revision->fid;
            $this->rid = $revision->rid;
        }

        $this->save();
        $this->updateEvents($revision->data[Field::_SCHEDULE][$field->flid]['data']);
    }

    /**
     * Get the arrayed version of the field data to store in a record preset.
     *
     * @param  array $data - The data array representing the record preset
     * @param  bool $exists - Typed field exists and has data
     * @return array - The updated $data
     */
    public function getRecordPresetArray($data, $exists=true) {
        if($exists)
            $data['events'] = ScheduleField::eventsToOldFormat($this->events()->get());
        else
            $data['events'] = null;

        return $data;
    }

    /**
     * Get the required information for a revision data array.
     *
     * @param  Field $field - Optional field to get storage options for certain typed fields
     * @return mixed - The revision data
     */
    public function getRevisionData($field = null) {
        return self::eventsToOldFormat($this->events()->get());
    }

    /**
     * Provides an example of the field's structure in an export to help with importing records.
     *
     * @param  string $slug - Field nickname
     * @param  string $expType - Type of export
     * @return mixed - The example
     */
    public function getExportSample($slug,$type) {
        switch($type) {
            case "XML":
                $xml = '<' . Field::xmlTagClear($slug) . ' type="Schedule">';
                $value = '<Event>';
                $value .= '<Title>' . utf8_encode('EVENT TITLE 1') . '</Title>';
                $value .= '<Begin>' . '08/19/1990 12:00 AM' . '</Begin>';
                $value .= '<End>' . '08/19/1990 12:30 AM' . '</End>';
                $value .= '<All_Day>' . utf8_encode('0 FOR TIMED EVENT') . '</All_Day>';
                $value .= '</Event>';
                $value .= '<Event>';
                $value .= '<Title>' . utf8_encode('EVENT TITLE 2') . '</Title>';
                $value .= '<Begin>' . '08/19/1990' . '</Begin>';
                $value .= '<End>' . '08/20/1990' . '</End>';
                $value .= '<All_Day>' . utf8_encode('1 FOR ALL DAY EVENT') . '</All_Day>';
                $value .= '</Event>';
                $xml .= $value;
                $xml .= '</' . Field::xmlTagClear($slug) . '>';

                return $xml;
                break;
            case "JSON":
                $fieldArray = [$slug => ['type' => 'Schedule']];

                $eventArray = array();
                $eventArray['title'] = 'EVENT TITLE 1';
                $eventArray['begin'] = '08/19/1990 12:00 AM';
                $eventArray['end'] = '08/19/1990 12:30 AM';
                $eventArray['allday'] = '0 FOR TIMED EVENT';
                $fieldArray[$slug]['value'] = $eventArray;

                $eventArray = array();
                $eventArray['title'] = 'EVENT TITLE 2';
                $eventArray['begin'] = '08/19/1990';
                $eventArray['end'] = '08/20/1990';
                $eventArray['allday'] = '1 FOR ALL DAY EVENT';
                $fieldArray[$slug]['value'] = $eventArray;

                return $fieldArray;
                break;
        }

    }

    /**
     * Updates the request for an API search to mimic the advanced search structure.
     *
     * @param  array $data - Data from the search
     * @param  int $flid - Field ID
     * @param  Request $request
     * @return Request - The update request
     */
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

    /**
     * Updates the request for an API to mimic record creation .
     *
     * @param  array $jsonField - JSON representation of field data
     * @param  int $flid - Field ID
     * @param  Request $recRequest
     * @param  int $uToken - Custom generated user token for file fields and tmp folders
     * @return Request - The update request
     */
    public function setRestfulRecordData($jsonField, $flid, $recRequest, $uToken=null) {
        $events = array();
        foreach($jsonField->events as $event) {
            $string = $event['title'] . ': ' . $event['start'] . ' - ' . $event['end'];
            array_push($events, $string);
        }
        $recRequest[$flid] = $events;

        return $recRequest;
    }

    /**
     * Performs a keyword search on this field and returns any results.
     *
     * @param  int $flid - Field ID
     * @param  string $arg - The keywords
     * @return array - The RIDs that match search
     */
    public function keywordSearchTyped($flid, $arg) {
        return DB::table(self::SUPPORT_NAME)
            ->select("rid")
            ->where("flid", "=", $flid)
            ->whereRaw("MATCH (`desc`) AGAINST (? IN BOOLEAN MODE)", [$arg])
            ->distinct()
            ->pluck('rid');
    }

    /**
     * Performs an advanced search on this field and returns any results.
     *
     * @param  int $flid - Field ID
     * @param  array $query - The advance search user query
     * @return Builder - The RIDs that match search
     */
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

    /**
     * Gets the default values for a schedule field.
     *
     * @param  Field $field - Field to pull defaults from
     * @return array - The defaults
     */
    public static function getDateList($field) {
        $def = $field->default;
        return self::getListOptionsFromString($def);
    }

    /**
     * Overrides the delete function to first delete support fields.
     */
    public function delete() {
        $this->deleteEvents();
        parent::delete();
    }

    /**
     * Returns the events for a record's schedule value.
     *
     * @return Builder - Query of values
     */
    public function events() {
        return DB::table(self::SUPPORT_NAME)->select("*")
            ->where("flid", "=", $this->flid)
            ->where("rid", "=", $this->rid);
    }

    /**
     * Determine if this field has data in the support table.
     *
     * @return bool - Has data
     */
    public function hasEvents() {
        return !! $this->events()->count();
    }

    /**
     * Adds events to the support table.
     *
     * @param  array $events - Events to add
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
     * Processes event string to fit into support format.
     *
     * @param  string $event - Event string value
     * @return array - The new values
     */
    private static function processEvent($event) {
        $event = explode(": ", $event);
        $desc = $event[0];

        $event = explode(" - ", $event[1]);

        $begin = trim($event[0]);
        $end = trim($event[1]);

        if(strpos($begin, ":") === false) { // No time specified.
            $begin = DateTime::createFromFormat("m/d/Y", $begin);
            $end = DateTime::createFromFormat("m/d/Y", $end);
            $allday = true;
        } else {
            $begin = DateTime::createFromFormat("m/d/Y g:i A", $begin);
            $end = DateTime::createFromFormat("m/d/Y g:i A", $end);
            $allday = false;
        }
        return [$begin, $end, $desc, $allday];
    }

    /**
     * Updates the current list of events by deleting the old ones and adding the array that has both new and old.
     *
     * @param  array $events - events to add
     */
    public function updateEvents(array $events) {
        $this->deleteEvents();
        $this->addEvents($events);
    }

    /**
     * Deletes events from the support table.
     */
    public function deleteEvents() {
        DB::table(self::SUPPORT_NAME)
            ->where("rid", "=", $this->rid)
            ->where("flid", "=", $this->flid)
            ->delete();
    }

    /**
     * Turns the support table into the old format beforehand.
     *
     * @param  array $events - Events from support
     * @param  bool $array_string - Array of old format or string of old format
     * @return mixed - String or array of old format
     */
    public static function eventsToOldFormat(array $events, $array_string = false) {
        $formatted = [];
        foreach($events as $event) {
            if($event->allday) {
                $begin = DateTime::createFromFormat("Y-m-d H:i:s", $event->begin)->format("m/d/Y");
                $end = DateTime::createFromFormat("Y-m-d H:i:s", $event->end)->format("m/d/Y");
            } else {
                $begin = DateTime::createFromFormat("Y-m-d H:i:s", $event->begin)->format("m/d/Y g:i A");
                $end = DateTime::createFromFormat("Y-m-d H:i:s", $event->end)->format("m/d/Y g:i A");
            }

            $formatted[] = $event->desc . ": "
                . $begin . " - "
                . $end;
        }

        if($array_string)
            return implode("[!]", $formatted);

        return $formatted;
    }
}
