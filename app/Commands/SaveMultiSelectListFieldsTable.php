<?php namespace App\Commands;

use Carbon\Carbon;
use App\MultiSelectListField;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class SaveMultiSelectListFieldsTable extends Command implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the command.
     */
    public function handle() {
        Log::info("Started backing up the Multi Select List Fields table.");

        $table_path = $this->backup_filepath . "/multi_select_list_fields/";
        $table_array = $this->makeBackupTableArray("multi_select_list_fields");
        if($table_array == false) { return;}

        $row_id = DB::table('backup_partial_progress')->insertGetId(
            $table_array
        );

        $this->backup_fs->makeDirectory($table_path);
        MultiSelectListField::chunk(500, function($mslfields) use ($table_path, $row_id) {
            $count = 0;
            $all_multiselectlistfields_data = new Collection();

            foreach($mslfields as $multiselectlistfield) {
                $individual_multiselectlistfield_data = new Collection();

                $individual_multiselectlistfield_data->put("id", $multiselectlistfield->id);
                $individual_multiselectlistfield_data->put("rid", $multiselectlistfield->rid);
                $individual_multiselectlistfield_data->put("fid", $multiselectlistfield->fid);
                $individual_multiselectlistfield_data->put("flid", $multiselectlistfield->flid);
                $individual_multiselectlistfield_data->put("options", $multiselectlistfield->options);
                $individual_multiselectlistfield_data->put("created_at", $multiselectlistfield->created_at->toDateTimeString());
                $individual_multiselectlistfield_data->put("updated_at", $multiselectlistfield->updated_at->toDateTimeString());

                $all_multiselectlistfields_data->push($individual_multiselectlistfield_data);
                $count++;
            }

            DB::table("backup_partial_progress")->where("id", $row_id)->increment("progress", $count, ["updated_at" => Carbon::now()] );
            $increment = DB::table("backup_partial_progress")->where("id", $row_id)->pluck("progress");
            $this->backup_fs->put($table_path . $increment . ".json", json_encode($all_multiselectlistfields_data));
        });
        DB::table("backup_overall_progress")->where("id", $this->backup_id)->increment("progress",1,["updated_at"=>Carbon::now()]);
    }
}