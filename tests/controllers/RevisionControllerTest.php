<?php

use App\Http\Controllers\RevisionController;

class RevisionControllerTest extends TestCase
{
    /**
     * Test the wipe rollbacks static method.
     */
    public function test_wipeRollbacks() {
        $revision = new App\Revision();
        $revision->fid = 1;
        $revision->rollback = 1;
        $revision->save();

        $untouched = new App\Revision();
        $untouched->fid = 2;
        $untouched->rollback = 1;
        $untouched->save();

        $id = $revision->id;

        RevisionController::wipeRollbacks(1);

        $revision = App\Revision::where("id", "=", $id)->first();
        $this->assertFalse(!!$revision->rollback);

        $untouched = App\Revision::where("id", "=", $untouched->id)->first();
        $this->assertTrue(!!$untouched->rollback);
    }
}