<?php

class UtilityTest extends TestCase
{
    /**
     * Test dummy project creation.
     */
    public function test_dummyProject() {
        $project = self::dummyProject();
        $this->assertInstanceOf('App\Project', $project);
        $this->assertContains("Project", $project->name);
        $this->assertContains("ptest", $project->slug);
        $this->assertContains("dummy", $project->description);
    }

    /**
     * Test dummy form creation.
     */
    public function test_dummyForm() {
        // Attempt to create a form with no projects in the system.
        $form = self::dummyForm();
        $this->assertNull($form);

        $project = self::dummyProject();

        // Attempt to create a form again.
        $form = self::dummyForm();
        $this->assertInstanceOf('App\Form', $form);

        // Create a form with a foreign key reference to the created project.
        $form = self::dummyForm($project->pid);
        $this->assertInstanceOf('App\Form', $form);
        $this->assertEquals($form->pid, $project->pid);
        $this->assertContains("Test Form", $form->name);
        $this->assertContains("ftest", $form->slug);
        $this->assertContains("dummy", $form->description);
    }

    /**
     * Test dummy field creation.
     */
    public function test_dummyField() {
        // Attempt to create a field with no projects or forms in the system.
        $field = self::dummyField("Text");
        $this->assertNull($field);

        $project = self::dummyProject();

        // Attempt to create a field with no forms in the system.
        $field = self::dummyField("Text");
        $this->assertNull($field);

        $form = self::dummyForm();

        // This should work.
        $field = self::dummyField("Text");
        $this->assertInstanceOf('App\Field', $field);

        // Try with invalid field type
        $field = self::dummyField("INVALID");
        $this->assertNull($field);

        //
        // Create some projects and fields to get the auto-increment values to be distinguishable (not just 1).
        //
        self::dummyProject(); self::dummyProject(); self::dummyProject(); self::dummyProject();
        self::dummyForm(); self::dummyForm(); self::dummyForm(); self::dummyForm(); self::dummyForm();

        // Test the pid and fid setting functionality.
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $field = self::dummyField("Geolocator", $project->pid, $form->fid);

        // The foreign keys should be set up as expected now.
        $this->assertInstanceOf('App\Field', $field);
        $this->assertEquals($project->pid, $field->pid);
        $this->assertEquals($form->fid, $field->fid);
    }

    /**
     * Test dummy record creation.
     */
    public function test_dummyRecord() {
        // Attempt to create a record with no projects or forms.
        $record = self::dummyRecord();
        $this->assertNull($record);

        $project = self::dummyProject();

        // Attempt to create a record with no forms in the system.
        $record = self::dummyRecord();
        $this->assertNull($record);

        $form = self::dummyForm();

        //Should work now.
        $record = self::dummyRecord();
        $this->assertInstanceOf('App\Record', $record);

        //
        // Create some projects and fields to get the auto-increment values to be distinguishable (not just 1).
        //
        self::dummyProject(); self::dummyProject(); self::dummyProject(); self::dummyProject();
        self::dummyForm(); self::dummyForm(); self::dummyForm(); self::dummyForm(); self::dummyForm();

        // Test setting the pid and fid.
        $project = self::dummyProject();
        $form = self::dummyForm($project->pid);
        $record = self::dummyRecord($project->pid, $form->fid);

        // Check foreign keys.
        $this->assertInstanceOf('App\Record', $record);
        $this->assertEquals($project->pid, $record->pid);
        $this->assertEquals($form->fid, $record->fid);

        $kid = $project->pid . "-" . $form->fid . "-" . $record->rid;

        $this->assertEquals($kid, $record->kid);
    }
}