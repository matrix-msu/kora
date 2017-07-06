<?php namespace App\Commands;

use App\Project;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SaveProjectsTable extends Command implements SelfHandling, ShouldQueue {

    /*
    |--------------------------------------------------------------------------
    | Save Projects Table
    |--------------------------------------------------------------------------
    |
    | This command handles the backup of the projects table
    |
    */

	use InteractsWithQueue, SerializesModels;

	/**
	 * Execute the command.
	 */
	public function handle() {
		Log::info("Started backing up Projects table");

		$table_path = $this->backup_filepath."/projects/";
        $table_array = $this->makeBackupTableArray("projects");
        if($table_array == false) { return;}

		$row_id = DB::table('backup_partial_progress')->insertGetId(
            $table_array
		);

		$this->backup_fs->makeDirectory($table_path);
		Project::chunk(500,function($projects) use ($table_path, $row_id){
			$count= 0;
			$all_projects_data = new Collection();
			foreach($projects as $project) {
				$individual_project_data = new Collection();

				$individual_project_data->put("pid", $project->pid);
				$individual_project_data->put("name", $project->name);
                $individual_project_data->put("slug", $project->slug);
				$individual_project_data->put("description", $project->description);
				$individual_project_data->put("adminGID", $project->adminGID);
				$individual_project_data->put("active", $project->active);
				$individual_project_data->put("created_at", $project->created_at->toDateTimeString());
				$individual_project_data->put("updated_at", $project->updated_at->toDateTimeString());

				$all_projects_data->push($individual_project_data);
				$count++;
			}
			DB::table('backup_partial_progress')->where('id',$row_id)->increment('progress',$count,['updated_at'=>Carbon::now()]);
			$increment = DB::table('backup_partial_progress')->where('id',$row_id)->pluck('progress');
			$this->backup_fs->put($table_path.$increment.".json",json_encode($all_projects_data));
		});
        DB::table("backup_overall_progress")->where("id", $this->backup_id)->increment("progress",1,["updated_at"=>Carbon::now()]);
	}

}
