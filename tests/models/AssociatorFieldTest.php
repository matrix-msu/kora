<?php

use App\Field;
use App\Revision;
use App\AssociatorField;
use App\Http\Controllers\RevisionController;

class AssociatorFieldTest extends TestCase
{
    public function test_rollback() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(Field::_ASSOCIATOR, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $old = "not sure how this is stored, I assume its a string...";

        $assoc_field = new AssociatorField();
        $assoc_field->rid = $record->rid;
        $assoc_field->flid = $field->flid;
        $assoc_field->records = $old;
        $assoc_field->save();

        $revision = RevisionController::storeRevision($record->rid, Revision::CREATE);

        $new = "fasldjfklsjdaflkasjdf";

        $assoc_field->records = $new;
        $assoc_field->save();

        $assoc_field = AssociatorField::rollback($revision, $field);

        $this->assertEquals($old, $assoc_field->records);
    }
}