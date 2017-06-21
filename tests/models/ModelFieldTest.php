<?php

use App\ModelField;
use App\Field;
use App\Revision;
use App\Http\Controllers\RevisionController;

/**
 * Class ModelFieldTest
 * @group field
 */
class ModelFieldTest extends TestCase
{
    const FILEINFO = <<<TEXT
[Name]blueimage.jpg[Name][Size]3478[Size][Type]text/csv[Type][Name]whalenia%40msu.edu.csv[Name][Size]3478[Size][Type]text/csv[Type][!][Name]Proj_Layout_2016-02-18 18-08-58.xml[Name][Size]87[Size][Type]application/xml[Type][!][Name]postmessageRelay.html[Name][Size]4087[Size][Type]text/html[Type]
TEXT;

    const OTHER_FILEINFO = <<<TEXT
[Name]redimage.jpg[Name][Size]3478[Size][Type]text/csv[Type][Name]whalenia%40msu.edu.csv[Name][Size]3478[Size][Type]text/csv[Type][!][Name]some_fine_xml.xml[Name][Size]87[Size][Type]application/xml[Type][!][Name]premessageRelay.html[Name][Size]4087[Size][Type]text/html[Type]
TEXT;


    public function test_getAdvancedSearchQuery() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_3D_MODEL, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $model_field = new ModelField();
        $model_field->rid = $record->rid;
        $model_field->flid = $field->flid;
        $model_field->model = self::FILEINFO;
        $model_field->save();

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

    public function test_rollback() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_3D_MODEL, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $model_field = new ModelField();
        $model_field->rid = $record->rid;
        $model_field->flid = $field->flid;
        $model_field->model = self::FILEINFO;
        $model_field->save();

        $revision = RevisionController::storeRevision($record->rid, Revision::CREATE);

        $model_field->model = self::OTHER_FILEINFO;
        $model_field->save();

        $model_field = ModelField::rollback($revision, $field);

        $this->assertEquals(self::FILEINFO, $model_field->model);
    }
}