<?php

use Illuminate\Support\Facades\DB;

class ProjectGroupTest extends TestCase
{
    /**
     * Test the delete method.
     *
     * When a project group is deleted, entries associated with it in the project_group_user pivot table should be deleted.
     */
    public function test_delete() {
        $user = new App\User();
        $user->save();

        $project_group = new \App\ProjectGroup();
        $project_group->save();

        $project_group->users()->attach($user->id);

        // Assert that the entry was made in the pivot table.
        $this->assertEquals($project_group->id, DB::table("project_group_user")
            ->select("project_group_id")
            ->where("project_group_id", "=", $project_group->id)
            ->first()->project_group_id);

        $project_group_id = $project_group->id;

        $project_group->delete();

        $this->assertEmpty(DB::table("project_group_user")
            ->select("project_group_id")
            ->where("project_group_id", "=", $project_group_id)
            ->get());
    }
}