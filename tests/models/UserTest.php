<?php

use Illuminate\Support\Facades\DB;

class UserTest extends TestCase
{
    /**
     * Test the delete method.
     *
     * When a user is deleted the following should also be deleted:
     *      -Any entries in the project_group_user pivot table.
     *      -Any entries in the form_group_user pivot table.
     *      -Any backup_support records associated with the user.
     */
    public function test_delete() {
        $user = new App\User();
        $user->save();

        $project_group = new \App\ProjectGroup();
        $project_group->save();

        $form_group = new \App\FormGroup();
        $form_group->save();

        DB::table("backup_support")->insert(["user_id" => $user->id]);

        $user_id = $user->id;

        $project_group->users()->attach($user_id);
        $form_group->users()->attach($user_id);

        // Assert that the entries were made in the pivot tables.

        $this->assertEquals($user_id, DB::table("form_group_user")
            ->select("user_id")
            ->where("user_id", "=", $user_id)
            ->first()->user_id);

        $this->assertEquals($user_id, DB::table("project_group_user")
            ->select("user_id")
            ->where("user_id", "=", $user_id)
            ->first()->user_id);

        $user->delete();

        $this->assertEmpty(DB::table("backup_support")->where("user_id", "=", $user_id)->get());
        $this->assertEmpty(DB::table("form_group_user")
            ->where("user_id", "=", $user_id)->get());
        $this->assertEmpty(DB::table("project_group_user")
            ->where("user_id", "=", $user_id)->get());
    }
}