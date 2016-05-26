<?php namespace App\Commands;

use App\Commands\Command;

use App\Project;
use App\Field;
use App\Record;
use App\TextField;
use Carbon\Carbon;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SaveTextFieldsTable extends Command implements SelfHandling, ShouldBeQueued {

	use InteractsWithQueue, SerializesModels;

	/**
	 * SaveTextFieldsTable constructor.
	 *
	 * @param $backup_fs
	 * @param $backup_filepath
	 * @param $backup_id
	 */
	public function __construct($backup_fs,$backup_filepath,$backup_id) {
		parent::__construct($backup_fs, $backup_filepath, $backup_id);
	}

	/**
	 * Execute the command.
	 *
	 * @return void
	 */
	public function handle()
	{
		
		//
		Log::info("Started backing up TextFields table");

		$table_path = $this->backup_filepath."/textfields/";
		$row_id = DB::table('backup_partial_progress')->insertGetId([
			'name'=>"Text Fields Table",
			"progress"=>0,
			"overall"=>DB::table('text_fields')->count(),
			"backup_id"=>$this->backup_id,
			"start"=>Carbon::now(),
			"created_at"=>Carbon::now(),
			"updated_at"=>Carbon::now()
		]);

		$this->backup_fs->makeDirectory($table_path);
		TextField::chunk(1000,function($textfields) use ($table_path,$row_id){
			$count= 0;

			$all_textfields_data = new Collection();
				foreach ($textfields as $textfield) {
				//	try {
						$individual_textfield_data = new Collection();
						$individual_textfield_data->put("id", $textfield->id);
						$individual_textfield_data->put("rid", $textfield->rid);
						$individual_textfield_data->put("flid", $textfield->flid);
						$individual_textfield_data->put("text", $textfield->text);
						$individual_textfield_data->put("created_at", $textfield->created_at->toDateTimeString());
						$individual_textfield_data->put("updated_at", $textfield->updated_at->toDateTimeString());
						$all_textfields_data->push($individual_textfield_data);
						$count++;
					//} catch (\Exception $e) {
					//	$this->ajax_error_list->push($e->getMessage());
					//}
				}
			DB::table('backup_partial_progress')->where('id',$row_id)->increment('progress',$count,['updated_at'=>Carbon::now()]);
			$increment = DB::table('backup_partial_progress')->where('id',$row_id)->pluck('progress');
			$this->backup_fs->put($table_path . $increment . ".json",json_encode($all_textfields_data));
		});




	}

}
