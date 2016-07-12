<?php

use Illuminate\Support\Facades\DB;

class FormGroupTest extends TestCase
{
    /**
     * Test the delete method.
     *
     * When a form group is deleted, all entries in the form_group_user pivot table with the group's id are deleted.
     */
    public function test_delete() {
        $user = new App\User();
        $user->save();

        $form_group = new \App\FormGroup();
        $form_group->save();

        $form_group->users()->attach($user->id);

        // Assert that the entry in the pivot table was made.
        $this->assertEquals($form_group->id, DB::table("form_group_user")
            ->select("form_group_id")
            ->where("form_group_id", "=", $form_group->id)
            ->first()->form_group_id);

        $form_group->delete();
        $this->assertEmpty(DB::table("form_group_user")
            ->select("form_group_id")
            ->where("form_group_id", "=", $form_group->id)->get());
    }
}