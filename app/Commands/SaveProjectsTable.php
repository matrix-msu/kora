<?php namespace App\Commands;

use App\Commands\Command;

use App\Project;
use App\Field;
use Carbon\Carbon;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SaveProjectsTable extends Command implements SelfHandling, ShouldBeQueued {

	use InteractsWithQueue, SerializesModels;
	

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle()
	{
		
		//
		Log::info("Started backing up Projects table");

		$table_path = $this->backup_filepath."/projects/";
		$row_id = DB::table('backup_partial_progress')->insertGetId(
			$this->makeBackupTableArray("projects")
		);

		$this->backup_fs->makeDirectory($table_path);
		Project::chunk(1000,function($projects) use ($table_path, $row_id){
			$count= 0;
			$all_projects_data = new Collection();
			foreach ($projects as $project) {
				//try {
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
				//} catch (\Exception $e) {
				//	$this->ajax_error_list->push($e->getMessage());
				//}
			}
			DB::table('backup_partial_progress')->where('id',$row_id)->increment('progress',$count,['updated_at'=>Carbon::now()]);
			$increment = DB::table('backup_partial_progress')->where('id',$row_id)->pluck('progress');
			$this->backup_fs->put($table_path.$increment.".json",json_encode($all_projects_data));
		});
        DB::table("backup_overall_progress")->where("id", $this->backup_id)->increment("progress",1,["updated_at"=>Carbon::now()]);
	}

}
