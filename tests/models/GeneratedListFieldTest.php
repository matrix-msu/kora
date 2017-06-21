<?php

use App\Field;
use App\Revision;
use App\GeneratedListField;
use App\Http\Controllers\RevisionController;

/**
 * Class GeneratedListFieldTest
 * @group field
 */
class GeneratedListFieldTest extends TestCase
{
    public function test_getAdvancedSearchQuery() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_MULTI_SELECT_LIST, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $list_field = new GeneratedListField();
        $list_field->rid = $record->rid;
        $list_field->flid = $field->flid;
        $list_field->options = "apple[!]banana[!]pear[!]peach";
        $list_field->save();

        // Valid.
        $dummy_query = [$field->flid."_input" => ["apple"]];

        $query = GeneratedListField::getAdvancedSearchQuery($field->flid, $dummy_query);

        $rid = $query->first()->rid;
        $this->assertEquals($rid, $record->rid);

        // Invalid.
        $dummy_query = [$field->flid."_input" => ["orange"]];

        $query = GeneratedListField::getAdvancedSearchQuery($field->flid, $dummy_query)->get();

        $this->assertEmpty($query);

        // Valid.
        $dummy_query = [$field->flid."_input" => ["apple", "banana", "pear"]];

        $query = GeneratedListField::getAdvancedSearchQuery($field->flid, $dummy_query);

        $rid = $query->first()->rid;
        $this->assertEquals($rid, $record->rid);

        // Valid.
        $dummy_query = [$field->flid."_input" => ["apple", "orange", "tire iron"]];

        $query = GeneratedListField::getAdvancedSearchQuery($field->flid, $dummy_query);

        $rid = $query->first()->rid;
        $this->assertEquals($rid, $record->rid);

        // Invalid.
        $dummy_query = [$field->flid."_input" => ["orange", "tire iron"]];

        $query = GeneratedListField::getAdvancedSearchQuery($field->flid, $dummy_query)->get();

        $this->assertEmpty($query);
    }

    public function test_rollback() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_GENERATED_LIST, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $old = "apple[!]banana[!]pear[!]peach";

        $list_field = new GeneratedListField();
        $list_field->rid = $record->rid;
        $list_field->flid = $field->flid;
        $list_field->options = $old;
        $list_field->save();

        $revision = RevisionController::storeRevision($record->rid, Revision::CREATE);

        $new = "pineapple[!]blueberry[!]blackberry";

        $list_field->options = $new;
        $list_field->save();

        $list_field = GeneratedListField::rollback($revision, $field);
        $this->assertEquals($old, $list_field->options);
    }
}