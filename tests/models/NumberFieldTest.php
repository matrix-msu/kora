<?php

use App\Field;
use App\Http\Controllers\RevisionController;
use App\NumberField;
use App\Revision;

/**
 * Class NumberFieldTest
 * @group field
 */
class NumberFieldTest extends TestCase
{
    public function test_getAdvancedSearchQuery() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_NUMBER, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $number_field = new NumberField();
        $number_field->rid = $record->rid;
        $number_field->flid = $field->flid;
        $number_field->number = 0;
        $number_field->save();

        // Test the usual case, where the desired number falls somewhere in the range.
        $dummy_query = [$field->flid."_left" => "-10", $field->flid."_right" => "10"];

        $query = NumberField::getAdvancedSearchQuery($field->flid, $dummy_query);

        $rid = $query->first()->rid;
        $this->assertEquals($rid, $record->rid);

        // Test the case where the number is outside the range.
        $dummy_query = [$field->flid."_left" => "-10", $field->flid."_right" => "-1"];

        $query = NumberField::getAdvancedSearchQuery($field->flid, $dummy_query)->get();

        $this->assertEmpty($query);

        // Test left empty, number in range.
        $dummy_query = [$field->flid."_left" => "", $field->flid."_right" => "10"];

        $query = NumberField::getAdvancedSearchQuery($field->flid, $dummy_query);

        $rid = $query->first()->rid;
        $this->assertEquals($rid, $record->rid);

        // Test left empty, number out of range.
        $dummy_query = [$field->flid."_left" => "", $field->flid."_right" => "-1"];

        $query = NumberField::getAdvancedSearchQuery($field->flid, $dummy_query)->get();

        $this->assertEmpty($query);

        // Test right empty, number in range.
        $dummy_query = [$field->flid."_left" => "-10", $field->flid."_right" => ""];

        $query = NumberField::getAdvancedSearchQuery($field->flid, $dummy_query);

        $rid = $query->first()->rid;
        $this->assertEquals($rid, $record->rid);

        // Test right empty, number out of range.
        $dummy_query = [$field->flid."_left" => "1", $field->flid."_right" => ""];

        $query = NumberField::getAdvancedSearchQuery($field->flid, $dummy_query)->get();

        $this->assertEmpty($query);

        // Test left and right equal.
        $dummy_query = [$field->flid."_left" => "0", $field->flid."_right" => "0"];

        $query = NumberField::getAdvancedSearchQuery($field->flid, $dummy_query);

        $rid = $query->first()->rid;
        $this->assertEquals($rid, $record->rid);

        //
        // Test the same as above, but with the inverted option on.
        //

        // Test the usual case, the number is in the range, invert on.
        $dummy_query = [$field->flid."_left" => "-2", $field->flid."_right" => "-1", $field->flid."_invert" => "on"];

        $query = NumberField::getAdvancedSearchQuery($field->flid, $dummy_query);

        $rid = $query->first()->rid;
        $this->assertEquals($rid, $record->rid);

        // Number is outside the range, invert on.
        $dummy_query = [$field->flid."_left" => "-1", $field->flid."_right" => "1", $field->flid."_invert" => "on"];

        $query = NumberField::getAdvancedSearchQuery($field->flid, $dummy_query)->get();

        $this->assertEmpty($query);

        // Test left empty, number in range, invert on.
        $dummy_query = [$field->flid."_left" => "", $field->flid."_right" => "-1", $field->flid."_invert" => "on"];

        $query = NumberField::getAdvancedSearchQuery($field->flid, $dummy_query);

        $rid = $query->first()->rid;
        $this->assertEquals($rid, $record->rid);

        // Test left empty, number out of range, invert on.
        $dummy_query = [$field->flid."_left" => "", $field->flid."_right" => "1", $field->flid."_invert" => "on"];

        $query = NumberField::getAdvancedSearchQuery($field->flid, $dummy_query)->get();

        $this->assertEmpty($query);

        // Test right empty, number in range, invert on.
        $dummy_query = [$field->flid."_left" => "1", $field->flid."_right" => "", $field->flid."_invert" => "on"];

        $query = NumberField::getAdvancedSearchQuery($field->flid, $dummy_query);

        $rid = $query->first()->rid;
        $this->assertEquals($rid, $record->rid);

        // Test right empty, number out of range, invert on.
        $dummy_query = [$field->flid."_left" => "-1", $field->flid."_right" => "", $field->flid."_invert" => "on"];

        $query = NumberField::getAdvancedSearchQuery($field->flid, $dummy_query)->get();

        $this->assertEmpty($query);

        // Test left and right equal, invert on.
        $dummy_query = [$field->flid."_left" => "0", $field->flid."_right" => "0", $field->flid."_invert" => "on"];

        $query = NumberField::getAdvancedSearchQuery($field->flid, $dummy_query)->get();

        $this->assertEmpty($query);
    }

    public function test_rollback() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_NUMBER, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $field->options = "[!Unit!]m[!Unit]";
        $field->save();

        $old = M_PI;

        $number_field = new NumberField();
        $number_field->rid = $record->rid;
        $number_field->flid = $field->flid;
        $number_field->number = $old;
        $number_field->save();

        $revision = RevisionController::storeRevision($record->rid, Revision::CREATE);

        $new = M_E;

        $number_field->number = $new;
        $number_field->save();

        $number_field = NumberField::rollback($revision, $field);
        $this->assertEquals($old, $number_field->number);
    }
}