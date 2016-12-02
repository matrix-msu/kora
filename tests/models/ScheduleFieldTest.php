<?php

use Illuminate\Support\Facades\DB;
use App\ScheduleField;
use App\Field;

/**
 * Class ScheduleFieldTest
 * @group field
 */
class ScheduleFieldTest extends TestCase
{
    public function test_getAdvancedSearchQuery() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_SCHEDULE, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $sched_field = new ScheduleField();
        $sched_field->rid = $record->rid;
        $sched_field->flid = $field->flid;
        $sched_field->events = "Today: 11/15/2016 - 11/15/2016[!]Tomorrow: 11/16/2016 - 11/16/2016[!]Forever: 11/17/2016 - 11/17/2016";
        $sched_field->save();

        $sched_field->addEvent("Today: 11/15/2016 - 11/15/2016");
        $sched_field->addEvent("Tomorrow: 11/16/2016 - 11/16/2016");
        $sched_field->addEvent("Forever: 11/17/2016 - 11/17/2016");

        $dummy_query = [
            $field->flid."_begin_month" => "11",
            $field->flid."_begin_day" => "1",
            $field->flid."_begin_year" => "2016",
            $field->flid."_end_month" => "11",
            $field->flid."_end_day" => "15",
            $field->flid."_end_year" => "2016"
        ];

        $query = ScheduleField::getAdvancedSearchQuery($field->flid, $dummy_query);
        $this->assertEquals($query->get()[0]->rid, $record->rid);

        // Test a non-four digit date.

        $sched_field->addEvent("Sometime: 10/31/444 - 11/15/456");

        $dummy_query = [
            $field->flid."_begin_month" => "10",
            $field->flid."_begin_day" => "31",
            $field->flid."_begin_year" => "444",
            $field->flid."_end_month" => "11",
            $field->flid."_end_day" => "15",
            $field->flid."_end_year" => "444"
        ];

        $query = ScheduleField::getAdvancedSearchQuery($field->flid, $dummy_query);
        $this->assertEquals($query->get()[0]->rid, $record->rid);

        $sched_field->addEvent("Some other time: 1/1/30 - 12/31/30");

        $dummy_query = [
            $field->flid."_begin_month" => "4",
            $field->flid."_begin_day" => "1",
            $field->flid."_begin_year" => "30",
            $field->flid."_end_month" => "10",
            $field->flid."_end_day" => "1",
            $field->flid."_end_year" => "30"
        ];

        $query = ScheduleField::getAdvancedSearchQuery($field->flid, $dummy_query);
        $this->assertEquals($query->get()[0]->rid, $record->rid);

        // Test some queries that shouldn't return any results.

        $dummy_query = [
            $field->flid."_begin_month" => "10",
            $field->flid."_begin_day" => "31",
            $field->flid."_begin_year" => "500",
            $field->flid."_end_month" => "11",
            $field->flid."_end_day" => "15",
            $field->flid."_end_year" => "2000"
        ];

        $query = ScheduleField::getAdvancedSearchQuery($field->flid, $dummy_query);
        $this->assertEmpty($query->get());

        $dummy_query = [
            $field->flid."_begin_month" => "11",
            $field->flid."_begin_day" => "13",
            $field->flid."_begin_year" => "2016",
            $field->flid."_end_month" => "11",
            $field->flid."_end_day" => "14",
            $field->flid."_end_year" => "2016"
        ];

        $query = ScheduleField::getAdvancedSearchQuery($field->flid, $dummy_query);
        $this->assertEmpty($query->get());

        $dummy_query = [
            $field->flid."_begin_month" => "1",
            $field->flid."_begin_day" => "13",
            $field->flid."_begin_year" => "1976",
            $field->flid."_end_month" => "5",
            $field->flid."_end_day" => "14",
            $field->flid."_end_year" => "1978"
        ];

        $query = ScheduleField::getAdvancedSearchQuery($field->flid, $dummy_query);
        $this->assertEmpty($query->get());

        $dummy_query = [
            $field->flid."_begin_month" => "9",
            $field->flid."_begin_day" => "10",
            $field->flid."_begin_year" => "2040",
            $field->flid."_end_month" => "7",
            $field->flid."_end_day" => "4",
            $field->flid."_end_year" => "2046"
        ];

        $query = ScheduleField::getAdvancedSearchQuery($field->flid, $dummy_query);
        $this->assertEmpty($query->get());
    }

    public function test_addEvent() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_SCHEDULE, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $sched_field = new ScheduleField();
        $sched_field->rid = $record->rid;
        $sched_field->flid = $field->flid;
        $sched_field->events = "Today: 11/15/2016 - 11/15/2016[!]Tomorrow: 11/16/2016 - 11/16/2016[!]Forever: 11/17/2016 - 11/17/2016";
        $sched_field->save();

        $sched_field->addEvent("Today: 11/15/2016 - 11/15/2016");

        // Check date search.
        $date_begin = DateTime::createFromFormat("m/d/Y H:i:s", "11/15/2016 00:00:00");
        $date_end = DateTime::createFromFormat("m/d/Y H:i:s", "11/15/2016 23:59:59");

        $rids = DB::table("schedule_support")->select("rid")
            ->whereBetween("begin", [$date_begin, $date_end])->get();

        $this->assertEquals($record->rid, $rids[0]->rid);

        // Try non-four digit date.
        $sched_field->addEvent("Today: 1/5/346 - 5/22/346");

        $date_begin = DateTime::createFromFormat("m/d/Y H:i:s", "1/5/346 00:00:00");
        $date_end = DateTime::createFromFormat("m/d/Y H:i:s", "1/5/346 23:59:59");

        $rids = DB::table("schedule_support")->select("rid")
            ->whereBetween("begin", [$date_begin, $date_end])->get();

        $this->assertEquals($record->rid, $rids[0]->rid);
    }
}