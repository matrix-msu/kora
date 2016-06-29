<?php

class RecordTest extends TestCase
{
    public function test_cascade() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);

        $fid = $form->fid;

        $record = self::dummyRecord($project->pid, $fid);
        $rid = $record->rid;

        $form->delete();

        $this->assertNull(App\Form::where("fid", "=", $fid)->first());

        $this->assertNull(App\Record::where("rid", "=", $record->rid)->first());
    }
}