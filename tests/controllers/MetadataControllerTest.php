<?php

class MetadataControllerTest extends TestCase
{
    /**
     * Test the isUniqueToForm method.
     */
    public function test_isUniqueToForm() {
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);

        $controller = new \App\Http\Controllers\MetadataController();

        // Any name will be unique if there is nothing in the table.
        $this->assertTrue($controller->isUniqueToForm($form->fid, "anything"));
        $this->assertTrue($controller->isUniqueToForm($form->fid, "name"));

        $meta_1 = new \App\Metadata();
        $meta_1->pid = $project->pid;
        $meta_1->fid = $form->fid;
        $meta_1->name = "name";
        $meta_1->save();

        $this->assertFalse($controller->isUniqueToForm($form->fid, "name"));
    }
}