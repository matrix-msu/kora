<?php

use App\DateField as DateField;
use App\Field;

/**
 * Class DateFieldTest
 * @group field
 */
class DateFieldTest extends TestCase
{
    /**
     * The options for the different cases of a possible date field's circa/era options.
     * @type array
     */
    const DATE = <<<TEXT
[!Circa!]No[!Circa!][!Start!]1900[!Start!][!End!]2020[!End!][!Format!]MMDDYYYY[!Format!][!Era!]No[!Era!]
TEXT;
    const DATE_ERA = <<<TEXT
[!Circa!]No[!Circa!][!Start!]1900[!Start!][!End!]2020[!End!][!Format!]MMDDYYYY[!Format!][!Era!]Yes[!Era!]
TEXT;
    const DATE_CIRCA = <<<TEXT
[!Circa!]Yes[!Circa!][!Start!]1900[!Start!][!End!]2020[!End!][!Format!]MMDDYYYY[!Format!][!Era!]No[!Era!]
TEXT;
    const DATE_ERA_CIRCA = <<<TEXT
[!Circa!]Yes[!Circa!][!Start!]1900[!Start!][!End!]2020[!End!][!Format!]MMDDYYYY[!Format!][!Era!]Yes[!Era!]
TEXT;

    /**
     * Test the month to number function in the date field class.
     */
    public function test_monthToNumber() {
        $englishMonths = ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'];

        for ($i = 0; $i < count($englishMonths); $i++) {
            $monthNumber = DateField::monthToNumber($englishMonths[$i]);
            $this->assertEquals($monthNumber, $i + 1);
        }

        $spanishMonths = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

        for ($i = 0; $i < count($spanishMonths); $i++) {
            $monthNumber = DateField::monthToNumber($spanishMonths[$i]);
            $this->assertEquals($monthNumber, $i + 1);
        }

        $frenchMonths = ['janvier', 'fevrier', 'mars', 'avril', 'mai', 'juin', 'juillet', 'aout', 'septembre', 'octobre', 'novembre', 'decembre'];

        for ($i = 0; $i < count($frenchMonths); $i++) {
            $monthNumber = DateField::monthToNumber($frenchMonths[$i]);
            $this->assertEquals($monthNumber, $i + 1);
        }
    }

    /**
     * Test the is month function in the date field class.
     */
    public function test_isMonth() {
        $englishMonths = ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'];
        $spanishMonths = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        $frenchMonths = ['janvier', 'fevrier', 'mars', 'avril', 'mai', 'juin', 'juillet', 'aout', 'septembre', 'octobre', 'novembre', 'decembre'];

        foreach ($englishMonths as $month) {
            $this->assertTrue(DateField::isMonth($month));
        }

        foreach ($spanishMonths as $month) {
            $this->assertTrue(DateField::isMonth($month));
        }

        foreach ($frenchMonths as $month) {
            $this->assertTrue(DateField::isMonth($month));
        }

        $this->assertFalse(DateField::isMonth("not a month!"));
        $this->assertFalse(DateField::isMonth("jannuuuaarrryyy"));
        $this->assertFalse(DateField::isMonth("^-.&%^@()[]0-9"));

    }

    public function test_getAdvancedSearchQuery() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_DATE, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        // Test the normal use case, dates are all CE, post 1970 date.
        $date = new DateField();
        $date->rid = $record->rid;
        $date->flid = $field->flid;
        $date->day = 25;
        $date->month = 1;
        $date->year = 1995;
        $date->era = "CE";
        $date->circa = 1; // Circa doesn't actually matter for advanced search.
        $date->save();

        $dummy_query = [$field->flid."_begin_month" => "11",
            $field->flid."_begin_day" => "30",
            $field->flid."_begin_year" => "1994",
            $field->flid."_begin_era" => "CE",
            $field->flid."_end_month" => "6",
            $field->flid."_end_day" => "11",
            $field->flid."_end_year" => "1996",
            $field->flid."_end_era" => "CE"];

        $query = DateField::getAdvancedSearchQuery($field->flid, $dummy_query);

        $rid = $query->first()->rid;
        $this->assertEquals($rid, $record->rid);

        // Test a CE pre 1970 date.
        $date->day = 31;
        $date->month = 10;
        $date->year = 1329;
        $date->save();

        $dummy_query = [$field->flid."_begin_month" => "3",
            $field->flid."_begin_day" => "30",
            $field->flid."_begin_year" => "1204",
            $field->flid."_begin_era" => "CE",
            $field->flid."_end_month" => "1",
            $field->flid."_end_day" => "11",
            $field->flid."_end_year" => "1400",
            $field->flid."_end_era" => "CE"];

        $query = DateField::getAdvancedSearchQuery($field->flid, $dummy_query);

        $rid = $query->first()->rid;
        $this->assertEquals($rid, $record->rid);

        // Test an entirely BCE search range.
        $date->day = 5;
        $date->month = 4;
        $date->year = 100;
        $date->era = "BCE";
        $date->save();

        $dummy_query = [$field->flid."_begin_month" => "2",
            $field->flid."_begin_day" => "7",
            $field->flid."_begin_year" => "110",
            $field->flid."_begin_era" => "BCE",
            $field->flid."_end_month" => "1",
            $field->flid."_end_day" => "6",
            $field->flid."_end_year" => "99",
            $field->flid."_end_era" => "BCE"];

        $query = DateField::getAdvancedSearchQuery($field->flid, $dummy_query);

        $rid = $query->first()->rid;
        $this->assertEquals($rid, $record->rid);

        // Test a range that searches across eras.
        $record_other = self::dummyRecord($project->pid, $form->fid);

        $date_other = new DateField();
        $date_other->rid = $record_other->rid;
        $date_other->flid = $field->flid;
        $date_other->day = 9;
        $date_other->month = 10;
        $date_other->year = 560;
        $date_other->circa = 0;
        $date_other->era = "CE";
        $date_other->save();

        $dummy_query = [$field->flid."_begin_month" => "2",
            $field->flid."_begin_day" => "7",
            $field->flid."_begin_year" => "110",
            $field->flid."_begin_era" => "BCE",
            $field->flid."_end_month" => "1",
            $field->flid."_end_day" => "3",
            $field->flid."_end_year" => "589",
            $field->flid."_end_era" => "CE"];

        $query = DateField::getAdvancedSearchQuery($field->flid, $dummy_query);

        $rids = $query->get();
        $rids[0] = $rids[0]->rid;
        $rids[1] = $rids[1]->rid;

        $this->assertContains($record->rid, $rids);
        $this->assertContains($record_other->rid, $rids);

        $date_other->delete();

        //
        // Test the same as above but with dates that do not fall in the range (no results returned). 
        //

        // Test the normal use case, dates are all CE, post 1970 date, date out of search range.

        $date->day = 17;
        $date->month = 11;
        $date->year = 1964;
        $date->save();

        $dummy_query = [$field->flid."_begin_month" => "11",
            $field->flid."_begin_day" => "30",
            $field->flid."_begin_year" => "1994",
            $field->flid."_begin_era" => "CE",
            $field->flid."_end_month" => "6",
            $field->flid."_end_day" => "11",
            $field->flid."_end_year" => "1996",
            $field->flid."_end_era" => "CE"];

        $query = DateField::getAdvancedSearchQuery($field->flid, $dummy_query)->get();

        $this->assertEmpty($query);

        // Test a CE pre 1970 date, date out of search range.
        $date->day = 1;
        $date->month = 1;
        $date->year = 1492;
        $date->save();

        $dummy_query = [$field->flid."_begin_month" => "3",
            $field->flid."_begin_day" => "30",
            $field->flid."_begin_year" => "1204",
            $field->flid."_begin_era" => "CE",
            $field->flid."_end_month" => "1",
            $field->flid."_end_day" => "11",
            $field->flid."_end_year" => "1400",
            $field->flid."_end_era" => "CE"];

        $query = DateField::getAdvancedSearchQuery($field->flid, $dummy_query)->get();

        $this->assertEmpty($query);

        // Test an entirely BCE search range, date out of search range.
        $date->day = 12;
        $date->month = 25;
        $date->year = 201;
        $date->era = "BCE";
        $date->save();

        $dummy_query = [$field->flid."_begin_month" => "2",
            $field->flid."_begin_day" => "7",
            $field->flid."_begin_year" => "110",
            $field->flid."_begin_era" => "BCE",
            $field->flid."_end_month" => "1",
            $field->flid."_end_day" => "6",
            $field->flid."_end_year" => "99",
            $field->flid."_end_era" => "BCE"];

        $query = DateField::getAdvancedSearchQuery($field->flid, $dummy_query)->get();

        $this->assertEmpty($query);

        // Test a range that searches across eras, dates out of range.
        $record_other = self::dummyRecord($project->pid, $form->fid);

        $date_other = new DateField();
        $date_other->rid = $record_other->rid;
        $date_other->flid = $field->flid;
        $date_other->day = 11;
        $date_other->month = 11;
        $date_other->year = 711;
        $date_other->circa = 0;
        $date_other->era = "CE";
        $date_other->save();

        $dummy_query = [$field->flid."_begin_month" => "2",
            $field->flid."_begin_day" => "7",
            $field->flid."_begin_year" => "110",
            $field->flid."_begin_era" => "BCE",
            $field->flid."_end_month" => "1",
            $field->flid."_end_day" => "3",
            $field->flid."_end_year" => "589",
            $field->flid."_end_era" => "CE"];

        $query = DateField::getAdvancedSearchQuery($field->flid, $dummy_query)->get();

        $this->assertEmpty($query);
    }
}