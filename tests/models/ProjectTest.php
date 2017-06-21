<?php

use App\Form;
use App\Project;
use Illuminate\Support\Facades\DB;

class ProjectTest extends TestCase
{
    /**
     * Test the delete method.
     *
     * When a project is deleted the following should be deleted:
     *      -The entry in the Project/Token pivot table (should NOT delete the token however).
     *      -Option presets associated with the project.
     *      -Project groups associated with the project.
     *      -Delete methods called on each form associated with the project.
     */
    public function test_delete() {
        $project = self::dummyProject();
        $form = self::dummyField($project->pid);

        $token = new App\Token();
        $token->token = \App\Http\Controllers\TokenController::tokenGen();
        $token->save();
        $token->projects()->attach($project->pid);

        $token_id = $token->id;

        $option_preset = new \App\OptionPreset();
        $option_preset->pid = $project->pid;
        $option_preset->type = "Type";
        $option_preset->name = "Name";
        $option_preset->preset = "preset";
        $option_preset->save();

        $project_group = new App\ProjectGroup();
        $project_group->name = "group";
        $project_group->create = 1;
        $project_group->edit = 1;
        $project_group->delete = 1;
        $project_group->pid = $project->pid;
        $project_group->save();

        $user = new App\User();
        $user->save();

        $project_group->users()->attach($user->id);

        $project_group_id = $project_group->id;
        $pid = $project->pid;
        $project->delete();

        $this->assertEmpty(DB::table("project_token")->where("project_id", "=", $pid)->get());
        $this->assertEmpty(DB::table("project_group_user")->where("project_group_id", "=", $project_group_id)->get());
        $this->assertNotEmpty(\App\Token::where("id", "=", $token_id)->get());
        $this->assertEmpty(\App\OptionPreset::where("pid", "=", $pid)->get());
        $this->assertEmpty(\App\ProjectGroup::where("pid", "=", $pid)->get());
        $this->assertEmpty(Form::where("pid", "=", $pid)->get());
        $this->assertEmpty(Project::where("pid", "=", $pid)->get());
    }
}