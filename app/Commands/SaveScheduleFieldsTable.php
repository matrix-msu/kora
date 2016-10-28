<?php namespace App\Commands;

use Carbon\Carbon;
use App\ScheduleField;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class SaveScheduleFieldsTable extends Command implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the command.
     */
    public function handle() {
        Log::info("Started backing up the Schedule Fields table.");

        $table_path = $this->backup_filepath . "/schedule_fields/";
        $table_array = $this->makeBackupTableArray("schedule_fields");
        if($table_array == false) { return;}

        $row_id = DB::table('backup_partial_progress')->insertGetId(
            $table_array
        );

        $this->backup_fs->makeDirectory($table_path);
        ScheduleField::chunk(500, function($schedulefields) use ($table_path, $row_id) {
            $count = 0;
            $all_schedulefields_data = new Collection();

            foreach ($schedulefields as $schedulefield) {
                $individual_schedulefield_data = new Collection();

                $individual_schedulefield_data->put("id", $schedulefield->id);
                $individual_schedulefield_data->put("rid", $schedulefield->rid);
                $individual_schedulefield_data->put("flid", $schedulefield->flid);
                $individual_schedulefield_data->put("events", $schedulefield->events);
                $individual_schedulefield_data->put("created_at", $schedulefield->created_at->toDateTimeString());
                $individual_schedulefield_data->put("updated_at", $schedulefield->updated_at->toDateTimeString());

                $all_schedulefields_data->push($individual_schedulefield_data);
                $count++;
            }

            DB::table("backup_partial_progress")->where("id", $row_id)->increment("progress", $count, ["updated_at" => Carbon::now()] );
            $increment = DB::table("backup_partial_progress")->where("id", $row_id)->pluck("progress");
            $this->backup_fs->put($table_path . $increment . ".json", json_encode($all_schedulefields_data));
        });
        DB::table("backup_overall_progress")->where("id", $this->backup_id)->increment("progress",1,["updated_at"=>Carbon::now()]);
    }
}