<?php
use App\Record;

/**
 * Class RecordTest
 */
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

    /**
     * Test the is KID pattern method.
     */
    public function test_isKIDPattern() {
        $string = "1312-123-0";
        $this->assertTrue(Record::isKIDPattern($string));

        $string = "1-2-3";
        $this->assertTrue(Record::isKIDPattern($string));

        $string = "not even close";
        $this->assertFalse(Record::isKIDPattern($string));

        $string = "12-close!-400";
        $this->assertFalse(Record::isKIDPattern($string));
    }
}