<?php

use App\Field;
use App\MultiSelectListField as MultiSelectListField;

/**
 * Class MultiSelectListFieldTest
 * @group field
 */
class MultiSelectListFieldTest extends TestCase
{
    public function test_getAdvancedSearchQuery() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_MULTI_SELECT_LIST, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $list_field = new MultiSelectListField();
        $list_field->rid = $record->rid;
        $list_field->flid = $field->flid;
        $list_field->options = "apple[!]banana[!]pear[!]peach";
        $list_field->save();

        // Valid.
        $dummy_query = [$field->flid."_input" => ["apple"]];

        $query = MultiSelectListField::getAdvancedSearchQuery($field->flid, $dummy_query);

        $rid = $query->first()->rid;
        $this->assertEquals($rid, $record->rid);

        // Invalid.
        $dummy_query = [$field->flid."_input" => ["orange"]];

        $query = MultiSelectListField::getAdvancedSearchQuery($field->flid, $dummy_query)->get();

        $this->assertEmpty($query);

        // Valid.
        $dummy_query = [$field->flid."_input" => ["apple", "banana", "pear"]];

        $query = MultiSelectListField::getAdvancedSearchQuery($field->flid, $dummy_query);

        $rid = $query->first()->rid;
        $this->assertEquals($rid, $record->rid);

        // Valid.
        $dummy_query = [$field->flid."_input" => ["apple", "orange", "tire iron"]];

        $query = MultiSelectListField::getAdvancedSearchQuery($field->flid, $dummy_query);

        $rid = $query->first()->rid;
        $this->assertEquals($rid, $record->rid);

        // Invalid.
        $dummy_query = [$field->flid."_input" => ["orange", "tire iron"]];

        $query = MultiSelectListField::getAdvancedSearchQuery($field->flid, $dummy_query)->get();

        $this->assertEmpty($query);
    }
}