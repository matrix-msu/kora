<?php

use App\Field;
use App\ListField as ListField;
use App\Search as Search;

/**
 * Class ListFieldTest
 * @group field
 */
class ListFieldTest extends TestCase
{
    public function test_getAdvancedSearchQuery() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_LIST, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $list_field = new ListField();
        $list_field->rid = $record->rid;
        $list_field->flid = $field->flid;
        $list_field->option = "selected";
        $list_field->save();

        $dummy_query = [$field->flid."_input" => "selected"];

        $query = ListField::getAdvancedSearchQuery($field->flid, $dummy_query);

        $rid = $query->first()->rid;
        $this->assertEquals($rid, $record->rid);

        $dummy_query = [$field->flid."_input" => "jangus"];

        $query = ListField::getAdvancedSearchQuery($field->flid, $dummy_query)->get();

        $this->assertEmpty($query);
    }
}