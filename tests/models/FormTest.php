<?php



class FormTest extends TestCase
{
    /**
     * Test the delete method.
     *
     * When a form is deleted the following should be deleted:
     *      -Record Presets associated with the form should be deleted.
     *      -Associations in which the form is the data form or associated form should be deleted.
     *      -Revisions associated with the form should be deleted.
     *      -Form groups associated with the form should be deleted.
     *      -All Records and Fields associated with the form should have their delete methods called.
     */
    public function test_delete() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField(\App\Field::_TEXT, $project->pid, $form->fid);
        $record = self::dummyRecord($project->pid, $form->fid);

        $record_preset = new \App\RecordPreset();
        $record_preset->fid = $form->fid;
        $record_preset->rid = $record->rid;
        $record_preset->name = "name";
        $record_preset->preset = "preset";
        $record_preset->save();

        $assoc_1 = new \App\Association();
        $assoc_1->dataForm = $form->fid;
        $assoc_1->assocForm = 0;
        $assoc_1->save();

        $assoc_2 = new \App\Association();
        $assoc_2->dataForm = 0;
        $assoc_2->assocForm = $form->fid;
        $assoc_2->save();

        $revision = new \App\Revision();
        $revision->fid = $form->fid;
        $revision->rid = $record->rid;
        $revision->userId = 1;
        $revision->owner = 1;
        $revision->type = "type";
        $revision->data = "something";
        $revision->oldData = "nothing";
        $revision->rollback = 1;

        $fid = $form->fid;
        $form->delete();

        $this->assertEmpty(\App\Form::where("fid", "=", $fid)->get());
        $this->assertEmpty(\App\RecordPreset::where("fid", "=", $fid)->get());
        $this->assertEmpty(\App\Association::where("dataForm", "=", $fid)->get());
        $this->assertEmpty(\App\Association::where("assocForm", "=", $fid)->get());
        $this->assertEmpty(\App\Revision::where("fid", "=", $fid)->get());
        $this->assertEmpty(\App\Record::where("fid", "=", $fid)->get());
        $this->assertEmpty(\App\Field::where("fid", "=", $fid)->get());
    }
}