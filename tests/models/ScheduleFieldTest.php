<?php

use App\Field;
use App\Search;
use App\Revision;
use App\ScheduleField;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\RevisionController;

/**
 * Class ScheduleFieldTest
 * @group field
 */
class ScheduleFieldTest extends TestCase
{
    public function test_keywordSearchTyped() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_SCHEDULE, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $sched = new ScheduleField();
        $sched->fid = $field->fid;
        $sched->rid = $record->rid;
        $sched->flid = $field->flid;
        $sched->save();

        $sched->addEvents(["Chance: 11/15/2016 - 11/15/2016",
            "The Rapper: 11/16/2016 - 11/16/2016",
            "Crust: 11/17/2016 - 11/17/2016"]);

        $arg = Search::processArgument("crust", Search::SEARCH_OR);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_OR)->get()[0]->rid);

        $arg = Search::processArgument("The Rapper", Search::SEARCH_EXACT);
        $this->assertEquals($record->rid, $field->keywordSearchTyped($arg, Search::SEARCH_EXACT)->get()[0]->rid);
    }

    public function test_deleteEvents() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_SCHEDULE, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $sched = new ScheduleField();
        $sched->fid = $field->fid;
        $sched->rid = $record->rid;
        $sched->flid = $field->flid;
        $sched->save();

        $sched->addEvents(["Chance: 11/15/2016 - 11/15/2016",
            "The Rapper: 11/16/2016 - 11/16/2016",
            "Crust: 11/17/2016 - 11/17/2016"]);

        $this->assertEquals(3, $sched->events()->count());

        $sched->deleteEvents();

        $this->assertEquals(0, $sched->events()->count());
    }

    public function test_updateEvents() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_SCHEDULE, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $sched = new ScheduleField();
        $sched->fid = $field->fid;
        $sched->rid = $record->rid;
        $sched->flid = $field->flid;
        $sched->save();

        $sched->addEvents(["Chance: 11/15/2016 - 11/15/2016",
            "The Rapper: 11/16/2016 - 11/16/2016",
            "Crust: 11/17/2016 - 11/17/2016"]);

        $events = $sched->events()->get();

        $descriptions = ['Chance', 'The Rapper', 'Crust'];
        $retrieved = array_map(function($element) {
            return $element->desc;
        }, $events);
        foreach($descriptions as $description) {
            $this->assertContains($description, $retrieved);
        }

        $sched->updateEvents(["Star Wars: 11/15/2016 - 11/15/2016",
            "Oblivion: 11/16/2016 - 11/16/2016",
            "Crust: 11/17/2016 - 11/17/2016"]);

        $events = $sched->events()->get();

        $descriptions = ['Star Wars', 'Oblivion', 'Crust'];
        $retrieved = array_map(function($element) {
            return $element->desc;
        }, $events);
        foreach($descriptions as $description) {
            $this->assertContains($description, $retrieved);
        }
    }

    public function test_getAdvancedSearchQuery() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_SCHEDULE, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $sched_field = new ScheduleField();
        $sched_field->fid = $field->fid;
        $sched_field->rid = $record->rid;
        $sched_field->flid = $field->flid;
        $sched_field->save();

        $sched_field->addEvents(["Today: 11/15/2016 - 11/15/2016",
                "Tomorrow: 11/16/2016 - 11/16/2016",
                "Forever: 11/17/2016 - 11/17/2016"]);

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

        $sched_field->addEvents(["Sometime: 10/31/444 - 11/15/456"]);

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

        $sched_field->addEvents(["Some other time: 1/1/30 - 12/31/30"]);

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

        // Test a search on an event with hour/min/sec information.
        $sched_field->addEvents(["Something or other: 1/5/1 11:15:00 AM - 1/5/1 11:30:30 AM"]);

        $dummy_query = [
            $field->flid."_begin_month" => "1",
            $field->flid."_begin_day" => "5",
            $field->flid."_begin_year" => "1",
            $field->flid."_end_month" => "1",
            $field->flid."_end_day" => "6",
            $field->flid."_end_year" => "1"
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

    public function test_addEvents() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_SCHEDULE, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $sched_field = new ScheduleField();
        $sched_field->fid = $field->fid;
        $sched_field->rid = $record->rid;
        $sched_field->flid = $field->flid;
        $sched_field->save();

        $sched_field->addEvents(["Today: 11/15/2016 - 11/15/2016"]);

        // Check date search.
        $date_begin = DateTime::createFromFormat("m/d/Y H:i:s", "11/15/2016 00:00:00");
        $date_end = DateTime::createFromFormat("m/d/Y H:i:s", "11/15/2016 23:59:59");

        $rids = DB::table("schedule_support")->select("rid")
            ->whereBetween("begin", [$date_begin, $date_end])->get();

        $this->assertEquals($record->rid, $rids[0]->rid);

        // Try non-four digit date.
        $sched_field->addEvents(["Today: 1/5/346 - 5/22/346"]);

        $date_begin = DateTime::createFromFormat("m/d/Y H:i:s", "1/5/346 00:00:00");
        $date_end = DateTime::createFromFormat("m/d/Y H:i:s", "1/5/346 23:59:59");

        $rids = DB::table("schedule_support")->select("rid")
            ->whereBetween("begin", [$date_begin, $date_end])->get();

        $this->assertEquals($record->rid, $rids[0]->rid);

        // Try with a time entry.
        $sched_field->addEvents(["Today: 1/5/1 11:15:00 AM - 5/22/2 11:30:30 AM"]);

        $date_begin = DateTime::createFromFormat("m/d/Y H:i:s", "1/5/1 00:00:00");
        $date_end = DateTime::createFromFormat("m/d/Y H:i:s", "1/5/2 23:59:59");

        $rids = DB::table("schedule_support")->select("rid")
            ->whereBetween("begin", [$date_begin, $date_end])->get();

        $this->assertEquals($record->rid, $rids[0]->rid);

    }

    public function test_events() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_SCHEDULE, $project->pid, $form->fid);
        $r1 = self::dummyRecord($project->pid, $form->fid);
        $r2 = self::dummyRecord($project->pid, $form->fid);
        $r3 = self::dummyRecord($project->pid, $form->fid);
        $r4 = self::dummyRecord($project->pid, $form->fid);

        $s1 = new ScheduleField();
        $s1->fid = $field->fid;
        $s1->rid = $r1->rid;
        $s1->flid = $field->flid;
        $s1->save();

        $s1->addEvents(["Today: 12/2/2016 - 12/2/2016",
                "Tomorrow: 12/3/2016 - 12/3/2016"]);

        $s2 = new ScheduleField();
        $s2->fid = $field->fid;
        $s2->rid = $r2->rid;
        $s2->flid = $field->flid;
        $s2->save();

        $s2->addEvents(["Christmas: 12/25/2016 - 12/25/2016",
                "New Years Eve: 12/31/2016 - 12/31/2016",
                "Something Else: 1/25/2017  - 5/1/2018"]);

        $s3 = new ScheduleField();
        $s3->fid = $field->fid;
        $s3->rid = $r3->rid;
        $s3->flid = $field->flid;
        $s3->save();

        $s3->addEvents(["Now: 12/1/2016 12:07 PM - 12/1/2016 12:07 PM"]);

        $s4 = new ScheduleField();
        $s4->fid = $field->fid;
        $s4->rid = $r4->rid;
        $s4->flid = $field->flid;
        $s4->save();

        // No events...

        $events1 = $s1->events()->get();
        $this->assertEquals(sizeof($events1), 2);
        $this->assertEquals($events1[0]->desc, "Today");

        $events2 = $s2->events()->get();
        $this->assertEquals(sizeof($events2), 3);
        $this->assertEquals($events2[1]->desc, "New Years Eve");

        $this->assertNotEquals($events1, $events2);

        $events3 = $s3->events()->get();
        $this->assertEquals(sizeof($events3), 1);
        $this->assertEquals($events3[0]->desc, "Now");

        $events4 = $s4->events()->get();
        $this->assertEquals(sizeof($events4), 0);
    }

    public function test_rollback() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_SCHEDULE, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $old = ["Chance: 11/15/2016 - 11/15/2016",
            "The Rapper: 11/16/2016 - 11/16/2016",
            "Crust: 11/17/2016 - 11/17/2016"];

        $sched = new ScheduleField();
        $sched->fid = $field->fid;
        $sched->rid = $record->rid;
        $sched->flid = $field->flid;
        $sched->save();

        $sched->addEvents($old);

        $revision = RevisionController::storeRevision($record->rid, Revision::CREATE);

        $new = ["Star Wars: 11/15/2016 - 11/15/2016",
            "Oblivion: 11/16/2016 - 11/16/2016",
            "Crust: 11/17/2016 - 11/17/2016"];

        $sched->updateEvents($new);

        ScheduleField::rollback($revision, $field);

        $events = ScheduleField::eventsToOldFormat($sched->events()->get());
        foreach ($old as $event_str) {
            $this->assertContains($event_str, $events);
        }
    }
}