<?php

use Illuminate\Support\Facades\DB;

class TokenTest extends TestCase
{
    /**
     * Test the delete method.
     *
     * When a token is deleted, entries sharing an id with the token should be deleted.
     */
    public function test_delete() {
        $project = self::dummyProject();

        $token = new App\Token();
        $token->save();

        $token->projects()->attach($project->pid);

        // Assert that the entry in the pivot table was made.
        $this->assertEquals($token->id, DB::table("project_token")
            ->select("token_id")
            ->where("token_id", "=", $token->id)
            ->first()->token_id);

        $token->delete();

        $this->assertEmpty(DB::table("project_token")
            ->select("token_id")
            ->where("token_id", "=", $token->id)
            ->get());
    }
}