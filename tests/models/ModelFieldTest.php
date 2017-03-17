<?php

use App\ModelField;
use App\Field;

/**
 * Class ModelFieldTest
 * @group field
 */
class ModelFieldTest extends TestCase
{
    const FILEINFO = <<<TEXT
[Name]blueimage.jpg[Name][Size]3478[Size][Type]text/csv[Type][Name]whalenia%40msu.edu.csv[Name][Size]3478[Size][Type]text/csv[Type][!][Name]Proj_Layout_2016-02-18 18-08-58.xml[Name][Size]87[Size][Type]application/xml[Type][!][Name]postmessageRelay.html[Name][Size]4087[Size][Type]text/html[Type]
TEXT;


    public function test_getAdvancedSearchQuery() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_3D_MODEL, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $play_field = new ModelField();
        $play_field->rid = $record->rid;
        $play_field->flid = $field->flid;
        $play_field->model = self::FILEINFO;
        $play_field->save();

        // Valid.
        $dummy_query = [$field->flid . "_input" => "postmessageRelay.html"];

        $query = ModelField::getAdvancedSearchQuery($field->flid, $dummy_query);

        $rid = $query->first()->rid;
        $this->assertEquals($rid, $record->rid);

        // Invalid.
        $dummy_query = [$field->flid . "_input" => "redimage.jpg"];

        $query = ModelField::getAdvancedSearchQuery($field->flid, $dummy_query);

        $this->assertEmpty($query->get());

        // Invalid.
        $dummy_query = [$field->flid . "_input" => "premessageRelay"];

        $query = ModelField::getAdvancedSearchQuery($field->flid, $dummy_query);

        $this->assertEmpty($query->get());

        // Valid.
        $dummy_query = [$field->flid . "_input" => ".html"];

        $query = ModelField::getAdvancedSearchQuery($field->flid, $dummy_query);

        $rid = $query->first()->rid;
        $this->assertEquals($record->rid, $rid);
    }
}