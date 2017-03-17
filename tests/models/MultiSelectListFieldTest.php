<?php

use App\Field;
use App\MultiSelectListField as MultiSelectListField;

/**
 * Class MultiSelectListFieldTest
 * @group field
 */
class MultiSelectListFieldTest extends TestCase
{
    /**
     * Test the keyword search for a multi-select list field.
     * @group search
     */
    public function test_keywordSearch() {
        $field = new MultiSelectListField();
        $field->options = "single";

        // Test a single value
        $args = ["double"];
        $this->assertFalse($field->keywordSearch($args, true));
        $this->assertFalse($field->keywordSearch($args, false));

        $args = ["dou"];
        $this->assertFalse($field->keywordSearch($args, true));
        $this->assertFalse($field->keywordSearch($args, false));

        $args = ["dou", "123fdklj", "not here", "nope"];
        $this->assertFalse($field->keywordSearch($args, true));
        $this->assertFalse($field->keywordSearch($args, false));

        $args = ["single"];
        $this->assertTrue($field->keywordSearch($args, true));
        $this->assertTrue($field->keywordSearch($args, false));

        $args = ["singx", "zing"];
        $this->assertFalse($field->keywordSearch($args, true));
        $this->assertFalse($field->keywordSearch($args, false));

        $args = ["sing", "blah blah", "bllsdfk"];
        $this->assertTrue($field->keywordSearch($args, true));
        $this->assertFalse($field->keywordSearch($args, false));

        $args = ["", null, 0];
        $this->assertFalse($field->keywordSearch($args, true));
        $this->assertFalse($field->keywordSearch($args, false));

        // Mimic how options are stored in the database.
        $field->options = "apple[!]banana[!]pear[!]peach";

        $args = ['not a match', 'no match', 'nothing'];
        $this->assertFalse($field->keywordSearch($args, true));
        $this->assertFalse($field->keywordSearch($args, false));

        $args = ['apple'];
        $this->assertTrue($field->keywordSearch($args, true));
        $this->assertTrue($field->keywordSearch($args, false));

        $args = ['ban'];
        $this->assertTrue($field->keywordSearch($args, true));
        $this->assertFalse($field->keywordSearch($args, false));

        $args = ['apple', 'banana', 'peach'];
        $this->assertTrue($field->keywordSearch($args, true));
        $this->assertTrue($field->keywordSearch($args, false));

        $args = ['apx', 'bam', 'pea'];
        $this->assertTrue($field->keywordSearch($args, true));
        $this->assertFalse($field->keywordSearch($args, false));

        $args = ['[!]'];
        $this->assertFalse($field->keywordSearch($args, true));
        $this->assertFalse($field->keywordSearch($args, false));
    }

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