<?php namespace App\Commands;

use App\VideoField;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;

class SaveVideoFieldsTable extends Command implements ShouldQueue {

    /*
    |--------------------------------------------------------------------------
    | Save Video Fields Table
    |--------------------------------------------------------------------------
    |
    | This command handles the backup of the video fields table
    |
    */

    use InteractsWithQueue, SerializesModels;

    /**
     * Execute the command.
     */
    public function handle() {
        Log::info("Started backing up the Video Fields table.");

        $table_path = $this->backup_filepath . "/video_fields/";
        $table_array = $this->makeBackupTableArray("video_fields");
        if($table_array == false) { return;}

        $row_id = DB::table('backup_partial_progress')->insertGetId(
            $table_array
        );

        $this->backup_fs->makeDirectory($table_path);
        VideoField::chunk(500, function($videos) use ($table_path, $row_id) {
            $count = 0;
            $all_videofields_data = new Collection();

            foreach($videos as $vid) {
                $individual_videofields_data = new Collection();

                $individual_videofields_data->put("id", $vid->id);
                $individual_videofields_data->put("rid", $vid->rid);
                $individual_videofields_data->put("fid", $vid->fid);
                $individual_videofields_data->put("flid", $vid->flid);
                $individual_videofields_data->put("video", $vid->video);
                $individual_videofields_data->put("created_at", $vid->created_at->toDateTimeString());
                $individual_videofields_data->put("updated_at", $vid->updated_at->toDateTimeString());

                $all_videofields_data->push($individual_videofields_data);
                $count++;
            }

            DB::table('backup_partial_progress')->where('id',$row_id)->increment('progress',$count,['updated_at'=>Carbon::now()]);
            $increment = DB::table('backup_partial_progress')->where('id',$row_id)->pluck('progress');
            $this->backup_fs->put($table_path . $increment . ".json",json_encode($all_videofields_data));
        });
        DB::table("backup_overall_progress")->where("id", $this->backup_id)->increment("progress",1,["updated_at"=>Carbon::now()]);
    }
}