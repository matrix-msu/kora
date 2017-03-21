<?php

use App\Field;
use App\NumberField as NumberField;

/**
 * Class NumberFieldTest
 * @group field
 */
class NumberFieldTest extends TestCase
{
    /**
     * Test the keyword search functionality for a number field.
     * @group search
     */
    public function test_keywordSearch() {
        $field = new NumberField();
        $field->number = "1"; // Numbers stored as strings in the database.

        // Simple case, search only for the exact number.
        $args = ['1'];
        $this->assertTrue($field->keywordSearch($args, true));
        $this->assertTrue($field->keywordSearch($args, false));

        // Nothing should go wrong with mixed input.
        $args = ['3', '3.12342', -13001.2304, '1000', 1];
        $this->assertTrue($field->keywordSearch($args, true));
        $this->assertTrue($field->keywordSearch($args, false));

        $field->number = "-234.123";

        $args = [-234.123];
        $this->assertTrue($field->keywordSearch($args, true));
        $this->assertTrue($field->keywordSearch($args, false));

        $args = ['-123', '234.123', '-234'];
        $this->assertFalse($field->keywordSearch($args, true));
        $this->assertFalse($field->keywordSearch($args, false));

        $args = ['not a number!', 'also not a number!'];
        $this->assertFalse($field->keywordSearch($args, true));
        $this->assertFalse($field->keywordSearch($args, false));

        $field->number = '0';

        $this->assertFalse($field->keywordSearch($args, true));
        $this->assertFalse($field->keywordSearch($args, false));

        $args = ['0'];
        $this->assertTrue($field->keywordSearch($args, true));
        $this->assertTrue($field->keywordSearch($args, false));

        $args = [0];
        $this->assertTrue($field->keywordSearch($args, true));
        $this->assertTrue($field->keywordSearch($args, false));

        $args = ['0.0'];
        $this->assertTrue($field->keywordSearch($args, true));
        $this->assertTrue($field->keywordSearch($args, false));
    }

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
}