<?php namespace App\Commands;

use App\ScheduleField;
use Carbon\Carbon;
use App\RichTextField;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldBeQueued;

class SaveScheduleSupportTable extends Command implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the command.
     */
    public function handle() {
        Log::info("Started backing up the Schedule Support table.");

        $table_path = $this->backup_filepath . "/schedule_support/";
        $table_array = $this->makeBackupTableArray("schedule_support");
        if($table_array == false) { return;}

        $row_id = DB::table('backup_partial_progress')->insertGetId(
            $table_array
        );

        $this->backup_fs->makeDirectory($table_path);
        DB::table(ScheduleField::SUPPORT_NAME)->chunk(500, function($schedsups) use ($table_path, $row_id) {
            $count = 0;
            $all_schedulesupport_data = new Collection();

            foreach($schedsups as $schedulesupport) {
                $individual_schedulesupport_data = new Collection();

                $individual_schedulesupport_data->put("id", $schedulesupport->id);
                $individual_schedulesupport_data->put("fid", $schedulesupport->fid);
                $individual_schedulesupport_data->put("rid", $schedulesupport->rid);
                $individual_schedulesupport_data->put("flid", $schedulesupport->flid);
                $individual_schedulesupport_data->put("begin", $schedulesupport->begin);
                $individual_schedulesupport_data->put("end", $schedulesupport->end);
                $individual_schedulesupport_data->put("desc", $schedulesupport->desc);
                $individual_schedulesupport_data->put("allday", $schedulesupport->allday);
                $individual_schedulesupport_data->put("created_at", $schedulesupport->created_at); // Already a string, don't format.
                $individual_schedulesupport_data->put("updated_at", $schedulesupport->updated_at);

                $all_schedulesupport_data->push($individual_schedulesupport_data);
                $count++;
            }

            DB::table('backup_partial_progress')->where('id',$row_id)->increment('progress',$count,['updated_at'=>Carbon::now()]);
            $increment = DB::table('backup_partial_progress')->where('id',$row_id)->pluck('progress');
            $this->backup_fs->put($table_path . $increment . ".json",json_encode($all_schedulesupport_data));
        });
        DB::table("backup_overall_progress")->where("id", $this->backup_id)->increment("progress",1,["updated_at"=>Carbon::now()]);
    }
}