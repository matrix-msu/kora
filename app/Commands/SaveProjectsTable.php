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

	public $backup_fs;
	public $backup_filepath;
	public $backup_id;

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct($backup_fs,$backup_filepath,$backup_id)
	{
		//
		$this->backup_fs = $backup_fs;
		$this->backup_filepath = $backup_filepath;
		$this->backup_id = $backup_id;
	}

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
		$table_id = DB::table('backup_partial_progress')->insertGetId(['name'=>"Projects Table","progress"=>0,"overall"=>DB::table('projects')->count(),"backup_id"=>$this->backup_id,"start"=>Carbon::now(),"created_at"=>Carbon::now(),"updated_at"=>Carbon::now()]);

		$this->backup_fs->makeDirectory($table_path);
		Project::chunk(1000,function($projects) use ($table_path){
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
				//} catch (\Exception $e) {
				//	$this->ajax_error_list->push($e->getMessage());
				//}
			}
			$this->backup_fs->put($table_path."1.json",json_encode($all_projects_data));
		});


	}

}
