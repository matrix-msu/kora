<?php namespace App\Commands;

use App\ProjectGroup;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SaveProjectGroupsTable extends Command implements SelfHandling, ShouldQueue {

    /*
    |--------------------------------------------------------------------------
    | Save Project Groups Table
    |--------------------------------------------------------------------------
    |
    | This command handles the backup of the project groups table
    |
    */

    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the command.
     */
    public function handle() {
        Log::info("Started backing up Project Groups Table.");

        $table_path = $this->backup_filepath . "/project_groups/";
        $table_array = $this->makeBackupTableArray("project_groups");
        if($table_array == false) { return;}

        $row_id = DB::table('backup_partial_progress')->insertGetId(
            $table_array
        );

        $this->backup_fs->makeDirectory($table_path);
        ProjectGroup::chunk(500, function($projectgroups) use ($table_path, $row_id) {
            $count = 0;
            $all_projectgroup_data = new Collection();

            foreach($projectgroups as $projectgroup) {
                $individual_projectgroup_data = new Collection();

                $individual_projectgroup_data->put("id", $projectgroup->id);
                $individual_projectgroup_data->put("name", $projectgroup->name);
                $individual_projectgroup_data->put("pid", $projectgroup->pid);
                $individual_projectgroup_data->put("create", $projectgroup->create);
                $individual_projectgroup_data->put("edit", $projectgroup->edit);
                $individual_projectgroup_data->put("delete", $projectgroup->delete);
                $individual_projectgroup_data->put("created_at", $projectgroup->created_at->toDateTimeString());
                $individual_projectgroup_data->put("updated_at", $projectgroup->updated_at->toDateTimeString());

                $all_projectgroup_data->push($individual_projectgroup_data);
                $count++;
            }

            DB::table("backup_partial_progress")->where("id", $row_id)->increment("progress", $count, ["updated_at" => Carbon::now()] );
            $increment = DB::table("backup_partial_progress")->where("id", $row_id)->pluck("progress");
            $this->backup_fs->put($table_path . $increment . ".json", json_encode($all_projectgroup_data));
        });
        DB::table("backup_overall_progress")->where("id", $this->backup_id)->increment("progress",1,["updated_at"=>Carbon::now()]);
    }
}