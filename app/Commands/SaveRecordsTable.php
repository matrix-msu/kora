<?php namespace App\Commands;

use App\Commands\Command;

use App\Project;
use App\Field;
use App\Record;
use Carbon\Carbon;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SaveRecordsTable extends Command implements SelfHandling, ShouldBeQueued {

	use InteractsWithQueue, SerializesModels;
	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle()
	{
		
		//
		Log::info("Started backing up Records table");

		$table_path = $this->backup_filepath."/records/";
		$row_id = DB::table('backup_partial_progress')->insertGetId(
			$this->makeBackupTableArray("records")
		);

		$this->backup_fs->makeDirectory($table_path);
		Record::chunk(1000,function($records) use ($table_path,$row_id){

			$count = 0;
			$all_records_data = new Collection();
			foreach ($records as $record) {
				//try {
					$individual_record_data = new Collection();
					$individual_record_data->put("rid", $record->rid);
					$individual_record_data->put("kid", $record->kid);
					$individual_record_data->put("pid", $record->pid);
					$individual_record_data->put("fid", $record->fid);
					$individual_record_data->put("owner", $record->owner);
					$individual_record_data->put("created_at", $record->created_at->toDateTimeString());
					$individual_record_data->put("updated_at", $record->updated_at->toDateTimeString());
					$all_records_data->push($individual_record_data);
					$count++;
				//} catch (\Exception $e) {
				//	$this->ajax_error_list->push($e->getMessage());
				//}
			}
			DB::table('backup_partial_progress')->where('id',$row_id)->increment('progress',$count,['updated_at'=>Carbon::now()]);
			$increment = DB::table('backup_partial_progress')->where('id',$row_id)->pluck('progress');
			$this->backup_fs->put($table_path.$increment.".json",json_encode($all_records_data));
		});
        DB::table("backup_overall_progress")->where("id", $this->backup_id)->increment("progress",1,["updated_at"=>Carbon::now()]);
	}

}
